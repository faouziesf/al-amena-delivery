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
            // Champs pour le chef dépôt
            $table->json('assigned_gouvernorats')->nullable()->comment('Gouvernorats assignés au chef dépôt');
            $table->string('depot_name')->nullable()->comment('Nom du dépôt géré');
            $table->text('depot_address')->nullable()->comment('Adresse du dépôt');
            $table->boolean('is_depot_manager')->default(false)->comment('Indicateur si c\'est un chef dépôt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_gouvernorats',
                'depot_name',
                'depot_address',
                'is_depot_manager'
            ]);
        });
    }
};