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
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->decimal('advance_balance', 10, 3)->default(0.000)->after('frozen_amount');
            $table->timestamp('advance_last_modified_at')->nullable()->after('advance_balance');
            $table->unsignedBigInteger('advance_last_modified_by')->nullable()->after('advance_last_modified_at');

            $table->foreign('advance_last_modified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_wallets', function (Blueprint $table) {
            $table->dropForeign(['advance_last_modified_by']);
            $table->dropColumn(['advance_balance', 'advance_last_modified_at', 'advance_last_modified_by']);
        });
    }
};
