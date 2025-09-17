<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\FinancialTransaction;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ClientWalletController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
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
        ];

        return view('client.wallet.index', compact('user', 'transactions', 'stats'));
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

        return view('client.wallet.withdrawal', compact('user', 'availableBalance'));
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

        $validated = $request->validate([
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:' . $availableBalance
            ],
            'reason' => 'nullable|string|max:500',
            'preferred_method' => 'required|in:BANK_TRANSFER,CASH_DELIVERY',
            'bank_details' => 'required_if:preferred_method,BANK_TRANSFER|nullable|string|max:500'
        ], [
            'amount.max' => "Le montant ne peut pas dépasser votre solde disponible ({$availableBalance} DT).",
            'bank_details.required_if' => 'Les détails bancaires sont requis pour un virement.',
            'preferred_method.required' => 'Veuillez sélectionner une méthode de retrait.',
        ]);

        try {
            DB::beginTransaction();

            // Créer la demande de retrait
            $withdrawal = WithdrawalRequest::create([
                'client_id' => $user->id,
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'method' => $validated['preferred_method'],
                'bank_details' => $validated['preferred_method'] === 'BANK_TRANSFER' ? $validated['bank_details'] : null,
                'status' => 'PENDING'
            ]);

            // Geler le montant dans le portefeuille
            $user->wallet->frozen_amount = ($user->wallet->frozen_amount ?? 0) + $validated['amount'];
            $user->wallet->save();

            // Enregistrer la transaction de gel
            $this->financialService->processTransaction([
                'user_id' => $user->id,
                'type' => 'WITHDRAWAL_FREEZE',
                'amount' => 0, // Pas de changement du solde, juste gel
                'description' => "Gel pour demande de retrait #{$withdrawal->request_code}",
                'metadata' => [
                    'withdrawal_id' => $withdrawal->id,
                    'method' => $validated['preferred_method'],
                    'frozen_amount' => $validated['amount']
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
     * Annuler une demande de retrait (uniquement si en attente)
     */
    public function cancelWithdrawal(WithdrawalRequest $withdrawal)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT' || $withdrawal->client_id !== $user->id) {
            abort(403, 'Accès non autorisé.');
        }

        if (!$withdrawal->canBeProcessed()) {
            return back()->with('error', 'Cette demande ne peut plus être annulée.');
        }

        try {
            DB::beginTransaction();

            // Dégeler le montant dans le portefeuille
            $user->ensureWallet();
            $user->wallet->frozen_amount = max(0, ($user->wallet->frozen_amount ?? 0) - $withdrawal->amount);
            $user->wallet->save();

            // Marquer la demande comme annulée
            $withdrawal->update([
                'status' => 'CANCELLED',
                'processed_at' => now(),
                'rejection_reason' => 'Annulé par le client'
            ]);

            // Enregistrer la transaction d'annulation
            $this->financialService->processTransaction([
                'user_id' => $user->id,
                'type' => 'WITHDRAWAL_CANCEL',
                'amount' => 0,
                'description' => "Annulation demande de retrait #{$withdrawal->request_code}",
                'metadata' => [
                    'withdrawal_id' => $withdrawal->id,
                    'unfrozen_amount' => $withdrawal->amount
                ]
            ]);

            DB::commit();

            return back()->with('success', 'Demande de retrait annulée avec succès. Le montant a été dégelé.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de l\'annulation: ' . $e->getMessage());
        }
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
     * Formulaire de rechargement (si applicable)
     */
    public function showTopupForm()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->ensureWallet();

        return view('client.wallet.topup', compact('user'));
    }

    /**
     * Traitement du rechargement
     */
    public function processTopup(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:10|max:1000',
            'payment_method' => 'required|in:CARD,BANK_TRANSFER',
        ]);

        // Ici, intégrer avec un service de paiement
        // Pour l'instant, simuler un paiement réussi

        try {
            $this->financialService->processTransaction([
                'user_id' => $user->id,
                'type' => 'CREDIT',
                'amount' => $validated['amount'],
                'description' => 'Rechargement portefeuille - ' . $validated['payment_method'],
                'metadata' => [
                    'payment_method' => $validated['payment_method'],
                    'topup' => true
                ]
            ]);

            return redirect()->route('client.wallet.index')
                ->with('success', "Rechargement de {$validated['amount']} DT effectué avec succès!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors du rechargement: ' . $e->getMessage());
        }
    }

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
            'available' => (float) ($user->wallet->balance - ($user->wallet->frozen_amount ?? 0))
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
}