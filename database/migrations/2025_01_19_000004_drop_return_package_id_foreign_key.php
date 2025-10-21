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
        // Pour SQLite : Supprimer la colonne supprimera automatiquement la foreign key
        if (Schema::hasColumn('packages', 'return_package_id')) {
            try {
                // Essayer de supprimer la foreign key d'abord (pour les autres DB)
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropForeign(['return_package_id']);
                });
            } catch (\Exception $e) {
                // Ignorer si erreur (SQLite ou FK n'existe pas)
            }
            
            try {
                // Supprimer la colonne
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropColumn('return_package_id');
                });
            } catch (\Exception $e) {
                // Si échec (SQLite avec FK), désactiver temporairement les FK
                DB::statement('PRAGMA foreign_keys = OFF');
                Schema::table('packages', function (Blueprint $table) {
                    $table->dropColumn('return_package_id');
                });
                DB::statement('PRAGMA foreign_keys = ON');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            // Ne pas recréer la colonne car return_packages n'existe plus
            // Cette migration est définitive
        });
    }
};
