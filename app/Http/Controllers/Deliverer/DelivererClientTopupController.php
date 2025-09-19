<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TopupRequest;
use App\Services\FinancialTransactionService;
use App\Services\ActionLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelivererClientTopupController extends Controller
{
    protected $financialService;
    protected $actionLogService;

    public function __construct(FinancialTransactionService $financialService, ActionLogService $actionLogService)
    {
        $this->financialService = $financialService;
        $this->actionLogService = $actionLogService;
    }

    /**
     * Interface de recharge client
     */
    public function index()
    {
        // Stats du livreur pour les recharges
        $stats = [
            'total_topups_today' => TopupRequest::where('processed_by_id', Auth::id())
                                             ->whereDate('processed_at', today())
                                             ->where('method', 'CASH')
                                             ->count(),
            'total_amount_today' => TopupRequest::where('processed_by_id', Auth::id())
                                              ->whereDate('processed_at', today())
                                              ->where('method', 'CASH')
                                              ->where('status', 'VALIDATED')
                                              ->sum('amount'),
            'pending_validation' => TopupRequest::where('processed_by_id', Auth::id())
                                               ->where('method', 'CASH')
                                               ->where('status', 'PENDING')
                                               ->count(),
            'total_clients_helped' => TopupRequest::where('processed_by_id', Auth::id())
                                                 ->where('method', 'CASH')
                                                 ->distinct('client_id')
                                                 ->count()
        ];

        // Historique récent des recharges
        $recentTopups = TopupRequest::where('processed_by_id', Auth::id())
                                  ->where('method', 'CASH')
                                  ->with(['client'])
                                  ->orderBy('processed_at', 'desc')
                                  ->limit(10)
                                  ->get();

        return view('deliverer.client-topup.index', compact('stats', 'recentTopups'));
    }

    /**
     * Processus de recharge client
     */
    public function processTopup(Request $request)
    {
        $validated = $request->validate([
            'client_phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:1|max:1000',
            'payment_method' => 'required|in:CASH',
            'client_name' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
            'receipt_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        try {
            return DB::transaction(function () use ($validated, $request) {
                // Rechercher le client par téléphone
                $client = User::where('phone', $validated['client_phone'])
                            ->where('role', 'CLIENT')
                            ->first();

                if (!$client) {
                    return response()->json([
                        'success' => false,
                        'message' => "Aucun client trouvé avec ce numéro : {$validated['client_phone']}"
                    ]);
                }

                // Vérifier que le nom correspond (sécurité)
                $clientName = strtolower(trim($validated['client_name']));
                $realName = strtolower(trim($client->name));
                
                if (strpos($realName, $clientName) === false && strpos($clientName, $realName) === false) {
                    return response()->json([
                        'success' => false,
                        'message' => "Le nom fourni ne correspond pas au client. Nom enregistré : {$client->name}"
                    ]);
                }

                // S'assurer que le client a un wallet
                $client->ensureWallet();

                // Prendre photo de reçu si fournie
                $proofPath = null;
                if ($request->hasFile('receipt_photo')) {
                    $proofPath = $request->file('receipt_photo')->store(
                        'topup_receipts/' . $client->id,
                        'public'
                    );
                }

                // Créer la demande de recharge
                $topupRequest = TopupRequest::create([
                    'request_code' => $this->generateTopupCode(),
                    'client_id' => $client->id,
                    'amount' => $validated['amount'],
                    'method' => 'CASH',
                    'proof_document' => $proofPath,
                    'notes' => $validated['notes'],
                    'status' => 'VALIDATED', // Auto-validé pour les recharges cash livreur
                    'processed_by_id' => Auth::id(),
                    'processed_at' => now(),
                    'validation_notes' => 'Recharge cash effectuée par livreur ' . Auth::user()->name,
                    'metadata' => [
                        'deliverer_id' => Auth::id(),
                        'deliverer_name' => Auth::user()->name,
                        'client_phone_verified' => $validated['client_phone'],
                        'client_name_verified' => $validated['client_name'],
                        'location' => 'field_topup'
                    ]
                ]);

                // Traitement financier : Double ajout (client + livreur)
                
                // 1. Ajouter au wallet client
                $this->financialService->processTransaction([
                    'user_id' => $client->id,
                    'type' => 'WALLET_RECHARGE',
                    'amount' => $validated['amount'],
                    'description' => "Recharge cash par livreur - {$topupRequest->request_code}",
                    'reference' => $topupRequest->request_code,
                    'metadata' => [
                        'topup_request_id' => $topupRequest->id,
                        'processed_by_deliverer' => Auth::id(),
                        'recharge_type' => 'cash_field'
                    ]
                ]);

                // 2. Ajouter au wallet livreur (wallet = caisse physique)
                $deliverer = Auth::user();
                $deliverer->ensureWallet();
                
                $this->financialService->processTransaction([
                    'user_id' => $deliverer->id,
                    'type' => 'CLIENT_FUND_ADD',
                    'amount' => $validated['amount'],
                    'description' => "Fond client {$client->name} - {$topupRequest->request_code}",
                    'reference' => $topupRequest->request_code,
                    'metadata' => [
                        'topup_request_id' => $topupRequest->id,
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                        'client_phone' => $client->phone,
                        'recharge_type' => 'cash_field'
                    ]
                ]);

                // Log de l'action
                $this->actionLogService->log(
                    'CLIENT_TOPUP_CASH',
                    'TopupRequest',
                    $topupRequest->id,
                    null,
                    'VALIDATED',
                    [
                        'client_id' => $client->id,
                        'client_name' => $client->name,
                        'amount' => $validated['amount'],
                        'deliverer_id' => Auth::id(),
                        'topup_code' => $topupRequest->request_code
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => "Recharge de {$validated['amount']} DT effectuée avec succès pour {$client->name}!",
                    'data' => [
                        'topup_code' => $topupRequest->request_code,
                        'client_name' => $client->name,
                        'amount' => $validated['amount'],
                        'new_client_balance' => $client->wallet->fresh()->balance,
                        'deliverer_wallet_increase' => $validated['amount']
                    ]
                ]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recharge : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechercher client par téléphone
     */
    public function searchClient(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20'
        ]);

        try {
            $client = User::where('phone', $validated['phone'])
                         ->where('role', 'CLIENT')
                         ->with('wallet')
                         ->first();

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun client trouvé avec ce numéro.'
                ]);
            }

            $client->ensureWallet();

            return response()->json([
                'success' => true,
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'phone' => $client->phone,
                    'email' => $client->email,
                    'wallet_balance' => $client->wallet->balance,
                    'account_status' => $client->account_status,
                    'last_topup' => TopupRequest::where('client_id', $client->id)
                                              ->where('status', 'VALIDATED')
                                              ->latest()
                                              ->first()?->processed_at?->diffForHumans()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche.'
            ], 500);
        }
    }

    /**
     * Historique des recharges effectuées
     */
    public function history(Request $request)
    {
        $query = TopupRequest::where('processed_by_id', Auth::id())
                           ->where('method', 'CASH')
                           ->with(['client'])
                           ->orderBy('processed_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('processed_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('processed_at', '<=', $request->date_to);
        }

        if ($request->filled('client_search')) {
            $search = $request->client_search;
            $query->whereHas('client', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $topups = $query->paginate(20)->appends($request->query());

        // Stats pour la période filtrée
        $stats = [
            'total_topups' => $query->count(),
            'total_amount' => $query->where('status', 'VALIDATED')->sum('amount'),
            'unique_clients' => $query->distinct('client_id')->count(),
            'average_amount' => $query->where('status', 'VALIDATED')->avg('amount')
        ];

        return view('deliverer.client-topup.history', compact('topups', 'stats'));
    }

    /**
     * Détails d'une recharge
     */
    public function show(TopupRequest $topupRequest)
    {
        if ($topupRequest->processed_by_id !== Auth::id()) {
            abort(403, 'Cette recharge n\'a pas été effectuée par vous.');
        }

        $topupRequest->load(['client', 'processedBy']);

        return view('deliverer.client-topup.show', compact('topupRequest'));
    }

    /**
     * Imprimer reçu de recharge
     */
    public function printReceipt(TopupRequest $topupRequest)
    {
        if ($topupRequest->processed_by_id !== Auth::id()) {
            abort(403, 'Accès non autorisé.');
        }

        $topupRequest->load(['client', 'processedBy']);

        return view('deliverer.client-topup.receipt', compact('topupRequest'));
    }

    /**
     * API Stats recharges
     */
    public function apiStats()
    {
        $delivererId = Auth::id();
        
        return response()->json([
            'today' => [
                'count' => TopupRequest::where('processed_by_id', $delivererId)
                                     ->whereDate('processed_at', today())
                                     ->where('method', 'CASH')
                                     ->count(),
                'amount' => TopupRequest::where('processed_by_id', $delivererId)
                                      ->whereDate('processed_at', today())
                                      ->where('method', 'CASH')
                                      ->where('status', 'VALIDATED')
                                      ->sum('amount')
            ],
            'this_week' => [
                'count' => TopupRequest::where('processed_by_id', $delivererId)
                                     ->whereBetween('processed_at', [now()->startOfWeek(), now()->endOfWeek()])
                                     ->where('method', 'CASH')
                                     ->count(),
                'amount' => TopupRequest::where('processed_by_id', $delivererId)
                                      ->whereBetween('processed_at', [now()->startOfWeek(), now()->endOfWeek()])
                                      ->where('method', 'CASH')
                                      ->where('status', 'VALIDATED')
                                      ->sum('amount')
            ],
            'total_clients_helped' => TopupRequest::where('processed_by_id', $delivererId)
                                                 ->where('method', 'CASH')
                                                 ->distinct('client_id')
                                                 ->count()
        ]);
    }

    // ==================== MÉTHODES PRIVÉES ====================

    private function generateTopupCode()
    {
        do {
            $code = 'TPD_' . Auth::id() . '_' . strtoupper(substr(uniqid(), -6));
        } while (TopupRequest::where('request_code', $code)->exists());

        return $code;
    }
}