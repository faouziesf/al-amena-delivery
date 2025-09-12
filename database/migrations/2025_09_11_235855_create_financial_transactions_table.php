<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('type', [
                'PACKAGE_CREATION_DEBIT', 'PACKAGE_DELIVERY_CREDIT', 
                'COD_COLLECTION', 'WALLET_RECHARGE', 'WALLET_WITHDRAWAL',
                'DELIVERER_PAYMENT', 'COMMERCIAL_EMPTYING', 'SYSTEM_ADJUSTMENT'
            ]);
            $table->decimal('amount', 10, 3);
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->string('package_id')->nullable();
            $table->text('description');
            $table->bigInteger('sequence_number')->unique();
            $table->decimal('wallet_balance_before', 10, 3);
            $table->decimal('wallet_balance_after', 10, 3)->nullable();
            $table->string('checksum')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type', 'status']);
            $table->index(['created_at', 'status']);
            $table->index('sequence_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_transactions');
    }
};