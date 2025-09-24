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
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('pickup_address');
            $table->string('pickup_phone')->nullable();
            $table->string('pickup_contact_name')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->string('delegation_from'); // Zone de pickup
            $table->dateTime('requested_pickup_date');
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'cancelled'])->default('pending');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('picked_up_at')->nullable();
            $table->json('packages')->nullable(); // Liste des colis concernés par ce pickup
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['assigned_deliverer_id', 'status']);
            $table->index(['delegation_from', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pickup_requests');
    }
};
