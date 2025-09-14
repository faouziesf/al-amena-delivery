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
        // Vérifier si la colonne existe déjà
        $hasColumn = Schema::hasColumn('client_profiles', 'internal_notes');
        
        if (!$hasColumn) {
            Schema::table('client_profiles', function (Blueprint $table) {
                $table->text('internal_notes')->nullable()->after('offer_return_price');
            });
            
            echo "✅ Colonne 'internal_notes' ajoutée avec succès.\n";
        } else {
            echo "ℹ️  Colonne 'internal_notes' existe déjà, aucune modification nécessaire.\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('client_profiles', 'internal_notes')) {
            Schema::table('client_profiles', function (Blueprint $table) {
                $table->dropColumn('internal_notes');
            });
        }
    }
};