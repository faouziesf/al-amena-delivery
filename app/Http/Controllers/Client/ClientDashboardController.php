<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Delegation;
use App\Models\Complaint;
use App\Models\WithdrawalRequest;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema; // AJOUTÉ
use Illuminate\Validation\Rule;

class ClientDashboardController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Dashboard principal du client
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->load(['wallet', 'clientProfile']);

        if (!$user->wallet) {
            $user->ensureWallet();
            $user->load('wallet');
        }

        $stats = $this->getDashboardStats();
        
        $recentPackages = Package::where('sender_id', $user->id)
            ->with(['delegationFrom', 'delegationTo'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $notifications = $user->notifications()
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentTransactions = $user->transactions()
            ->where('status', 'COMPLETED')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        return view('client.dashboard', compact(
            'user',
            'stats', 
            'recentPackages',
            'notifications',
            'recentTransactions'
        ));
    }

    /**
     * Liste des colis du client
     */
    public function packages(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $query = Package::where('sender_id', $user->id)
            ->with(['delegationFrom', 'delegationTo', 'assignedDeliverer']);

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
    public function packageShow(Package $package)
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

        // Charger les modifications COD seulement si la relation existe
        if (method_exists($package, 'codModifications')) {
            $package->load('codModifications.modifiedByCommercial');
        }

        return view('client.packages.show', compact('package'));
    }

    /**
     * Formulaire de création de colis - VERSION SIMPLIFIÉE
     */
    public function createPackage(Request $request)
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
        
        // Vérifier si les nouvelles fonctionnalités sont disponibles
        $hasAdvancedFeatures = Schema::hasTable('saved_addresses');
        
        if ($hasAdvancedFeatures) {
            // Utiliser la version avancée si les tables existent
            $supplierAddresses = $user->savedAddresses()
                                     ->suppliers()
                                     ->with('delegation')
                                     ->recentlyUsed()
                                     ->get();
                                     
            $clientAddresses = $user->savedAddresses()
                                   ->clients()
                                   ->with('delegation')
                                   ->recentlyUsed()
                                   ->get();

            $lastSupplierData = session('last_supplier_data', []);
            $lastClientData = session('last_client_data', []);
            $prefillSupplier = $request->get('continue') === 'true' && !empty($lastSupplierData);

            return view('client.packages.create-advanced', compact(
                'user', 
                'delegations', 
                'supplierAddresses',
                'clientAddresses',
                'lastSupplierData',
                'lastClientData',
                'prefillSupplier'
            ));
        } else {
            // Utiliser la version basique
            return view('client.packages.create', compact('user', 'delegations'));
        }
    }

    /**
     * Enregistrement d'un nouveau colis - VERSION ADAPTATIVE
     */
    public function storePackage(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        // Vérifier si c'est le format avancé ou basique
        $isAdvancedFormat = $request->has('supplier_name');

        if ($isAdvancedFormat) {
            return $this->storeAdvancedPackage($request);
        } else {
            return $this->storeBasicPackage($request);
        }
    }

    /**
     * Création de colis format basique (ancien)
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

            $user->ensureWallet();
            $user->load(['wallet', 'clientProfile']);

            if (!$user->clientProfile) {
                throw new \Exception("Profil client non trouvé.");
            }

            $clientProfile = $user->clientProfile;
            $deliveryFee = $clientProfile->offer_delivery_price;
            $returnFee = $clientProfile->offer_return_price;

            $package = new Package([
                'sender_id' => $user->id,
                'sender_data' => [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'address' => $user->address
                ],
                'delegation_from' => $validated['delegation_from'],
                'recipient_data' => [
                    'name' => $validated['recipient_name'],
                    'phone' => $validated['recipient_phone'],
                    'address' => $validated['recipient_address']
                ],
                'delegation_to' => $validated['delegation_to'],
                'content_description' => $validated['content_description'],
                'notes' => $validated['notes'],
                'cod_amount' => $validated['cod_amount'],
                'delivery_fee' => $deliveryFee,
                'return_fee' => $returnFee,
                'status' => 'CREATED'
            ]);

            $escrowAmount = $this->calculateEscrowAmount($package, $validated['cod_amount'], $deliveryFee, $returnFee);
            
            if (!$user->wallet->hasSufficientBalance($escrowAmount)) {
                throw new \Exception("Solde insuffisant. Montant requis: {$escrowAmount} DT");
            }

            $package->amount_in_escrow = $escrowAmount;
            $package->save();

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
     * Création de colis format avancé (nouveau)
     */
    private function storeAdvancedPackage(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            // Données fournisseur/pickup
            'supplier_name' => 'required|string|max:255',
            'supplier_phone' => 'required|string|max:20',
            'pickup_delegation_id' => 'required|exists:delegations,id',
            'pickup_address' => 'required|string|max:500',
            'pickup_notes' => 'nullable|string|max:1000',
            'save_supplier_address' => 'boolean',
            'supplier_address_label' => 'nullable|string|max:100',
            
            // Données destinataire
            'delegation_to' => 'required|exists:delegations,id|different:pickup_delegation_id',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:20',
            'recipient_address' => 'required|string|max:500',
            'save_client_address' => 'boolean',
            'client_address_label' => 'nullable|string|max:100',
            
            // Détails du colis
            'content_description' => 'required|string|max:255',
            'cod_amount' => 'required|numeric|min:0|max:9999.999',
            'package_weight' => 'nullable|numeric|min:0|max:999.999',
            'package_value' => 'nullable|numeric|min:0|max:99999.999',
            'package_length' => 'nullable|numeric|min:0|max:999',
            'package_width' => 'nullable|numeric|min:0|max:999',
            'package_height' => 'nullable|numeric|min:0|max:999',
            'is_fragile' => 'boolean',
            'requires_signature' => 'boolean',
            'special_instructions' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            
            // Actions
            'create_another' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $user->ensureWallet();
            $user->load(['wallet', 'clientProfile']);

            if (!$user->clientProfile) {
                throw new \Exception("Profil client non trouvé.");
            }

            $clientProfile = $user->clientProfile;
            $deliveryFee = $clientProfile->offer_delivery_price;
            $returnFee = $clientProfile->offer_return_price;

            // Préparer les données du package
            $packageData = [
                'sender_id' => $user->id,
                'sender_data' => [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'address' => $user->address
                ],
                'delegation_from' => $validated['pickup_delegation_id'], // Pour compatibilité
                'recipient_data' => [
                    'name' => $validated['recipient_name'],
                    'phone' => $validated['recipient_phone'],
                    'address' => $validated['recipient_address']
                ],
                'delegation_to' => $validated['delegation_to'],
                'content_description' => $validated['content_description'],
                'notes' => $validated['notes'],
                'cod_amount' => $validated['cod_amount'],
                'delivery_fee' => $deliveryFee,
                'return_fee' => $returnFee,
                'status' => 'CREATED'
            ];

            // Ajouter les données avancées si les colonnes existent
            if (Schema::hasColumn('packages', 'supplier_data')) {
                $packageData['supplier_data'] = [
                    'name' => $validated['supplier_name'],
                    'phone' => $validated['supplier_phone'],
                ];
                $packageData['pickup_delegation_id'] = $validated['pickup_delegation_id'];
                $packageData['pickup_address'] = $validated['pickup_address'];
                $packageData['pickup_phone'] = $validated['supplier_phone'];
                $packageData['pickup_notes'] = $validated['pickup_notes'];
                
                if ($validated['package_weight']) {
                    $packageData['package_weight'] = $validated['package_weight'];
                }
                
                if ($validated['package_value']) {
                    $packageData['package_value'] = $validated['package_value'];
                }
                
                if ($validated['package_length'] || $validated['package_width'] || $validated['package_height']) {
                    $packageData['package_dimensions'] = [
                        'length' => $validated['package_length'] ?? 0,
                        'width' => $validated['package_width'] ?? 0,
                        'height' => $validated['package_height'] ?? 0,
                        'unit' => 'cm'
                    ];
                }
                
                $packageData['is_fragile'] = $validated['is_fragile'] ?? false;
                $packageData['requires_signature'] = $validated['requires_signature'] ?? false;
                $packageData['special_instructions'] = $validated['special_instructions'];
            }

            $package = new Package($packageData);

            $escrowAmount = $this->calculateEscrowAmount($package, $validated['cod_amount'], $deliveryFee, $returnFee);
            
            if (!$user->wallet->hasSufficientBalance($escrowAmount)) {
                throw new \Exception("Solde insuffisant. Montant requis: {$escrowAmount} DT");
            }

            $package->amount_in_escrow = $escrowAmount;
            $package->save();

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

            // Sauvegarder les adresses si demandé et si la fonctionnalité est disponible
            if (Schema::hasTable('saved_addresses')) {
                if ($validated['save_supplier_address'] ?? false) {
                    $this->saveAddress($user, 'SUPPLIER', [
                        'name' => $validated['supplier_name'],
                        'phone' => $validated['supplier_phone'],
                        'address' => $validated['pickup_address'],
                        'delegation_id' => $validated['pickup_delegation_id'],
                        'label' => $validated['supplier_address_label']
                    ]);
                }

                if ($validated['save_client_address'] ?? false) {
                    $this->saveAddress($user, 'CLIENT', [
                        'name' => $validated['recipient_name'],
                        'phone' => $validated['recipient_phone'],
                        'address' => $validated['recipient_address'],
                        'delegation_id' => $validated['delegation_to'],
                        'label' => $validated['client_address_label']
                    ]);
                }
            }

            // Sauvegarder les données en session pour la création en série
            session([
                'last_supplier_data' => [
                    'name' => $validated['supplier_name'],
                    'phone' => $validated['supplier_phone'],
                    'pickup_delegation_id' => $validated['pickup_delegation_id'],
                    'pickup_address' => $validated['pickup_address'],
                    'pickup_notes' => $validated['pickup_notes']
                ],
                'last_client_data' => [
                    'delegation_to' => $validated['delegation_to'],
                    'recipient_name' => $validated['recipient_name'],
                    'recipient_phone' => $validated['recipient_phone'],
                    'recipient_address' => $validated['recipient_address']
                ]
            ]);

            DB::commit();

            $successMessage = "Colis #{$package->package_code} créé avec succès!";

            if ($validated['create_another'] ?? false) {
                return redirect()->route('client.packages.create', ['continue' => 'true'])
                    ->with('success', $successMessage . ' Vous pouvez créer un autre colis.');
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
     * Interface d'import CSV (si disponible)
     */
    public function importCsvForm()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        if (!Schema::hasTable('import_batches')) {
            return redirect()->route('client.packages.create')
                ->with('info', 'La fonctionnalité d\'import CSV n\'est pas encore disponible.');
        }

        $recentImports = ImportBatch::where('user_id', $user->id)
                                  ->orderBy('created_at', 'desc')
                                  ->limit(10)
                                  ->get();

        return view('client.packages.import', compact('recentImports'));
    }

    // ... (autres méthodes du contrôleur)

    /**
     * Méthodes privées
     */
    private function calculateEscrowAmount($package, $codAmount, $deliveryFee, $returnFee)
    {
        if ($codAmount >= $deliveryFee) {
            return $returnFee;
        } else {
            return $deliveryFee;
        }
    }

    private function saveAddress($user, $type, $data)
    {
        if (!class_exists(\App\Models\SavedAddress::class)) {
            return null;
        }

        return \App\Models\SavedAddress::create([
            'user_id' => $user->id,
            'type' => $type,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'delegation_id' => $data['delegation_id'],
            'label' => $data['label'] ?? null,
        ]);
    }

    private function getDashboardStats(): array
    {
        $user = Auth::user();
        
        $user->ensureWallet();
        $user->load('wallet');
        
        $packages = $user->packages();
        
        return [
            'wallet_balance' => (float) ($user->wallet->balance ?? 0),
            'wallet_pending' => (float) ($user->wallet->pending_amount ?? 0),
            'total_packages' => $packages->count(),
            'in_progress_packages' => $packages->whereIn('status', ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP'])->count(),
            'delivered_packages' => $packages->whereIn('status', ['DELIVERED', 'PAID'])->count(),
            'returned_packages' => $packages->where('status', 'RETURNED')->count(),
            'pending_complaints' => $user->complaints()->where('status', 'PENDING')->count(),
            'pending_withdrawals' => $user->withdrawalRequests()->where('status', 'PENDING')->count(),
            'unread_notifications' => $user->notifications()->where('read', false)->count(),
            'monthly_packages' => $packages->whereMonth('created_at', now()->month)->count(),
            'monthly_delivered' => $packages->whereIn('status', ['DELIVERED', 'PAID'])
                                           ->whereMonth('updated_at', now()->month)->count()
        ];
    }

    // API - Statistiques dashboard
    public function apiStats()
    {
        return response()->json($this->getDashboardStats());
    }

    // API - Solde wallet
    public function apiWalletBalance()
    {
        $user = Auth::user();
        $user->ensureWallet();
        $user->load('wallet');
        
        return response()->json([
            'balance' => (float) $user->wallet->balance,
            'pending' => (float) $user->wallet->pending_amount,
            'available' => (float) ($user->wallet->balance - ($user->wallet->frozen_amount ?? 0))
        ]);
    }
}