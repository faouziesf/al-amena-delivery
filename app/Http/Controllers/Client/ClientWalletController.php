<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientWalletController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Interface du portefeuille
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role !== 'CLIENT') {
            abort(403, 'Accès non autorisé.');
        }

        $user->ensureWallet();
        $user->load(['wallet', 'clientProfile']);

        // Récupérer l'historique des transactions
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
            'preferred_method' => 'required|in:CASH,BANK_TRANSFER',
            'bank_details' => 'required_if:preferred_method,BANK_TRANSFER|nullable|string|max:500'
        ], [
            'amount.max' => "Le montant ne peut pas dépasser votre solde disponible ({$availableBalance} DT).",
            'bank_details.required_if' => 'Les détails bancaires sont requis pour un virement.'
        ]);

        try {
            DB::beginTransaction();

            // Créer la demande de retrait
            $withdrawal = WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'preferred_method' => $validated['preferred_method'],
                'bank_details' => $validated['bank_details'],
                'status' => 'PENDING'
            ]);

            // Geler le montant dans le portefeuille
            $user->wallet->frozen_amount = ($user->wallet->frozen_amount ?? 0) + $validated['amount'];
            $user->wallet->save();

            // Enregistrer la transaction de gel
            $this->financialService->processTransaction([
                'user_id' => $user->id,
                'type' => 'WITHDRAWAL_FREEZE',
                'amount' => -$validated['amount'],
                'withdrawal_request_id' => $withdrawal->id,
                'description' => "Gel pour demande de retrait #{$withdrawal->id}",
                'metadata' => [
                    'withdrawal_id' => $withdrawal->id,
                    'method' => $validated['preferred_method']
                ]
            ]);

            DB::commit();

            return redirect()->route('client.withdrawals')
                ->with('success', "Demande de retrait #{$withdrawal->id} créée avec succès!");

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
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client.withdrawals.index', compact('withdrawals'));
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
            'pending' => (float) $user->wallet->pending_amount,
            'frozen' => (float) ($user->wallet->frozen_amount ?? 0),
            'available' => (float) ($user->wallet->balance - ($user->wallet->frozen_amount ?? 0))
        ]);
    }

    /**
     * API - Historique des transactions (récentes)
     */
    public function apiTransactions()
    {
        $transactions = auth()->user()->transactions()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['transactions' => $transactions]);
    }
}