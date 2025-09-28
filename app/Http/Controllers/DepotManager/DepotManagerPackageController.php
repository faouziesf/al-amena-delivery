<?php

namespace App\Http\Controllers\DepotManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Package;
use App\Models\User;

class DepotManagerPackageController extends Controller
{
    /**
     * Liste des colis du dépôt
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        // Obtenir les livreurs gérés
        $managedDeliverers = $user->getManagedDeliverers();
        $delivererIds = $managedDeliverers->pluck('id')->toArray();

        $query = Package::whereIn('assigned_deliverer_id', $delivererIds)
                       ->with(['assignedDeliverer', 'sender', 'delegationFrom', 'delegationTo']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gouvernorat')) {
            $query->whereHas('assignedDeliverer', function($q) use ($request) {
                $q->where('assigned_delegation', $request->gouvernorat);
            });
        }

        if ($request->filled('deliverer_id')) {
            $query->where('assigned_deliverer_id', $request->deliverer_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('package_code', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('recipient_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('recipient_phone', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(25);

        // Statistiques rapides
        $stats = [
            'total' => Package::whereIn('assigned_deliverer_id', $delivererIds)->count(),
            'in_progress' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                  ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
                                  ->count(),
            'delivered_today' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                      ->where('status', 'DELIVERED')
                                      ->whereDate('delivered_at', today())
                                      ->count(),
            'urgent' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                             ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])
                             ->where('delivery_attempts', '>=', 3)
                             ->count()
        ];

        return view('depot-manager.packages.index', compact('packages', 'stats', 'managedDeliverers', 'user'))
            ->with('deliverers', $managedDeliverers);
    }

    /**
     * Afficher les détails d'un colis
     */
    public function show(Package $package)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        // Le chef dépôt peut accéder à tous les colis
        $package->load(['assignedDeliverer', 'sender', 'delegationFrom', 'delegationTo', 'statusHistory', 'pickupAddress', 'pickupDelegation', 'complaints']);

