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
        // Add wallet_balance field to users table for depot managers
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('depot_wallet_balance', 16, 3)->default(0)->after('is_depot_manager');
        });

        // Create depot_manager_wallet_transactions table to track wallet operations
        Schema::create('depot_manager_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_code')->unique();
            $table->foreignId('depot_manager_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['DELIVERER_EMPTYING', 'CASH_PAYMENT', 'SUPERVISOR_ADJUSTMENT', 'SUPERVISOR_EMPTYING']);
            $table->decimal('amount', 16, 3);
            $table->decimal('balance_before', 16, 3);
            $table->decimal('balance_after', 16, 3);
            $table->foreignId('related_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('related_withdrawal_id')->nullable()->constrained('withdrawal_requests')->onDelete('set null');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('depot_manager_id');
            $table->index('type');
            $table->index('created_at');
        });

        // Add depot_manager_id to deliverer_wallet_emptyings table
        Schema::table('deliverer_wallet_emptyings', function (Blueprint $table) {
            $table->foreignId('depot_manager_id')->nullable()->after('commercial_id')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliverer_wallet_emptyings', function (Blueprint $table) {
            $table->dropForeign(['depot_manager_id']);
            $table->dropColumn('depot_manager_id');
        });

        Schema::dropIfExists('depot_manager_wallet_transactions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('depot_wallet_balance');
        });
    }
};
