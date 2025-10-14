<?php

namespace App\Http\Controllers\Deliverer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DelivererClientTopupController extends Controller
{
    /**
     * Afficher l'interface de recharge client
     */
    public function index()
    {
        return view('deliverer.client-topup.index');
    }

    /**
     * Rechercher un client par email ou numéro de compte
     */
    public function searchClient(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:3'
        ]);

        $search = $request->search;

        // Rechercher par email ou ID
        $client = User::where('role', 'CLIENT')
            ->where(function($query) use ($search) {
                $query->where('email', 'LIKE', "%{$search}%")
                      ->orWhere('id', $search)
                      ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->first();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        // Récupérer le wallet du client
        $wallet = UserWallet::firstOrCreate(
            ['user_id' => $client->id],
            ['balance' => 0, 'pending_amount' => 0, 'frozen_amount' => 0, 'advance_balance' => 0]
        );

        return response()->json([
            'success' => true,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'balance' => $wallet->balance,
                'balance_formatted' => number_format($wallet->balance, 3) . ' DT'
            ]
        ]);
    }

    /**
     * Ajouter un montant au solde du client
     */
    public function addTopup(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:1|max:10000'
        ]);

        $deliverer = Auth::user();
        $client = User::findOrFail($request->client_id);

        // Vérifier que c'est bien un client
        if ($client->role !== 'CLIENT') {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur invalide'
            ], 400);
        }

        $amount = (float) $request->amount;

        DB::beginTransaction();
        try {
            // 1. Ajouter au solde du client
            $clientWallet = UserWallet::firstOrCreate(
                ['user_id' => $client->id],
                ['balance' => 0, 'pending_amount' => 0, 'frozen_amount' => 0, 'advance_balance' => 0]
            );

            $clientWallet->addFunds(
                $amount,
                "Recharge effectuée par livreur #{$deliverer->id} ({$deliverer->name})",
                "DELIVERER_TOPUP_{$deliverer->id}_{$client->id}_" . time()
            );

            // 2. Ajouter au wallet du livreur
            $delivererWallet = UserWallet::firstOrCreate(
                ['user_id' => $deliverer->id],
                ['balance' => 0, 'pending_amount' => 0, 'frozen_amount' => 0, 'advance_balance' => 0]
            );

            $delivererWallet->addFunds(
                $amount,
                "Commission recharge client #{$client->id} ({$client->name})",
                "TOPUP_COMMISSION_{$client->id}_{$deliverer->id}_" . time()
            );

            // 3. Logger l'opération
            \Log::info("Recharge client par livreur", [
                'deliverer_id' => $deliverer->id,
                'deliverer_name' => $deliverer->name,
                'client_id' => $client->id,
                'client_name' => $client->name,
                'amount' => $amount,
                'timestamp' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Recharge de {$amount} DT effectuée avec succès",
                'client_new_balance' => $clientWallet->fresh()->balance,
                'deliverer_new_balance' => $delivererWallet->fresh()->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erreur recharge client par livreur", [
                'error' => $e->getMessage(),
                'deliverer_id' => $deliverer->id,
                'client_id' => $request->client_id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recharge: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Historique des recharges effectuées par le livreur
     */
    public function history()
    {
        $deliverer = Auth::user();

        $topups = FinancialTransaction::where('user_id', $deliverer->id)
            ->where('description', 'LIKE', '%Commission recharge client%')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('deliverer.client-topup.history', compact('topups'));
    }
}
