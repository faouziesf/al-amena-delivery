<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\CommercialService;
use App\Models\User;
use App\Models\Delegation;
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    protected $commercialService;

    public function __construct(CommercialService $commercialService)
    {
        $this->commercialService = $commercialService;
    }

    public function index(Request $request)
    {
        $query = User::with(['clientProfile', 'wallet', 'createdBy', 'verifiedBy'])
                    ->where('role', 'CLIENT');

        // Filtres
        if ($request->filled('status')) {
            $query->where('account_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('clientProfile', function ($profile) use ($search) {
                      $profile->where('shop_name', 'like', "%{$search}%")
                             ->orWhere('fiscal_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('created_by_me')) {
            $query->where('created_by', Auth::id());
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => User::where('role', 'CLIENT')->count(),
            'active' => User::where('role', 'CLIENT')->where('account_status', 'ACTIVE')->count(),
            'pending' => User::where('role', 'CLIENT')->where('account_status', 'PENDING')->count(),
            'created_by_me' => User::where('role', 'CLIENT')->where('created_by', Auth::id())->count(),
        ];

        return view('commercial.clients.index', compact('clients', 'stats'));
    }

    public function create()
    {
        $delegations = Delegation::active()->orderBy('name')->get();
        return view('commercial.clients.create', compact('delegations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'delivery_price' => 'required|numeric|min:0|max:999.999',
            'return_price' => 'required|numeric|min:0|max:999.999',
            'shop_name' => 'nullable|string|max:255',
            'fiscal_number' => 'nullable|string|max:50',
            'business_sector' => 'nullable|string|max:100',
            'identity_document' => 'nullable|string|max:100',
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'phone.required' => 'Le téléphone est obligatoire.',
            'address.required' => 'L\'adresse est obligatoire.',
            'delivery_price.required' => 'Le prix de livraison est obligatoire.',
            'delivery_price.numeric' => 'Le prix de livraison doit être un nombre.',
            'delivery_price.min' => 'Le prix de livraison ne peut pas être négatif.',
            'return_price.required' => 'Le prix de retour est obligatoire.',
            'return_price.numeric' => 'Le prix de retour doit être un nombre.',
            'return_price.min' => 'Le prix de retour ne peut pas être négatif.',
        ]);

        try {
            $client = $this->commercialService->createClientAccount(
                $validated,
                Auth::user()
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Compte client créé avec succès pour {$client->name}.",
                    'client' => [
                        'id' => $client->id,
                        'name' => $client->name,
                        'email' => $client->email,
                        'account_status' => $client->account_status
                    ]
                ], 201);
            }

            return redirect()->route('commercial.clients.index')
                ->with('success', "Compte client créé avec succès pour {$client->name}. Email: {$client->email}");
        } catch (\Exception $e) {
            \Log::error('Erreur création client:', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password', 'password_confirmation']),
                'commercial_id' => Auth::id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création: ' . $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()])
                ->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    public function show(User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $client->load(['clientProfile', 'wallet', 'createdBy', 'verifiedBy', 'transactions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        $packages = $client->packages()
                          ->with(['delegationFrom', 'delegationTo', 'assignedDeliverer'])
                          ->orderBy('created_at', 'desc')
                          ->limit(20)
                          ->get();

        $stats = [
            'wallet_balance' => $client->wallet->balance ?? 0,
            'pending_amount' => $client->wallet->pending_amount ?? 0,
            'total_packages' => $client->packages()->count(),
            'packages_in_progress' => $client->packages()->inProgress()->count(),
            'packages_delivered' => $client->packages()->delivered()->count(),
            'complaints' => $client->complaints()->count(),
            'pending_complaints' => $client->complaints()->where('status', 'PENDING')->count(),
        ];

        return view('commercial.clients.show', compact('client', 'packages', 'stats'));
    }

    public function edit(User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $client->load('clientProfile');
        $delegations = Delegation::active()->orderBy('name')->get();
        
        return view('commercial.clients.edit', compact('client', 'delegations'));
    }

    public function update(Request $request, User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($client->id)],
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'delivery_price' => 'required|numeric|min:0|max:999.999',
            'return_price' => 'required|numeric|min:0|max:999.999',
            'shop_name' => 'nullable|string|max:255',
            'fiscal_number' => 'nullable|string|max:50',
            'business_sector' => 'nullable|string|max:100',
            'identity_document' => 'nullable|string|max:100',
            'new_password' => 'nullable|min:6|confirmed',
        ], [
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 6 caractères.',
            'new_password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        try {
            // Mise à jour utilisateur
            $userData = $request->only(['name', 'email', 'phone', 'address']);
            
            // Si un nouveau mot de passe est fourni
            if ($request->filled('new_password')) {
                $userData['password'] = Hash::make($request->new_password);
            }
            
            $client->update($userData);

            // Mise à jour profil client
            $client->clientProfile()->updateOrCreate(
                ['user_id' => $client->id],
                $request->only([
                    'shop_name', 'fiscal_number', 'business_sector', 
                    'identity_document', 'delivery_price', 'return_price'
                ])
            );

            // Log de l'action
            app(\App\Services\ActionLogService::class)->log(
                'CLIENT_PROFILE_UPDATED',
                'User',
                $client->id,
                'profile_data',
                'updated',
                [
                    'updated_by' => Auth::user()->name,
                    'fields_updated' => array_keys($request->only([
                        'name', 'email', 'phone', 'address', 'shop_name', 
                        'fiscal_number', 'business_sector', 'identity_document',
                        'delivery_price', 'return_price'
                    ])),
                    'password_changed' => $request->filled('new_password')
                ]
            );

            return redirect()->route('commercial.clients.show', $client)
                ->with('success', 'Informations client mises à jour avec succès.');
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour client:', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'commercial_id' => Auth::id()
            ]);

            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()])
                ->withInput($request->except(['new_password', 'new_password_confirmation']));
        }
    }

    public function validateAccount(User $client, Request $request)
    {
        if ($client->role !== 'CLIENT' || $client->account_status === 'ACTIVE') {
            return back()->with('info', 'Ce compte ne peut pas être validé.');
        }

        try {
            $this->commercialService->validateClientAccount($client, Auth::user(), [
                'validation_notes' => $request->input('notes', 'Compte validé par commercial')
            ]);

            return back()->with('success', "Compte client de {$client->name} validé avec succès.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la validation: ' . $e->getMessage()]);
        }
    }

    public function suspendAccount(User $client, Request $request)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $client->update([
                'account_status' => 'SUSPENDED',
                'verified_at' => null,
                'verified_by' => Auth::id(),
            ]);

            // Log de l'action
            app(\App\Services\ActionLogService::class)->log(
                'CLIENT_ACCOUNT_SUSPENDED',
                'User',
                $client->id,
                'ACTIVE',
                'SUSPENDED',
                [
                    'suspended_by' => Auth::user()->name,
                    'reason' => $request->reason
                ]
            );

            return back()->with('success', "Compte de {$client->name} suspendu avec succès.");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la suspension: ' . $e->getMessage()]);
        }
    }

    // ==================== GESTION WALLET ====================

    public function walletHistory(User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $wallet = $client->wallet;
        $transactions = $client->transactions()
                             ->orderBy('created_at', 'desc')
                             ->paginate(30);

        return view('commercial.clients.wallet', compact('client', 'wallet', 'transactions'));
    }

    public function addFunds(Request $request, User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.001|max:9999.999',
            'description' => 'required|string|max:255',
        ]);

        try {
            $result = $this->commercialService->addFundsToWallet(
                $client,
                $request->amount,
                $request->description,
                Auth::user()
            );

            return back()->with('success', 
                "Fonds ajoutés avec succès. Nouveau solde: " . number_format($result['wallet_balance'], 3) . " DT"
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'ajout: ' . $e->getMessage()]);
        }
    }

    public function deductFunds(Request $request, User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $currentBalance = $client->wallet->balance ?? 0;
        
        $request->validate([
            'amount' => 'required|numeric|min:0.001|max:' . $currentBalance,
            'description' => 'required|string|max:255',
        ], [
            'amount.max' => 'Le montant ne peut pas dépasser le solde actuel (' . number_format($currentBalance, 3) . ' DT).'
        ]);

        try {
            $result = $this->commercialService->deductFundsFromWallet(
                $client,
                $request->amount,
                $request->description,
                Auth::user()
            );

            return back()->with('success', 
                "Fonds déduits avec succès. Nouveau solde: " . number_format($result['wallet_balance'], 3) . " DT"
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la déduction: ' . $e->getMessage()]);
        }
    }

    // ==================== API ENDPOINTS ====================

    public function apiSearch(Request $request)
    {
        $search = $request->input('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $clients = User::where('role', 'CLIENT')
                      ->where('account_status', 'ACTIVE')
                      ->where(function ($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                      })
                      ->with('clientProfile:user_id,shop_name')
                      ->limit(10)
                      ->get(['id', 'name', 'email', 'phone'])
                      ->map(function ($client) {
                          return [
                              'id' => $client->id,
                              'name' => $client->name,
                              'email' => $client->email,
                              'phone' => $client->phone,
                              'shop_name' => $client->clientProfile->shop_name ?? null,
                              'display_name' => $client->name . ' (' . $client->email . ')',
                          ];
                      });

        return response()->json($clients);
    }

    public function apiStats(User $client)
    {
        if ($client->role !== 'CLIENT') {
            return response()->json(['error' => 'Client not found'], 404);
        }

        return response()->json([
            'wallet_balance' => $client->wallet->balance ?? 0,
            'pending_amount' => $client->wallet->pending_amount ?? 0,
            'total_packages' => $client->packages()->count(),
            'packages_in_progress' => $client->packages()->inProgress()->count(),
            'packages_delivered' => $client->packages()->delivered()->count(),
            'pending_complaints' => $client->complaints()->where('status', 'PENDING')->count(),
        ]);
    }

    public function apiRecentClients()
    {
        $clients = User::where('role', 'CLIENT')
                      ->where('created_by', Auth::id())
                      ->with(['clientProfile', 'wallet'])
                      ->orderBy('created_at', 'desc')
                      ->limit(10)
                      ->get()
                      ->map(function ($client) {
                          return [
                              'id' => $client->id,
                              'name' => $client->name,
                              'email' => $client->email,
                              'shop_name' => $client->clientProfile->shop_name ?? null,
                              'account_status' => $client->account_status,
                              'wallet_balance' => number_format($client->wallet->balance ?? 0, 3),
                              'created_at' => $client->created_at->diffForHumans(),
                              'show_url' => route('commercial.clients.show', $client->id),
                          ];
                      });

        return response()->json($clients);
    }
}