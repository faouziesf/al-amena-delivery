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
            // Ajouter un champ JSON pour stocker plusieurs gouvernorats pour les livreurs
            $table->json('deliverer_gouvernorats')->nullable()->after('assigned_delegation');
        });

        // Migrer les données existantes: copier assigned_delegation vers deliverer_gouvernorats
        DB::statement("
            UPDATE users 
            SET deliverer_gouvernorats = JSON_ARRAY(assigned_delegation)
            WHERE role = 'DELIVERER' 
            AND assigned_delegation IS NOT NULL
            AND deliverer_type = 'DELEGATION'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('deliverer_gouvernorats');
        });
    }
};
