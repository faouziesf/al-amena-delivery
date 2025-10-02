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
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->enum('validation_status', ['PENDING', 'VALIDATED', 'REJECTED'])->default('PENDING')->after('internal_notes');
            $table->foreignId('validated_by')->nullable()->constrained('users')->after('validation_status');
            $table->timestamp('validated_at')->nullable()->after('validated_by');
            $table->text('validation_notes')->nullable()->after('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['validation_status', 'validated_by', 'validated_at', 'validation_notes']);
        });
    }
};
