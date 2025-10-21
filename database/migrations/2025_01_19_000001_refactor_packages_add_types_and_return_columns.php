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
        Schema::table('packages', function (Blueprint $table) {
            // Vérifier et ajouter le type de colis
            if (!Schema::hasColumn('packages', 'package_type')) {
                $table->string('package_type', 20)->default('NORMAL')->after('package_code');
                $table->index('package_type');
            }
            
            // Colonnes pour les colis de retour (migré depuis return_packages)
            if (!Schema::hasColumn('packages', 'return_package_code')) {
                $table->string('return_package_code', 50)->nullable()->unique()->after('package_type');
            }
            if (!Schema::hasColumn('packages', 'original_package_id')) {
                $table->unsignedBigInteger('original_package_id')->nullable()->after('return_package_code');
            }
            if (!Schema::hasColumn('packages', 'return_reason')) {
                $table->string('return_reason', 100)->nullable()->after('original_package_id');
            }
            if (!Schema::hasColumn('packages', 'return_notes')) {
                $table->text('return_notes')->nullable()->after('return_reason');
            }
            if (!Schema::hasColumn('packages', 'return_requested_at')) {
                $table->timestamp('return_requested_at')->nullable()->after('return_notes');
            }
            if (!Schema::hasColumn('packages', 'return_accepted_at')) {
                $table->timestamp('return_accepted_at')->nullable()->after('return_requested_at');
            }
        });
        
        // Ajouter les index séparément (en dehors du Schema::table pour éviter les erreurs)
        try {
            Schema::table('packages', function (Blueprint $table) {
                if (!Schema::hasColumn('packages', 'return_package_code')) {
                    $table->index('return_package_code');
                }
                if (!Schema::hasColumn('packages', 'original_package_id')) {
                    $table->index('original_package_id');
                }
            });
        } catch (\Exception $e) {
            // Index déjà existant, ignorer
        }
        
        // Ajouter la foreign key
        try {
            Schema::table('packages', function (Blueprint $table) {
                $table->foreign('original_package_id')
                      ->references('id')
                      ->on('packages')
                      ->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Foreign key déjà existante, ignorer
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Supprimer foreign key
            $table->dropForeign(['original_package_id']);
            
            // Supprimer index
            $table->dropIndex(['package_type']);
            $table->dropIndex(['return_package_code']);
            $table->dropIndex(['original_package_id']);
            
            // Supprimer colonnes
            $table->dropColumn([
                'package_type',
                'return_package_code',
                'original_package_id',
                'return_reason',
                'return_notes',
                'return_requested_at',
                'return_accepted_at'
            ]);
        });
    }
};
