<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Delegation;
use App\Models\SavedAddress;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class ClientPackageController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Liste des colis du client
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $query = Package::where('sender_id', $user->id)
            ->with(['delegationFrom', 'delegationTo', 'assignedDeliverer']);

        // Filtres
        if ($request->filled('status')) {
            if ($request->status === 'in_progress') {
                $query->whereIn('status', ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP']);
            } elseif ($request->status === 'delivered') {
                $query->whereIn('status', ['DELIVERED', 'PAID']);
            } elseif ($request->status === 'returned') {
                $query->where('status', 'RETURNED');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $query->where('package_code', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('client.packages.index', compact('packages'));
    }

    /**
     * Détails d'un colis
     */
    public function show(Package $package)
    {
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        $package->load([
            'delegationFrom', 
            'delegationTo', 
            'assignedDeliverer',
            'statusHistory.changedBy',
            'complaints.assignedCommercial'
        ]);

        if (method_exists($package, 'codModifications')) {
            $package->load('codModifications.modifiedByCommercial');
        }

        return view('client.packages.show', compact('package'));
    }

    /**
     * Formulaire de création de colis - VERSION OPTIMISÉE
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->load(['wallet', 'clientProfile']);

        if (!$user->isActive() || !$user->clientProfile) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre compte doit être validé avant de créer des colis.');
        }

        if (!$user->wallet) {
            $user->ensureWallet();
            $user->load('wallet');
        }

        $delegations = Delegation::where('active', true)->orderBy('name')->get();
        $hasAdvancedFeatures = Schema::hasTable('saved_addresses');
        
        // Récupérer les statistiques du jour
        $todayStats = $this->getTodayStats($user);
        
        if ($hasAdvancedFeatures) {
            // Charger les adresses sauvegardées avec cache
            $supplierAddresses = Cache::remember("supplier_addresses_{$user->id}", 300, function() use ($user) {
                return $user->savedAddresses()
                           ->suppliers()
                           ->with('delegation')
                           ->orderBy('last_used_at', 'desc')
                           ->orderBy('use_count', 'desc')
                           ->limit(15)
                           ->get();
            });
                                     
            $clientAddresses = Cache::remember("client_addresses_{$user->id}", 300, function() use ($user) {
                return $user->savedAddresses()
                           ->clients()
                           ->with('delegation')
                           ->orderBy('last_used_at', 'desc')
                           ->orderBy('use_count', 'desc')
                           ->limit(15)
                           ->get();
            });

            // Récupérer les dernières données de session
            $lastSupplierData = session('last_supplier_data', []);
            $lastClientData = session('last_client_data', []);

            // Utiliser la version rapide ou avancée selon les préférences
            $viewName = $request->get('fast') === 'true' ? 'client.packages.create-fast' : 'client.packages.create-advanced';
            
            return view($viewName, array_merge(compact(
                'user', 
                'delegations', 
                'supplierAddresses',
                'clientAddresses',
                'lastSupplierData',
                'lastClientData'
            ), $todayStats));
        } else {
            return view('client.packages.create', array_merge(compact('user', 'delegations'), $todayStats));
        }
    }

    /**
     * Enregistrement d'un nouveau colis - VERSION OPTIMISÉE
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $isAdvancedFormat = $request->has('supplier_name');
        $continueCreating = $request->has('continue_creating');

        if ($isAdvancedFormat) {
            return $this->storeAdvancedPackage($request, $continueCreating);
        } else {
            return $this->storeBasicPackage($request);
        }
    }

    /**
     * Création de colis format basique
     */
    private function storeBasicPackage(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'delegation_from' => 'required|exists:delegations,id',
            'delegation_to' => 'required|exists:delegations,id|different:delegation_from',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'recipient_address' => 'required|string|max:500',
            'content_description' => 'required|string|max:255',
            'cod_amount' => 'required|numeric|min:0|max:9999.999',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $package = $this->createPackageFromData($user, $validated, 'basic');
            
            DB::commit();

            return redirect()->route('client.packages.show', $package)
                ->with('success', "Colis #{$package->package_code} créé avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', "Erreur lors de la création: " . $e->getMessage());
        }
    }

    /**
     * Création de colis format avancé - VERSION OPTIMISÉE
     */
    private function storeAdvancedPackage(Request $request, $continueCreating = false)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'required|string|max:20',
            'pickup_delegation_id' => 'required|exists:delegations,id',
            'pickup_address' => 'required|string|max:500',
            'save_supplier_address' => 'nullable|boolean',
            'supplier_address_label' => 'nullable|string|max:100',
            
            'delegation_to' => 'required|exists:delegations,id|different:pickup_delegation_id',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'recipient_address' => 'required|string|max:500',
            'save_client_address' => 'nullable|boolean',
            'client_address_label' => 'nullable|string|max:100',
            
            'content_description' => 'required|string|max:255',
            'cod_amount' => 'required|numeric|min:0|max:9999.999',
            'package_weight' => 'nullable|numeric|min:0|max:999.999',
            'package_value' => 'nullable|numeric|min:0|max:99999.999',
            'package_length' => 'nullable|numeric|min:0|max:999',
            'package_width' => 'nullable|numeric|min:0|max:999',
            'package_height' => 'nullable|numeric|min:0|max:999',
            'is_fragile' => 'nullable|boolean',
            'requires_signature' => 'nullable|boolean',
            'special_instructions' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            
            'continue_creating' => 'nullable|boolean'
        ]);

        try {
            DB::beginTransaction();

            $package = $this->createPackageFromData($user, $validated, 'advanced');

            // Traitement des adresses sauvegardées
            $this->handleSavedAddresses($user, $validated);

            // Sauvegarder les données en session pour le prochain colis
            $this->updateSessionData($validated);

            // Invalider le cache des adresses
            $this->clearAddressesCache($user);

            DB::commit();

            $successMessage = "✅ Colis #{$package->package_code} créé avec succès!";

            // COMPORTEMENT OPTIMISÉ : Rester sur la page de création
            if ($continueCreating || $validated['continue_creating'] ?? false) {
                return redirect()->route('client.packages.create')
                    ->with('success', $successMessage . ' Prêt pour le prochain colis.');
            } else {
                return redirect()->route('client.packages.show', $package)
                    ->with('success', $successMessage);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', "Erreur lors de la création: " . $e->getMessage());
        }
    }

    /**
     * Gestion des adresses sauvegardées
     */
    public function savedAddresses()
    {
        $user = Auth::user();
        
        $supplierAddresses = $user->savedAddresses()
                                 ->suppliers()
                                 ->with('delegation')
                                 ->orderBy('last_used_at', 'desc')
                                 ->paginate(10, ['*'], 'suppliers');
                                 
        $clientAddresses = $user->savedAddresses()
                               ->clients()
                               ->with('delegation')
                               ->orderBy('last_used_at', 'desc')
                               ->paginate(10, ['*'], 'clients');

        return view('client.saved-addresses.index', compact('supplierAddresses', 'clientAddresses'));
    }

    /**
     * Dupliquer un colis existant
     */
    public function duplicate(Package $package)
    {
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        // Pré-remplir la session avec les données du colis
        session([
            'last_supplier_data' => [
                'name' => $package->supplier_data['name'] ?? '',
                'phone' => $package->supplier_data['phone'] ?? '',
                'pickup_delegation_id' => $package->pickup_delegation_id ?? $package->delegation_from,
                'pickup_address' => $package->pickup_address ?? '',
                'pickup_notes' => $package->pickup_notes ?? $package->notes
            ],
            'last_client_data' => [
                'delegation_to' => $package->delegation_to,
                'recipient_name' => $package->recipient_data['name'],
                'recipient_phone' => $package->recipient_data['phone'],
                'recipient_address' => $package->recipient_data['address']
            ],
            'last_package_data' => [
                'content_description' => $package->content_description,
                'package_weight' => $package->package_weight ?? 0,
                'package_value' => $package->package_value ?? 0,
                'is_fragile' => $package->is_fragile ?? false,
                'requires_signature' => $package->requires_signature ?? false,
                'special_instructions' => $package->special_instructions ?? ''
            ]
        ]);

        return redirect()->route('client.packages.create')
            ->with('info', "Données du colis #{$package->package_code} pré-remplies pour duplication.");
    }

    // ==================== API ENDPOINTS ====================

    /**
     * API - Statut d'un colis
     */
    public function apiStatus($packageId)
    {
        $package = auth()->user()->sentPackages()->findOrFail($packageId);
        return response()->json(['status' => $package->status]);
    }

    /**
     * API - Dernières données fournisseur
     */
    public function apiLastSupplierData()
    {
        $lastSupplierData = session('last_supplier_data', []);
        return response()->json($lastSupplierData);
    }

    /**
     * API - Statistiques du jour
     */
    public function apiTodayStats()
    {
        $user = auth()->user();
        $stats = $this->getTodayStats($user);
        return response()->json($stats);
    }

    /**
     * API - Adresses sauvegardées
     */
    public function apiSavedAddresses($type = null)
    {
        $user = auth()->user();
        
        if (!Schema::hasTable('saved_addresses')) {
            return response()->json([]);
        }

        $cacheKey = "api_saved_addresses_{$user->id}_" . ($type ?: 'all');
        
        $addresses = Cache::remember($cacheKey, 300, function() use ($user, $type) {
            $query = $user->savedAddresses()->with('delegation');
            
            if ($type) {
                $query->where('type', strtoupper($type));
            }
            
            return $query->orderBy('last_used_at', 'desc')
                          ->orderBy('use_count', 'desc')
                          ->limit(20)
                          ->get();
        });

        return response()->json($addresses);
    }

    /**
     * API - Auto-complétion fournisseurs
     */
    public function apiSupplierAutocomplete(Request $request)
    {
        $search = $request->get('q', '');
        $user = auth()->user();
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $suppliers = $user->savedAddresses()
                         ->suppliers()
                         ->where(function($query) use ($search) {
                             $query->where('name', 'LIKE', "%{$search}%")
                                   ->orWhere('label', 'LIKE', "%{$search}%");
                         })
                         ->with('delegation')
                         ->orderBy('use_count', 'desc')
                         ->limit(10)
                         ->get();

        return response()->json($suppliers->map(function($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'delegation_id' => $supplier->delegation_id,
                'delegation_name' => $supplier->delegation->name,
                'label' => $supplier->label,
                'display' => $supplier->display_name . ' - ' . $supplier->delegation->name
            ];
        }));
    }

    /**
     * API - Auto-complétion clients
     */
    public function apiClientAutocomplete(Request $request)
    {
        $search = $request->get('q', '');
        $user = auth()->user();
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $clients = $user->savedAddresses()
                       ->clients()
                       ->where(function($query) use ($search) {
                           $query->where('name', 'LIKE', "%{$search}%")
                                 ->orWhere('phone', 'LIKE', "%{$search}%")
                                 ->orWhere('label', 'LIKE', "%{$search}%");
                       })
                       ->with('delegation')
                       ->orderBy('use_count', 'desc')
                       ->limit(10)
                       ->get();

        return response()->json($clients->map(function($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'phone' => $client->phone,
                'address' => $client->address,
                'delegation_id' => $client->delegation_id,
                'delegation_name' => $client->delegation->name,
                'label' => $client->label,
                'display' => $client->display_name . ' - ' . $client->delegation->name
            ];
        }));
    }

    /**
     * API - Auto-complétion contenu
     */
    public function apiContentAutocomplete(Request $request)
    {
        $search = $request->get('q', '');
        $user = auth()->user();
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        // Récupérer les descriptions les plus utilisées
        $contents = $user->packages()
                        ->where('content_description', 'LIKE', "%{$search}%")
                        ->select('content_description')
                        ->groupBy('content_description')
                        ->orderByRaw('COUNT(*) DESC')
                        ->limit(10)
                        ->pluck('content_description');

        return response()->json($contents);
    }

    /**
     * API - Création rapide de colis
     */
    public function apiQuickCreate(Request $request)
    {
        try {
            $validated = $request->validate([
                'supplier_name' => 'required|string|max:255',
                'supplier_phone' => 'required|string|max:20',
                'pickup_delegation_id' => 'required|exists:delegations,id',
                'pickup_address' => 'required|string|max:500',
                'recipient_name' => 'required|string|max:255',
                'recipient_phone' => 'required|string|max:20',
                'recipient_address' => 'required|string|max:500',
                'delegation_to' => 'required|exists:delegations,id|different:pickup_delegation_id',
                'content_description' => 'required|string|max:255',
                'cod_amount' => 'required|numeric|min:0|max:9999.999'
            ]);

            DB::beginTransaction();
            
            $package = $this->createPackageFromData(auth()->user(), $validated, 'api');
            
            DB::commit();

            return response()->json([
                'success' => true,
                'package_id' => $package->id,
                'package_code' => $package->package_code,
                'message' => "Colis #{$package->package_code} créé avec succès!"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API - Données de session
     */
    public function apiSessionData()
    {
        return response()->json([
            'supplier_data' => session('last_supplier_data', []),
            'client_data' => session('last_client_data', []),
            'package_data' => session('last_package_data', [])
        ]);
    }

    // ==================== MÉTHODES PRIVÉES ====================

    /**
     * Créer un package à partir des données validées
     */
    private function createPackageFromData($user, $validated, $type = 'advanced')
    {
        $user->ensureWallet();
        $user->load(['wallet', 'clientProfile']);

        if (!$user->clientProfile) {
            throw new \Exception("Profil client non trouvé.");
        }

        $clientProfile = $user->clientProfile;
        $deliveryFee = $clientProfile->offer_delivery_price;
        $returnFee = $clientProfile->offer_return_price;

        // Données de base du package
        $packageData = [
            'sender_id' => $user->id,
            'sender_data' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'address' => $user->address
            ],
            'recipient_data' => [
                'name' => $validated['recipient_name'],
                'phone' => $validated['recipient_phone'],
                'address' => $validated['recipient_address']
            ],
            'content_description' => $validated['content_description'],
            'notes' => $validated['notes'] ?? null,
            'cod_amount' => $validated['cod_amount'],
            'delivery_fee' => $deliveryFee,
            'return_fee' => $returnFee,
            'status' => 'CREATED'
        ];

        // Ajuster selon le type
        if ($type === 'advanced' || $type === 'api') {
            $packageData['delegation_from'] = $validated['pickup_delegation_id'];
            $packageData['delegation_to'] = $validated['delegation_to'];
            
            if (Schema::hasColumn('packages', 'supplier_data')) {
                $packageData['supplier_data'] = [
                    'name' => $validated['supplier_name'],
                    'phone' => $validated['supplier_phone'],
                ];
                $packageData['pickup_delegation_id'] = $validated['pickup_delegation_id'];
                $packageData['pickup_address'] = $validated['pickup_address'];
                $packageData['pickup_phone'] = $validated['supplier_phone'];
                $packageData['pickup_notes'] = $validated['notes'] ?? null;
                
                // Options avancées
                if (!empty($validated['package_weight'])) {
                    $packageData['package_weight'] = $validated['package_weight'];
                }
                
                if (!empty($validated['package_value'])) {
                    $packageData['package_value'] = $validated['package_value'];
                }
                
                if (!empty($validated['package_length']) || !empty($validated['package_width']) || !empty($validated['package_height'])) {
                    $packageData['package_dimensions'] = [
                        'length' => $validated['package_length'] ?? 0,
                        'width' => $validated['package_width'] ?? 0,
                        'height' => $validated['package_height'] ?? 0,
                        'unit' => 'cm'
                    ];
                }
                
                $packageData['is_fragile'] = $validated['is_fragile'] ?? false;
                $packageData['requires_signature'] = $validated['requires_signature'] ?? false;
                $packageData['special_instructions'] = $validated['special_instructions'] ?? null;
            }
        } else {
            // Type basique
            $packageData['delegation_from'] = $validated['delegation_from'];
            $packageData['delegation_to'] = $validated['delegation_to'];
        }

        $package = new Package($packageData);

        // Calcul et déduction de l'escrow
        $escrowAmount = $this->calculateEscrowAmount($package, $validated['cod_amount'], $deliveryFee, $returnFee);
        
        if (!$user->wallet->hasSufficientBalance($escrowAmount)) {
            throw new \Exception("Solde insuffisant. Montant requis: {$escrowAmount} DT");
        }

        $package->amount_in_escrow = $escrowAmount;
        $package->save();

        // Transaction financière
        $this->financialService->processTransaction([
            'user_id' => $user->id,
            'type' => 'PACKAGE_CREATION_DEBIT',
            'amount' => -$escrowAmount,
            'package_id' => $package->id,
            'description' => "Création colis #{$package->package_code}",
            'metadata' => [
                'package_code' => $package->package_code,
                'escrow_type' => $validated['cod_amount'] >= $deliveryFee ? 'return_fee' : 'delivery_fee'
            ]
        ]);

        $package->updateStatus('AVAILABLE', $user, 'Colis créé et disponible pour pickup');

        return $package;
    }

    /**
     * Traitement des adresses sauvegardées
     */
    private function handleSavedAddresses($user, $validated)
    {
        if (!Schema::hasTable('saved_addresses')) {
            return;
        }

        if (!empty($validated['save_supplier_address'])) {
            $this->saveAddress($user, 'SUPPLIER', [
                'name' => $validated['supplier_name'],
                'phone' => $validated['supplier_phone'],
                'address' => $validated['pickup_address'],
                'delegation_id' => $validated['pickup_delegation_id'],
                'label' => $validated['supplier_address_label'] ?? null
            ]);
        }

        if (!empty($validated['save_client_address'])) {
            $this->saveAddress($user, 'CLIENT', [
                'name' => $validated['recipient_name'],
                'phone' => $validated['recipient_phone'],
                'address' => $validated['recipient_address'],
                'delegation_id' => $validated['delegation_to'],
                'label' => $validated['client_address_label'] ?? null
            ]);
        }
    }

    /**
     * Mise à jour des données de session
     */
    private function updateSessionData($validated)
    {
        session([
            'last_supplier_data' => [
                'name' => $validated['supplier_name'],
                'phone' => $validated['supplier_phone'],
                'pickup_delegation_id' => $validated['pickup_delegation_id'],
                'pickup_address' => $validated['pickup_address'],
                'pickup_notes' => $validated['notes'] ?? null
            ]
        ]);
    }

    /**
     * Statistiques du jour
     */
    private function getTodayStats($user)
    {
        $today = now()->startOfDay();
        
        $todayPackages = $user->packages()
            ->where('created_at', '>=', $today)
            ->get();
        
        $todayRevenue = $todayPackages
            ->whereIn('status', ['DELIVERED', 'PAID'])
            ->sum('cod_amount');
        
        return [
            'todayPackagesCount' => $todayPackages->count(),
            'todayRevenue' => $todayRevenue,
            'todayDelivered' => $todayPackages->whereIn('status', ['DELIVERED', 'PAID'])->count(),
            'todayPending' => $todayPackages->whereIn('status', ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP'])->count()
        ];
    }

    /**
     * Calcul du montant d'escrow
     */
    private function calculateEscrowAmount($package, $codAmount, $deliveryFee, $returnFee)
    {
        if ($codAmount >= $deliveryFee) {
            return $returnFee;
        } else {
            return $deliveryFee;
        }
    }

    /**
     * Sauvegarder une adresse
     */
    private function saveAddress($user, $type, $data)
    {
        if (!class_exists(SavedAddress::class)) {
            return null;
        }

        // Vérifier si l'adresse existe déjà
        $existingAddress = SavedAddress::where('user_id', $user->id)
            ->where('type', $type)
            ->where('name', $data['name'])
            ->where('phone', $data['phone'])
            ->where('delegation_id', $data['delegation_id'])
            ->first();

        if ($existingAddress) {
            $existingAddress->markAsUsed();
            return $existingAddress;
        }

        // Créer une nouvelle adresse
        return SavedAddress::create([
            'user_id' => $user->id,
            'type' => $type,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'delegation_id' => $data['delegation_id'],
            'label' => $data['label'] ?? null,
            'last_used_at' => now(),
            'use_count' => 1
        ]);
    }

    /**
     * Vider le cache des adresses
     */
    private function clearAddressesCache($user)
    {
        Cache::forget("supplier_addresses_{$user->id}");
        Cache::forget("client_addresses_{$user->id}");
        Cache::forget("api_saved_addresses_{$user->id}_all");
        Cache::forget("api_saved_addresses_{$user->id}_supplier");
        Cache::forget("api_saved_addresses_{$user->id}_client");
    }
}