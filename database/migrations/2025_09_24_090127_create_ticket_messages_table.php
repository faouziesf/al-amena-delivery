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
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // Qui a envoyé le message
            $table->enum('sender_type', ['CLIENT', 'COMMERCIAL', 'SUPERVISOR']); // Type de l'expéditeur
            $table->text('message'); // Contenu du message
            $table->json('attachments')->nullable(); // Pièces jointes éventuelles
            $table->boolean('is_internal')->default(false); // Message interne (entre commerciaux/superviseurs)
            $table->timestamp('read_at')->nullable(); // Quand le message a été lu par le destinataire
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->timestamps();

            // Index
            $table->index(['ticket_id', 'created_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index('sender_type');
            $table->index('is_internal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
