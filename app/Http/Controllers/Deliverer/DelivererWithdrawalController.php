<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Services\FinancialTransactionService;
use App\Services\ActionLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelivererWithdrawalController extends Controller
{
    protected $financialService;
    protected $actionLogService;

    public function __construct(FinancialTransactionService $financialService, ActionLogService $actionLogService)
    {
        $this->financialService = $financialService;
        $this->actionLogService = $actionLogService;
    }

    /**
     * Retraits assignés au livreur
     */
    public function assigned(Request $request)
    {
        $query = WithdrawalRequest::where('assigned_deliverer_id', Auth::id())
                                 ->where('method', 'CASH_DELIVERY')
                                 ->whereIn('status', ['APPROVED', 'IN_PROGRESS'])
                                 ->with(['client', 'processedByCommercial'])
                                 ->orderBy('created_at', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_code', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('name', 'LIKE', "%{$search}%")
                                  ->orWhere('phone', 'LIKE', "%{$search}%");
                  });
            });
        }

        $withdrawals = $query->paginate(20)->appends($request->query());

        return view('deliverer.withdrawals.assigned', compact('withdrawals'));
    }

    /**
     * Détails d'une demande de retrait
     */
    public function show(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->assigned_deliverer_id !== Auth::id()) {
            abort(403, 'Cette demande ne vous est pas assignée.');
        }

        $withdrawal->load(['client', 'processedByCommercial']);

        return view('deliverer.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Accepter une demande de retrait
     */
    public function accept(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->assigned_deliverer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande ne vous est pas assignée.'
            ], 403);
        }

        if ($withdrawal->status !== 'APPROVED') {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande ne peut pas être acceptée.'
            ], 400);
        }

        try {
            $withdrawal->update([
                'status' => 'IN_PROGRESS',
                'accepted_at' => now()
            ]);

            $this->actionLogService->log(
                'WITHDRAWAL_ACCEPTED',
                'WithdrawalRequest',
                $withdrawal->id,
                'APPROVED',
                'IN_PROGRESS',
                [
                    'request_code' => $withdrawal->request_code,
                    'client_id' => $withdrawal->client_id,
                    'amount' => $withdrawal->amount
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Demande #{$withdrawal->request_code} acceptée. Vous pouvez procéder à la livraison."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'acceptation.'
            ], 500);
        }
    }

    /**
     * Marquer comme livré
     */
    public function deliver(WithdrawalRequest $withdrawal, Request $request)
    {
        if ($withdrawal->assigned_deliverer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande ne vous est pas assignée.'
            ], 403);
        }

        $validated = $request->validate([
            'delivery_confirmation_code' => 'required|string|size:6',
            'client_signature' => 'nullable|string',
            'delivery_notes' => 'nullable|string|max:500',
            'delivery_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        if ($validated['delivery_confirmation_code'] !== $withdrawal->delivery_receipt_code) {
            return response()->json([
                'success' => false,
                'message' => 'Code de confirmation incorrect.'
            ], 400);
        }

        try {
            return DB::transaction(function () use ($withdrawal, $validated, $request) {
                $updateData = [
                    'status' => 'COMPLETED',
                    'delivered_at' => now(),
                    'processing_notes' => $validated['delivery_notes'] ?? null
                ];

                // Photo de livraison
                if ($request->hasFile('delivery_photo')) {
                    $path = $request->file('delivery_photo')->store('payment_deliveries/' . $withdrawal->id, 'public');
                    $updateData['delivery_proof'] = [
                        'photo_path' => $path,
                        'signature' => $validated['client_signature'] ?? null,
                        'delivery_notes' => $validated['delivery_notes'] ?? null,
                        'delivered_by' => Auth::user()->name,
                        'delivered_at' => now()->toISOString()
                    ];
                }

                $withdrawal->update($updateData);

                // Ajouter au wallet livreur
                $this->addToDelivererWallet($withdrawal);

                $this->actionLogService->log(
                    'WITHDRAWAL_DELIVERED',
                    'WithdrawalRequest',
                    $withdrawal->id,
                    $withdrawal->status,
                    'COMPLETED',
                    [
                        'request_code' => $withdrawal->request_code,
                        'client_id' => $withdrawal->client_id,
                        'amount' => $withdrawal->amount,
                        'deliverer_id' => Auth::id()
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => "Paiement #{$withdrawal->request_code} livré avec succès! {$withdrawal->amount} DT ajouté à votre wallet."
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la livraison.'
            ], 500);
        }
    }

    /**
     * Marquer comme échec
     */
    public function markAsFailed(WithdrawalRequest $withdrawal, Request $request)
    {
        if ($withdrawal->assigned_deliverer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette demande ne vous est pas assignée.'
            ], 403);
        }

        $validated = $request->validate([
            'failure_reason' => 'required|string|in:CLIENT_ABSENT,ADDRESS_NOT_FOUND,CLIENT_REFUSES,INSUFFICIENT_FUNDS,OTHER',
            'failure_notes' => 'required|string|max:500',
            'next_attempt_date' => 'nullable|date|after:now'
        ]);

        try {
            return DB::transaction(function () use ($withdrawal, $validated) {
                $withdrawal->increment('delivery_attempts');
                
                $statusMessage = "Tentative #{$withdrawal->delivery_attempts} échouée - {$validated['failure_reason']}";
                if ($validated['failure_notes']) {
                    $statusMessage .= ' - Notes: ' . $validated['failure_notes'];
                }

                if ($withdrawal->delivery_attempts >= 3) {
                    $withdrawal->update([
                        'status' => 'REJECTED',
                        'rejection_reason' => '3 tentatives de livraison échouées',
                        'processing_notes' => $statusMessage
                    ]);
                    
                    $message = "Demande #{$withdrawal->request_code} marquée comme échouée après 3 tentatives.";
                } else {
                    $withdrawal->update([
                        'processing_notes' => $statusMessage,
                        'next_attempt_planned' => $validated['next_attempt_date'] ?? null
                    ]);
                    
                    $message = "Tentative #{$withdrawal->delivery_attempts} enregistrée.";
                }

                $this->actionLogService->log(
                    'WITHDRAWAL_ATTEMPT_FAILED',
                    'WithdrawalRequest',
                    $withdrawal->id,
                    null,
                    null,
                    [
                        'request_code' => $withdrawal->request_code,
                        'attempt_count' => $withdrawal->delivery_attempts,
                        'reason' => $validated['failure_reason'],
                        'deliverer_id' => Auth::id()
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement.'
            ], 500);
        }
    }

    /**
     * Générer reçu de livraison
     */
    public function deliveryReceipt(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->assigned_deliverer_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        if (!$withdrawal->delivery_receipt_code) {
            $withdrawal->update([
                'delivery_receipt_code' => $this->generateReceiptCode()
            ]);
        }

        $withdrawal->load(['client', 'processedByCommercial']);

        return view('deliverer.withdrawals.receipt', compact('withdrawal'));
    }

    // ==================== MÉTHODES PRIVÉES ====================

    private function addToDelivererWallet(WithdrawalRequest $withdrawal)
    {
        $deliverer = Auth::user();
        $deliverer->ensureWallet();

        $this->financialService->processTransaction([
            'user_id' => $deliverer->id,
            'type' => 'PAYMENT_DELIVERY',
            'amount' => $withdrawal->amount,
            'description' => "Livraison paiement #{$withdrawal->request_code} - Client: {$withdrawal->client->name}",
            'reference' => $withdrawal->request_code,
            'metadata' => [
                'withdrawal_request_id' => $withdrawal->id,
                'client_id' => $withdrawal->client_id,
                'client_name' => $withdrawal->client->name,
                'delivery_type' => 'cash_payment'
            ]
        ]);
    }

    private function generateReceiptCode()
    {
        do {
            $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (WithdrawalRequest::where('delivery_receipt_code', $code)->exists());

        return $code;
    }
}