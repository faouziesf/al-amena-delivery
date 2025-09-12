<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deliverer_wallet_emptyings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverer_id')->constrained('users');
            $table->foreignId('commercial_id')->constrained('users');
            $table->decimal('wallet_amount', 10, 3); // Montant affiché dans le wallet
            $table->decimal('physical_amount', 10, 3); // Montant physique remis
            $table->decimal('discrepancy_amount', 10, 3)->default(0); // Différence (wallet - physique)
            $table->timestamp('emptying_date');
            $table->text('notes')->nullable();
            $table->boolean('receipt_generated')->default(false);
            $table->string('receipt_path')->nullable();
            $table->json('emptying_details')->nullable(); // Détails sources (COD colis X, fond client Y)
            $table->boolean('deliverer_acknowledged')->default(false);
            $table->timestamp('deliverer_acknowledged_at')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['deliverer_id', 'emptying_date']);
            $table->index(['commercial_id', 'emptying_date']);
            $table->index('emptying_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliverer_wallet_emptyings');
    }
};