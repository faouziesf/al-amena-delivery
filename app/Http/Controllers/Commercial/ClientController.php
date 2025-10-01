<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\ActionLogService;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Delegation;
use App\Models\ClientProfile;
use App\Models\FinancialTransaction;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClientController extends Controller
{
    protected $actionLogService;

    public function __construct(ActionLogService $actionLogService)
    {
        $this->actionLogService = $actionLogService;
    }

    // ==================== VUES PRINCIPALES ====================

    public function index(Request $request)
    {
        $query = User::with(['clientProfile', 'wallet', 'createdBy', 'verifiedBy'])
                    ->where('role', 'CLIENT');

        // Filtres de recherche
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

        if ($request->filled('business_sector')) {
            $query->whereHas('clientProfile', function ($profile) use ($request) {
                $profile->where('business_sector', 'like', "%{$request->business_sector}%");
            });
        }

        if ($request->filled('min_wallet')) {
            $query->whereHas('wallet', function ($wallet) use ($request) {
                $wallet->where('balance', '>=', $request->min_wallet);
            });
        }

        if ($request->filled('created_after')) {
            $query->whereDate('created_at', '>=', $request->created_after);
        }

        // Export CSV
        if ($request->filled('export') && $request->export === 'csv') {
            return $this->exportClientsCsv($query);
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

    public function create(Request $request)
    {
        $delegations = Delegation::active()->orderBy('name')->get();
        
        // Si c'est une duplication, charger les données du client source
        $sourceClient = null;
        if ($request->filled('duplicate')) {
            $sourceClient = User::with('clientProfile')
                               ->where('role', 'CLIENT')
                               ->find($request->duplicate);
        }

        return view('commercial.clients.create', compact('delegations', 'sourceClient'));
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
            'internal_notes' => 'nullable|string|max:1000',
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
            'delivery_price.max' => 'Le prix de livraison ne peut pas dépasser 999.999 DT.',
            'return_price.required' => 'Le prix de retour est obligatoire.',
            'return_price.numeric' => 'Le prix de retour doit être un nombre.',
            'return_price.min' => 'Le prix de retour ne peut pas être négatif.',
            'return_price.max' => 'Le prix de retour ne peut pas dépasser 999.999 DT.',
        ]);

        try {
            DB::beginTransaction();

            // Créer l'utilisateur client
            $client = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'role' => 'CLIENT',
                'account_status' => 'PENDING',
                'created_by' => Auth::id(),
            ]);

            // Créer le profil client
            $client->clientProfile()->create([
                'offer_delivery_price' => $validated['delivery_price'],
                'offer_return_price' => $validated['return_price'],
                'shop_name' => $validated['shop_name'],
                'fiscal_number' => $validated['fiscal_number'],
                'business_sector' => $validated['business_sector'],
                'identity_document' => $validated['identity_document'],
                'internal_notes' => $validated['internal_notes'],
                'validation_status' => 'PENDING',
            ]);

            // Créer le portefeuille
            $client->ensureWallet();

            // Log de l'action
            $this->actionLogService->logAction(Auth::user(), 'CLIENT_ACCOUNT_CREATED',
                "Compte client créé: {$client->name} ({$client->email})", $client->id);

            DB::commit();

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

            return redirect()->route('commercial.clients.show', $client)
                ->with('success', "Compte client créé avec succès pour {$client->name}.");
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur création client:', [
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

    public function show(User $client, Request $request)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $client->load(['clientProfile', 'wallet', 'createdBy', 'verifiedBy']);

        // Charger les transactions récentes - CORRECTION
        $transactions = FinancialTransaction::where('user_id', $client->id)
                                         ->orderBy('created_at', 'desc')
                                         ->limit(20)
                                         ->get();

        // Assigner les transactions au client pour la vue
        $client->setRelation('transactions', $transactions);

        // Charger les colis récents
        $packages = Package::where('sender_id', $client->id)
                          ->with(['delegationFrom', 'delegationTo', 'assignedDeliverer'])
                          ->orderBy('created_at', 'desc')
                          ->limit(20)
                          ->get();

        // Calculer les statistiques avec vérifications
        $stats = [
            'wallet_balance' => $client->wallet ? $client->wallet->balance : 0,
            'pending_amount' => $client->wallet ? $client->wallet->pending_amount : 0,
            'total_packages' => Package::where('sender_id', $client->id)->count(),
            'packages_in_progress' => Package::where('sender_id', $client->id)->inProgress()->count(),
            'packages_delivered' => Package::where('sender_id', $client->id)->delivered()->count(),
            'complaints' => 0, // Placeholder - ajustez selon vos besoins
            'pending_complaints' => 0, // Placeholder - ajustez selon vos besoins
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
            'internal_notes' => 'nullable|string|max:1000',
            'new_password' => 'nullable|min:6|confirmed',
        ], [
            'new_password.min' => 'Le nouveau mot de passe doit contenir au moins 6 caractères.',
            'new_password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        try {
            DB::beginTransaction();

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
                    'identity_document', 'delivery_price', 'return_price', 'internal_notes'
                ])
            );

            // Log de l'action
            $this->actionLogService->log(
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
                        'delivery_price', 'return_price', 'internal_notes'
                    ])),
                    'password_changed' => $request->filled('new_password')
                ]
            );

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Client mis à jour avec succès.',
                    'client' => $client->load('clientProfile')
                ]);
            }

            return redirect()->route('commercial.clients.show', $client)
                ->with('success', 'Informations client mises à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur mise à jour client:', [
                'client_id' => $client->id,
                'error' => $e->getMessage(),
                'commercial_id' => Auth::id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
                ], 422);
            }

            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()])
                ->withInput($request->except(['new_password', 'new_password_confirmation']));
        }
    }

    // ==================== ACTIONS SPÉCIFIQUES ====================

    public function validateAccount(User $client, Request $request)
    {
        if ($client->role !== 'CLIENT' || $client->account_status === 'ACTIVE') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ce compte ne peut pas être validé.'], 400);
            }
            return back()->with('info', 'Ce compte ne peut pas être validé.');
        }

        try {
            DB::beginTransaction();

            // Valider le compte client
            $client->update([
                'account_status' => 'ACTIVE',
                'email_verified_at' => now(),
                'validated_by' => Auth::id(),
                'validated_at' => now(),
            ]);

            // Mettre à jour le profil client
            $client->clientProfile()->update([
                'validation_status' => 'VALIDATED',
                'validated_by' => Auth::id(),
                'validated_at' => now(),
                'validation_notes' => $request->input('notes', 'Compte validé par commercial'),
            ]);

            // Log de l'action
            $this->actionLogService->logAction(Auth::user(), 'CLIENT_ACCOUNT_VALIDATED',
                "Compte client validé: {$client->name} ({$client->email})", $client->id);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Compte client de {$client->name} validé avec succès."
                ]);
            }

            return back()->with('success', "Compte client de {$client->name} validé avec succès.");
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Erreur lors de la validation: ' . $e->getMessage()], 422);
            }
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
            $this->actionLogService->log(
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

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Compte de {$client->name} suspendu avec succès."
                ]);
            }

            return back()->with('success', "Compte de {$client->name} suspendu avec succès.");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Erreur lors de la suspension: ' . $e->getMessage()], 422);
            }
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

    // ==================== MÉTHODES WALLET CORRIGÉES ====================

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
            DB::beginTransaction();

            // CORRIGÉ : Utiliser la méthode ensureWallet() sécurisée
            $wallet = $client->ensureWallet();
            
            if (!$wallet) {
                throw new \Exception('Impossible de créer ou récupérer le wallet du client.');
            }

            $oldBalance = $wallet->balance;
            $newBalance = $oldBalance + $request->amount;

            // Mettre à jour le wallet
            $wallet->update([
                'balance' => $newBalance,
                'last_transaction_at' => now()
            ]);

            // Créer la transaction
            FinancialTransaction::create([
                'transaction_id' => 'TXN_ADD_' . strtoupper(uniqid()),
                'user_id' => $client->id,
                'type' => 'CREDIT',
                'amount' => $request->amount,
                'status' => 'COMPLETED',
                'description' => $request->description,
                'wallet_balance_before' => $oldBalance,
                'wallet_balance_after' => $newBalance,
                'metadata' => json_encode([
                    'added_by_commercial' => Auth::id(),
                    'commercial_name' => Auth::user()->name,
                    'ip_address' => $request->ip()
                ]),
                'completed_at' => now()
            ]);

            // Log de l'action
            if (app()->bound(\App\Services\ActionLogService::class)) {
                app(\App\Services\ActionLogService::class)->log(
                    'WALLET_FUNDS_ADDED',
                    'UserWallet',
                    $wallet->id,
                    $oldBalance,
                    $newBalance,
                    [
                        'amount' => $request->amount,
                        'description' => $request->description,
                        'added_by' => Auth::user()->name
                    ]
                );
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fonds ajoutés avec succès.',
                    'new_balance' => $newBalance
                ]);
            }

            return back()->with('success', 
                "Fonds ajoutés avec succès. Nouveau solde: " . number_format($newBalance, 3) . " DT"
            );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur ajout fonds:', [
                'client_id' => $client->id,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'ajout: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Erreur lors de l\'ajout: ' . $e->getMessage()]);
        }
    }

    public function deductFunds(Request $request, User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        // CORRIGÉ : Utiliser ensureWallet() et vérifier l'existence
        $wallet = $client->ensureWallet();
        $currentBalance = $wallet ? $wallet->balance : 0;
        
        $request->validate([
            'amount' => 'required|numeric|min:0.001|max:' . $currentBalance,
            'description' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            if (!$wallet) {
                throw new \Exception('Le client n\'a pas de wallet.');
            }

            if ($wallet->balance < $request->amount) {
                throw new \Exception('Solde insuffisant.');
            }

            $oldBalance = $wallet->balance;
            $newBalance = $oldBalance - $request->amount;

            // Mettre à jour le wallet
            $wallet->update([
                'balance' => $newBalance,
                'last_transaction_at' => now()
            ]);

            // Créer la transaction
            FinancialTransaction::create([
                'transaction_id' => 'TXN_DED_' . strtoupper(uniqid()),
                'user_id' => $client->id,
                'type' => 'DEBIT',
                'amount' => -$request->amount,
                'status' => 'COMPLETED',
                'description' => $request->description,
                'wallet_balance_before' => $oldBalance,
                'wallet_balance_after' => $newBalance,
                'metadata' => json_encode([
                    'deducted_by_commercial' => Auth::id(),
                    'commercial_name' => Auth::user()->name,
                    'ip_address' => $request->ip()
                ]),
                'completed_at' => now()
            ]);

            // Log de l'action
            if (app()->bound(\App\Services\ActionLogService::class)) {
                app(\App\Services\ActionLogService::class)->log(
                    'WALLET_FUNDS_DEDUCTED',
                    'UserWallet',
                    $wallet->id,
                    $oldBalance,
                    $newBalance,
                    [
                        'amount' => $request->amount,
                        'description' => $request->description,
                        'deducted_by' => Auth::user()->name
                    ]
                );
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fonds déduits avec succès.',
                    'new_balance' => $newBalance
                ]);
            }

            return back()->with('success', 
                "Fonds déduits avec succès. Nouveau solde: " . number_format($newBalance, 3) . " DT"
            );

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Erreur déduction fonds:', [
                'client_id' => $client->id,
                'amount' => $request->amount,
                'current_balance' => $currentBalance,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la déduction: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Erreur lors de la déduction: ' . $e->getMessage()]);
        }
    }

    // ==================== OPÉRATIONS GROUPÉES ====================

    public function bulkValidate(Request $request)
    {
        $request->validate([
            'client_ids' => 'required|array|min:1',
            'client_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            $validatedCount = 0;
            $clients = User::whereIn('id', $request->client_ids)
                          ->where('role', 'CLIENT')
                          ->where('account_status', 'PENDING')
                          ->get();

            foreach ($clients as $client) {
                // Valider le compte client
                $client->update([
                    'account_status' => 'ACTIVE',
                    'email_verified_at' => now(),
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                ]);

                // Mettre à jour le profil client
                $client->clientProfile()->update([
                    'validation_status' => 'VALIDATED',
                    'validated_by' => Auth::id(),
                    'validated_at' => now(),
                    'validation_notes' => $request->notes ?? 'Validation groupée',
                ]);

                // Log de l'action
                $this->actionLogService->logAction(Auth::user(), 'CLIENT_ACCOUNT_VALIDATED_BULK',
                    "Compte client validé (groupé): {$client->name} ({$client->email})", $client->id);

                $validatedCount++;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "$validatedCount compte(s) validé(s) avec succès.",
                    'validated_count' => $validatedCount
                ]);
            }

            return back()->with('success', "$validatedCount compte(s) validé(s) avec succès.");
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Erreur lors de la validation groupée: ' . $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => 'Erreur lors de la validation groupée: ' . $e->getMessage()]);
        }
    }

    // ==================== EXPORT & UTILITIES ====================

    public function exportClientsCsv($query = null)
    {
        if (!$query) {
            $query = User::with(['clientProfile', 'wallet'])
                        ->where('role', 'CLIENT');
        }

        $clients = $query->get();

        $filename = 'clients_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($clients) {
            $file = fopen('php://output', 'w');
            
            // Headers CSV
            fputcsv($file, [
                'ID',
                'Nom',
                'Email',
                'Téléphone',
                'Adresse',
                'Boutique',
                'Matricule Fiscal',
                'Secteur Activité',
                'Prix Livraison (DT)',
                'Prix Retour (DT)',
                'Solde Wallet (DT)',
                'Statut',
                'Date Création',
                'Créé Par',
                'Date Validation',
                'Validé Par'
            ], ';');

            // Données
            foreach ($clients as $client) {
                fputcsv($file, [
                    $client->id,
                    $client->name,
                    $client->email,
                    $client->phone,
                    $client->address,
                    $client->clientProfile->shop_name ?? '',
                    $client->clientProfile->fiscal_number ?? '',
                    $client->clientProfile->business_sector ?? '',
                    number_format($client->clientProfile->offer_delivery_price ?? 0, 3),
                    number_format($client->clientProfile->offer_return_price ?? 0, 3),
                    number_format($client->wallet->balance ?? 0, 3),
                    $client->account_status,
                    $client->created_at->format('d/m/Y H:i'),
                    $client->createdBy->name ?? '',
                    $client->verified_at ? $client->verified_at->format('d/m/Y H:i') : '',
                    $client->verifiedBy->name ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportClientData(User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $client->load(['clientProfile', 'wallet', 'packages', 'transactions']);

        $filename = 'client_' . $client->id . '_' . now()->format('Y_m_d_H_i_s') . '.json';
        
        $data = [
            'client_info' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'account_status' => $client->account_status,
                'created_at' => $client->created_at->toISOString(),
                'verified_at' => $client->verified_at ? $client->verified_at->toISOString() : null,
            ],
            'business_info' => [
                'shop_name' => $client->clientProfile->shop_name ?? null,
                'fiscal_number' => $client->clientProfile->fiscal_number ?? null,
                'business_sector' => $client->clientProfile->business_sector ?? null,
                'identity_document' => $client->clientProfile->identity_document ?? null,
            ],
            'pricing' => [
                'delivery_price' => $client->clientProfile->offer_delivery_price ?? 0,
                'return_price' => $client->clientProfile->offer_return_price ?? 0,
            ],
            'wallet' => [
                'current_balance' => $client->wallet->balance ?? 0,
                'pending_amount' => $client->wallet->pending_amount ?? 0,
            ],
            'statistics' => [
                'total_packages' => $client->packages->count(),
                'delivered_packages' => $client->packages->where('status', 'DELIVERED')->count(),
                'total_transactions' => $client->transactions->count(),
            ],
            'export_info' => [
                'exported_at' => now()->toISOString(),
                'exported_by' => Auth::user()->name,
            ]
        ];

        return response()->json($data)
                        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportWalletHistory(User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404);
        }

        $transactions = $client->transactions()
                              ->orderBy('created_at', 'desc')
                              ->get();

        $filename = 'wallet_history_' . $client->id . '_' . now()->format('Y_m_d_H_i_s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions, $client) {
            $file = fopen('php://output', 'w');
            
            // Headers CSV
            fputcsv($file, [
                'Date',
                'Description',
                'Type',
                'Montant (DT)',
                'Statut',
                'Référence'
            ], ';');

            // Données
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('d/m/Y H:i'),
                    $transaction->description,
                    $transaction->type,
                    number_format($transaction->amount, 3),
                    $transaction->status,
                    $transaction->reference ?? ''
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

        // Charger le wallet si il n'est pas déjà chargé
        if (!$client->relationLoaded('wallet')) {
            $client->load('wallet');
        }

        $stats = [
            'wallet_balance' => $client->wallet ? (float) $client->wallet->balance : 0,
            'pending_amount' => $client->wallet ? (float) $client->wallet->pending_amount : 0,
            'total_packages' => $client->packages()->count(),
            'packages_in_progress' => $client->packages()->inProgress()->count(),
            'packages_delivered' => $client->packages()->delivered()->count(),
            'complaints' => method_exists($client, 'complaints') ? $client->complaints()->count() : 0,
            'pending_complaints' => method_exists($client, 'complaints') ? $client->complaints()->where('status', 'PENDING')->count() : 0,
        ];

        return response()->json($stats);
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

    public function apiClientProfile(User $client)
    {
        if ($client->role !== 'CLIENT') {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $client->load(['clientProfile', 'wallet']);

        return response()->json([
            'id' => $client->id,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'address' => $client->address,
            'account_status' => $client->account_status,
            'shop_name' => $client->clientProfile->shop_name ?? null,
            'fiscal_number' => $client->clientProfile->fiscal_number ?? null,
            'business_sector' => $client->clientProfile->business_sector ?? null,
            'delivery_price' => $client->clientProfile->offer_delivery_price ?? 0,
            'return_price' => $client->clientProfile->offer_return_price ?? 0,
            'wallet_balance' => $client->wallet->balance ?? 0,
            'pending_amount' => $client->wallet->pending_amount ?? 0,
            'created_at' => $client->created_at->toISOString(),
            'verified_at' => $client->verified_at ? $client->verified_at->toISOString() : null,
        ]);
    }

    public function apiClientForDuplication(User $client)
    {
        if ($client->role !== 'CLIENT') {
            return response()->json(['error' => 'Client not found'], 404);
        }

        $client->load('clientProfile');

        return response()->json([
            'name' => $client->name,
            'phone' => $client->phone,
            'address' => $client->address,
            'shop_name' => $client->clientProfile->shop_name ?? '',
            'fiscal_number' => $client->clientProfile->fiscal_number ?? '',
            'business_sector' => $client->clientProfile->business_sector ?? '',
            'identity_document' => $client->clientProfile->identity_document ?? '',
            'delivery_price' => $client->clientProfile->offer_delivery_price ?? 0,
            'return_price' => $client->clientProfile->offer_return_price ?? 0,
            'internal_notes' => $client->clientProfile->internal_notes ?? '',
        ]);
    }

    // ==================== DASHBOARD STATS ====================

    public function apiGlobalStats()
    {
        $stats = [
            'total_clients' => User::where('role', 'CLIENT')->count(),
            'active_clients' => User::where('role', 'CLIENT')->where('account_status', 'ACTIVE')->count(),
            'pending_clients' => User::where('role', 'CLIENT')->where('account_status', 'PENDING')->count(),
            'suspended_clients' => User::where('role', 'CLIENT')->where('account_status', 'SUSPENDED')->count(),
            'clients_this_month' => User::where('role', 'CLIENT')
                                       ->whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count(),
            'validated_this_week' => User::where('role', 'CLIENT')
                                        ->where('account_status', 'ACTIVE')
                                        ->whereBetween('verified_at', [now()->startOfWeek(), now()->endOfWeek()])
                                        ->count(),
            'created_by_me' => User::where('role', 'CLIENT')->where('created_by', Auth::id())->count(),
        ];

        return response()->json($stats);
    }
}