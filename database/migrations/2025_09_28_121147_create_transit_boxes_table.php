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
        Schema::create('transit_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transit_routes')->onDelete('cascade');
            $table->string('code')->unique(); // Ex: SFAX-TUN-28092025-01
            $table->string('destination_governorate');
            $table->integer('packages_count')->default(0);
            $table->enum('status', ['PENDING', 'LOADED', 'IN_TRANSIT', 'DELIVERED'])->default('PENDING');
            $table->timestamp('loaded_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('package_ids')->nullable(); // IDs des colis dans cette boîte
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['route_id', 'status']);
            $table->index('destination_governorate');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transit_boxes');
    }
};
