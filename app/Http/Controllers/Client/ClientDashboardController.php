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
use Illuminate\Validation\Rule;

class ClientDashboardController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        // CORRECTION: Les middlewares sont maintenant gérés dans les routes (Laravel 11+)
        // Suppression des lignes suivantes qui causaient l'erreur :
        // $this->middleware('auth');
        // $this->middleware('verified');
        // $this->middleware('role:CLIENT');
        
        $this->financialService = $financialService;
    }

    /**
     * Dashboard principal du client
     */
    public function index()
    {
        $user = Auth::user();
        
        // Vérification du rôle manuellement si le middleware n'existe pas
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->load(['wallet', 'clientProfile']);

        // S'assurer que le wallet existe
        if (!$user->wallet) {
            $user->ensureWallet();
            $user->load('wallet'); // Recharger la relation
        }

        // Statistiques générales
        $stats = $this->getDashboardStats();
        
        // Colis récents (5 derniers)
        $recentPackages = Package::where('sender_id', $user->id)
            ->with(['delegationFrom', 'delegationTo'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Notifications non lues
        $notifications = $user->notifications()
            ->where('read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Transactions récentes du wallet
        $recentTransactions = $user->financialTransactions()
            ->where('status', 'COMPLETED')
            ->with('package')
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
        
        // Vérification du rôle
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $query = Package::where('sender_id', $user->id)
            ->with(['delegationFrom', 'delegationTo', 'assignedDeliverer']);

        // Filtrage par statut
        if ($request->filled('status')) {
            if ($request->status === 'in_progress') {
                $query->whereIn('status', ['CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 'DELIVERED']);
            } elseif ($request->status === 'delivered') {
                $query->where('status', 'PAID');
            } elseif ($request->status === 'returned') {
                $query->where('status', 'RETURNED');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Recherche par code
        if ($request->filled('search')) {
            $query->where('package_code', 'LIKE', '%' . $request->search . '%');
        }

        // Filtrage par période
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
        // Vérification que le colis appartient au client
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        $package->load([
            'delegationFrom', 
            'delegationTo', 
            'assignedDeliverer',
            'statusHistory.changedByUser',
            'complaints.assignedCommercial',
            'codModifications.modifiedByCommercial'
        ]);

        return view('client.packages.show', compact('package'));
    }

    /**
     * Formulaire de création de colis
     */
    public function createPackage()
    {
        $user = Auth::user();
        
        // Vérification du rôle
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->load(['wallet', 'clientProfile']);

        // Vérification que le compte est actif
        if (!$user->isActive() || !$user->clientProfile) {
            return redirect()->route('client.dashboard')
                ->with('error', 'Votre compte doit être validé avant de créer des colis.');
        }

        // S'assurer que le wallet existe
        if (!$user->wallet) {
            $user->ensureWallet();
            $user->load('wallet');
        }

        $delegations = Delegation::active()->orderBy('name')->get();

        return view('client.packages.create', compact('user', 'delegations'));
    }

    /**
     * Enregistrement d'un nouveau colis
     */
    public function storePackage(Request $request)
    {
        $user = Auth::user();
        
        // Vérification du rôle
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

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

            // S'assurer que le wallet et le profil client existent
            $user->ensureWallet();
            $user->load(['wallet', 'clientProfile']);

            if (!$user->clientProfile) {
                throw new \Exception("Profil client non trouvé.");
            }

            // Récupération des tarifs client
            $clientProfile = $user->clientProfile;
            $deliveryFee = $clientProfile->offer_delivery_price;
            $returnFee = $clientProfile->offer_return_price;

            // Création du colis
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

            // Calcul du montant à déduire du wallet
            $escrowAmount = $package->calculateEscrowAmount();
            
            // Vérification du solde
            if (!$user->wallet->hasSufficientBalance($escrowAmount)) {
                throw new \Exception("Solde insuffisant. Montant requis: {$escrowAmount} DT");
            }

            $package->amount_in_escrow = $escrowAmount;
            $package->save();

            // Déduction du wallet
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

            // Changement de statut vers AVAILABLE
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
     * Wallet et historique financier
     */
    public function wallet(Request $request)
    {
        $user = Auth::user();
        
        // Vérification du rôle
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->ensureWallet();
        $user->load('wallet');

        // Statistiques du wallet
        $walletStats = $user->wallet->getStats();

        // Transactions avec pagination
        $transactions = $user->financialTransactions()
            ->where('status', 'COMPLETED')
            ->with('package')
            ->orderBy('completed_at', 'desc');

        // Filtrage par type
        if ($request->filled('type')) {
            if ($request->type === 'credit') {
                $transactions->where('amount', '>', 0);
            } elseif ($request->type === 'debit') {
                $transactions->where('amount', '<', 0);
            }
        }

        // Filtrage par période
        if ($request->filled('month')) {
            $month = \Carbon\Carbon::parse($request->month);
            $transactions->whereMonth('completed_at', $month->month)
                        ->whereYear('completed_at', $month->year);
        }

        $transactions = $transactions->paginate(20);

        return view('client.wallet.index', compact('user', 'walletStats', 'transactions'));
    }

    /**
     * Demande de retrait du wallet
     */
    public function createWithdrawal()
    {
        $user = Auth::user();
        
        // Vérification du rôle
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->ensureWallet();
        $user->load('wallet');

        return view('client.wallet.withdrawal', compact('user'));
    }

    /**
     * Traitement de la demande de retrait
     */
    public function storeWithdrawal(Request $request)
    {
        $user = Auth::user();
        
        // Vérification du rôle
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $user->ensureWallet();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $user->wallet_balance,
            'method' => ['required', Rule::in(['BANK_TRANSFER', 'CASH_DELIVERY'])],
            'bank_iban' => 'required_if:method,BANK_TRANSFER|string|max:34',
            'bank_name' => 'required_if:method,BANK_TRANSFER|string|max:100',
            'bank_beneficiary' => 'required_if:method,BANK_TRANSFER|string|max:100'
        ]);

        try {
            $withdrawal = WithdrawalRequest::create([
                'client_id' => $user->id,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'bank_details' => $validated['method'] === 'BANK_TRANSFER' ? [
                    'iban' => $validated['bank_iban'],
                    'bank_name' => $validated['bank_name'],
                    'beneficiary' => $validated['bank_beneficiary']
                ] : null,
                'status' => 'PENDING'
            ]);

            return redirect()->route('client.withdrawals')
                ->with('success', "Demande de retrait #{$withdrawal->request_code} créée avec succès!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', "Erreur lors de la création: " . $e->getMessage());
        }
    }

    /**
     * Liste des demandes de retrait
     */
    public function withdrawals()
    {
        $user = Auth::user();
        
        // Vérification du rôle
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $withdrawals = $user->withdrawalRequests()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.wallet.withdrawals', compact('withdrawals'));
    }

    /**
     * Réclamations du client
     */
    public function complaints(Request $request)
    {
        $user = Auth::user();
        
        // Vérification du rôle
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }
        
        $query = $user->complaints()->with(['package', 'assignedCommercial']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $complaints = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('client.complaints.index', compact('complaints'));
    }

    /**
     * Créer une réclamation
     */
    public function createComplaint(Package $package)
    {
        // Vérification que le colis appartient au client
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        if (!$package->canBeComplained()) {
            return redirect()->back()
                ->with('error', 'Ce colis ne peut plus faire l\'objet d\'une réclamation.');
        }

        return view('client.complaints.create', compact('package'));
    }

    /**
     * Enregistrer une réclamation
     */
    public function storeComplaint(Package $package, Request $request)
    {
        if ($package->sender_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à ce colis.');
        }

        $validated = $request->validate([
            'type' => ['required', Rule::in([
                'CHANGE_COD', 'DELIVERY_DELAY', 'REQUEST_RETURN',
                'RETURN_DELAY', 'RESCHEDULE_TODAY', 'FOURTH_ATTEMPT', 'CUSTOM'
            ])],
            'description' => 'required|string|max:1000',
            'new_cod_amount' => 'required_if:type,CHANGE_COD|numeric|min:0|max:9999.999'
        ]);

        try {
            $complaint = Complaint::create([
                'package_id' => $package->id,
                'client_id' => Auth::id(),
                'type' => $validated['type'],
                'description' => $validated['description'],
                'additional_data' => $validated['type'] === 'CHANGE_COD' ? [
                    'current_cod' => $package->cod_amount,
                    'requested_cod' => $validated['new_cod_amount']
                ] : [],
                'status' => 'PENDING',
                'priority' => $validated['type'] === 'CHANGE_COD' ? 'HIGH' : 'NORMAL'
            ]);

            return redirect()->route('client.complaints')
                ->with('success', "Réclamation #{$complaint->complaint_code} créée avec succès!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', "Erreur lors de la création: " . $e->getMessage());
        }
    }

    /**
     * API - Statistiques dashboard
     */
    public function apiStats()
    {
        return response()->json($this->getDashboardStats());
    }

    /**
     * API - Solde wallet
     */
    public function apiWalletBalance()
    {
        $user = Auth::user();
        $user->ensureWallet();
        
        return response()->json([
            'balance' => (float) $user->wallet_balance,
            'pending' => (float) $user->wallet_pending,
            'available' => (float) $user->wallet->available_balance
        ]);
    }

    // ==================== MÉTHODES PRIVÉES ====================

    /**
     * Calcule les statistiques du dashboard
     */
    private function getDashboardStats(): array
    {
        $user = Auth::user();
        
        // S'assurer que le wallet existe
        $user->ensureWallet();
        $user->load('wallet');
        
        $packages = $user->sentPackages();
        
        return [
            'wallet_balance' => (float) ($user->wallet->balance ?? 0),
            'wallet_pending' => (float) ($user->wallet->pending_amount ?? 0),
            'total_packages' => $packages->count(),
            'in_progress_packages' => $packages->inProgress()->count(),
            'delivered_packages' => $packages->delivered()->count(),
            'returned_packages' => $packages->returned()->count(),
            'pending_complaints' => $user->complaints()->where('status', 'PENDING')->count(),
            'pending_withdrawals' => $user->withdrawalRequests()->where('status', 'PENDING')->count(),
            'unread_notifications' => $user->unread_notifications_count ?? 0,
            'monthly_packages' => $packages->whereMonth('created_at', now()->month)->count(),
            'monthly_delivered' => $packages->delivered()
                                           ->whereMonth('updated_at', now()->month)->count()
        ];
    }
}