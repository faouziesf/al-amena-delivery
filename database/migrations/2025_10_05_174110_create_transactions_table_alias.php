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
        // Créer une vue transactions qui pointe vers financial_transactions
        // Ceci résout le problème de compatibilité
        DB::statement('CREATE VIEW IF NOT EXISTS transactions AS SELECT * FROM financial_transactions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS transactions');
    }
};
