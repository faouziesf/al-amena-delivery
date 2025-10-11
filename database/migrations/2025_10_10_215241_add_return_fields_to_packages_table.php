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
            // Compteur de tentatives UNAVAILABLE
            $table->integer('unavailable_attempts')->default(0)->after('status');

            // Dates importantes pour le workflow de retours
            $table->timestamp('awaiting_return_since')->nullable()->after('unavailable_attempts');
            $table->timestamp('return_in_progress_since')->nullable()->after('awaiting_return_since');
            $table->timestamp('returned_to_client_at')->nullable()->after('return_in_progress_since');

            // Raison du retour (REFUSED, 3x UNAVAILABLE, etc.)
            $table->text('return_reason')->nullable()->after('returned_to_client_at');

            // Référence au colis retour créé
            $table->foreignId('return_package_id')
                  ->nullable()
                  ->after('return_reason')
                  ->constrained('return_packages')
                  ->onDelete('set null');

            // Index pour requêtes fréquentes
            $table->index('unavailable_attempts');
            $table->index('awaiting_return_since');
            $table->index('return_in_progress_since');
            $table->index('returned_to_client_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['return_package_id']);
            $table->dropColumn([
                'unavailable_attempts',
                'awaiting_return_since',
                'return_in_progress_since',
                'returned_to_client_at',
                'return_reason',
                'return_package_id'
            ]);
        });
    }
};
