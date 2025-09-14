<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer des wallets pour tous les utilisateurs qui n'en ont pas
        $usersWithoutWallets = DB::table('users')
            ->leftJoin('user_wallets', 'users.id', '=', 'user_wallets.user_id')
            ->whereNull('user_wallets.user_id')
            ->whereIn('users.role', ['CLIENT', 'DELIVERER'])
            ->select('users.id')
            ->get();

        foreach ($usersWithoutWallets as $user) {
            DB::table('user_wallets')->insert([
                'user_id' => $user->id,
                'balance' => 0.000,
                'pending_amount' => 0.000,
                'frozen_amount' => 0.000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Créé " . count($usersWithoutWallets) . " wallets manquants.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback pour cette migration car elle ne fait que corriger des données manquantes
    }
};