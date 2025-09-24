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
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('allow_opening')->default(false)->after('requires_signature');
            $table->enum('payment_method', ['cash_only', 'check_only', 'both'])->default('cash_only')->after('allow_opening');
            $table->unsignedBigInteger('pickup_address_id')->nullable()->after('pickup_request_id');

            // Index pour la relation pickup_address
            $table->foreign('pickup_address_id')->references('id')->on('client_pickup_addresses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['pickup_address_id']);
            $table->dropColumn(['allow_opening', 'payment_method', 'pickup_address_id']);
        });
    }
};