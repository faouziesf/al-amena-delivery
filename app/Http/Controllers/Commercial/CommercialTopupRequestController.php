<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\TopupRequest;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommercialTopupRequestController extends Controller
{
    /**
     * Liste des demandes de recharge
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = TopupRequest::with(['client', 'user', 'processedBy'])
                            ->when($user->role === 'DEPOT_MANAGER', function($q) use ($user) {
                                // Chef dépôt ne voit que les demandes des clients de son gouvernorat
                                $assignedGouvernorats = is_array($user->assigned_gouvernorats)
                                    ? $user->assigned_gouvernorats
                                    : json_decode($user->assigned_gouvernorats, true) ?? [];

                                if (!empty($assignedGouvernorats)) {
                                    return $q->where(function($subQuery) use ($assignedGouvernorats) {
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
                            });

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('method', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_code', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'LIKE', '%' . $search . '%')
                                ->orWhere('email', 'LIKE', '%' . $search . '%')
                                ->orWhere('phone', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        $topupRequests = $query->orderBy('created_at', 'desc')->paginate(25);

        // Statistiques
        $stats = [
            'pending' => TopupRequest::where('status', 'PENDING')->count(),
            'approved_today' => TopupRequest::where('status', 'VALIDATED')
                                         ->whereDate('updated_at', today())
                                         ->count(),
            'total_amount_pending' => TopupRequest::where('status', 'PENDING')
                                                ->sum('amount'),
            'bank_transfer_pending' => TopupRequest::where('status', 'PENDING')
                                                 ->where('method', 'BANK_TRANSFER')
                                                 ->count(),
            'cash_pending' => TopupRequest::where('status', 'PENDING')
                                        ->where('method', 'CASH')
                                        ->count()
        ];

        // Déterminer le layout selon le rôle
        $viewPath = $user->role === 'DEPOT_MANAGER' ? 'depot-manager.commercial.topup-requests.index' : 'commercial.topup-requests.index';

        return view($viewPath, compact('topupRequests', 'stats'));
    }

    /**
     * Afficher les détails d'une demande de recharge
     */
    public function show(TopupRequest $topupRequest)
    {
        $user = Auth::user();

        // Vérifier les permissions pour chef dépôt
        if ($user->role === 'DEPOT_MANAGER') {
            $clientDelegation = $topupRequest->client->assigned_delegation ?? null;
            if (!in_array($clientDelegation, $user->assigned_gouvernorats_array)) {
                abort(403, 'Vous ne pouvez pas accéder à cette demande.');
            }
        }

        $topupRequest->load(['client', 'processedBy']);

        // Historique des transactions du client
        $recentTransactions = Transaction::where('user_id', $topupRequest->client_id)
                                        ->where('type', 'TOPUP')
                                        ->orderBy('created_at', 'desc')
                                        ->limit(10)
                                        ->get();

        // Déterminer le layout selon le rôle
        $viewPath = $user->role === 'DEPOT_MANAGER' ? 'depot-manager.commercial.topup-requests.show' : 'commercial.topup-requests.show';

        return view($viewPath, compact('topupRequest', 'recentTransactions'));
    }

    /**
     * Approuver une demande de recharge
     */
    public function approve(Request $request, TopupRequest $topupRequest)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if ($user->role === 'DEPOT_MANAGER') {
            $clientDelegation = $topupRequest->client->assigned_delegation ?? null;
            if (!in_array($clientDelegation, $user->assigned_gouvernorats_array)) {
                abort(403, 'Vous ne pouvez pas traiter cette demande.');
            }
        }

        if ($topupRequest->status !== 'PENDING') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            // Mettre à jour la demande
            $topupRequest->update([
                'status' => 'VALIDATED',
                'processed_by_id' => $user->id,
                'processed_at' => now(),
                'validation_notes' => $request->notes
            ]);

            // Obtenir ou créer le wallet
            $wallet = UserWallet::firstOrCreate(
                ['user_id' => $topupRequest->client_id],
                ['balance' => 0]
            );

            // Ajouter les fonds
            $wallet->increment('balance', $topupRequest->amount);

            // Créer la transaction
            Transaction::create([
                'user_id' => $topupRequest->client_id,
                'type' => 'TOPUP',
                'amount' => $topupRequest->amount,
                'description' => "Recharge wallet - {$topupRequest->method}",
                'reference' => 'TOPUP_' . $topupRequest->id,
                'status' => 'COMPLETED',
                'processed_by' => $user->id,
                'metadata' => [
                    'topup_request_id' => $topupRequest->id,
                    'method' => $topupRequest->method,
                    'notes' => $request->notes
                ]
            ]);

            DB::commit();

            return back()->with('success', 'Demande de recharge approuvée avec succès. Le wallet du client a été crédité.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du traitement de la demande: ' . $e->getMessage());
        }
    }

    /**
     * Rejeter une demande de recharge
     */
    public function reject(Request $request, TopupRequest $topupRequest)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if ($user->role === 'DEPOT_MANAGER') {
            $clientDelegation = $topupRequest->client->assigned_delegation ?? null;
            if (!in_array($clientDelegation, $user->assigned_gouvernorats_array)) {
                abort(403, 'Vous ne pouvez pas traiter cette demande.');
            }
        }

        if ($topupRequest->status !== 'PENDING') {
            return back()->with('error', 'Cette demande a déjà été traitée.');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $topupRequest->update([
            'status' => 'REJECTED',
            'processed_by_id' => $user->id,
            'processed_at' => now(),
            'rejection_reason' => $request->rejection_reason
        ]);

        return back()->with('success', 'Demande de recharge rejetée.');
    }

    /**
     * API: Obtenir les statistiques des demandes de recharge
     */
    public function apiStats()
    {
        $user = Auth::user();

        $baseQuery = TopupRequest::query();

        if ($user->role === 'DEPOT_MANAGER') {
            $clientIds = User::where('role', 'CLIENT')
                           ->whereIn('assigned_delegation', $user->assigned_gouvernorats_array)
                           ->pluck('id');
            $baseQuery->whereIn('client_id', $clientIds);
        }

        $stats = [
            'pending_count' => $baseQuery->clone()->where('status', 'PENDING')->count(),
            'pending_amount' => $baseQuery->clone()->where('status', 'PENDING')->sum('amount'),
            'approved_today' => $baseQuery->clone()->where('status', 'VALIDATED')
                                         ->whereDate('updated_at', today())
                                         ->count(),
            'approved_this_week' => $baseQuery->clone()->where('status', 'VALIDATED')
                                             ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                                             ->count(),
            'by_type' => [
                'bank_transfer' => $baseQuery->clone()->where('method', 'BANK_TRANSFER')
                                            ->where('status', 'PENDING')
                                            ->count(),
                'cash' => $baseQuery->clone()->where('method', 'CASH')
                                   ->where('status', 'PENDING')
                                   ->count()
            ]
        ];

        return response()->json($stats);
    }

    /**
     * API: Obtenir les demandes en attente
     */
    public function apiPending()
    {
        $user = Auth::user();

        $query = TopupRequest::with(['client'])
                            ->where('status', 'PENDING');

        if ($user->role === 'DEPOT_MANAGER') {
            $clientIds = User::where('role', 'CLIENT')
                           ->whereIn('assigned_delegation', $user->assigned_gouvernorats_array)
                           ->pluck('id');
            $query->whereIn('client_id', $clientIds);
        }

        $pending = $query->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get()
                        ->map(function($request) {
                            return [
                                'id' => $request->id,
                                'client_name' => $request->client->name,
                                'amount' => $request->amount,
                                'type' => $request->method,
                                'created_at' => $request->created_at->format('d/m/Y H:i'),
                                'url' => route('commercial.topup-requests.show', $request)
                            ];
                        });

        return response()->json($pending);
    }
}