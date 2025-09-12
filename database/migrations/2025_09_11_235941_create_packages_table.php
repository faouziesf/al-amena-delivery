<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code')->unique();
            $table->foreignId('sender_id')->constrained('users');
            
            // Informations expéditeur
            $table->json('sender_data'); // nom, téléphone, adresse
            $table->foreignId('delegation_from')->constrained('delegations');
            
            // Informations destinataire
            $table->json('recipient_data'); // nom, téléphone, adresse
            $table->foreignId('delegation_to')->constrained('delegations');
            
            // Détails du colis
            $table->string('content_description');
            $table->text('notes')->nullable();
            $table->decimal('cod_amount', 10, 3)->default(0);
            $table->decimal('delivery_fee', 8, 3);
            $table->decimal('return_fee', 8, 3);
            
            // Statuts et assignation
            $table->enum('status', [
                'CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 
                'DELIVERED', 'PAID', 'REFUSED', 'RETURNED', 
                'UNAVAILABLE', 'VERIFIED', 'CANCELLED'
            ])->default('CREATED');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->integer('delivery_attempts')->default(0);
            
            // Gestion financière
            $table->boolean('cod_modifiable_by_commercial')->default(true);
            $table->decimal('amount_in_escrow', 10, 3)->default(0);
            
            // Horodatage
            $table->timestamps();
            
            // Index pour performances
            $table->index(['status', 'assigned_deliverer_id']);
            $table->index(['sender_id', 'status']);
            $table->index(['delegation_from', 'delegation_to']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
};