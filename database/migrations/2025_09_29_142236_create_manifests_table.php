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
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number')->unique();
            $table->foreignId('sender_id')->constrained('users');
            $table->json('package_ids'); // IDs des colis dans ce manifeste
            $table->foreignId('pickup_address_id')->constrained('client_pickup_addresses');
            $table->string('pickup_address_name');
            $table->string('pickup_phone');
            $table->integer('total_packages');
            $table->decimal('total_cod_amount', 10, 3)->default(0);
            $table->decimal('total_weight', 8, 3)->nullable();
            $table->foreignId('pickup_request_id')->nullable()->constrained('pickup_requests');
            $table->enum('status', ['CREATED', 'REQUESTED', 'COLLECTED', 'CANCELLED'])->default('CREATED');
            $table->timestamp('generated_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifests');
    }
};
