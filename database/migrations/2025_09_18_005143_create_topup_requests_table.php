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
        Schema::create('topup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 3);
            $table->enum('method', ['BANK_TRANSFER', 'BANK_DEPOSIT', 'CASH']);
            $table->string('bank_transfer_id')->nullable()->unique(); // Identifiant de virement/versement
            $table->string('proof_document')->nullable(); // Chemin vers le justificatif
            $table->text('notes')->nullable(); // Notes du client
            $table->enum('status', ['PENDING', 'VALIDATED', 'REJECTED', 'CANCELLED'])->default('PENDING');
            $table->foreignId('processed_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('validation_notes')->nullable(); // Notes du validateur
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['client_id', 'status']);
            $table->index(['status', 'method']);
            $table->index(['processed_by_id', 'status']);
            $table->index('created_at');
            $table->index('bank_transfer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topup_requests');
    }
};