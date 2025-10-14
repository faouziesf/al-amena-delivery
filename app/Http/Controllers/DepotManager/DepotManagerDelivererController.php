<?php

namespace App\Http\Controllers\DepotManager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Package;

class DepotManagerDelivererController extends Controller
{
    /**
     * Liste des livreurs du dépôt
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $query = User::where('role', 'DELIVERER')
                    ->where('deliverer_type', 'DELEGATION') // Chef dépôt ne gère que les livreurs DELEGATION
                    ->whereIn('assigned_delegation', $user->assigned_gouvernorats_array);

        // Filtres
        if ($request->filled('gouvernorat')) {
            $query->where('assigned_delegation', $request->gouvernorat);
        }

        if ($request->filled('status')) {
            $query->where('account_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('phone', 'LIKE', '%' . $request->search . '%');
            });
        }

        $deliverers = $query->with(['assignedPackages' => function($q) {
                               $q->whereIn('status', ['OUT_FOR_DELIVERY', 'UNAVAILABLE']);
                           }])
                           ->orderBy('account_status')
                           ->orderBy('assigned_delegation')
                           ->orderBy('name')
                           ->paginate(20);

        return view('depot-manager.deliverers.index', compact('deliverers', 'user'));
    }

    /**
     * Afficher les détails d'un livreur
     */
    public function show(User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            abort(403, 'Vous ne pouvez pas gérer ce livreur.');
        }

        // Statistiques du livreur
        $stats = [
            'packages_in_progress' => Package::where('assigned_deliverer_id', $deliverer->id)
                                           ->whereIn('status', ['OUT_FOR_DELIVERY', 'UNAVAILABLE'])
                                           ->count(),
            'delivered_this_month' => Package::where('assigned_deliverer_id', $deliverer->id)
                                           ->where('status', 'DELIVERED')
                                           ->whereMonth('delivered_at', now()->month)
                                           ->count(),
            'delivered_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                                      ->where('status', 'DELIVERED')
                                      ->whereDate('delivered_at', today())
                                      ->count(),
            'cod_collected_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                                          ->where('status', 'DELIVERED')
                                          ->whereDate('delivered_at', today())
                                          ->sum('cod_amount'),
            'returns_this_month' => Package::where('assigned_deliverer_id', $deliverer->id)
                                         ->where('status', 'RETURNED')
                                         ->whereMonth('returned_at', now()->month)
                                         ->count()
        ];

        // Colis actuels
        $currentPackages = Package::where('assigned_deliverer_id', $deliverer->id)
                                 ->whereIn('status', ['OUT_FOR_DELIVERY', 'UNAVAILABLE'])
                                 ->with(['sender', 'delegationFrom', 'delegationTo'])
                                 ->orderBy('created_at', 'desc')
                                 ->get();

        // Historique récent
        $recentHistory = Package::where('assigned_deliverer_id', $deliverer->id)
                               ->whereIn('status', ['DELIVERED', 'RETURNED'])
                               ->with(['sender', 'delegationFrom', 'delegationTo'])
                               ->orderBy('updated_at', 'desc')
                               ->limit(20)
                               ->get();

