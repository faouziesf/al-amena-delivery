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
                               $q->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE']);
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
                                           ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
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
                                 ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE'])
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
            'assigned_delegation' => 'required|string|in:' . implode(',', $user->assigned_gouvernorats_array),
            'address' => 'nullable|string|max:500',
            'deliverer_type' => 'required|string|in:STANDARD,EXPRESS,HEAVY',
        ]);

        DB::transaction(function () use ($request, $user) {
            $deliverer = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'DELIVERER',
                'assigned_delegation' => $request->assigned_delegation,
                'address' => $request->address,
                'deliverer_type' => $request->deliverer_type,
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

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $deliverer->id,
            'phone' => 'required|string|max:20',
            'assigned_delegation' => 'required|string|in:' . implode(',', $user->assigned_gouvernorats_array),
            'address' => 'nullable|string|max:500',
            'deliverer_type' => 'required|string|in:STANDARD,EXPRESS,HEAVY',
            'account_status' => 'required|string|in:ACTIVE,SUSPENDED',
        ]);

        $deliverer->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'assigned_delegation' => $request->assigned_delegation,
            'address' => $request->address,
            'deliverer_type' => $request->deliverer_type,
            'account_status' => $request->account_status,
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
                                  ->whereIn('status', ['ACCEPTED', 'PICKED_UP', 'UNAVAILABLE']);

            if ($request->filled('package_ids')) {
                $packageQuery->whereIn('id', $request->package_ids);
            }

            $packages = $packageQuery->get();

            foreach ($packages as $package) {
                $package->update([
                    'assigned_deliverer_id' => $newDeliverer->id,
                    'status' => 'ACCEPTED' // Reset status for new deliverer
                ]);
            }
        });

        return back()->with('success', 'Colis réassignés avec succès.');
    }

    /**
     * Ajouter des fonds au wallet d'un livreur
     */
    public function addFunds(Request $request, User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas gérer ce livreur.'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.001|max:10000',
            'description' => 'required|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $deliverer, $user) {
                $wallet = $deliverer->ensureWallet();
                $wallet->addFunds($request->amount, $user->id, $request->description);
            });

            return response()->json([
                'success' => true,
                'message' => "Fonds ajoutés avec succès au wallet de {$deliverer->name}",
                'balance' => $deliverer->fresh()->wallet->balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout des fonds: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Déduire des fonds du wallet d'un livreur
     */
    public function deductFunds(Request $request, User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas gérer ce livreur.'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.001',
            'description' => 'required|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $deliverer, $user) {
                $wallet = $deliverer->ensureWallet();

                if ($wallet->balance < $request->amount) {
                    throw new \Exception('Solde insuffisant pour cette déduction');
                }

                $wallet->deductFunds($request->amount, $user->id, $request->description);
            });

            return response()->json([
                'success' => true,
                'message' => "Fonds déduits avec succès du wallet de {$deliverer->name}",
                'balance' => $deliverer->fresh()->wallet->balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la déduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter une avance au wallet d'un livreur
     */
    public function addAdvance(Request $request, User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas gérer ce livreur.'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.001|max:1000',
            'description' => 'required|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $deliverer, $user) {
                $wallet = $deliverer->ensureWallet();
                $wallet->addAdvance($request->amount, $user->id, $request->description);
            });

            return response()->json([
                'success' => true,
                'message' => "Avance accordée avec succès à {$deliverer->name}",
                'advance_balance' => $deliverer->fresh()->wallet->advance_balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de l\'avance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retirer une avance du wallet d'un livreur
     */
    public function removeAdvance(Request $request, User $deliverer)
    {
        $user = Auth::user();

        if (!$user->canManageGouvernorat($deliverer->assigned_delegation)) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas gérer ce livreur.'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.001',
            'description' => 'required|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $deliverer, $user) {
                $wallet = $deliverer->ensureWallet();

                if ($wallet->advance_balance < $request->amount) {
                    throw new \Exception('Avance insuffisante pour cette opération');
                }

                $wallet->removeAdvance($request->amount, $user->id, $request->description);
            });

            return response()->json([
                'success' => true,
                'message' => "Avance retirée avec succès à {$deliverer->name}",
                'advance_balance' => $deliverer->fresh()->wallet->advance_balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait de l\'avance: ' . $e->getMessage()
            ], 500);
        }
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