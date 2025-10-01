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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Ajouter le champ pour assigner un chef de dépôt spécifique
            $table->foreignId('assigned_depot_manager_id')
                  ->nullable()
                  ->after('assigned_deliverer_id')
                  ->constrained('users')
                  ->onDelete('set null');

            // Index pour les requêtes de filtrage par chef de dépôt
            $table->index('assigned_depot_manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropForeign(['assigned_depot_manager_id']);
            $table->dropIndex(['assigned_depot_manager_id']);
            $table->dropColumn('assigned_depot_manager_id');
        });
    }
};
