<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Package;
use App\Models\Complaint;
use App\Services\ActionLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }
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
            'total' => User::count(),
            'active' => User::where('status', 'ACTIVE')->count(),
            'pending' => User::where('status', 'PENDING')->count(),
            'suspended' => User::where('status', 'SUSPENDED')->count(),
        ];

        // Définir un rôle par défaut pour la vue index
        $role = 'ALL';

        return view('supervisor.users.by-role', compact('users', 'stats', 'role'));
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
        // Debug log pour chef dépôt
        if ($request->role === 'DEPOT_MANAGER') {
            \Log::info('Création Chef Dépôt - Données reçues:', [
                'role' => $request->role,
                'assigned_gouvernorats' => $request->assigned_gouvernorats,
                'all_data' => $request->all()
            ]);
        }

        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:CLIENT,DELIVERER,COMMERCIAL,SUPERVISOR,DEPOT_MANAGER',
            'account_status' => 'required|in:ACTIVE,PENDING,SUSPENDED',
        ];

        // Validation spéciale pour les clients
        if ($request->role === 'CLIENT') {
            $validationRules['address'] = 'required|string|max:500';
            $validationRules['delivery_price'] = 'required|numeric|min:0.001|max:999.999';
            $validationRules['return_price'] = 'required|numeric|min:0|max:999.999';
            $validationRules['shop_name'] = 'nullable|string|max:255';
            $delegations = array_keys(User::getAvailableDelegations());
            $validationRules['delegation_id'] = 'required|in:' . implode(',', $delegations);
        }

        // Validation spéciale pour les livreurs
        if ($request->role === 'DELIVERER') {
            // Déterminer le type de livreur depuis is_transit_deliverer
            $isTransit = $request->input('is_transit_deliverer');
            
            // Pour les livreurs locaux (non transit), les gouvernorats sont requis
            if ($isTransit === 'false' || $isTransit === false || $isTransit === '0') {
                $delegations = array_keys(User::getAvailableDelegations());
                $validationRules['deliverer_gouvernorats'] = 'required|array|min:1';
                $validationRules['deliverer_gouvernorats.*'] = 'required|string|in:' . implode(',', $delegations);
            }

            $validationRules['is_transit_deliverer'] = 'required|boolean';
            $validationRules['vehicle_type'] = 'nullable|in:MOTO,VOITURE,CAMIONNETTE';
            $validationRules['vehicle_registration'] = 'nullable|string|max:50';
        }

        // Validation spéciale pour les chefs dépôt
        if ($request->role === 'DEPOT_MANAGER') {
            $delegations = array_keys(User::getAvailableDelegations());
            $validationRules['assigned_gouvernorats'] = 'required|array|min:1';
            $validationRules['assigned_gouvernorats.*'] = 'in:' . implode(',', $delegations);
        }

        $customMessages = [
            'assigned_gouvernorats.required' => 'Veuillez sélectionner au moins un gouvernorat pour le chef dépôt.',
            'assigned_gouvernorats.array' => 'Les gouvernorats doivent être sous forme de liste.',
            'assigned_gouvernorats.min' => 'Veuillez sélectionner au moins un gouvernorat.',
            'assigned_gouvernorats.*.in' => 'Un des gouvernorats sélectionnés n\'est pas valide.',
            'deliverer_gouvernorats.required' => 'Veuillez sélectionner au moins un gouvernorat pour le livreur.',
            'deliverer_gouvernorats.array' => 'Les gouvernorats doivent être sous forme de liste.',
            'deliverer_gouvernorats.min' => 'Veuillez sélectionner au moins un gouvernorat.',
            'deliverer_gouvernorats.*.in' => 'Un des gouvernorats sélectionnés n\'est pas valide.',
        ];

        $validated = $request->validate($validationRules, $customMessages);

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
                    'email_verified_at' => now(),
                ];

                // Ajouter les champs spécifiques pour les clients
                if ($request->role === 'CLIENT') {
                    $userData['address'] = $request->address;
                    $userData['assigned_delegation'] = $request->delegation_id;
                }

                // Ajouter les champs spécifiques pour les livreurs
                if ($request->role === 'DELIVERER') {
                    $isTransit = $request->input('is_transit_deliverer');
                    
                    // Mapper is_transit_deliverer vers deliverer_type
                    if ($isTransit === 'true' || $isTransit === true || $isTransit === '1') {
                        $userData['deliverer_type'] = 'TRANSIT';
                        $userData['assigned_delegation'] = null;
                        $userData['deliverer_gouvernorats'] = null;
                    } else {
                        // Livreur local = DELEGATION
                        $userData['deliverer_type'] = 'DELEGATION';
                        // Utiliser le premier gouvernorat comme assigned_delegation (compatibilité)
                        $userData['assigned_delegation'] = $request->deliverer_gouvernorats[0] ?? null;
                        $userData['deliverer_gouvernorats'] = $request->deliverer_gouvernorats;
                    }
                    
                    $userData['delegation_latitude'] = null;
                    $userData['delegation_longitude'] = null;
                    $userData['delegation_radius_km'] = 10;
                }

                // Ajouter les champs spécifiques pour les chefs dépôt
                if ($request->role === 'DEPOT_MANAGER') {
                    $userData['assigned_gouvernorats'] = json_encode($request->assigned_gouvernorats);
                    $userData['is_depot_manager'] = true;
                }

                $user = User::create($userData);

                // Créer le wallet si nécessaire
                if (in_array($user->role, ['CLIENT', 'DELIVERER'])) {
                    $user->ensureWallet();
                }
                
                // Créer le profil client avec les tarifs
                if ($request->role === 'CLIENT') {
                    \App\Models\ClientProfile::create([
                        'user_id' => $user->id,
                        'shop_name' => $request->shop_name,
                        'offer_delivery_price' => $request->delivery_price,
                        'offer_return_price' => $request->return_price,
                        'validation_status' => 'VALIDATED',
                    ]);
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

    /**
     * Gérer le wallet d'un chef dépôt (ajout/retrait/vidage)
     */
    public function manageDepotWallet(Request $request, User $user)
    {
        if (!$user->isDepotManager()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet utilisateur n\'est pas un chef dépôt.'
            ], 400);
        }

        $request->validate([
            'action' => 'required|in:add,deduct,empty',
            'amount' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                $supervisor = auth()->user();
                $amount = $request->amount;
                $action = $request->action;

                if ($action === 'add') {
                    // Ajouter des fonds au wallet du chef dépôt
                    $user->adjustDepotWallet($amount, $supervisor->id, 'SUPERVISOR_ADJUSTMENT', $request->notes);
                } elseif ($action === 'deduct') {
                    // Retirer des fonds du wallet du chef dépôt
                    $user->adjustDepotWallet(-$amount, $supervisor->id, 'SUPERVISOR_ADJUSTMENT', $request->notes);
                } elseif ($action === 'empty') {
                    // Vider complètement le wallet du chef dépôt
                    $currentBalance = $user->depot_wallet_balance;
                    if ($currentBalance > 0) {
                        $user->adjustDepotWallet(-$currentBalance, $supervisor->id, 'SUPERVISOR_EMPTYING', $request->notes);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Wallet du chef dépôt mis à jour avec succès.',
                'new_balance' => $user->fresh()->depot_wallet_balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du wallet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher l'historique du wallet d'un chef dépôt
     */
    public function depotWalletHistory(User $user)
    {
        if (!$user->isDepotManager()) {
            abort(403, 'Cet utilisateur n\'est pas un chef dépôt.');
        }

        $transactions = $user->getDepotWalletTransactions(100);

        return view('supervisor.users.depot-wallet-history', compact('user', 'transactions'));
    }

    /**
     * Vue des utilisateurs par rôle
     */
    public function byRole($role)
    {
        $validRoles = ['CLIENT', 'DELIVERER', 'COMMERCIAL', 'DEPOT_MANAGER'];
        
        if (!in_array($role, $validRoles)) {
            abort(404);
        }

        $users = User::where('role', $role)
            ->with(['wallet'])
            ->withCount(['sentPackages' => function($query) {
                $query->whereIn('status', ['DELIVERED', 'RETURNED']);
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => User::where('role', $role)->count(),
            'active' => User::where('role', $role)->where('account_status', 'ACTIVE')->count(),
            'pending' => User::where('role', $role)->where('account_status', 'PENDING')->count(),
            'suspended' => User::where('role', $role)->where('account_status', 'SUSPENDED')->count(),
        ];

        return view('supervisor.users.by-role', compact('users', 'role', 'stats'));
    }

    /**
     * Activité récente d'un utilisateur
     */
    public function activity(User $user)
    {
        $recentActivity = $this->actionLogService->getUserActivity($user->id, 50);

        return view('supervisor.users.activity', compact('user', 'recentActivity'));
    }

    /**
     * Impersonation: Se connecter en tant qu'utilisateur
     */
    public function impersonate(User $user)
    {
        // Vérifier que l'utilisateur cible n'est pas un superviseur
        if ($user->role === 'SUPERVISOR') {
            return redirect()->back()
                ->with('error', 'Impossible de se connecter en tant que superviseur.');
        }

        // Vérifier que l'utilisateur cible est actif
        if ($user->account_status !== 'ACTIVE') {
            return redirect()->back()
                ->with('error', 'Impossible de se connecter en tant qu\'utilisateur inactif.');
        }

        // Enregistrer le superviseur original dans la session
        session()->put('impersonator_id', Auth::id());
        session()->put('impersonating', true);

        // Logger l'action
        $this->actionLogService->logImpersonation(Auth::id(), $user->id, 'START');

        // Se connecter en tant qu'utilisateur
        Auth::login($user);

        // Rediriger vers le dashboard approprié
        $dashboard = match($user->role) {
            'CLIENT' => route('client.dashboard'),
            'DELIVERER' => route('deliverer.dashboard'),
            'COMMERCIAL' => route('commercial.dashboard'),
            'DEPOT_MANAGER' => route('depot-manager.dashboard'),
            default => route('dashboard'),
        };

        return redirect($dashboard)
            ->with('success', "Vous êtes maintenant connecté en tant que {$user->name}");
    }

    /**
     * Arrêter l'impersonation
     */
    public function stopImpersonation()
    {
        if (!session()->has('impersonator_id')) {
            return redirect()->route('supervisor.dashboard')
                ->with('error', 'Aucune impersonation en cours.');
        }

        $impersonatorId = session()->get('impersonator_id');
        $currentUserId = Auth::id();

        // Logger la fin de l'impersonation
        $this->actionLogService->logImpersonation($impersonatorId, $currentUserId, 'STOP');

        // Récupérer le superviseur original
        $supervisor = User::find($impersonatorId);

        if (!$supervisor) {
            session()->forget(['impersonator_id', 'impersonating']);
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Superviseur introuvable.');
        }

        // Retirer les données de session
        session()->forget(['impersonator_id', 'impersonating']);

        // Se reconnecter en tant que superviseur
        Auth::login($supervisor);

        return redirect()->route('supervisor.dashboard')
            ->with('success', 'Impersonation terminée avec succès.');
    }

    /**
     * Génère un mot de passe temporaire sécurisé
     */
    public function generateTempPassword(User $user)
    {
        // Générer un mot de passe temporaire fort
        $tempPassword = Str::random(12);
        
        // Hash et sauvegarder
        $user->update([
            'password' => Hash::make($tempPassword),
            'password_reset_required' => true, // Nécessite un champ dans la migration
        ]);

        // Logger l'action
        $this->actionLogService->log(
            'PASSWORD_RESET_GENERATED',
            'User',
            $user->id,
            null,
            ['reset_by' => Auth::id()]
        );

        // TODO: Envoyer l'email avec le mot de passe temporaire
        // Mail::to($user->email)->send(new TemporaryPasswordEmail($tempPassword));

        return redirect()->back()
            ->with('success', 'Mot de passe temporaire généré avec succès.')
            ->with('temp_password', $tempPassword); // Afficher une seule fois
    }

    /**
     * API: Liste des utilisateurs pour sélection
     */
    public function apiUsersList(Request $request)
    {
        $role = $request->get('role');
        $search = $request->get('search');

        $query = User::select('id', 'name', 'email', 'phone', 'role', 'account_status');

        if ($role) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $users = $query->where('account_status', 'ACTIVE')
            ->limit(50)
            ->get();

        return response()->json($users);
    }
}