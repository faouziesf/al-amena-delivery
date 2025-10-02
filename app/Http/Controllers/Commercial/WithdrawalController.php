<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    // Plus besoin de CommercialService car les méthodes approve/reject utilisent maintenant directement le modèle

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = WithdrawalRequest::with(['client', 'processedByCommercial', 'assignedDeliverer']);

        // FILTRAGE PAR GOUVERNORATS POUR CHEF DÉPÔT
        if ($user->role === 'DEPOT_MANAGER' && $user->assigned_gouvernorats) {
            $assignedGouvernorats = is_array($user->assigned_gouvernorats)
                ? $user->assigned_gouvernorats
                : json_decode($user->assigned_gouvernorats, true) ?? [];

            if (!empty($assignedGouvernorats)) {
                // Les chefs de dépôt voient seulement les paiements espèces des gouvernorats qu'ils gèrent
                $query->where('method', 'CASH_DELIVERY')
                      ->where(function($subQuery) use ($assignedGouvernorats) {
                          // Méthode 1: Via saved_addresses (adresse par défaut)
                          $subQuery->whereIn('client_id', function($innerQuery) use ($assignedGouvernorats) {
                              $innerQuery->select('user_id')
                                       ->from('saved_addresses')
                                       ->where('type', 'CLIENT')
                                       ->where('is_default', true)
                                       ->whereIn('delegation_id', $assignedGouvernorats);
                          })
                          // Méthode 2: Via saved_addresses (n'importe quelle adresse si pas de défaut)
                          ->orWhereIn('client_id', function($innerQuery) use ($assignedGouvernorats) {
                              $innerQuery->select('user_id')
                                       ->from('saved_addresses')
                                       ->where('type', 'CLIENT')
                                       ->whereIn('delegation_id', $assignedGouvernorats)
                                       ->whereNotExists(function($existsQuery) {
                                           $existsQuery->from('saved_addresses as sa2')
                                                    ->whereRaw('sa2.user_id = saved_addresses.user_id')
                                                    ->where('sa2.type', 'CLIENT')
                                                    ->where('sa2.is_default', true);
                                       });
                          })
                          // Méthode 3: Via packages récents du client
                          ->orWhereIn('client_id', function($innerQuery) use ($assignedGouvernorats) {
                              $innerQuery->select('sender_id')
                                       ->from('packages')
                                       ->whereIn('delegation_to', $assignedGouvernorats)
                                       ->whereNotExists(function($existsQuery) {
                                           $existsQuery->from('saved_addresses as sa3')
                                                    ->whereRaw('sa3.user_id = packages.sender_id')
                                                    ->where('sa3.type', 'CLIENT');
                                       });
                          });
                      });
            }
        }

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // Pas de filtrage par défaut - afficher tous les statuts

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

        // Statistiques avec filtrage par délégations pour chef dépôt
        $statsQuery = WithdrawalRequest::query();
        if ($user->role === 'DEPOT_MANAGER' && $user->assigned_gouvernorats) {
            $assignedGouvernorats = is_array($user->assigned_gouvernorats)
                ? $user->assigned_gouvernorats
                : json_decode($user->assigned_gouvernorats, true) ?? [];

            if (!empty($assignedGouvernorats)) {
                $statsQuery->whereHas('client', function($q) use ($assignedGouvernorats) {
                    $q->whereIn('delegation_id', $assignedGouvernorats);
                });
            }
        }

        $stats = [
            'total_pending' => (clone $statsQuery)->where('status', 'PENDING')->count(),
            'total_amount_pending' => (clone $statsQuery)->where('status', 'PENDING')->sum('amount'),
            'bank_transfers_pending' => (clone $statsQuery)->where('status', 'PENDING')
                                                        ->where('method', 'BANK_TRANSFER')
                                                        ->count(),
            'cash_deliveries_pending' => (clone $statsQuery)->where('status', 'PENDING')
                                                         ->where('method', 'CASH_DELIVERY')
                                                         ->count(),
            'approved_awaiting_delivery' => (clone $statsQuery)->where('status', 'APPROVED')
                                                            ->where('method', 'CASH_DELIVERY')
                                                            ->count(),
            'processed_by_me_today' => (clone $statsQuery)->where('processed_by_commercial_id', Auth::id())
                                                       ->whereDate('processed_at', today())
                                                       ->count(),
        ];

        // Déterminer le layout selon le rôle
        $viewPath = $user->role === 'DEPOT_MANAGER' ? 'depot-manager.commercial.withdrawals.index' : 'commercial.withdrawals.index';

        return view($viewPath, compact('withdrawals', 'stats'));
    }

    public function show(WithdrawalRequest $withdrawal)
    {
        $withdrawal->load([
            'client.wallet',
            'processedByCommercial',
            'assignedDeliverer'
        ]);

        // Déterminer le layout selon le rôle
        $user = Auth::user();
        $viewPath = $user->role === 'DEPOT_MANAGER' ? 'depot-manager.commercial.withdrawals.show' : 'commercial.withdrawals.show';

        return view($viewPath, compact('withdrawal'));
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
            // Le montant a déjà été débité lors de la création de la demande
            // Nous procédons directement à l'approbation
            $withdrawal->approve(Auth::user(), $request->notes);

            $message = 'Demande de retrait approuvée avec succès.';
            if ($withdrawal->method === 'CASH_DELIVERY') {
                $message .= ' Code de livraison: ' . $withdrawal->delivery_receipt_code;
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'approbation: ' . $e->getMessage()]);
        }
    }

    public function markAsProcessed(Request $request, WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'APPROVED' || $withdrawal->method !== 'BANK_TRANSFER') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Seuls les virements bancaires approuvés peuvent être marqués comme traités.'], 400);
            }
            return back()->withErrors(['error' => 'Seuls les virements bancaires approuvés peuvent être marqués comme traités.']);
        }

        $request->validate([
            'processing_notes' => 'nullable|string|max:500',
        ]);

        try {
            $withdrawal->markAsProcessed($request->processing_notes);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Virement bancaire marqué comme traité avec succès.']);
            }
            return back()->with('success', 'Virement bancaire marqué comme traité avec succès.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors du traitement: ' . $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => 'Erreur lors du traitement: ' . $e->getMessage()]);
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
            // Utiliser la méthode reject du modèle qui gère automatiquement le remboursement
            $withdrawal->reject(Auth::user(), $request->rejection_reason);

            return back()->with('success', 'Demande de retrait rejetée avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du rejet: ' . $e->getMessage()]);
        }
    }

    public function assignToDeliverer(Request $request, WithdrawalRequest $withdrawal)
    {
        if ($withdrawal->status !== 'APPROVED' || $withdrawal->method !== 'CASH_DELIVERY') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Cette demande ne peut pas être assignée à un livreur.'], 400);
            }
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

            // Assigner le livreur et marquer comme prêt pour livraison
            $withdrawal->assignToDeliverer($deliverer);
            $withdrawal->prepareForDelivery();

            $message = "Retrait assigné au livreur {$deliverer->name} et prêt pour livraison. Code: {$withdrawal->delivery_receipt_code}";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deliverer_name' => $deliverer->name,
                    'delivery_code' => $withdrawal->delivery_receipt_code
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Erreur lors de l\'assignation: ' . $e->getMessage()], 500);
            }
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
                    // Le montant a déjà été débité lors de la création de la demande
                    // Nous procédons directement à l'approbation
                    $withdrawal->approve(Auth::user(), $request->bulk_notes);

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
        $user = Auth::user();

        // Vérifier les permissions selon le rôle
        if ($user->role === 'DELIVERER') {
            // Pour les livreurs : vérifier qu'ils sont assignés et que le statut est READY_FOR_DELIVERY
            if ($withdrawal->assigned_deliverer_id !== $user->id ||
                !in_array($withdrawal->status, ['READY_FOR_DELIVERY', 'IN_PROGRESS'])) {
                $errorMsg = 'Vous n\'êtes pas autorisé à livrer ce retrait.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 403);
                }
                return back()->withErrors(['error' => $errorMsg]);
            }
        } else {
            // Pour les commerciaux : vérifier le statut approprié
            if (!in_array($withdrawal->status, ['IN_PROGRESS', 'READY_FOR_DELIVERY']) ||
                $withdrawal->method !== 'CASH_DELIVERY') {
                $errorMsg = 'Ce retrait ne peut pas être marqué comme livré.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 400);
                }
                return back()->withErrors(['error' => $errorMsg]);
            }
        }

        $request->validate([
            'delivery_notes' => 'nullable|string|max:500',
            'client_signature' => 'nullable|string', // Base64 de la signature si disponible
        ]);

        try {
            $deliveryProof = [
                'delivered_by' => $user->name,
                'delivered_by_role' => $user->role,
                'delivery_notes' => $request->delivery_notes,
                'client_signature' => $request->client_signature,
                'confirmed_at' => now()->toISOString(),
            ];

            // Utiliser la nouvelle méthode markAsDeliveredFinal
            $withdrawal->markAsDeliveredFinal($deliveryProof);

            $message = "Retrait livré avec succès. Montant: " . number_format($withdrawal->amount, 3) . " DT";

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de la confirmation: ' . $e->getMessage()], 500);
            }
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

    /**
     * API pour récupérer les paiements espèce en attente d'assignation
     */
    public function apiPendingCash()
    {
        $withdrawals = WithdrawalRequest::where('status', 'APPROVED')
                                       ->where('method', 'CASH_DELIVERY')
                                       ->whereNull('assigned_deliverer_id')
                                       ->with(['client:id,name,email,phone'])
                                       ->orderBy('created_at', 'desc')
                                       ->get(['id', 'request_code', 'amount', 'client_id', 'delivery_receipt_code', 'created_at']);

        return response()->json($withdrawals);
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

    public function deliveryReceipt(WithdrawalRequest $withdrawal)
    {
        $withdrawal->load(['client', 'assignedDeliverer']);

        if (!$withdrawal->delivery_receipt_code) {
            abort(404, 'Aucun code de reçu trouvé pour cette demande de retrait');
        }

        return view('commercial.withdrawals.delivery_receipt', compact('withdrawal'));
    }
}