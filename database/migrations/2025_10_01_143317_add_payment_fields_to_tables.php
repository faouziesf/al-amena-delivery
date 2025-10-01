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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->foreignId('assigned_package_id')->nullable()->constrained('packages')->onDelete('set null');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->foreignId('payment_withdrawal_id')->nullable()->constrained('withdrawal_requests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_package_id');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_withdrawal_id');
        });
    }
};