        return view('depot-manager.deliverers.show', compact('deliverer', 'stats', 'currentPackages', 'recentHistory'));
    }

    /**
     * Formulaire de création d'un nouveau livreur
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        return view('depot-manager.deliverers.create', compact('user'));
    }

    /**
     * Créer un nouveau livreur
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'deliverer_gouvernorats' => 'required|array|min:1',
            'deliverer_gouvernorats.*' => 'required|string|in:' . implode(',', $user->assigned_gouvernorats_array),
            'address' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $user) {
            // Utiliser le premier gouvernorat comme assigned_delegation (pour compatibilité)
            $firstGouvernorat = $request->deliverer_gouvernorats[0];
            
            $deliverer = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'DELIVERER',
                'assigned_delegation' => $firstGouvernorat,
                'deliverer_gouvernorats' => $request->deliverer_gouvernorats,
                'address' => $request->address,
                'deliverer_type' => 'DELEGATION', // Chef dépôt ne peut créer que des livreurs DELEGATION
                'account_status' => 'ACTIVE',
                'verified_at' => now(),
                'verified_by' => $user->id,
                'created_by' => $user->id,
            ]);

            // Créer automatiquement le wallet
            $deliverer->ensureWallet();
        });

        return redirect()->route('depot-manager.deliverers.index')
                        ->with('success', 'Livreur créé avec succès.');
    }

    /**
     * Formulaire d'édition d'un livreur
     */
    public function edit(User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            abort(403, 'Vous ne pouvez pas modifier ce livreur.');
        }

        // Chef dépôt ne peut modifier que les livreurs DELEGATION
        if ($deliverer->deliverer_type !== 'DELEGATION') {
            abort(403, 'Vous ne pouvez modifier que les livreurs de délégation.');
        }

        return view('depot-manager.deliverers.edit', compact('deliverer', 'user'));
    }

    /**
     * Mettre à jour un livreur
     */
    public function update(Request $request, User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            abort(403, 'Vous ne pouvez pas modifier ce livreur.');
        }

        // Chef dépôt ne peut modifier que les livreurs DELEGATION
        if ($deliverer->deliverer_type !== 'DELEGATION') {
            abort(403, 'Vous ne pouvez modifier que les livreurs de délégation.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $deliverer->id,
            'phone' => 'required|string|max:20',
            'deliverer_gouvernorats' => 'required|array|min:1',
            'deliverer_gouvernorats.*' => 'required|string|in:' . implode(',', $user->assigned_gouvernorats_array),
            'address' => 'nullable|string|max:500',
            'account_status' => 'required|string|in:ACTIVE,SUSPENDED',
        ]);

        // Utiliser le premier gouvernorat comme assigned_delegation (pour compatibilité)
        $firstGouvernorat = $request->deliverer_gouvernorats[0];

        $deliverer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'assigned_delegation' => $firstGouvernorat,
            'deliverer_gouvernorats' => $request->deliverer_gouvernorats,
            'address' => $request->address,
            'account_status' => $request->account_status,
            // deliverer_type reste DELEGATION et n'est pas modifiable par chef dépôt
        ]);

        return redirect()->route('depot-manager.deliverers.show', $deliverer)
                        ->with('success', 'Livreur mis à jour avec succès.');
    }

    /**
     * Suspendre/Activer un livreur
     */
    public function toggleStatus(User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            abort(403, 'Vous ne pouvez pas modifier ce livreur.');
        }

        $newStatus = $deliverer->account_status === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
        $deliverer->update(['account_status' => $newStatus]);

        $action = $newStatus === 'ACTIVE' ? 'activé' : 'suspendu';

        return back()->with('success', "Livreur {$action} avec succès.");
    }

    /**
     * Réassigner les colis d'un livreur à un autre
     */
    public function reassignPackages(Request $request, User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            abort(403, 'Vous ne pouvez pas gérer ce livreur.');
        }

        $request->validate([
            'new_deliverer_id' => 'required|exists:users,id',
            'package_ids' => 'nullable|array',
            'package_ids.*' => 'exists:packages,id'
        ]);

        $newDeliverer = User::findOrFail($request->new_deliverer_id);

        if (!$user->canManageGouvernorat($newDeliverer->assigned_delegation)) {
            abort(403, 'Vous ne pouvez pas assigner à ce livreur.');
        }

        DB::transaction(function () use ($request, $deliverer, $newDeliverer) {
            $packageQuery = Package::where('assigned_deliverer_id', $deliverer->id)
                                  ->whereIn('status', ['OUT_FOR_DELIVERY', 'UNAVAILABLE']);

            if ($request->filled('package_ids')) {
                $packageQuery->whereIn('id', $request->package_ids);
            }

            $packages = $packageQuery->get();

            foreach ($packages as $package) {
                $package->update([
                    'assigned_deliverer_id' => $newDeliverer->id,
                    'reassigned_at' => now(),
                    'reassigned_by' => $deliverer->id,
                    'reassignment_reason' => 'Réassignation par chef de dépôt'
                    // Le statut reste OUT_FOR_DELIVERY
                ]);
                
                // Enregistrer dans l'historique
                $package->statusHistory()->create([
                    'status' => 'OUT_FOR_DELIVERY',
                    'changed_by' => auth()->id(),
                    'notes' => "Réassigné de {$deliverer->name} à {$newDeliverer->name}",
                    'created_at' => now()
                ]);
            }
        });

        return back()->with('success', 'Colis réassignés avec succès.');
    }

    /**
     * Vider le wallet d'un livreur (transfert vers wallet chef dépôt)
     * SEULE OPÉRATION AUTORISÉE pour le chef dépôt sur les wallets livreurs
     */
    public function emptyDelivererWallet(Request $request, User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas gérer ce livreur.'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $deliverer, $user) {
                $wallet = $deliverer->ensureWallet();
                $amount = $request->amount;

                if ($wallet->balance < $amount) {
                    throw new \Exception('Le solde du livreur est insuffisant pour ce vidage.');
                }

                // Déduire du wallet du livreur
                $wallet->deductFunds($amount, $user->id, 'Vidage wallet par chef dépôt: ' . ($request->notes ?? ''));

                // Ajouter au wallet du chef dépôt
                $user->addToDepotWallet($amount, $deliverer->id, $request->notes);

                // Enregistrer dans deliverer_wallet_emptyings
                DB::table('deliverer_wallet_emptyings')->insert([
                    'deliverer_id' => $deliverer->id,
                    'depot_manager_id' => $user->id,
                    'amount' => $amount,
                    'notes' => $request->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => "Wallet de {$deliverer->name} vidé avec succès. {$request->amount} DT ajoutés à votre caisse.",
                'deliverer_balance' => $deliverer->fresh()->wallet->balance,
                'depot_wallet_balance' => $user->fresh()->depot_wallet_balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher les statistiques des livreurs
     */
    public function stats(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            abort(403, 'Accès réservé aux chefs dépôt.');
        }

        // Obtenir tous les livreurs gérés
        $deliverers = User::where('role', 'DELIVERER')
                         ->where('deliverer_type', 'DELEGATION')
                         ->whereIn('assigned_delegation', $user->assigned_gouvernorats_array)
                         ->with('wallet')
                         ->get();

        // Calculer les statistiques pour chaque livreur
        $delivererStats = $deliverers->map(function ($deliverer) {
            $today = now()->startOfDay();
            $thisMonth = now()->startOfMonth();

            return [
                'deliverer' => $deliverer,
                'delivered_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                                          ->where('status', 'DELIVERED')
                                          ->where('delivered_at', '>=', $today)
                                          ->count(),
                'delivered_this_month' => Package::where('assigned_deliverer_id', $deliverer->id)
                                               ->where('status', 'DELIVERED')
                                               ->where('delivered_at', '>=', $thisMonth)
                                               ->count(),
                'cod_collected_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                                              ->where('status', 'DELIVERED')
                                              ->where('delivered_at', '>=', $today)
                                              ->sum('cod_amount'),
                'cod_collected_this_month' => Package::where('assigned_deliverer_id', $deliverer->id)
                                                   ->where('status', 'DELIVERED')
                                                   ->where('delivered_at', '>=', $thisMonth)
                                                   ->sum('cod_amount'),
                'returns_today' => Package::where('assigned_deliverer_id', $deliverer->id)
                                        ->where('status', 'RETURNED')
                                        ->where('updated_at', '>=', $today)
                                        ->count(),
                'returns_this_month' => Package::where('assigned_deliverer_id', $deliverer->id)
                                             ->where('status', 'RETURNED')
                                             ->where('updated_at', '>=', $thisMonth)
                                             ->count(),
                'in_progress' => Package::where('assigned_deliverer_id', $deliverer->id)
                                      ->whereIn('status', ['OUT_FOR_DELIVERY', 'UNAVAILABLE'])
                                      ->count(),
                'wallet_balance' => $deliverer->wallet ? $deliverer->wallet->balance : 0,
            ];
        });

        // Statistiques globales
        $globalStats = [
            'total_deliverers' => $deliverers->count(),
            'active_deliverers' => $deliverers->where('account_status', 'ACTIVE')->count(),
            'total_delivered_today' => $delivererStats->sum('delivered_today'),
            'total_delivered_this_month' => $delivererStats->sum('delivered_this_month'),
            'total_cod_today' => $delivererStats->sum('cod_collected_today'),
            'total_cod_this_month' => $delivererStats->sum('cod_collected_this_month'),
            'total_returns_today' => $delivererStats->sum('returns_today'),
            'total_returns_this_month' => $delivererStats->sum('returns_this_month'),
            'total_in_progress' => $delivererStats->sum('in_progress'),
        ];

        return view('depot-manager.deliverers.stats', compact('delivererStats', 'globalStats', 'user'));
    }

    /**
     * API pour récupérer les livreurs disponibles
     */
    public function apiAvailable(Request $request)
    {
        $user = Auth::user();

        if (!$user->isDepotManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            // Obtenir les livreurs gérés par ce dépôt manager
            $managedDeliverers = $user->getManagedDeliverers();

            // Ajouter le nombre de colis actuels pour chaque livreur
            $deliverers = $managedDeliverers->map(function ($deliverer) {
                $packagesCount = Package::where('assigned_deliverer_id', $deliverer->id)
                    ->whereIn('status', ['ACCEPTED', 'PICKED_UP'])
                    ->count();

                return [
                    'id' => $deliverer->id,
                    'name' => $deliverer->name,
                    'delegation_name' => $deliverer->delegationName ?? 'N/A',
                    'packages_count' => $packagesCount,
                    'status' => $deliverer->account_status
                ];
            });

            return response()->json([
                'success' => true,
                'deliverers' => $deliverers->values()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des livreurs: ' . $e->getMessage()
            ], 500);
        }
    }
}