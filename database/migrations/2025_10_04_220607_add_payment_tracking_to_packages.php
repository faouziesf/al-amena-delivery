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
            // Track how return fees were paid for proper refunds
            $table->decimal('advance_used_for_fees', 16, 3)->default(0)->after('amount_in_escrow');
            $table->decimal('balance_used_for_fees', 16, 3)->default(0)->after('advance_used_for_fees');
            $table->string('fee_payment_source', 20)->nullable()->after('balance_used_for_fees'); // 'advance', 'balance', 'mixed'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['advance_used_for_fees', 'balance_used_for_fees', 'fee_payment_source']);
        });
    }
};
