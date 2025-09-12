<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Services\CommercialService;
use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    protected $commercialService;

    public function __construct(CommercialService $commercialService)
    {
        $this->commercialService = $commercialService;
    }

    public function index(Request $request)
    {
        $query = WithdrawalRequest::with(['client', 'processedByCommercial', 'assignedDeliverer']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // Par défaut, afficher les demandes en attente et approuvées
            $query->whereIn('status', ['PENDING', 'APPROVED']);
        }

        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }

        if ($request->filled('processed_by_me') && $request->processed_by_me) {
            $query->where('processed_by_commercial_id', Auth::id());
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_code', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($client) use ($search) {
                      $client->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $withdrawals = $query->orderBy('created_at', 'asc')->paginate(20);

        $stats = [
            'total_pending' => WithdrawalRequest::where('status', 'PENDING')->count(),
            'total_amount_pending' => WithdrawalRequest::where('status', 'PENDING')->sum('amount'),
            'bank_transfers_pending' => WithdrawalRequest::where('status', 'PENDING')
                                                        ->where('method', 'BANK_TRANSFER')
                                                        ->count(),
            'cash_deliveries_pending' => WithdrawalRequest::where('status', 'PENDING')
                                                         ->where('method', 'CASH_DELIVERY')
                                                         ->count(),
            'approved_awaiting_delivery' => WithdrawalRequest::where('status', 'APPROVED')
                                                            ->where('method', 'CASH_DELIVERY')
                                                            ->count(),
            'processed_by_me_today' => WithdrawalRequest::where('processed_by_commercial_id', Auth::id())
                                                       ->whereDate('processed_at', today())
                                                       ->count(),
        ];

        return view('commercial.withdrawals.index', compact('withdrawals', 'stats'));
    }

    public function show(WithdrawalRequest $withdrawal)
    {
        $withdrawal->load([
            'client.wallet',
            'processedByCommercial',
            'assignedDeliverer'
        ]);

        return view('commercial.withdrawals.show', compact('withdrawal'));
    }

    public function approve(Request $request, WithdrawalRequest $withdrawal)
    {
        if (!$withdrawal->canBeProcessed()) {
            return back()->withErrors(['error' => 'Cette demande de retrait ne peut plus être traitée.']);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Vérifier que le client a suffisamment de fonds
            if ($withdrawal->client->wallet->balance < $withdrawal->amount) {
                return back()->withErrors([
                    'error' => 'Le client n\'a pas suffisamment de fonds. Solde actuel: ' . 
                               number_format($withdrawal->client->wallet->balance, 3) . ' DT'
                ]);
            }

            $this->commercialService->processWithdrawalRequest(
                $withdrawal,
                $withdrawal->method === 'BANK_TRANSFER' ? 'approve_bank_transfer' : 'approve_cash_delivery',
                ['notes' => $request->notes],
                Auth::user()
            );

            $message = 'Demande de retrait approuvée avec succès.';
            if ($withdrawal->method === 'CASH_DELIVERY') {
                $message .= ' Code de livraison: ' . $withdrawal->delivery_receipt_code;
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'approbation: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, WithdrawalRequest $withdrawal)
    {
        if (!$withdrawal->canBeProcessed()) {
            return back()->withErrors(['error' => 'Cette demande de retrait ne peut plus être traitée.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $this->commercialService->processWithdrawalRequest(
                $withdrawal,
                'reject',
                ['rejection_reason' => $request->rejection_reason],
                Auth::user()
            );

            return back()->with('success', 'Demande de retrait rejetée avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du rejet: ' . $e->getMessage()]);
        }
    }

    public function assignToDeliverer(Request $request, WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'APPROVED' || $withdrawal->method !== 'CASH_DELIVERY') {
            return back()->withErrors(['error' => 'Cette demande ne peut pas être assignée à un livreur.']);
        }

        $request->validate([
            'deliverer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $deliverer = User::where('role', 'DELIVERER')
                           ->where('account_status', 'ACTIVE')
                           ->findOrFail($request->deliverer_id);

            $this->commercialService->assignWithdrawalToDeliverer($withdrawal, $deliverer);

            return back()->with('success', 
                "Retrait assigné au livreur {$deliverer->name}. Code: {$withdrawal->delivery_receipt_code}"
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'assignation: ' . $e->getMessage()]);
        }
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'withdrawal_ids' => 'required|array|min:1',
            'withdrawal_ids.*' => 'exists:withdrawal_requests,id',
            'bulk_notes' => 'nullable|string|max:500',
        ]);

        try {
            $approvedCount = 0;
            $errors = [];
            
            $withdrawals = WithdrawalRequest::whereIn('id', $request->withdrawal_ids)
                                          ->where('status', 'PENDING')
                                          ->get();

            foreach ($withdrawals as $withdrawal) {
                try {
                    // Vérifier le solde du client
                    if ($withdrawal->client->wallet->balance < $withdrawal->amount) {
                        $errors[] = "Retrait {$withdrawal->request_code}: Solde insuffisant";
                        continue;
                    }

                    $this->commercialService->processWithdrawalRequest(
                        $withdrawal,
                        $withdrawal->method === 'BANK_TRANSFER' ? 'approve_bank_transfer' : 'approve_cash_delivery',
                        ['notes' => $request->bulk_notes],
                        Auth::user()
                    );

                    $approvedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Retrait {$withdrawal->request_code}: {$e->getMessage()}";
                }
            }

            $message = "{$approvedCount} demandes approuvées avec succès.";
            if (!empty($errors)) {
                $message .= " Erreurs: " . implode(', ', $errors);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'approbation groupée: ' . $e->getMessage()]);
        }
    }

    public function generateDeliveryReceipt(WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->method !== 'CASH_DELIVERY' || $withdrawal->status !== 'APPROVED') {
            abort(404);
        }

        // TODO: Implémenter la génération PDF avec DomPDF
        // $pdf = PDF::loadView('commercial.withdrawals.delivery_receipt', compact('withdrawal'));
        // return $pdf->stream("bon_livraison_{$withdrawal->delivery_receipt_code}.pdf");
        
        // Pour l'instant, rediriger vers une vue simple
        return view('commercial.withdrawals.delivery_receipt', compact('withdrawal'));
    }

    public function markAsDelivered(Request $request, WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'IN_PROGRESS' || $withdrawal->method !== 'CASH_DELIVERY') {
            return back()->withErrors(['error' => 'Ce retrait ne peut pas être marqué comme livré.']);
        }

        $request->validate([
            'delivery_notes' => 'nullable|string|max:500',
            'client_signature' => 'nullable|string', // Base64 de la signature si disponible
        ]);

        try {
            $withdrawal->markAsDelivered([
                'delivered_by_commercial' => Auth::user()->name,
                'delivery_notes' => $request->delivery_notes,
                'client_signature' => $request->client_signature,
                'confirmed_at' => now()->toISOString(),
            ]);

            return back()->with('success', 
                "Retrait marqué comme livré. Montant: " . number_format($withdrawal->amount, 3) . " DT"
            );
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la confirmation: ' . $e->getMessage()]);
        }
    }

    // ==================== API ENDPOINTS ====================

    public function apiPending()
    {
        $withdrawals = WithdrawalRequest::with(['client'])
                                      ->where('status', 'PENDING')
                                      ->orderBy('created_at', 'asc')
                                      ->limit(10)
                                      ->get()
                                      ->map(function ($withdrawal) {
                                          return [
                                              'id' => $withdrawal->id,
                                              'request_code' => $withdrawal->request_code,
                                              'client_name' => $withdrawal->client->name,
                                              'amount' => number_format($withdrawal->amount, 3),
                                              'method' => $withdrawal->method_display,
                                              'created_at' => $withdrawal->created_at->diffForHumans(),
                                              'show_url' => route('commercial.withdrawals.show', $withdrawal->id),
                                          ];
                                      });

        return response()->json($withdrawals);
    }

    public function apiStats()
    {
        $stats = [
            'pending_count' => WithdrawalRequest::where('status', 'PENDING')->count(),
            'pending_amount' => WithdrawalRequest::where('status', 'PENDING')->sum('amount'),
            'approved_cash_delivery' => WithdrawalRequest::where('status', 'APPROVED')
                                                        ->where('method', 'CASH_DELIVERY')
                                                        ->count(),
            'in_progress_count' => WithdrawalRequest::where('status', 'IN_PROGRESS')->count(),
            'completed_today' => WithdrawalRequest::where('status', 'COMPLETED')
                                                 ->whereDate('delivered_at', today())
                                                 ->count(),
            'processed_by_me_today' => WithdrawalRequest::where('processed_by_commercial_id', Auth::id())
                                                       ->whereDate('processed_at', today())
                                                       ->count(),
        ];

        return response()->json($stats);
    }

    public function apiAwaitingDelivery()
    {
        $withdrawals = WithdrawalRequest::with(['client', 'assignedDeliverer'])
                                      ->where('status', 'APPROVED')
                                      ->where('method', 'CASH_DELIVERY')
                                      ->orderBy('processed_at', 'asc')
                                      ->get()
                                      ->map(function ($withdrawal) {
                                          return [
                                              'id' => $withdrawal->id,
                                              'request_code' => $withdrawal->request_code,
                                              'delivery_receipt_code' => $withdrawal->delivery_receipt_code,
                                              'client_name' => $withdrawal->client->name,
                                              'client_phone' => $withdrawal->client->phone,
                                              'amount' => number_format($withdrawal->amount, 3),
                                              'assigned_deliverer' => $withdrawal->assignedDeliverer->name ?? 'Non assigné',
                                              'processed_at' => $withdrawal->processed_at->diffForHumans(),
                                              'assign_url' => route('commercial.withdrawals.assign', $withdrawal->id),
                                          ];
                                      });

        return response()->json($withdrawals);
    }

    public function apiSearchClients(Request $request)
    {
        $search = $request->input('q', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        // Chercher les clients avec un solde suffisant
        $clients = User::where('role', 'CLIENT')
                      ->where('account_status', 'ACTIVE')
                      ->where(function ($query) use ($search) {
                          $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                      })
                      ->with('wallet')
                      ->limit(10)
                      ->get()
                      ->filter(function ($client) {
                          return ($client->wallet->balance ?? 0) > 0;
                      })
                      ->map(function ($client) {
                          return [
                              'id' => $client->id,
                              'name' => $client->name,
                              'email' => $client->email,
                              'phone' => $client->phone,
                              'wallet_balance' => number_format($client->wallet->balance, 3),
                              'display_name' => "{$client->name} ({$client->wallet->balance} DT)",
                          ];
                      })
                      ->values();

        return response()->json($clients);
    }
}