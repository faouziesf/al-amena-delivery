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
            // Ajouter bank_account_id si pas présent
            if (!Schema::hasColumn('withdrawal_requests', 'bank_account_id')) {
                $table->foreignId('bank_account_id')->nullable()->after('bank_details')
                    ->constrained('client_bank_accounts')->nullOnDelete();
            }
            
            // Ajouter reason si pas présent
            if (!Schema::hasColumn('withdrawal_requests', 'reason')) {
                $table->text('reason')->nullable()->after('amount');
            }
            
            // S'assurer que bank_details est JSON
            if (Schema::hasColumn('withdrawal_requests', 'bank_details')) {
                $table->json('bank_details')->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            if (Schema::hasColumn('withdrawal_requests', 'bank_account_id')) {
                $table->dropForeign(['bank_account_id']);
                $table->dropColumn('bank_account_id');
            }
            
            if (Schema::hasColumn('withdrawal_requests', 'reason')) {
                $table->dropColumn('reason');
            }
        });
    }
};
