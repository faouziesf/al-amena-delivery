<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Package;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['wallet']);

        // Filtres
        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }


        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'ACTIVE')->count(),
            'pending_users' => User::where('status', 'PENDING')->count(),
            'suspended_users' => User::where('status', 'SUSPENDED')->count(),
            'clients' => User::where('role', 'CLIENT')->count(),
            'deliverers' => User::where('role', 'DELIVERER')->count(),
            'commercials' => User::where('role', 'COMMERCIAL')->count(),
            'depot_managers' => User::where('role', 'DEPOT_MANAGER')->count(),
        ];

        return view('supervisor.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        $delegations = User::getAvailableDelegations();
        $delivererTypes = User::getDelivererTypes();
        $gouvernorats = User::getAvailableDelegations(); // Même structure pour les gouvernorats
        return view('supervisor.users.create', compact('delegations', 'delivererTypes', 'gouvernorats'));
    }

    public function store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:CLIENT,DELIVERER,COMMERCIAL,SUPERVISOR,DEPOT_MANAGER',
            'account_status' => 'required|in:ACTIVE,PENDING,SUSPENDED',
        ];

        // Validation spéciale pour les livreurs
        if ($request->role === 'DELIVERER') {
            $delivererTypes = array_keys(User::getDelivererTypes());
            $validationRules['deliverer_type'] = 'required|in:' . implode(',', $delivererTypes);

            // Validation conditionnelle selon le type de livreur
            if ($request->deliverer_type === 'DELEGATION') {
                $delegations = array_keys(User::getAvailableDelegations());
                $validationRules['assigned_delegation'] = 'required|in:' . implode(',', $delegations);
            } elseif ($request->deliverer_type === 'JOKER') {
                $validationRules['assigned_delegation'] = 'nullable';
            } else { // TRANSIT
                $validationRules['assigned_delegation'] = 'nullable';
            }

            $validationRules['delegation_latitude'] = 'nullable|numeric|between:-90,90';
            $validationRules['delegation_longitude'] = 'nullable|numeric|between:-180,180';
            $validationRules['delegation_radius_km'] = 'nullable|integer|min:1|max:50';
        }

        // Validation spéciale pour les chefs dépôt
        if ($request->role === 'DEPOT_MANAGER') {
            $delegations = array_keys(User::getAvailableDelegations());
            $validationRules['assigned_gouvernorats'] = 'required|array|min:1';
            $validationRules['assigned_gouvernorats.*'] = 'in:' . implode(',', $delegations);
        }

        $request->validate($validationRules);

        try {
            DB::transaction(function () use ($request) {
                $userData = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($request->password),
                    'role' => $request->role,
                    'account_status' => $request->account_status,
                    'verified_at' => now(),
                ];

                // Ajouter les champs spécifiques pour les livreurs
                if ($request->role === 'DELIVERER') {
                    $userData['deliverer_type'] = $request->deliverer_type;
                    $userData['assigned_delegation'] = $request->assigned_delegation;
                    $userData['delegation_latitude'] = $request->delegation_latitude;
                    $userData['delegation_longitude'] = $request->delegation_longitude;
                    $userData['delegation_radius_km'] = $request->delegation_radius_km ?? 10;
                }

                // Ajouter les champs spécifiques pour les chefs dépôt
                if ($request->role === 'DEPOT_MANAGER') {
                    $userData['assigned_gouvernorats'] = json_encode($request->assigned_gouvernorats);
                }

                $user = User::create($userData);

                // Créer le wallet si nécessaire
                if (in_array($user->role, ['CLIENT', 'DELIVERER'])) {
                    $user->ensureWallet();
                }
            });

            return redirect()->route('supervisor.users.index')
                            ->with('success', 'Utilisateur créé avec succès.');
        } catch (\Exception $e) {
            return back()->withInput()
                        ->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    public function show(User $user)
    {
        $user->load(['wallet', 'packages', 'complaints']);

        $stats = [
            'total_packages' => $user->packages()->count(),
            'delivered_packages' => $user->packages()->where('status', 'DELIVERED')->count(),
            'wallet_balance' => $user->wallet->balance ?? 0,
            'total_complaints' => $user->complaints()->count(),
        ];

        $recentActivity = collect();

        // Récupérer les paquets récents
        $recentPackages = $user->packages()
                              ->latest()
                              ->limit(10)
                              ->get()
                              ->map(function ($package) {
                                  return [
                                      'type' => 'package',
                                      'icon' => 'package',
                                      'title' => "Colis #{$package->tracking_number}",
                                      'description' => "Status: {$package->status}",
                                      'date' => $package->created_at,
                                      'url' => route('supervisor.packages.show', $package->id),
                                  ];
                              });

        $recentActivity = $recentActivity->merge($recentPackages);

        // Trier par date
        $recentActivity = $recentActivity->sortByDesc('date')->take(15);

        return view('supervisor.users.show', compact('user', 'stats', 'recentActivity'));
    }

    public function edit(User $user)
    {
        $delegations = \App\Models\Delegation::where('active', true)->get();
        return view('supervisor.users.edit', compact('user', 'delegations'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:CLIENT,DELIVERER,COMMERCIAL,SUPERVISOR,DEPOT_MANAGER',
            'status' => 'required|in:ACTIVE,PENDING,SUSPENDED',
        ]);

        DB::transaction(function () use ($request, $user) {
            $oldRole = $user->role;

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'status' => $request->status,
            ]);

            // Créer le wallet si le rôle a changé vers CLIENT ou DELIVERER
            if (!in_array($oldRole, ['CLIENT', 'DELIVERER']) &&
                in_array($request->role, ['CLIENT', 'DELIVERER'])) {
                $user->ensureWallet();
            }
        });

        return redirect()->route('supervisor.users.show', $user)
                        ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        // Vérifier si l'utilisateur peut être supprimé
        if ($user->packages()->exists()) {
            return back()->with('error', 'Impossible de supprimer cet utilisateur car il a des colis associés.');
        }

        if ($user->role === 'SUPERVISOR') {
            return back()->with('error', 'Impossible de supprimer un superviseur.');
        }

        $user->delete();

        return redirect()->route('supervisor.users.index')
                        ->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function activate(User $user)
    {
        $user->update(['status' => 'ACTIVE']);
        return back()->with('success', 'Utilisateur activé avec succès.');
    }

    public function deactivate(User $user)
    {
        $user->update(['status' => 'SUSPENDED']);
        return back()->with('success', 'Utilisateur désactivé avec succès.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Mot de passe réinitialisé avec succès.');
    }

    public function forceLogout(User $user)
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
        return back()->with('success', 'Utilisateur déconnecté de toutes les sessions.');
    }

    public function permissions(User $user)
    {
        return view('supervisor.users.permissions', compact('user'));
    }

    public function updatePermissions(Request $request, User $user)
    {
        // Cette méthode sera implémentée quand on ajoura un système de permissions
        return back()->with('success', 'Permissions mises à jour avec succès.');
    }

    public function bulkActivate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        User::whereIn('id', $request->user_ids)->update(['status' => 'ACTIVE']);

        return back()->with('success', count($request->user_ids) . ' utilisateur(s) activé(s) avec succès.');
    }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        User::whereIn('id', $request->user_ids)->update(['status' => 'SUSPENDED']);

        return back()->with('success', count($request->user_ids) . ' utilisateur(s) désactivé(s) avec succès.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        // Vérifier qu'aucun utilisateur n'a de colis
        $usersWithPackages = User::whereIn('id', $request->user_ids)
                                ->whereHas('packages')
                                ->count();

        if ($usersWithPackages > 0) {
            return back()->with('error', 'Impossible de supprimer des utilisateurs qui ont des colis associés.');
        }

        User::whereIn('id', $request->user_ids)->delete();

        return back()->with('success', count($request->user_ids) . ' utilisateur(s) supprimé(s) avec succès.');
    }

    public function export(Request $request)
    {
        // Implémentation de l'export sera ajoutée plus tard
        return back()->with('info', 'Fonction d\'export en cours de développement.');
    }

    // API Methods
    public function apiSearch(Request $request)
    {
        $query = User::query();

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->q}%")
                  ->orWhere('email', 'like', "%{$request->q}%")
                  ->orWhere('phone', 'like', "%{$request->q}%");
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->limit(10)->get(['id', 'name', 'email', 'phone', 'role', 'status']);

        return response()->json($users);
    }

    public function apiStats()
    {
        return response()->json([
            'total_users' => User::count(),
            'active_users' => User::where('status', 'ACTIVE')->count(),
            'pending_users' => User::where('status', 'PENDING')->count(),
            'suspended_users' => User::where('status', 'SUSPENDED')->count(),
            'by_role' => [
                'CLIENT' => User::where('role', 'CLIENT')->count(),
                'DELIVERER' => User::where('role', 'DELIVERER')->count(),
                'COMMERCIAL' => User::where('role', 'COMMERCIAL')->count(),
                'SUPERVISOR' => User::where('role', 'SUPERVISOR')->count(),
                'DEPOT_MANAGER' => User::where('role', 'DEPOT_MANAGER')->count(),
            ]
        ]);
    }

    public function apiActiveSessions()
    {
        $sessions = DB::table('sessions')
                     ->join('users', 'sessions.user_id', '=', 'users.id')
                     ->select('users.name', 'users.email', 'sessions.last_activity', 'sessions.ip_address')
                     ->whereNotNull('sessions.user_id')
                     ->orderBy('sessions.last_activity', 'desc')
                     ->get();

        return response()->json($sessions);
    }
}