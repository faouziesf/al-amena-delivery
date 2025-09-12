<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('client_id')->constrained('users');
            $table->decimal('amount', 10, 3);
            
            // Méthode de retrait
            $table->enum('method', ['BANK_TRANSFER', 'CASH_DELIVERY']);
            $table->json('bank_details')->nullable(); // IBAN, nom banque, etc.
            
            // Statuts et traitement
            $table->enum('status', ['PENDING', 'APPROVED', 'IN_PROGRESS', 'COMPLETED', 'REJECTED'])->default('PENDING');
            $table->foreignId('processed_by_commercial_id')->nullable()->constrained('users');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users');
            
            // Livraison en espèces
            $table->string('delivery_receipt_code')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('delivery_proof')->nullable(); // signature, photo, etc.
            
            // Notes et raison de rejet
            $table->text('processing_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Horodatage
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['status', 'method']);
            $table->index(['client_id', 'status']);
            $table->index(['processed_by_commercial_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};