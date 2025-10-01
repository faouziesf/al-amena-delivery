<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientAdvanceController extends Controller
{
    // Middleware is now handled in routes instead of constructor in Laravel 11

    /**
     * Afficher la liste des clients avec leur solde d'avance
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);

        $clients = User::where('role', 'CLIENT')
            ->with(['wallet'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage);

        return view('commercial.client-advances.index', compact('clients', 'search'));
    }

    /**
     * Afficher les détails d'avance d'un client
     */
    public function show(User $client)
    {
        // Vérifier que c'est bien un client
        if ($client->role !== 'CLIENT') {
            abort(404, 'Client non trouvé');
        }

        $client->ensureWallet();
        $client->load([
            'wallet.advanceModifiedBy',
            'wallet.transactions' => function ($query) {
                $query->whereIn('type', ['ADVANCE_CREDIT', 'ADVANCE_DEBIT', 'ADVANCE_USAGE'])
                      ->orderBy('created_at', 'desc')
                      ->limit(20);
            }
        ]);

        return view('commercial.client-advances.show', compact('client'));
    }

    /**
     * Ajouter une avance à un client
     */
    public function addAdvance(Request $request, User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404, 'Client non trouvé');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.001|max:1000',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $client->ensureWallet();
            $amount = (float) $request->amount;
            $description = $request->description ?: 'Avance accordée par ' . Auth::user()->name;

            $client->wallet->addAdvance($amount, Auth::id(), $description);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Avance de {$amount} DT accordée avec succès à {$client->name}",
                'advance_balance' => $client->wallet->fresh()->advance_balance,
                'formatted_advance_balance' => $client->wallet->fresh()->formatted_advance_balance
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de l\'avance: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Retirer une avance d'un client
     */
    public function removeAdvance(Request $request, User $client)
    {
        if ($client->role !== 'CLIENT') {
            abort(404, 'Client non trouvé');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.001',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $client->ensureWallet();
            $amount = (float) $request->amount;
            $description = $request->description ?: 'Avance retirée par ' . Auth::user()->name;

            if ($client->wallet->advance_balance < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Montant supérieur à l\'avance disponible (' . $client->wallet->formatted_advance_balance . ')'
                ]);
            }

            $client->wallet->removeAdvance($amount, Auth::id(), $description);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Avance de {$amount} DT retirée avec succès du compte de {$client->name}",
                'advance_balance' => $client->wallet->fresh()->advance_balance,
                'formatted_advance_balance' => $client->wallet->fresh()->formatted_advance_balance
            ]);

        } catch (ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retrait de l\'avance: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * API: Recherche de clients pour autocomplete
     */
    public function searchClients(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json(['clients' => []]);
        }

        $clients = User::where('role', 'CLIENT')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->with('wallet')
            ->limit(10)
            ->get()
            ->map(function ($client) {
                $client->ensureWallet();
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'advance_balance' => $client->wallet->advance_balance,
                    'formatted_advance_balance' => $client->wallet->formatted_advance_balance,
                    'total_balance' => $client->wallet->balance,
                    'formatted_total_balance' => $client->wallet->formatted_balance
                ];
            });

        return response()->json(['clients' => $clients]);
    }

    /**
     * API: Statistiques des avances
     */
    public function statistics()
    {
        $stats = UserWallet::whereHas('user', function ($query) {
                $query->where('role', 'CLIENT');
            })
            ->selectRaw('
                COUNT(*) as total_clients,
                COUNT(CASE WHEN advance_balance > 0 THEN 1 END) as clients_with_advance,
                SUM(advance_balance) as total_advance_amount,
                AVG(advance_balance) as average_advance
            ')
            ->first();

        $recentTransactions = FinancialTransaction::whereIn('type', ['ADVANCE_CREDIT', 'ADVANCE_DEBIT', 'ADVANCE_USAGE'])
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'total_clients' => (int) $stats->total_clients,
            'clients_with_advance' => (int) $stats->clients_with_advance,
            'total_advance_amount' => (float) $stats->total_advance_amount,
            'average_advance' => (float) $stats->average_advance,
            'recent_transactions' => $recentTransactions,
            'formatted_total_advance_amount' => number_format($stats->total_advance_amount, 3) . ' DT'
        ]);
    }
}
