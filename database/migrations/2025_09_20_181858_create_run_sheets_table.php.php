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
        Schema::create('run_sheets', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('sheet_code')->unique()->comment('Code unique de la feuille de route');
            $table->foreignId('deliverer_id')->constrained('users')->onDelete('cascade')->comment('ID du livreur assigné');
            $table->foreignId('delegation_id')->constrained('delegations')->onDelete('cascade')->comment('Délégation principale');
            $table->date('date')->comment('Date de la feuille de route');
            
            // Statut et configuration
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('PENDING')->comment('Statut de la feuille');
            $table->json('package_types')->comment('Types de colis inclus: pickups, deliveries, returns');
            $table->string('sort_criteria')->default('address')->comment('Critère de tri: address, cod_amount, created_at');
            $table->boolean('include_cod_summary')->default(false)->comment('Inclure le résumé COD');
            
            // Données des colis
            $table->json('packages_data')->comment('Données complètes des colis de la feuille');
            $table->integer('packages_count')->default(0)->comment('Nombre total de colis');
            $table->decimal('total_cod_amount', 10, 3)->default(0)->comment('Montant total COD');
            
            // Timestamps d\'actions
            $table->timestamp('printed_at')->nullable()->comment('Date d\'impression de la feuille');
            $table->timestamp('started_at')->nullable()->comment('Date de début d\'exécution');
            $table->timestamp('completed_at')->nullable()->comment('Date de finalisation');
            
            // Notes et observations
            $table->text('preparation_notes')->nullable()->comment('Notes lors de la préparation');
            $table->text('completion_notes')->nullable()->comment('Notes de finalisation du livreur');
            
            // Statistiques de completion
            $table->json('completion_stats')->nullable()->comment('Statistiques finales: livrés, retournés, etc.');
            
            // Données de géolocalisation (optionnel)
            $table->json('route_optimization')->nullable()->comment('Données d\'optimisation de route');
            $table->decimal('estimated_distance', 8, 2)->nullable()->comment('Distance estimée en km');
            $table->integer('estimated_duration')->nullable()->comment('Durée estimée en minutes');
            
            // Données d\'export/impression
            $table->string('pdf_path')->nullable()->comment('Chemin du PDF généré');
            $table->integer('print_count')->default(0)->comment('Nombre d\'impressions');
            $table->json('export_formats')->nullable()->comment('Formats d\'export utilisés');
            
            // Métadonnées
            $table->json('metadata')->nullable()->comment('Métadonnées supplémentaires');
            $table->string('ip_address')->nullable()->comment('IP de création');
            $table->text('user_agent')->nullable()->comment('User agent de création');
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['deliverer_id', 'date'], 'idx_runsheets_deliverer_date');
            $table->index(['delegation_id', 'date'], 'idx_runsheets_delegation_date');
            $table->index(['status', 'date'], 'idx_runsheets_status_date');
            $table->index(['deliverer_id', 'status'], 'idx_runsheets_deliverer_status');
            $table->index('date', 'idx_runsheets_date');
            $table->index('sheet_code', 'idx_runsheets_code');
            $table->index('created_at', 'idx_runsheets_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('run_sheets');
    }
};