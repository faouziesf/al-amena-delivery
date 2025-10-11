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
        Schema::create('return_packages', function (Blueprint $table) {
            $table->id();

            // Référence au colis original
            $table->foreignId('original_package_id')
                  ->constrained('packages')
                  ->onDelete('cascade');

            // Code unique du colis retour
            $table->string('return_package_code')->unique();

            // COD toujours à 0 pour les retours
            $table->decimal('cod', 10, 2)->default(0);

            // Statut du colis retour (flux normal de livraison)
            // AT_DEPOT → PICKED_UP → IN_TRANSIT → DELIVERED
            $table->string('status')->default('AT_DEPOT');

            // Informations expéditeur (votre société)
            $table->json('sender_info');

            // Informations destinataire (le fournisseur original)
            $table->json('recipient_info');

            // Raison du retour (copié depuis le colis original)
            $table->text('return_reason')->nullable();

            // Commentaire du chef de dépôt
            $table->text('comment')->nullable();

            // Qui a créé ce retour
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // Dates importantes
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            // Livreur assigné (si nécessaire)
            $table->foreignId('assigned_deliverer_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Index pour performances
            $table->index('status');
            $table->index('original_package_id');
            $table->index('created_by');
            $table->index('printed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_packages');
    }
};
