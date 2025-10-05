<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\FinancialTransaction;
use App\Models\TopupRequest;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ClientWalletController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
        $this->configureProofStorage();
    }

    /**
     * Configurer le disque de stockage pour les justificatifs
     */
    private function configureProofStorage()
    {
        // S'assurer que le répertoire de stockage existe
        if (!Storage::disk('private')->exists('topup_proofs')) {
            Storage::disk('private')->makeDirectory('topup_proofs');
        }
    }

    /**
     * Interface principale du portefeuille
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->ensureWallet();
        $user->load(['wallet', 'clientProfile']);

        // Récupérer l'historique des transactions récentes
        $transactions = $user->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Récupérer les demandes de retrait
        $withdrawalRequests = WithdrawalRequest::where('client_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Récupérer les demandes de topup
        $topupRequests = TopupRequest::where('client_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculer les avances (colis livrés non encore payés)
        $advanceInfo = $this->calculateClientAdvance($user);

        // Statistiques du portefeuille
        $stats = [
            'total_credited' => $user->transactions()
                ->where('amount', '>', 0)
                ->where('status', 'COMPLETED')
                ->sum('amount'),
            'total_debited' => abs($user->transactions()
                ->where('amount', '<', 0)
                ->where('status', 'COMPLETED')
                ->sum('amount')),
            'pending_amount' => $user->wallet->pending_amount ?? 0,
            'frozen_amount' => $user->wallet->frozen_amount ?? 0,
            'pending_withdrawals' => $withdrawalRequests->where('status', 'PENDING')->count(),
            'pending_withdrawal_amount' => $withdrawalRequests->where('status', 'PENDING')->sum('amount'),
            'pending_topups' => $topupRequests->where('status', 'PENDING')->count(),
            'pending_topup_amount' => $topupRequests->where('status', 'PENDING')->sum('amount'),
        ];

        return view('client.wallet.index', compact('user', 'transactions', 'stats', 'withdrawalRequests', 'topupRequests', 'advanceInfo'));
    }

    /**
     * Historique détaillé des transactions avec filtres
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->ensureWallet();

        // Query de base pour les transactions
        $query = $user->transactions()->with(['package']);

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Recherche par description ou ID de transaction
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhere('transaction_id', 'like', "%{$request->search}%");
            });
        }

        // Pagination
        $transactions = $query->orderBy('created_at', 'desc')
                             ->paginate(25)
                             ->withQueryString();

        return view('client.wallet.transactions', compact('transactions'));
    }

    /**
     * Formulaire de demande de retrait
     */
    public function createWithdrawal()
    {
        $user = Auth::user();

        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->ensureWallet();
        $user->load(['wallet']);

        $availableBalance = $user->wallet->balance - ($user->wallet->frozen_amount ?? 0);

        if ($availableBalance <= 0) {
            return redirect()->route('client.wallet.index')
                ->with('error', 'Aucun montant disponible pour retrait.');
        }

        // Charger les comptes bancaires et adresses de pickup du client
        $bankAccounts = $user->bankAccounts()->recent()->get() ?? collect();
        $pickupAddresses = $user->clientPickupAddresses()->get() ?? collect();

        return view('client.wallet.withdrawal', compact('user', 'availableBalance', 'bankAccounts', 'pickupAddresses'));
    }

    /**
     * Enregistrement d'une demande de retrait
     */
    public function storeWithdrawal(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->ensureWallet();
        $user->load('wallet');

        $availableBalance = $user->wallet->balance - ($user->wallet->frozen_amount ?? 0);

        // Validation de base
        $rules = [
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:' . $availableBalance
            ],
            'reason' => 'nullable|string|max:500',
            'preferred_method' => 'required|in:BANK_TRANSFER,CASH_DELIVERY',
        ];

        $messages = [
            'amount.max' => "Le montant ne peut pas dépasser votre solde disponible ({$availableBalance} DT).",
            'preferred_method.required' => 'Veuillez sélectionner une méthode de retrait.',
        ];

        // Validation spécifique pour virement bancaire
        if ($request->preferred_method === 'BANK_TRANSFER') {
            // Si un compte bancaire existant est sélectionné
            if ($request->has('bank_account_id') && $request->bank_account_id) {
                $rules['bank_account_id'] = 'required|exists:client_bank_accounts,id';
                $messages['bank_account_id.required'] = 'Veuillez sélectionner un compte bancaire.';
                $messages['bank_account_id.exists'] = 'Le compte bancaire sélectionné n\'existe pas.';
            } else {
                // Sinon, les détails d'un nouveau compte sont requis
                $rules['bank_name'] = 'required|string|max:255';
                $rules['account_holder_name'] = 'required|string|max:255';
                $rules['iban'] = 'required|string|regex:/^TN\d{22}$/';

                $messages['bank_name.required'] = 'Le nom de la banque est requis pour un nouveau compte.';
                $messages['account_holder_name.required'] = 'Le nom du titulaire est requis.';
                $messages['iban.required'] = 'L\'IBAN est requis.';
                $messages['iban.regex'] = 'Format IBAN invalide. Format attendu: TN suivi de 22 chiffres.';
            }
        }

        $validated = $request->validate($rules, $messages);

        try {
            DB::beginTransaction();

            // Préparer les détails bancaires selon le cas
            $bankDetails = null;
            if ($validated['preferred_method'] === 'BANK_TRANSFER') {
                if (isset($validated['bank_account_id'])) {
                    // Utiliser un compte bancaire existant
                    $bankAccount = $user->bankAccounts()->findOrFail($validated['bank_account_id']);
                    $bankDetails = [
                        'bank_account_id' => $bankAccount->id,
                        'bank_name' => $bankAccount->bank_name,
                        'account_holder_name' => $bankAccount->account_holder_name,
                        'iban' => $bankAccount->iban
                    ];
                } else {
                    // Utiliser les détails d'un nouveau compte
                    $bankDetails = [
                        'bank_name' => $validated['bank_name'],
                        'account_holder_name' => $validated['account_holder_name'],
                        'iban' => strtoupper(preg_replace('/\s+/', '', $validated['iban']))
                    ];
                }
            }

            // Créer la demande de retrait
            $withdrawal = WithdrawalRequest::create([
                'client_id' => $user->id,
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'method' => $validated['preferred_method'],
                'bank_details' => $bankDetails ? json_encode($bankDetails) : null,
                'status' => 'PENDING'
            ]);

            // Geler le montant dans le portefeuille (réservation)
            $previousBalance = $user->wallet->balance;
            $user->wallet->frozen_amount = ($user->wallet->frozen_amount ?? 0) + $validated['amount'];
            $user->wallet->save();

            // Enregistrer la transaction de gel avec statut PENDING
            $transaction = $this->financialService->processTransaction([
                'user_id' => $user->id,
                'type' => 'WITHDRAWAL_RESERVE',
                'amount' => 0, // Pas de changement du solde, juste réservation
                'status' => 'PENDING',
                'description' => "Réservation pour demande de retrait #{$withdrawal->request_code}",
                'reference' => $withdrawal->request_code,
                'metadata' => [
                    'withdrawal_id' => $withdrawal->id,
                    'method' => $validated['preferred_method'],
                    'bank_details' => $bankDetails,
                    'reserved_amount' => $validated['amount'],
                    'previous_balance' => $previousBalance
                ]
            ]);

            DB::commit();

            return redirect()->route('client.withdrawals')
                ->with('success', "Demande de retrait #{$withdrawal->request_code} créée avec succès! Le montant a été gelé dans votre portefeuille.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Calculer les avances du client (colis livrés non encore payés)
     */
    private function calculateClientAdvance($user)
    {
        // Récupérer les colis livrés mais non encore payés par le client
        $deliveredPackages = $user->packages()
            ->where('status', 'DELIVERED')
            ->where('cod_amount', '>', 0)
            ->where('paid_to_client', false)
            ->get();

        $totalAdvance = $deliveredPackages->sum('cod_amount');
        $packageCount = $deliveredPackages->count();

        return [
            'total_amount' => $totalAdvance,
            'package_count' => $packageCount,
            'packages' => $deliveredPackages->take(5), // Afficher les 5 plus récents
            'has_advance' => $totalAdvance > 0
        ];
    }

    /**
     * Annuler une demande de retrait
     */
    public function cancelWithdrawal(WithdrawalRequest $withdrawal)
    {
        $user = Auth::user();

        if ($withdrawal->client_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        try {
            $withdrawal->cancel('Annulée par le client');

            return redirect()->route('client.wallet.index')
                ->with('success', "Demande de retrait #{$withdrawal->request_code} annulée avec succès. Le montant a été remboursé dans votre portefeuille.");

        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }

    /**
     * Liste des demandes de retrait
     */
    public function withdrawals()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $withdrawals = $user->withdrawalRequests()
            ->with(['processedByCommercial', 'assignedDeliverer'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client.withdrawals.index', compact('withdrawals'));
    }

    /**
     * Afficher une demande de retrait spécifique
     */
    public function showWithdrawal(WithdrawalRequest $withdrawal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT' || $withdrawal->client_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        $withdrawal->load(['processedByCommercial', 'assignedDeliverer']);

        return view('client.withdrawals.show', compact('withdrawal'));
    }


    /**
     * Télécharger un relevé de compte PDF
     */
    public function downloadStatement(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->ensureWallet();

        // Période par défaut: 30 derniers jours
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : now()->subMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : now();

        $transactions = $user->transactions()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'balance' => $user->wallet->balance,
            'period_from' => $dateFrom->format('d/m/Y'),
            'period_to' => $dateTo->format('d/m/Y'),
            'total_credits' => $transactions->where('amount', '>', 0)->sum('amount'),
            'total_debits' => abs($transactions->where('amount', '<', 0)->sum('amount')),
            'transaction_count' => $transactions->count(),
        ];

        $pdf = PDF::loadView('client.wallet.statement-pdf', compact('user', 'transactions', 'stats'));
        
        return $pdf->download("releve-compte-{$user->id}-" . now()->format('Y-m-d') . ".pdf");
    }

    /**
     * Exporter les transactions en CSV
     */
    public function exportTransactions(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $query = $user->transactions()->with(['package']);

        // Même logique de filtres que la méthode transactions()
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'transactions-' . $user->id . '-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // BOM pour UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes CSV
            fputcsv($file, [
                'ID Transaction',
                'Date',
                'Type',
                'Description',
                'Montant (DT)',
                'Statut',
                'Colis',
                'Solde avant (DT)',
                'Solde après (DT)'
            ], ';');
            
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_id,
                    $transaction->created_at->format('d/m/Y H:i:s'),
                    $transaction->type_display,
                    $transaction->description,
                    number_format($transaction->amount, 3, ',', ''),
                    $transaction->status_display,
                    $transaction->package ? $transaction->package->package_code : '',
                    number_format($transaction->wallet_balance_before ?? 0, 3, ',', ''),
                    number_format($transaction->wallet_balance_after ?? 0, 3, ',', ''),
                ], ';');
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Formulaire de demande de rechargement
     */
    public function showTopupForm()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->ensureWallet();

        // Récupérer les demandes récentes (seulement si la table existe)
        $recentRequests = collect();
        try {
            if (class_exists('App\Models\TopupRequest')) {
                $recentRequests = $user->topupRequests()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            }
        } catch (\Exception $e) {
            // Si la table n'existe pas encore, on continue avec une collection vide
            $recentRequests = collect();
        }

        return view('client.wallet.topup', compact('user', 'recentRequests'));
    }

    /**
     * Traitement de la demande de rechargement
     */
    public function processTopup(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:10000',
            'method' => 'required|in:BANK_TRANSFER,BANK_DEPOSIT,CASH',
            'bank_transfer_id' => 'required_if:method,BANK_TRANSFER,BANK_DEPOSIT|nullable|string|max:100',
            'proof_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
            'notes' => 'nullable|string|max:500',
        ], [
            'amount.min' => 'Le montant minimum est de 10 DT.',
            'amount.max' => 'Le montant maximum est de 10 000 DT.',
            'bank_transfer_id.required_if' => 'L\'identifiant de virement/versement est requis.',
            'proof_document.max' => 'Le fichier ne doit pas dépasser 5 MB.',
            'proof_document.mimes' => 'Le fichier doit être une image (JPG, PNG) ou un PDF.',
        ]);

        // Vérifier l'unicité de l'identifiant bancaire si fourni (seulement si TopupRequest existe)
        if (!empty($validated['bank_transfer_id']) && class_exists('App\Models\TopupRequest')) {
            if (!TopupRequest::isBankTransferIdUnique($validated['bank_transfer_id'])) {
                return back()
                    ->withInput()
                    ->withErrors(['bank_transfer_id' => 'Cet identifiant de virement/versement a déjà été utilisé.']);
            }
        }

        try {
            DB::beginTransaction();

            // Traitement du fichier justificatif
            $proofPath = null;
            if ($request->hasFile('proof_document')) {
                $file = $request->file('proof_document');
                $filename = 'topup_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $proofPath = $file->storeAs('topup_proofs', $filename, 'private');
            }

            // Créer la demande de rechargement (seulement si TopupRequest existe)
            $topupRequest = null;
            if (class_exists('App\Models\TopupRequest')) {
                $topupRequest = TopupRequest::create([
                    'client_id' => $user->id,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                    'bank_transfer_id' => $validated['bank_transfer_id'] ?? null,
                    'proof_document' => $proofPath,
                    'notes' => $validated['notes'] ?? null,
                    'metadata' => [
                        'created_from' => 'web',
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_at_formatted' => now()->format('d/m/Y H:i:s')
                    ]
                ]);
            }

            // Créer une transaction en attente
            $this->financialService->processTransaction([
                'user_id' => $user->id,
                'type' => 'TOPUP_REQUEST',
                'amount' => 0, // Pas de changement du solde pour l'instant
                'description' => "Demande de rechargement" . ($topupRequest ? " #{$topupRequest->request_code}" : '') . " - " . $validated['method'],
                'status' => 'PENDING',
                'metadata' => [
                    'topup_request_id' => $topupRequest ? $topupRequest->id : null,
                    'method' => $validated['method'],
                    'bank_transfer_id' => $validated['bank_transfer_id'] ?? null
                ]
            ]);

            DB::commit();

            $redirectRoute = class_exists('App\Models\TopupRequest') ? 'client.wallet.topup.requests' : 'client.wallet.index';
            $successMessage = "Demande de rechargement" . ($topupRequest ? " #{$topupRequest->request_code}" : '') . " créée avec succès!";

            return redirect()->route($redirectRoute)->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Liste des demandes de rechargement du client
     */
    public function topupRequests(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        if (!class_exists('App\Models\TopupRequest')) {
            return redirect()->route('client.wallet.index')
                ->with('error', 'Fonctionnalité non disponible.');
        }

        $query = $user->topupRequests()->with(['processedBy']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->orderBy('created_at', 'desc')
                         ->paginate(15)
                         ->withQueryString();

        // Statistiques
        $stats = [
            'total' => $user->topupRequests()->count(),
            'pending' => $user->topupRequests()->pending()->count(),
            'validated' => $user->topupRequests()->validated()->count(),
            'rejected' => $user->topupRequests()->rejected()->count(),
            'total_amount_pending' => $user->topupRequests()->pending()->sum('amount'),
        ];

        return view('client.wallet.topup-requests', compact('requests', 'stats'));
    }

    /**
     * Afficher une demande de rechargement spécifique
     */
    public function showTopupRequest(TopupRequest $topupRequest)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT' || $topupRequest->client_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        $topupRequest->load(['processedBy']);

        return view('client.wallet.topup-request-show', compact('topupRequest'));
    }

    /**
     * Annuler une demande de rechargement
     */
    public function cancelTopupRequest(TopupRequest $topupRequest)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT' || $topupRequest->client_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        if (!$topupRequest->canBeCancelled()) {
            return back()->with('error', 'Cette demande ne peut plus être annulée.');
        }

        try {
            DB::beginTransaction();

            $topupRequest->cancel('Annulé par le client');

            // Mettre à jour la transaction associée
            FinancialTransaction::where('metadata->topup_request_id', $topupRequest->id)
                ->update([
                    'status' => 'CANCELLED',
                    'description' => "Demande de rechargement #{$topupRequest->request_code} - ANNULÉE"
                ]);

            DB::commit();

            return back()->with('success', 'Demande de rechargement annulée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
    }

    /**
     * Télécharger le justificatif d'une demande
     */
    public function downloadTopupProof(TopupRequest $topupRequest)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT' || $topupRequest->client_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        if (!$topupRequest->proof_document || !Storage::disk('private')->exists($topupRequest->proof_document)) {
            abort(404, 'Justificatif non trouvé.');
        }

        $originalExtension = pathinfo($topupRequest->proof_document, PATHINFO_EXTENSION);
        $filename = 'justificatif_' . $topupRequest->request_code . '.' . $originalExtension;

        return Storage::disk('private')->download($topupRequest->proof_document, $filename);
    }

    /**
     * Afficher les détails d'une transaction spécifique
     */
    public function showTransaction(FinancialTransaction $transaction)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT' || $transaction->user_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        $transaction->load(['package']);

        // Obtenir les transactions liées (même package ou même référence)
        $relatedTransactions = collect();
        
        if ($transaction->package_id) {
            $relatedTransactions = FinancialTransaction::where('package_id', $transaction->package_id)
                ->where('user_id', $user->id)
                ->where('id', '!=', $transaction->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('client.wallet.transaction-details', compact('transaction', 'relatedTransactions'));
    }

    // ==================== API METHODS ====================

    /**
     * API - Solde du portefeuille
     */
    public function apiBalance()
    {
        $user = Auth::user();
        $user->ensureWallet();
        $user->load('wallet');

        return response()->json([
            'balance' => (float) $user->wallet->balance,
            'pending' => (float) ($user->wallet->pending_amount ?? 0),
            'frozen' => (float) ($user->wallet->frozen_amount ?? 0),
            'advance_balance' => (float) ($user->wallet->advance_balance ?? 0),
            'available' => (float) ($user->wallet->balance - ($user->wallet->frozen_amount ?? 0)),
            'total_available_for_return_fees' => (float) ($user->wallet->getTotalAvailableForReturnFeesAttribute()),
            'currency' => 'DT'
        ]);
    }

    /**
     * API - Historique des transactions (récentes)
     */
    public function apiTransactions(Request $request)
    {
        $limit = min($request->get('limit', 10), 50);
        
        $transactions = auth()->user()->transactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'type' => $transaction->type,
                    'type_display' => $transaction->type_display,
                    'amount' => (float) $transaction->amount,
                    'formatted_amount' => $transaction->formatted_amount,
                    'description' => $transaction->description,
                    'status' => $transaction->status,
                    'status_display' => $transaction->status_display,
                    'created_at' => $transaction->created_at->toISOString(),
                    'created_at_human' => $transaction->created_at->diffForHumans(),
                ];
            });

        return response()->json(['transactions' => $transactions]);
    }

    /**
     * API - Résumé du portefeuille
     */
    public function apiSummary(Request $request)
    {
        $user = auth()->user();
        $user->ensureWallet();

        $period = $request->get('period', 30); // jours
        $since = now()->subDays($period);

        $transactions = $user->transactions()
            ->where('created_at', '>=', $since)
            ->where('status', 'COMPLETED')
            ->get();

        return response()->json([
            'wallet' => [
                'balance' => (float) $user->wallet->balance,
                'pending' => (float) ($user->wallet->pending_amount ?? 0),
                'frozen' => (float) ($user->wallet->frozen_amount ?? 0),
                'available' => (float) ($user->wallet->balance - ($user->wallet->frozen_amount ?? 0))
            ],
            'period_stats' => [
                'days' => $period,
                'total_credits' => (float) $transactions->where('amount', '>', 0)->sum('amount'),
                'total_debits' => (float) abs($transactions->where('amount', '<', 0)->sum('amount')),
                'transaction_count' => $transactions->count(),
                'net_amount' => (float) $transactions->sum('amount')
            ]
        ]);
    }

    /**
     * API - Vérifier si le solde est suffisant
     */
    public function apiCheckBalance(Request $request)
    {
        $amount = $request->get('amount', 0);
        $user = auth()->user();
        $user->ensureWallet();

        $availableBalance = $user->wallet->balance - ($user->wallet->frozen_amount ?? 0);

        return response()->json([
            'sufficient' => $availableBalance >= $amount,
            'available_balance' => (float) $availableBalance,
            'requested_amount' => (float) $amount,
            'difference' => (float) ($availableBalance - $amount)
        ]);
    }

    /**
     * API - Vérifier l'unicité d'un identifiant bancaire
     */
    public function apiCheckBankTransferId(Request $request)
    {
        $bankTransferId = $request->get('bank_transfer_id');
        $excludeId = $request->get('exclude_id');
        
        if (!$bankTransferId) {
            return response()->json(['unique' => true]);
        }

        if (!class_exists('App\Models\TopupRequest')) {
            return response()->json(['unique' => true, 'message' => 'Fonctionnalité non disponible']);
        }

        $isUnique = TopupRequest::isBankTransferIdUnique($bankTransferId, $excludeId);
        
        return response()->json([
            'unique' => $isUnique,
            'message' => $isUnique ? 'Identifiant disponible' : 'Cet identifiant a déjà été utilisé'
        ]);
    }

    /**
     * Webhook - Paiement reçu
     */
    public function webhookPaymentReceived(Request $request)
    {
        // Traitement des webhooks de paiement externe
        // À implémenter selon le service de paiement utilisé
        
        return response()->json(['status' => 'received']);
    }

    /**
     * Webhook - Retrait traité
     */
    public function webhookWithdrawalProcessed(Request $request)
    {
        // Traitement des webhooks de retrait traité
        // À implémenter selon le service bancaire utilisé
        
        return response()->json(['status' => 'received']);
    }
}