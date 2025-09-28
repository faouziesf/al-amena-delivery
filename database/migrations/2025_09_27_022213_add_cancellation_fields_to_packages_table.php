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
            // Champs pour l'annulation par le client
            $table->boolean('cancelled_by_client')->default(false)->after('return_reason')->comment('Indique si le colis a été annulé par le client');
            $table->text('cancellation_reason')->nullable()->after('cancelled_by_client')->comment('Raison de l\'annulation du colis');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason')->comment('Date d\'annulation du colis');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at')->comment('ID de l\'utilisateur qui a annulé le colis');
            $table->string('auto_return_reason')->nullable()->after('cancelled_by')->comment('Raison automatique du retour');

            // Clé étrangère pour cancelled_by
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');

            // Index
            $table->index(['cancelled_by_client']);
            $table->index(['cancelled_at']);
            $table->index(['cancelled_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropIndex(['cancelled_by_client']);
            $table->dropIndex(['cancelled_at']);
            $table->dropIndex(['cancelled_by']);
            $table->dropColumn(['cancelled_by_client', 'cancellation_reason', 'cancelled_at', 'cancelled_by', 'auto_return_reason']);
        });
    }
};
