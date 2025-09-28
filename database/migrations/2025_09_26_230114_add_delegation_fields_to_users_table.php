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
            // Champs de délégation pour les livreurs
            $table->string('assigned_delegation')->nullable()->after('role')->comment('Délégation assignée au livreur');
            $table->decimal('delegation_latitude', 10, 8)->nullable()->after('assigned_delegation')->comment('Latitude de la délégation');
            $table->decimal('delegation_longitude', 11, 8)->nullable()->after('delegation_latitude')->comment('Longitude de la délégation');
            $table->integer('delegation_radius_km')->default(10)->after('delegation_longitude')->comment('Rayon de couverture en km');

            // Index pour optimiser les requêtes
            $table->index(['role', 'assigned_delegation']);
            $table->index(['assigned_delegation']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'assigned_delegation']);
            $table->dropIndex(['assigned_delegation']);
            $table->dropColumn([
                'assigned_delegation',
                'delegation_latitude',
                'delegation_longitude',
                'delegation_radius_km'
            ]);
        });
    }
};