        return view('depot-manager.packages.show', compact('package'));
    }

    /**
     * Réassigner un colis à un autre livreur
     */
    public function reassign(Request $request, Package $package)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $request->validate([
            'new_deliverer_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500'
        ]);

        $newDeliverer = User::findOrFail($request->new_deliverer_id);

        // Vérifier que le nouveau livreur est actif
        if ($newDeliverer->role !== 'DELIVERER' || $newDeliverer->status !== 'ACTIVE') {
            return back()->with('error', 'Le livreur sélectionné n\'est pas valide ou actif.');
        }

        $package->update([
            'assigned_deliverer_id' => $newDeliverer->id,
            'status' => 'ACCEPTED'
        ]);

        return back()->with('success', 'Colis réassigné avec succès.');
    }

    /**
     * Dashboard avec statistiques détaillées comme commercial
     */
    public function dashboard()
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $managedDeliverers = $user->getManagedDeliverers();
        $delivererIds = $managedDeliverers->pluck('id')->toArray();

        // Statistiques générales
        $stats = [
            'total_packages' => Package::whereIn('assigned_deliverer_id', $delivererIds)->count(),
            'delivered_today' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                      ->where('status', 'DELIVERED')
                                      ->whereDate('delivered_at', today())
                                      ->count(),
            'in_progress' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                  ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
                                  ->count(),
            'cod_collected_today' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                          ->where('status', 'DELIVERED')
                                          ->whereDate('delivered_at', today())
                                          ->sum('cod_amount'),
            'urgent_packages' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                      ->whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])
                                      ->where('delivery_attempts', '>=', 3)
                                      ->count()
        ];

        // Performance par gouvernorat
        $gouvernoratStats = [];
        foreach ($user->assigned_gouvernorats_array as $gouvernorat) {
            $gouvernoratDeliverers = $managedDeliverers->where('assigned_delegation', $gouvernorat);
            $gouvernoratDelivererIds = $gouvernoratDeliverers->pluck('id')->toArray();

            $gouvernoratStats[$gouvernorat] = [
                'deliverers_count' => $gouvernoratDeliverers->count(),
                'packages_total' => Package::whereIn('assigned_deliverer_id', $gouvernoratDelivererIds)->count(),
                'delivered_today' => Package::whereIn('assigned_deliverer_id', $gouvernoratDelivererIds)
                                          ->where('status', 'DELIVERED')
                                          ->whereDate('delivered_at', today())
                                          ->count(),
                'in_progress' => Package::whereIn('assigned_deliverer_id', $gouvernoratDelivererIds)
                                      ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
                                      ->count()
            ];
        }

        // Top livreurs du jour
        $topDeliverers = $managedDeliverers->map(function($deliverer) {
            return [
                'deliverer' => $deliverer,
                'deliveries_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                                           ->where('status', 'DELIVERED')
                                           ->whereDate('delivered_at', today())
                                           ->count(),
                'cod_collected' => Package::where('assigned_deliverer_id', $deliverer->id)
                                        ->where('status', 'DELIVERED')
                                        ->whereDate('delivered_at', today())
                                        ->sum('cod_amount')
            ];
        })->sortByDesc('deliveries_today')->take(10);

        return view('depot-manager.dashboard', compact('stats', 'gouvernoratStats', 'topDeliverers', 'user'));
    }

    /**
     * Rapports et analyses
     */
    public function reports(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $managedDeliverers = $user->getManagedDeliverers();
        $delivererIds = $managedDeliverers->pluck('id')->toArray();

        $period = $request->get('period', 'week');
        $startDate = match($period) {
            'today' => today(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfWeek()
        };

        // Données pour les graphiques
        $reportData = [
            'deliveries_by_day' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                        ->where('status', 'DELIVERED')
                                        ->where('delivered_at', '>=', $startDate)
                                        ->selectRaw('DATE(delivered_at) as date, COUNT(*) as count')
                                        ->groupBy('date')
                                        ->orderBy('date')
                                        ->get(),
            'cod_by_day' => Package::whereIn('assigned_deliverer_id', $delivererIds)
                                 ->where('status', 'DELIVERED')
                                 ->where('delivered_at', '>=', $startDate)
                                 ->selectRaw('DATE(delivered_at) as date, SUM(cod_amount) as total')
                                 ->groupBy('date')
                                 ->orderBy('date')
                                 ->get(),
            'performance_by_deliverer' => $managedDeliverers->map(function($deliverer) use ($startDate) {
                return [
                    'deliverer' => $deliverer,
                    'deliveries' => Package::where('assigned_deliverer_id', $deliverer->id)
                                         ->where('status', 'DELIVERED')
                                         ->where('delivered_at', '>=', $startDate)
                                         ->count(),
                    'cod_collected' => Package::where('assigned_deliverer_id', $deliverer->id)
                                            ->where('status', 'DELIVERED')
                                            ->where('delivered_at', '>=', $startDate)
                                            ->sum('cod_amount'),
                    'returns' => Package::where('assigned_deliverer_id', $deliverer->id)
                                      ->where('status', 'RETURNED')
                                      ->where('returned_at', '>=', $startDate)
                                      ->count()
                ];
            })->sortByDesc('deliveries')
        ];

        return view('depot-manager.reports.index', compact('reportData', 'period', 'user'));
    }

    /**
     * Consulter TOUS les colis (pas seulement ceux du gouvernorat)
     */
    public function allPackages(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $query = Package::with(['assignedDeliverer', 'sender', 'delegationFrom', 'delegationTo']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gouvernorat')) {
            $query->whereHas('assignedDeliverer', function($q) use ($request) {
                $q->where('assigned_delegation', $request->gouvernorat);
            });
        }

        if ($request->filled('deliverer_id')) {
            $query->where('assigned_deliverer_id', $request->deliverer_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('package_code', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('recipient_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('recipient_phone', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $packages = $query->orderBy('created_at', 'desc')->paginate(25);

        // Statistiques générales
        $stats = [
            'total' => Package::count(),
            'in_progress' => Package::whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])->count(),
            'delivered_today' => Package::where('status', 'DELIVERED')->whereDate('delivered_at', today())->count(),
            'urgent' => Package::whereIn('status', ['PICKED_UP', 'UNAVAILABLE'])->where('delivery_attempts', '>=', 3)->count()
        ];

        // Tous les livreurs pour les filtres
        $allDeliverers = User::where('role', 'DELIVERER')->orderBy('first_name')->get();

        return view('depot-manager.packages.all-packages', compact('packages', 'stats', 'allDeliverers', 'user'));
    }

    /**
     * Interface dédiée pour les retours et échanges
     */
    public function returnsExchanges(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $query = Package::with(['assignedDeliverer', 'sender', 'delegationFrom', 'delegationTo'])
                        ->whereIn('status', ['RETURNED', 'EXCHANGE_REQUESTED', 'EXCHANGE_PROCESSED']);

        // Filtres
        if ($request->filled('type')) {
            if ($request->type === 'returns') {
                $query->where('status', 'RETURNED');
            } elseif ($request->type === 'exchanges') {
                $query->whereIn('status', ['EXCHANGE_REQUESTED', 'EXCHANGE_PROCESSED']);
            }
        }

        if ($request->filled('gouvernorat')) {
            $query->whereHas('assignedDeliverer', function($q) use ($request) {
                $q->where('assigned_delegation', $request->gouvernorat);
            });
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('package_code', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('recipient_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('recipient_phone', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('returned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('returned_at', '<=', $request->date_to);
        }

        $packages = $query->orderBy('returned_at', 'desc')->paginate(25);

        // Statistiques pour retours/échanges
        $stats = [
            'total_returns' => Package::where('status', 'RETURNED')->count(),
            'total_exchanges' => Package::whereIn('status', ['EXCHANGE_REQUESTED', 'EXCHANGE_PROCESSED'])->count(),
            'returns_today' => Package::where('status', 'RETURNED')->whereDate('returned_at', today())->count(),
            'exchanges_pending' => Package::where('status', 'EXCHANGE_REQUESTED')->count()
        ];

        return view('depot-manager.packages.returns-exchanges', compact('packages', 'stats', 'user'));
    }

    /**
     * Traiter un retour
     */
    public function processReturn(Request $request, Package $package)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $request->validate([
            'return_reason' => 'required|string|max:500',
            'return_action' => 'required|in:return_to_sender,process_exchange,dispose'
        ]);

        $package->update([
            'return_reason' => $request->return_reason,
            'return_processed_at' => now(),
            'return_processed_by' => $user->id,
            'status' => $request->return_action === 'process_exchange' ? 'EXCHANGE_PROCESSED' : 'RETURNED'
        ]);

        return back()->with('success', 'Retour traité avec succès.');
    }

    /**
     * Imprimer le bon de livraison des retours
     */
    public function printReturnReceipt(Package $package)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        if (!in_array($package->status, ['RETURNED', 'EXCHANGE_PROCESSED'])) {
            abort(404, 'Ce colis n\'est pas un retour ou échange.');
        }

        return view('depot-manager.packages.return-receipt', compact('package', 'user'));
    }

    /**
     * Marquer un échange comme traité
     */
    public function processExchange(Request $request, Package $package)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $request->validate([
            'exchange_notes' => 'nullable|string|max:500',
            'new_package_code' => 'nullable|string|max:50'
        ]);

        $package->update([
            'status' => 'EXCHANGE_PROCESSED',
            'exchange_processed_at' => now(),
            'exchange_processed_by' => $user->id,
            'exchange_notes' => $request->exchange_notes,
            'new_package_code' => $request->new_package_code
        ]);

        return back()->with('success', 'Échange traité avec succès.');
    }

    /**
     * Interface retours fournisseur - Tous les colis avec statut RETURNED
     */
    public function supplierReturns(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        // Récupérer les livreurs gérés par ce chef dépôt
        $managedDeliverers = $user->getManagedDeliverers();
        $delivererIds = $managedDeliverers->pluck('id')->toArray();

        $query = Package::with(['sender', 'assignedDeliverer', 'pickupAddress', 'pickupDelegation', 'delegationFrom', 'delegationTo'])
                        ->where('status', 'RETURNED')
                        ->whereNotNull('returned_at')
                        // Filtrer uniquement les colis assignés aux livreurs de ce dépôt
                        ->whereIn('assigned_deliverer_id', $delivererIds);

        // Filtres
        if ($request->filled('processed')) {
            if ($request->processed === 'yes') {
                $query->whereNotNull('return_processed_at');
            } else {
                $query->whereNull('return_processed_at');
            }
        }

        if ($request->filled('gouvernorat')) {
            $query->whereHas('assignedDeliverer', function($q) use ($request) {
                $q->where('assigned_delegation', $request->gouvernorat);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('returned_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('returned_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('package_code', 'LIKE', '%' . $request->search . '%')
                  ->orWhereHas('sender', function($sq) use ($request) {
                      $sq->where('first_name', 'LIKE', '%' . $request->search . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $request->search . '%');
                  });
            });
        }

        $packages = $query->orderBy('returned_at', 'desc')->paginate(25);

        // Statistiques filtrées par dépôt
        $stats = [
            'total_returns' => Package::where('status', 'RETURNED')
                                     ->whereIn('assigned_deliverer_id', $delivererIds)
                                     ->count(),
            'unprocessed' => Package::where('status', 'RETURNED')
                                   ->whereIn('assigned_deliverer_id', $delivererIds)
                                   ->whereNull('return_processed_at')
                                   ->count(),
            'processed_today' => Package::where('status', 'RETURNED')
                                       ->whereIn('assigned_deliverer_id', $delivererIds)
                                       ->whereNotNull('return_processed_at')
                                       ->whereDate('return_processed_at', today())
                                       ->count(),
            'returns_today' => Package::where('status', 'RETURNED')
                                     ->whereIn('assigned_deliverer_id', $delivererIds)
                                     ->whereDate('returned_at', today())
                                     ->count()
        ];

        return view('depot-manager.packages.supplier-returns', compact('packages', 'stats', 'user'));
    }

    /**
     * Interface scanner code-barres pour traitement en lot
     */
    public function batchScanner(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        return view('depot-manager.packages.batch-scanner', compact('user'));
    }

    /**
     * Traiter les colis scannés en lot
     */
    public function processBatchScan(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $request->validate([
            'package_codes' => 'required|array|min:1',
            'package_codes.*' => 'required|string',
            'action' => 'required|in:return_to_supplier,mark_processed',
            'batch_notes' => 'nullable|string|max:1000'
        ]);

        $packageCodes = $request->package_codes;

        // Récupérer les livreurs gérés par ce chef dépôt
        $managedDeliverers = $user->getManagedDeliverers();
        $delivererIds = $managedDeliverers->pluck('id')->toArray();

        $packages = Package::whereIn('package_code', $packageCodes)
                         ->whereIn('assigned_deliverer_id', $delivererIds)
                         ->get();

        $processed = 0;
        $errors = [];

        foreach ($packages as $package) {
            try {
                if ($request->action === 'return_to_supplier') {
                    $package->update([
                        'return_processed_at' => now(),
                        'return_processed_by' => $user->id,
                        'return_reason' => $request->batch_notes ?? 'Traitement en lot - Retour fournisseur',
                        'status' => 'RETURNED'
                    ]);
                } elseif ($request->action === 'mark_processed') {
                    $package->update([
                        'return_processed_at' => now(),
                        'return_processed_by' => $user->id
                    ]);
                }
                $processed++;
            } catch (\Exception $e) {
                $errors[] = "Erreur pour {$package->package_code}: " . $e->getMessage();
            }
        }

        $notFound = array_diff($packageCodes, $packages->pluck('package_code')->toArray());
        foreach ($notFound as $code) {
            $errors[] = "Colis non trouvé: {$code}";
        }

        $message = "{$processed} colis traités avec succès.";
        if (!empty($errors)) {
            $message .= " Erreurs: " . implode(', ', $errors);
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'processed' => $processed,
            'errors' => $errors
        ]);
    }

    /**
     * Impression groupée des bons de livraison retours
     */
    public function printBatchReturns(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $request->validate([
            'package_ids' => 'required|array|min:1',
            'package_ids.*' => 'required|integer|exists:packages,id'
        ]);

        // Récupérer les livreurs gérés par ce chef dépôt pour vérification
        $managedDeliverers = $user->getManagedDeliverers();
        $delivererIds = $managedDeliverers->pluck('id')->toArray();

        $packages = Package::with(['sender', 'assignedDeliverer', 'delegationFrom', 'delegationTo', 'pickupAddress', 'pickupDelegation'])
                          ->whereIn('id', $request->package_ids)
                          ->where('status', 'RETURNED')
                          // Vérifier que les colis appartiennent à ce dépôt
                          ->whereIn('assigned_deliverer_id', $delivererIds)
                          ->get();

        if ($packages->isEmpty()) {
            return back()->with('error', 'Aucun colis retourné trouvé.');
        }

        return view('depot-manager.packages.batch-return-receipt', compact('packages', 'user'));
    }

    /**
     * Bon de livraison pour partie retour des échanges
     */
    public function printExchangeReturnReceipt(Package $package)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        if (!in_array($package->status, ['EXCHANGE_REQUESTED', 'EXCHANGE_PROCESSED'])) {
            abort(404, 'Ce colis n\'est pas un échange.');
        }

        // Charger les relations pickup
        $package->load(['pickupAddress', 'pickupDelegation', 'delegationFrom', 'delegationTo', 'sender', 'assignedDeliverer']);

        return view('depot-manager.packages.exchange-return-receipt', compact('package', 'user'));
    }

    /**
     * Tableau de bord des actions requises
     */
    public function dashboardActions()
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        // Récupérer les livreurs gérés par ce chef dépôt
        $managedDeliverers = $user->getManagedDeliverers();
        $delivererIds = $managedDeliverers->pluck('id')->toArray();

        // Colis en attente de retour (statut "Préparation de Retour")
        $pendingReturns = Package::with(['assignedDeliverer', 'sender', 'delegationFrom', 'delegationTo'])
                                ->whereIn('assigned_deliverer_id', $delivererIds)
                                ->where('status', 'PREPARATION_RETOUR')
                                ->orderBy('updated_at', 'desc')
                                ->get();

        return view('depot-manager.dashboard-actions', compact('pendingReturns', 'user'));
    }

    /**
     * Rechercher un colis d'échange par code
     */
    public function searchExchange(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé']);
        }

        $request->validate([
            'package_code' => 'required|string'
        ]);

        $package = Package::where('package_code', $request->package_code)
                         ->where('est_echange', true)
                         ->where('status', 'DELIVERED') // Doit être livré pour pouvoir traiter l'échange
                         ->with(['sender', 'delegationFrom', 'delegationTo'])
                         ->first();

        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Colis non trouvé ou non éligible pour échange'
            ]);
        }

        return response()->json([
            'success' => true,
            'package' => [
                'id' => $package->id,
                'package_code' => $package->package_code,
                'content_description' => $package->content_description,
                'cod_amount' => number_format($package->cod_amount, 3),
                'recipient_data' => $package->recipient_data,
                'sender' => $package->sender
            ]
        ]);
    }

    /**
     * Générer étiquette de retour pour échange
     */
    public function generateExchangeLabel(Package $package)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        if (!$package->est_echange || $package->status !== 'DELIVERED') {
            abort(404, 'Ce colis n\'est pas éligible pour générer une étiquette d\'échange.');
        }

        // Créer un nouveau colis de retour pour l'échange
        $returnPackage = new Package();
        $returnPackage->package_code = 'EXG-' . now()->format('YmdHis') . '-' . rand(1000, 9999);
        $returnPackage->sender_id = $package->sender_id;
        $returnPackage->sender_data = $package->sender_data;
        $returnPackage->delegation_from = $package->delegation_to; // Inversion pour retour
        $returnPackage->delegation_to = $package->delegation_from; // Inversion pour retour
        $returnPackage->recipient_data = $package->sender_data; // Retour à l'expéditeur
        $returnPackage->content_description = 'RETOUR ÉCHANGE - ' . $package->content_description;
        $returnPackage->cod_amount = 0; // Pas de COD pour retour d'échange
        $returnPackage->delivery_fee = $package->return_fee ?? 0;
        $returnPackage->return_fee = 0;
        $returnPackage->status = 'CREATED';
        $returnPackage->est_echange = false; // Le retour n'est plus un échange
        $returnPackage->notes = 'Retour d\'échange généré depuis: ' . $package->package_code;
        $returnPackage->original_exchange_id = $package->id;
        $returnPackage->save();

        // Générer le bon de livraison pour ce retour d'échange
        return view('depot-manager.packages.exchange-label', compact('returnPackage', 'package', 'user'));
    }

    /**
     * Obtenir les détails d'un colis
     */
    public function packageDetails(Package $package)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé']);
        }

        return response()->json([
            'id' => $package->id,
            'package_code' => $package->package_code,
            'content_description' => $package->content_description,
            'cod_amount' => $package->cod_amount,
            'recipient_data' => $package->recipient_data,
            'delivery_attempts' => $package->delivery_attempts ?? 0,
            'status' => $package->status
        ]);
    }

    /**
     * Traiter un retour individuel depuis le dashboard
     */
    public function processReturnFromDashboard(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé']);
        }

        $request->validate([
            'package_id' => 'required|exists:packages,id'
        ]);

        $package = Package::findOrFail($request->package_id);

        // Vérifier que le colis est en attente de retour
        if ($package->status !== 'PREPARATION_RETOUR') {
            return response()->json([
                'success' => false,
                'message' => 'Ce colis n\'est pas en attente de retour'
            ]);
        }

        // Mettre à jour le statut
        $package->update([
            'status' => 'RETURNED',
            'returned_at' => now(),
            'return_processed_by' => $user->id,
            'return_processed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Retour traité avec succès',
            'print_url' => route('depot-manager.packages.return-receipt', $package->id)
        ]);
    }

    /**
     * Traiter tous les retours en attente
     */
    public function processAllReturns()
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé']);
        }

        // Récupérer les livreurs gérés
        $managedDeliverers = $user->getManagedDeliverers();
        $delivererIds = $managedDeliverers->pluck('id')->toArray();

        $packages = Package::whereIn('assigned_deliverer_id', $delivererIds)
                          ->where('status', 'PREPARATION_RETOUR')
                          ->get();

        $processed = 0;

        foreach ($packages as $package) {
            $package->update([
                'status' => 'RETURNED',
                'returned_at' => now(),
                'return_processed_by' => $user->id,
                'return_processed_at' => now()
            ]);
            $processed++;
        }

        return response()->json([
            'success' => true,
            'processed' => $processed,
            'message' => "$processed retours traités avec succès"
        ]);
    }

    /**
     * Afficher l'étiquette d'échange pour impression
     */
    public function printExchangeLabel(Package $returnPackage)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que c'est bien un colis de retour d'échange
        if ($returnPackage->type !== 'return' || $returnPackage->return_type !== 'exchange') {
            abort(404, 'Étiquette non trouvée');
        }

        // Récupérer le colis original
        $package = Package::find($returnPackage->original_package_id);

        if (!$package) {
            abort(404, 'Colis original non trouvé');
        }

        return view('depot-manager.packages.exchange-label', compact('returnPackage', 'package', 'user'));
    }

    // ==================== SYSTÈME BOÎTES DE TRANSIT ====================

    /**
     * Vue d'ensemble du système de boîtes de transit
     */
    public function cratesIndex()
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès non autorisé');
        }

        // Statistiques des boîtes
        $stats = [
            'boxes_in_preparation' => 12,
            'boxes_sealed_today' => 8,
            'boxes_in_transit' => 15,
            'total_packages_sorted' => 156
        ];

        return view('depot-manager.crates.index', compact('stats'));
    }

    /**
     * Interface principale de gestion des boîtes
     */
    public function boxManager()
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès non autorisé');
        }

        // Données simulées pour les 24 gouvernorats
        $governorates = [
            'TUNIS', 'SFAX', 'SOUSSE', 'KAIROUAN', 'BIZERTE', 'GABES',
            'ARIANA', 'MANOUBA', 'NABEUL', 'ZAGHOUAN', 'BEJA', 'JENDOUBA',
            'KASSE', 'SILIANA', 'KEBILI', 'TOZEUR', 'GAFSA', 'SIDI',
            'MEDENINE', 'TATAOUINE', 'MAHDIA', 'MONASTIR', 'KASER', 'BENARO'
        ];

        $boxes = [];
        foreach ($governorates as $gov) {
            $boxes[] = [
                'governorate' => $gov,
                'code' => $gov . '-TUN-' . date('dmY') . '-01',
                'packageCount' => rand(0, 20),
                'isSealed' => rand(0, 1) ? true : false,
                'packages' => []
            ];
        }

        return view('depot-manager.crates.box-manager', compact('boxes', 'user'));
    }

    /**
     * Scanner un colis pour l'ajouter à une boîte
     */
    public function scanPackageForBox(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé']);
        }

        $request->validate([
            'package_code' => 'required|string'
        ]);

        try {
            $packageCode = $request->package_code;

            // Simulation de la logique de destination
            $governorates = ['SFAX', 'SOUSSE', 'BIZERTE', 'GABES', 'ARIANA', 'NABEUL', 'MONASTIR'];
            $hash = 0;
            for ($i = 0; $i < strlen($packageCode); $i++) {
                $hash += ord($packageCode[$i]);
            }
            $destinationGovernorate = $governorates[$hash % count($governorates)];

            return response()->json([
                'success' => true,
                'destination' => $destinationGovernorate,
                'message' => "Colis {$packageCode} assigné à la boîte {$destinationGovernorate}",
                'scan_time' => now()->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du scan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sceller une boîte et générer le bon de transit
     */
    public function sealBox(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé']);
        }

        $request->validate([
            'box_code' => 'required|string',
            'governorate' => 'required|string',
            'package_count' => 'required|integer|min:1'
        ]);

        try {
            $boxCode = $request->box_code;

            // Générer l'URL du bon de boîte
            $receiptUrl = route('depot-manager.crates.box-receipt', $boxCode);

            return response()->json([
                'success' => true,
                'message' => "Boîte {$request->governorate} scellée avec succès",
                'receipt_url' => $receiptUrl,
                'box_code' => $boxCode
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du scellage: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Générer le bon de boîte pour impression
     */
    public function generateBoxReceipt($boxCode)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès non autorisé');
        }

        // Extraire les informations du code de boîte
        $parts = explode('-', $boxCode);
        if (count($parts) < 4) {
            abort(404, 'Code de boîte invalide');
        }

        $governorate = $parts[0];
        $origin = $parts[1];
        $date = $parts[2];
        $sequence = $parts[3];

        // Données simulées pour le bon
        $boxData = [
            'code' => $boxCode,
            'governorate' => $governorate,
            'origin_depot' => 'Dépôt Central ' . $origin,
            'package_count' => rand(10, 30),
            'creation_date' => date('d/m/Y'),
            'creation_time' => date('H:i:s'),
            'generated_by' => $user->name,
            'packages' => []
        ];

        // Générer des colis simulés
        for ($i = 1; $i <= $boxData['package_count']; $i++) {
            $boxData['packages'][] = [
                'code' => 'PKG' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'scan_time' => date('H:i:s', strtotime("-{$i} minutes"))
            ];
        }

        return view('depot-manager.crates.box-receipt', compact('boxData'));
    }

    /**
     * Réceptionner une boîte depuis un autre dépôt
     */
    public function receiveBox(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé']);
        }

        $request->validate([
            'box_code' => 'required|string'
        ]);

        try {
            $boxCode = $request->box_code;

            // Extraire les informations du code
            $parts = explode('-', $boxCode);
            if (count($parts) >= 4) {
                $governorate = $parts[0];
                $packageCount = rand(10, 30);

                return response()->json([
                    'success' => true,
                    'governorate' => $governorate,
                    'package_count' => $packageCount,
                    'message' => "Boîte de {$governorate} reçue avec {$packageCount} colis",
                    'reception_time' => now()->format('H:i')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Code de boîte invalide'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réception: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtenir les détails d'une boîte
     */
    public function getBoxDetails($boxId)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé']);
        }

        // Données simulées
        $boxDetails = [
            'id' => $boxId,
            'code' => 'SFAX-TUN-' . date('dmY') . '-01',
            'governorate' => 'SFAX',
            'package_count' => rand(5, 25),
            'status' => rand(0, 1) ? 'Scellée' : 'En préparation',
            'packages' => []
        ];

        // Générer des colis simulés
        for ($i = 1; $i <= $boxDetails['package_count']; $i++) {
            $boxDetails['packages'][] = [
                'code' => 'PKG' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'scan_time' => date('H:i:s', strtotime("-{$i} minutes"))
            ];
        }

        return response()->json([
            'success' => true,
            'box' => $boxDetails
        ]);
    }
}