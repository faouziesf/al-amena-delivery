<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Type de livreur
            $table->enum('deliverer_type', ['DELEGATION', 'JOKER', 'TRANSIT'])
                  ->default('DELEGATION')
                  ->after('assigned_delegation')
                  ->comment('Type de livreur: DELEGATION (délégation fixe), JOKER (toutes délégations), TRANSIT (changement uniquement)');

            // Index pour optimiser les requêtes
            $table->index(['role', 'deliverer_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'deliverer_type']);
            $table->dropColumn('deliverer_type');
        });
    }
};