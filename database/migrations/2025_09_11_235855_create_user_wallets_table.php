<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('balance', 10, 3)->default(0);
            $table->decimal('pending_amount', 10, 3)->default(0);
            $table->decimal('frozen_amount', 10, 3)->default(0);
            $table->timestamp('last_transaction_at')->nullable();
            $table->string('last_transaction_id')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index(['user_id', 'balance']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_wallets');
    }
};