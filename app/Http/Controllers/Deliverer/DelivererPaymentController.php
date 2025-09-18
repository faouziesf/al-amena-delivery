<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\User;
use App\Services\FinancialTransactionService;
use App\Services\ActionLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DelivererPaymentController extends Controller
{
    protected $financialService;
    protected $actionLogService;

    public function __construct(FinancialTransactionService $financialService, ActionLogService $actionLogService)
    {
        $this->financialService = $financialService;
        $this->actionLogService = $actionLogService;
    }

    /**
     * LISTE 5: Paiements Clients à livrer en espèces
     * Page principale des demandes de retrait assignées au livreur
     */
    public function index(Request $request)
    {
        $query = WithdrawalRequest::where('assigned_deliverer_id', Auth::id())
                                 ->where('method', 'CASH_DELIVERY') // Seulement livraisons espèces
                                 ->whereIn('status', ['IN_PROGRESS', 'APPROVED'])
                                 ->with(['client', 'processedByCommercial'])
                                 ->orderBy('created_at', 'asc');

        // Filtres
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

        $paymentRequests = $query->paginate(20)->appends($request->query());

        return view('deliverer.payments.index', compact('paymentRequests'));
    }

    /**
     * Détails d'une demande de paiement
     */
    public function show(WithdrawalRequest $withdrawalRequest)
    {
        // Vérifier que c'est assigné à ce livreur
        if ($withdrawalRequest->assigned_deliverer_id !== Auth::id()) {
            abort(403, 'Ce paiement ne vous est pas assigné.');
        }

        $withdrawalRequest->load(['client', 'processedByCommercial']);

        return view('deliverer.payments.show', compact('withdrawalRequest'));
    }

    /**
     * Marquer un paiement comme livré avec confirmation
     */
    public function markDelivered(WithdrawalRequest $withdrawalRequest, Request $request)
    {
        if ($withdrawalRequest->assigned_deliverer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce paiement ne vous est pas assigné.'
            ], 403);
        }

        if (!in_array($withdrawalRequest->status, ['IN_PROGRESS', 'APPROVED'])) {
            return response()->json([
                'success' => false,
                'message' => 'Ce paiement ne peut pas être livré dans son état actuel.'
            ], 400);
        }

        $validated = $request->validate([
            'delivery_confirmation_code' => 'required|string|size:6', // Code à 6 chiffres généré
            'client_signature' => 'nullable|string',
            'delivery_notes' => 'nullable|string|max:500',
            'delivery_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        // Vérifier le code de confirmation
        if ($validated['delivery_confirmation_code'] !== $withdrawalRequest->delivery_receipt_code) {
            return response()->json([
                'success' => false,
                'message' => 'Code de confirmation incorrect. Vérifiez le code-barres sur le bon de livraison.'
            ], 400);
        }

        try {
            return DB::transaction(function () use ($withdrawalRequest, $validated, $request) {
                $updateData = [
                    'status' => 'COMPLETED',
                    'delivered_at' => now(),
                    'processing_notes' => $validated['delivery_notes'] ?? null
                ];

                // Photo de livraison
                if ($request->hasFile('delivery_photo')) {
                    $path = $request->file('delivery_photo')->store('payment_deliveries/' . $withdrawalRequest->id, 'public');
                    $updateData['delivery_proof'] = [
                        'photo_path' => $path,
                        'signature' => $validated['client_signature'] ?? null,
                        'delivery_notes' => $validated['delivery_notes'] ?? null,
                        'delivered_by' => Auth::user()->name,
                        'delivered_at' => now()->toISOString()
                    ];
                }

                $withdrawalRequest->update($updateData);

                // WALLET LIVREUR = CAISSE PHYSIQUE : Ajouter le montant immédiatement
                $this->addPaymentAmountToDelivererWallet($withdrawalRequest);

                // Log de l'action
                $this->actionLogService->log(
                    'PAYMENT_DELIVERED',
                    'WithdrawalRequest',
                    $withdrawalRequest->id,
                    $withdrawalRequest->status,
                    'COMPLETED',
                    [
                        'request_code' => $withdrawalRequest->request_code,
                        'client_id' => $withdrawalRequest->client_id,
                        'amount' => $withdrawalRequest->amount,
                        'deliverer_id' => Auth::id()
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => "Paiement #{$withdrawalRequest->request_code} livré avec succès! Montant {$withdrawalRequest->amount} DT ajouté à votre wallet."
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la livraison du paiement.'
            ], 500);
        }
    }

    /**
     * Marquer client non disponible pour paiement
     */
    public function markUnavailable(WithdrawalRequest $withdrawalRequest, Request $request)
    {
        if ($withdrawalRequest->assigned_deliverer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce paiement ne vous est pas assigné.'
            ], 403);
        }

        $validated = $request->validate([
            'unavailable_reason' => 'required|string|in:CLIENT_ABSENT,ADDRESS_NOT_FOUND,CLIENT_REFUSES,OTHER',
            'attempt_notes' => 'required|string|max:500',
            'next_attempt_date' => 'nullable|date|after:now'
        ]);

        try {
            return DB::transaction(function () use ($withdrawalRequest, $validated) {
                // Increment attempt counter
                $withdrawalRequest->increment('delivery_attempts');
                
                $statusMessage = "Tentative de livraison #{$withdrawalRequest->delivery_attempts} - {$validated['unavailable_reason']}";
                if ($validated['attempt_notes']) {
                    $statusMessage .= ' - Notes: ' . $validated['attempt_notes'];
                }

                // Si plus de 3 tentatives, marquer comme problématique
                if ($withdrawalRequest->delivery_attempts >= 3) {
                    $withdrawalRequest->update([
                        'status' => 'REJECTED',
                        'rejection_reason' => '3 tentatives de livraison échouées',
                        'processing_notes' => $statusMessage
                    ]);
                    
                    $message = "Paiement #{$withdrawalRequest->request_code} marqué comme échoué après 3 tentatives.";
                } else {
                    $withdrawalRequest->update([
                        'processing_notes' => $statusMessage,
                        'next_attempt_planned' => $validated['next_attempt_date'] ?? null
                    ]);
                    
                    $message = "Tentative #{$withdrawalRequest->delivery_attempts} enregistrée pour le paiement #{$withdrawalRequest->request_code}.";
                }

                // Log de l'action
                $this->actionLogService->log(
                    'PAYMENT_ATTEMPT_FAILED',
                    'WithdrawalRequest',
                    $withdrawalRequest->id,
                    null,
                    null,
                    [
                        'request_code' => $withdrawalRequest->request_code,
                        'attempt_count' => $withdrawalRequest->delivery_attempts,
                        'reason' => $validated['unavailable_reason'],
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
                'message' => 'Erreur lors de l\'enregistrement de la tentative.'
            ], 500);
        }
    }

    /**
     * API - Compter les paiements en attente
     */
    public function apiPaymentsCount()
    {
        $count = WithdrawalRequest::where('assigned_deliverer_id', Auth::id())
                                 ->where('method', 'CASH_DELIVERY')
                                 ->whereIn('status', ['IN_PROGRESS', 'APPROVED'])
                                 ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * API - Générer code de confirmation pour paiement
     */
    public function apiGenerateConfirmationCode(WithdrawalRequest $withdrawalRequest)
    {
        if ($withdrawalRequest->assigned_deliverer_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.'
            ], 403);
        }

        // Générer un nouveau code si nécessaire
        if (!$withdrawalRequest->delivery_receipt_code) {
            $code = $this->generateDeliveryReceiptCode();
            $withdrawalRequest->update([
                'delivery_receipt_code' => $code
            ]);
        }

        return response()->json([
            'success' => true,
            'code' => $withdrawalRequest->delivery_receipt_code,
            'barcode_url' => $this->generateBarcodeUrl($withdrawalRequest->delivery_receipt_code)
        ]);
    }

    /**
     * Imprimer bon de livraison paiement
     */
    public function printDeliveryReceipt(WithdrawalRequest $withdrawalRequest)
    {
        if ($withdrawalRequest->assigned_deliverer_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        // Générer code si nécessaire
        if (!$withdrawalRequest->delivery_receipt_code) {
            $withdrawalRequest->update([
                'delivery_receipt_code' => $this->generateDeliveryReceiptCode()
            ]);
        }

        $withdrawalRequest->load(['client', 'processedByCommercial']);

        return view('deliverer.payments.receipt', compact('withdrawalRequest'));
    }

    /**
     * Historique des paiements livrés
     */
    public function history(Request $request)
    {
        $query = WithdrawalRequest::where('assigned_deliverer_id', Auth::id())
                                 ->where('method', 'CASH_DELIVERY')
                                 ->where('status', 'COMPLETED')
                                 ->with(['client', 'processedByCommercial'])
                                 ->orderBy('delivered_at', 'desc');

        if ($request->filled('date_from')) {
            $query->whereDate('delivered_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('delivered_at', '<=', $request->date_to);
        }

        $completedPayments = $query->paginate(20)->appends($request->query());

        // Stats pour la période
        $stats = [
            'total_delivered' => $completedPayments->total(),
            'total_amount' => $query->sum('amount'),
            'average_amount' => $query->avg('amount')
        ];

        return view('deliverer.payments.history', compact('completedPayments', 'stats'));
    }

    // ==================== MÉTHODES PRIVÉES ====================

    /**
     * Ajouter montant paiement au wallet livreur (wallet = caisse physique)
     */
    private function addPaymentAmountToDelivererWallet(WithdrawalRequest $withdrawalRequest)
    {
        $deliverer = Auth::user();
        $deliverer->ensureWallet();

        // Transaction : Paiement client → Wallet livreur (immédiat)
        $this->financialService->processTransaction([
            'user_id' => $deliverer->id,
            'type' => 'PAYMENT_DELIVERY',
            'amount' => $withdrawalRequest->amount,
            'description' => "Livraison paiement #{$withdrawalRequest->request_code} - Client: {$withdrawalRequest->client->name}",
            'reference' => $withdrawalRequest->request_code,
            'metadata' => [
                'withdrawal_request_id' => $withdrawalRequest->id,
                'client_id' => $withdrawalRequest->client_id,
                'client_name' => $withdrawalRequest->client->name,
                'delivery_type' => 'cash_payment'
            ]
        ]);
    }

    /**
     * Générer code de réception pour bon de livraison
     */
    private function generateDeliveryReceiptCode()
    {
        do {
            $code = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        } while (WithdrawalRequest::where('delivery_receipt_code', $code)->exists());

        return $code;
    }

    /**
     * Générer URL code-barres
     */
    private function generateBarcodeUrl($code)
    {
        // Utiliser un service de génération de code-barres
        // Par exemple: https://barcode.tec-it.com/en/Code128?data=123456
        return "https://barcode.tec-it.com/en/Code128?data={$code}";
    }

    /**
     * Valider que le livreur a les fonds nécessaires (sécurité)
     */
    private function validateDelivererHasFunds(WithdrawalRequest $withdrawalRequest)
    {
        $deliverer = Auth::user();
        $commercial = $withdrawalRequest->processedByCommercial;
        
        // Dans ce workflow, le commercial remet les espèces au livreur AVANT la livraison
        // Le livreur doit donc avoir reçu les fonds du commercial
        
        // Cette vérification peut être faite via un système de "remise d'espèces"
        // ou simplement par confiance du processus commercial → livreur
        
        return true; // Simplifié pour MVP
    }

    /**
     * Process de récupération des fonds auprès du commercial
     * Cette fonction serait appelée quand le commercial remet les espèces au livreur
     */
    public function receiveFundsFromCommercial(WithdrawalRequest $withdrawalRequest, Request $request)
    {
        $validated = $request->validate([
            'commercial_confirmation' => 'required|string',
            'amount_received' => 'required|numeric',
            'receipt_photo' => 'nullable|image'
        ]);

        // Vérifier que le montant correspond
        if (abs($validated['amount_received'] - $withdrawalRequest->amount) > 0.001) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant reçu ne correspond pas au montant à livrer.'
            ], 400);
        }

        try {
            $withdrawalRequest->update([
                'status' => 'IN_PROGRESS', // Prêt pour livraison
                'funds_received_at' => now(),
                'funds_received_confirmation' => $validated['commercial_confirmation']
            ]);

            return response()->json([
                'success' => true,
                'message' => "Fonds reçus du commercial. Vous pouvez maintenant procéder à la livraison."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation de réception des fonds.'
            ], 500);
        }
    }
}