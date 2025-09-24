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
        Schema::table('client_pickup_addresses', function (Blueprint $table) {
            $table->string('gouvernorat')->nullable()->after('delegation');
            $table->string('tel2')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_pickup_addresses', function (Blueprint $table) {
            $table->dropColumn(['gouvernorat', 'tel2']);
        });
    }
};