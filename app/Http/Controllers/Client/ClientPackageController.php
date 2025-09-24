<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Delegation;
use App\Models\SavedAddress;
use App\Models\ImportBatch;
use App\Models\ClientPickupAddress;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ClientPackageController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Interface principale avec onglets
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        // Déterminer l'onglet actif
        $activeTab = $request->get('tab', 'all');
        
        // Récupérer les statistiques
        $stats = $this->getPackageStats($user);
        
        // Récupérer les colis selon l'onglet - TOUJOURS avec pagination
        $packages = $this->getPackagesByTab($user, $activeTab, $request);
        
        return view('client.packages.index', compact('packages', 'activeTab', 'stats'));
    }

    /**
     * Récupérer les statistiques des colis
     */
    private function getPackageStats($user)
    {
        return [
            'total' => $user->sentPackages()->count(),
            'pending' => $user->sentPackages()->where('status', 'AVAILABLE')->count(),
            'in_progress' => $user->sentPackages()->whereIn('status', ['CREATED', 'ACCEPTED', 'PICKED_UP'])->count(),
            'delivered' => $user->sentPackages()->whereIn('status', ['DELIVERED', 'PAID'])->count(),
            'returned' => $user->sentPackages()->where('status', 'RETURNED')->count(),
        ];
    }

    /**
     * Récupérer les colis par onglet - TOUJOURS avec pagination
     */
    private function getPackagesByTab($user, $tab, $request)
    {
        $query = Package::where('sender_id', $user->id)
            ->with(['delegationFrom', 'delegationTo', 'assignedDeliverer', 'pickupAddress']);

        // Filtrer par onglet
        switch ($tab) {
            case 'pending':
                $query->where('status', 'AVAILABLE');
                break;
            case 'in_progress':
                $query->whereIn('status', ['CREATED', 'ACCEPTED', 'PICKED_UP']);
                break;
            case 'delivered':
                $query->whereIn('status', ['DELIVERED', 'PAID']);
                break;
            case 'returned':
                $query->where('status', 'RETURNED');
                break;
            case 'all':
            default:
                // Tous les colis - aucun filtre de statut
                break;
        }

        // Appliquer les filtres de recherche
        $this->applyFilters($query, $request);

        // TOUJOURS retourner un objet paginé
        return $query->orderBy('created_at', 'desc')->paginate(15);
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
            return redirect()->route('client.packages.index')
                ->with('error', 'Votre compte doit être validé avant de créer des colis.');
        }

        if (!$user->wallet) {
            $user->ensureWallet();
            $user->load('wallet');
        }

        $delegations = Delegation::where('active', true)->orderBy('name')->get();
        $hasAdvancedFeatures = Schema::hasTable('saved_addresses');

        // Récupérer les adresses de pickup du client
        $pickupAddresses = ClientPickupAddress::forClient($user->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Récupérer les gouvernorats et délégations depuis la config
        $gouvernorats = config('tunisia.gouvernorats');
        $delegationsData = config('tunisia.delegations');
        
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
                'pickupAddresses',
                'gouvernorats',
                'delegationsData',
                'lastSupplierData',
                'lastClientData'
            ), $todayStats));
        } else {
            return view('client.packages.create', array_merge(compact(
                'user',
                'delegations',
                'pickupAddresses',
                'gouvernorats',
                'delegationsData'
            ), $todayStats));
        }
    }

    /**
     * Enregistrement d'un nouveau colis - VERSION OPTIMISÉE AVEC VALIDATION CORRIGÉE
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
     * Création de colis format basique AVEC VALIDATION CORRIGÉE
     */
    private function storeBasicPackage(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'pickup_address_id' => 'required|exists:client_pickup_addresses,id',
            'nom_complet' => 'required|string|max:255',
            'telephone_1' => 'required|string|max:20',
            'telephone_2' => 'nullable|string|max:20',
            'adresse_complete' => 'required|string|max:500',
            'gouvernorat' => 'required|string|max:100',
            'delegation' => 'required|string|max:100',
            'contenu' => 'required|string|max:255',
            'prix' => 'required|numeric|min:0|max:9999.999',
            'commentaire' => 'nullable|string|max:1000',
            'fragile' => 'boolean',
            'signature_obligatoire' => 'boolean',
            'autorisation_ouverture' => 'boolean',
            'payment_method' => 'required|in:especes_seulement,cheque_seulement,especes_et_cheques'
        ]);

        try {
            DB::beginTransaction();

            $package = $this->createPackageFromData($user, $validated, 'basic');
            
            DB::commit();

            return redirect()->route('client.packages.index')
                ->with('success', "Colis #{$package->package_code} créé avec succès!");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', "Erreur lors de la création: " . $e->getMessage());
        }
    }

    /**
     * Création de colis format avancé - VERSION OPTIMISÉE AVEC VALIDATION CORRIGÉE
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
            
            'delegation_to' => [
                'required',
                'exists:delegations,id',
                'different:pickup_delegation_id'  // CORRECTION: Empêcher même délégation pickup/livraison
            ],
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
        ], [
            'delegation_to.different' => 'La délégation de livraison doit être différente de la délégation de pickup.'
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
                return redirect()->route('client.packages.index')
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
     * Supprimer un colis (seulement si statut CREATED ou AVAILABLE)
     */
    public function destroy(Package $package)
    {
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        // Vérifier que le colis peut être supprimé
        if (!in_array($package->status, ['CREATED', 'AVAILABLE'])) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce colis. Statut actuel: ' . $package->status
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Rembourser l'escrow au client
            if ($package->amount_in_escrow > 0) {
                $this->financialService->processTransaction([
                    'user_id' => $package->sender_id,
                    'type' => 'PACKAGE_DELETION_CREDIT',
                    'amount' => $package->amount_in_escrow,
                    'package_id' => $package->id,
                    'description' => "Remboursement suppression colis #{$package->package_code}",
                    'metadata' => [
                        'package_code' => $package->package_code,
                        'original_escrow' => $package->amount_in_escrow
                    ]
                ]);
            }

            // Supprimer le colis et son historique
            if (method_exists($package, 'statusHistory')) {
                $package->statusHistory()->delete();
            }
            $packageCode = $package->package_code;
            $package->delete();

            DB::commit();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Colis #{$packageCode} supprimé avec succès."
                ]);
            }

            return redirect()->route('client.packages.index')
                ->with('success', "Colis #{$packageCode} supprimé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Suppression en masse de colis
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'package_ids' => 'required|array|min:1|max:50',
            'package_ids.*' => 'exists:packages,id'
        ]);

        $user = Auth::user();
        $packages = Package::whereIn('id', $validated['package_ids'])
            ->where('sender_id', $user->id)
            ->whereIn('status', ['CREATED', 'AVAILABLE'])
            ->get();

        if ($packages->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun colis éligible à la suppression trouvé.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $deletedCount = 0;
            $totalRefund = 0;

            foreach ($packages as $package) {
                // Rembourser l'escrow
                if ($package->amount_in_escrow > 0) {
                    $totalRefund += $package->amount_in_escrow;
                    $this->financialService->processTransaction([
                        'user_id' => $user->id,
                        'type' => 'PACKAGE_DELETION_CREDIT',
                        'amount' => $package->amount_in_escrow,
                        'package_id' => $package->id,
                        'description' => "Remboursement suppression groupée #{$package->package_code}",
                        'metadata' => [
                            'package_code' => $package->package_code,
                            'bulk_deletion' => true,
                            'original_escrow' => $package->amount_in_escrow
                        ]
                    ]);
                }

                // Supprimer l'historique et le colis
                if (method_exists($package, 'statusHistory')) {
                    $package->statusHistory()->delete();
                }
                $package->delete();
                $deletedCount++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$deletedCount} colis supprimés avec succès. Remboursement: {$totalRefund} DT",
                'deleted_count' => $deletedCount,
                'total_refund' => $totalRefund
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression groupée: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Imprimer le bon de livraison d'un colis - AVEC CODES QR ET BARRES CORRIGÉS
     */
    public function printDeliveryNote(Package $package)
    {
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        $package->load(['delegationFrom', 'delegationTo', 'assignedDeliverer', 'pickupAddress']);

        return view('client.packages.delivery-note', compact('package'));
    }

    /**
     * Imprimer plusieurs bons de livraison
     */
    public function printMultipleDeliveryNotes(Request $request)
    {
        $validated = $request->validate([
            'package_ids' => 'required|array|min:1|max:50',
            'package_ids.*' => 'exists:packages,id'
        ]);

        $packages = Package::whereIn('id', $validated['package_ids'])
            ->where('sender_id', Auth::id())
            ->with(['delegationFrom', 'delegationTo', 'assignedDeliverer', 'pickupAddress'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($packages->count() === 0) {
            return back()->with('error', 'Aucun colis trouvé pour l\'impression.');
        }

        return view('client.packages.delivery-notes-bulk', compact('packages'));
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

    /**
     * Export des colis
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        $query = Package::where('sender_id', $user->id)
            ->with(['delegationFrom', 'delegationTo', 'assignedDeliverer', 'pickupAddress']);

        $this->applyFilters($query, $request);
        $packages = $query->orderBy('created_at', 'desc')->get();

        $csvContent = "Code Colis;Date Création;Statut;Pickup;Délégation Pickup;Destinataire;Délégation Livraison;Contenu;Montant COD;Notes\n";
        
        foreach ($packages as $package) {
            $csvContent .= sprintf(
                "%s;%s;%s;%s;%s;%s;%s;%s;%s;%s\n",
                $package->package_code,
                $package->created_at->format('d/m/Y H:i'),
                $package->status,
                $package->supplier_data['name'] ?? 'N/A',
                $package->delegationFrom->name ?? 'N/A',
                $package->recipient_data['name'] ?? 'N/A',
                $package->delegationTo->name ?? 'N/A',
                $package->content_description,
                number_format($package->cod_amount, 3),
                str_replace([';', "\n", "\r"], [',', ' ', ' '], $package->notes ?? '')
            );
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="colis_export_' . date('Y-m-d_H-i') . '.csv"');
    }

    /**
     * Tracking public d'un colis
     */
    public function publicTracking($package_code)
    {
        $package = Package::where('package_code', $package_code)
            ->with(['delegationFrom', 'delegationTo', 'statusHistory.changedBy'])
            ->firstOrFail();

        return view('public.tracking', compact('package'));
    }

    /**
     * Tracking via QR code (version mobile optimisée)
     */
    public function qrTracking($package_code)
    {
        $package = Package::where('package_code', $package_code)
            ->with(['delegationFrom', 'delegationTo', 'statusHistory.changedBy'])
            ->firstOrFail();

        return view('public.tracking-mobile', compact('package'));
    }

    /**
     * API - Résumé/statistiques des colis
     */
    public function apiSummary()
    {
        $user = auth()->user();
        
        $stats = [
            'total_packages' => $user->sentPackages()->count(),
            'pending_packages' => $user->sentPackages()->where('status', 'AVAILABLE')->count(),
            'in_progress_packages' => $user->sentPackages()->whereIn('status', ['ACCEPTED', 'PICKED_UP'])->count(),
            'delivered_packages' => $user->sentPackages()->whereIn('status', ['DELIVERED', 'PAID'])->count(),
            'returned_packages' => $user->sentPackages()->where('status', 'RETURNED')->count(),
            'created_packages' => $user->sentPackages()->where('status', 'CREATED')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Colis en attente (statut AVAILABLE) - Redirection vers index avec onglet
     */
    public function pending(Request $request)
    {
        return redirect()->route('client.packages.index', ['tab' => 'pending'] + $request->query());
    }

    /**
     * Colis en cours (ACCEPTED, PICKED_UP) - Redirection vers index avec onglet
     */
    public function inProgress(Request $request)
    {
        return redirect()->route('client.packages.index', ['tab' => 'in_progress'] + $request->query());
    }

    /**
     * Colis livrés (DELIVERED, PAID) - Redirection vers index avec onglet
     */
    public function delivered(Request $request)
    {
        return redirect()->route('client.packages.index', ['tab' => 'delivered'] + $request->query());
    }

    /**
     * Colis retournés (RETURNED) - Redirection vers index avec onglet
     */
    public function returned(Request $request)
    {
        return redirect()->route('client.packages.index', ['tab' => 'returned'] + $request->query());
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

        // Récupérer l'adresse de pickup sélectionnée
        $pickupAddress = null;
        if (isset($validated['pickup_address_id'])) {
            $pickupAddress = ClientPickupAddress::find($validated['pickup_address_id']);
        }

        // Données de base du package
        $packageData = [
            'sender_id' => $user->id,
            'pickup_address_id' => $validated['pickup_address_id'] ?? null,
            'sender_data' => [
                'name' => $pickupAddress ? $pickupAddress->contact_name : $user->name,
                'phone' => $pickupAddress ? $pickupAddress->phone : $user->phone,
                'address' => $pickupAddress ? $pickupAddress->address : $user->address
            ],
            'recipient_data' => [
                'name' => $validated['nom_complet'] ?? $validated['recipient_name'],
                'phone' => $validated['telephone_1'] ?? $validated['recipient_phone'],
                'phone2' => $validated['telephone_2'] ?? $validated['recipient_phone2'] ?? null,
                'address' => $validated['adresse_complete'] ?? $validated['recipient_address'],
                'gouvernorat' => $validated['gouvernorat'] ?? $validated['recipient_gouvernorat'] ?? null,
                'delegation' => $validated['delegation'] ?? $validated['recipient_delegation'] ?? null
            ],
            'content_description' => $validated['contenu'] ?? $validated['content_description'],
            'notes' => $validated['commentaire'] ?? $validated['notes'] ?? null,
            'cod_amount' => $validated['prix'] ?? $validated['cod_amount'],
            'delivery_fee' => $deliveryFee,
            'return_fee' => $returnFee,
            'status' => 'CREATED',
            'is_fragile' => $validated['fragile'] ?? $validated['is_fragile'] ?? false,
            'requires_signature' => $validated['signature_obligatoire'] ?? $validated['requires_signature'] ?? false,
            'allow_opening' => $validated['autorisation_ouverture'] ?? $validated['allow_opening'] ?? false,
            'payment_method' => $this->mapPaymentMethod($validated['payment_method'] ?? 'cash_only')
        ];

        // Récupérer les délégations depuis l'adresse pickup et destination
        // Pour delegation_from : récupérer depuis l'adresse de pickup
        if ($pickupAddress) {
            // Mapper le nom de délégation de l'adresse pickup vers un ID
            $delegationFromId = $this->findDelegationIdByName($pickupAddress->delegation);
            $packageData['delegation_from'] = $delegationFromId;
        } else {
            $packageData['delegation_from'] = null;
        }

        // Pour delegation_to : traiter selon le format
        if ($type === 'basic' && isset($validated['delegation'])) {
            // Format basique : convertir le nom en ID
            $delegationToId = $this->findDelegationIdByName($validated['delegation']);
            $packageData['delegation_to'] = $delegationToId;
        } else {
            // Format avancé : utiliser directement l'ID fourni
            $packageData['delegation_to'] = $validated['delegation_to'] ?? null;
        }

        $package = new Package($packageData);

        // VALIDATION WALLET OBLIGATOIRE AVANT CRÉATION
        $codAmount = $validated['prix'] ?? $validated['cod_amount'];
        $escrowAmount = $this->calculateEscrowAmount($package, $codAmount, $deliveryFee, $returnFee);
        $pendingAmount = $this->calculatePendingAmount($codAmount, $deliveryFee, $returnFee);

        // Vérifier le solde disponible (balance - frozen_amount)
        $availableBalance = $user->wallet->balance - ($user->wallet->frozen_amount ?? 0);

        if ($availableBalance < $escrowAmount) {
            throw new \Exception("Solde insuffisant. Montant requis: " . number_format($escrowAmount, 3) . " DT, disponible: " . number_format($availableBalance, 3) . " DT. Rechargez votre portefeuille avant de créer ce colis.");
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
                'escrow_type' => $codAmount >= $deliveryFee ? 'return_fee' : 'delivery_fee'
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
        
        $todayPackages = $user->sentPackages()
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
     * Calcul du montant d'escrow selon les règles SPÉCIFICATIONS EXACTES
     */
    private function calculateEscrowAmount($package, $codAmount, $deliveryFee, $returnFee)
    {
        // Cas 1: COD ≥ Frais Livraison (X ≥ Y)
        if ($codAmount >= $deliveryFee) {
            // Déduction: Z (frais retour) du wallet client
            return $returnFee;
        }
        // Cas 2: COD < Frais Livraison (X < Y)
        else {
            // Déduction: Y (frais livraison) du wallet client
            return $deliveryFee;
        }
    }

    /**
     * Calcul du montant en attente selon les règles SPÉCIFICATIONS EXACTES
     */
    private function calculatePendingAmount($codAmount, $deliveryFee, $returnFee)
    {
        // Cas 1: COD ≥ Frais Livraison (X ≥ Y)
        if ($codAmount >= $deliveryFee) {
            // Montant en attente: (X + Z) - Y
            return ($codAmount + $returnFee) - $deliveryFee;
        }
        // Cas 2: COD < Frais Livraison (X < Y)
        else {
            // Montant en attente: X complet
            return $codAmount;
        }
    }

    /**
     * Appliquer les filtres communs
     */
    private function applyFilters($query, $request)
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('package_code', 'LIKE', '%' . $search . '%')
                  ->orWhereJsonContains('recipient_data->name', $search)
                  ->orWhereJsonContains('supplier_data->name', $search)
                  ->orWhere('content_description', 'LIKE', '%' . $search . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
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
            $existingAddress->increment('use_count');
            $existingAddress->update(['last_used_at' => now()]);
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

    /**
     * Mapper les nouvelles valeurs de payment_method vers les valeurs de la base de données
     */
    private function mapPaymentMethod($paymentMethod)
    {
        $mapping = [
            'especes_seulement' => 'cash_only',
            'cheque_seulement' => 'check_only',
            'especes_et_cheques' => 'both',
            // Support des anciennes valeurs au cas où
            'cash_only' => 'cash_only',
            'check_only' => 'check_only',
            'both' => 'both'
        ];

        return $mapping[$paymentMethod] ?? 'cash_only';
    }

    /**
     * Trouver l'ID d'une délégation par son nom ou créer une nouvelle délégation si elle n'existe pas
     */
    private function findDelegationIdByName($delegationName)
    {
        if (empty($delegationName)) {
            return null;
        }

        $delegationName = trim($delegationName);

        // Rechercher la délégation existante par nom (insensible à la casse)
        $delegation = Delegation::where(function($query) use ($delegationName) {
            $query->whereRaw('LOWER(name) = ?', [strtolower($delegationName)])
                  ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($delegationName) . '%']);
        })->first();

        // Si trouvé, retourner l'ID
        if ($delegation) {
            return $delegation->id;
        }

        // Si pas trouvé, créer une nouvelle délégation
        $newDelegation = Delegation::create([
            'name' => ucfirst($delegationName),
            'zone' => 'Zone Auto-créée',
            'active' => true,
            'created_by' => auth()->id() ?? 1
        ]);

        return $newDelegation->id;
    }
}