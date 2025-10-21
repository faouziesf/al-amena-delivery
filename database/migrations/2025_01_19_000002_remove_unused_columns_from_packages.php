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
        // Pour SQLite, on doit recréer la table sans les colonnes inutiles
        // car SQLite ne supporte pas bien DROP COLUMN avec foreign keys
        
        // Vérifier si les colonnes existent avant de les supprimer
        $hasSupplierData = Schema::hasColumn('packages', 'supplier_data');
        $hasPickupDelegationId = Schema::hasColumn('packages', 'pickup_delegation_id');
        $hasPickupAddress = Schema::hasColumn('packages', 'pickup_address');
        $hasPickupPhone = Schema::hasColumn('packages', 'pickup_phone');
        $hasPickupNotes = Schema::hasColumn('packages', 'pickup_notes');
        
        // Si aucune colonne n'existe, ne rien faire
        if (!$hasSupplierData && !$hasPickupDelegationId && !$hasPickupAddress && !$hasPickupPhone && !$hasPickupNotes) {
            return;
        }
        
        // Supprimer les colonnes une par une avec gestion d'erreur
        if ($hasSupplierData) {
            try {
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropColumn('supplier_data');
                });
            } catch (\Exception $e) {
                // Ignorer si erreur
            }
        }
        
        if ($hasPickupAddress) {
            try {
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropColumn('pickup_address');
                });
            } catch (\Exception $e) {
                // Ignorer si erreur
            }
        }
        
        if ($hasPickupPhone) {
            try {
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropColumn('pickup_phone');
                });
            } catch (\Exception $e) {
                // Ignorer si erreur
            }
        }
        
        if ($hasPickupNotes) {
            try {
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropColumn('pickup_notes');
                });
            } catch (\Exception $e) {
                // Ignorer si erreur
            }
        }
        
        // Pour pickup_delegation_id, supprimer d'abord la foreign key si elle existe
        if ($hasPickupDelegationId) {
            try {
                // Essayer de supprimer la foreign key
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropForeign(['pickup_delegation_id']);
                });
            } catch (\Exception $e) {
                // Foreign key n'existe pas ou déjà supprimée
            }
            
            try {
                // Puis supprimer la colonne
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropColumn('pickup_delegation_id');
                });
            } catch (\Exception $e) {
                // Ignorer si erreur
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Restaurer les colonnes (au cas où rollback nécessaire)
            $table->json('supplier_data')->nullable();
            $table->unsignedBigInteger('pickup_delegation_id')->nullable();
            $table->string('pickup_address')->nullable();
            $table->string('pickup_phone', 20)->nullable();
            $table->text('pickup_notes')->nullable();
        });
    }
};
