<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['CLIENT', 'DELIVERER', 'COMMERCIAL', 'SUPERVISOR'])->after('email');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->enum('account_status', ['PENDING', 'ACTIVE', 'SUSPENDED'])->default('PENDING')->after('role');
            $table->timestamp('verified_at')->nullable()->after('account_status');
            $table->foreignId('verified_by')->nullable()->constrained('users')->after('verified_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->after('verified_by');
            $table->timestamp('last_login')->nullable()->after('created_by');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'role', 'phone', 'address', 'account_status', 
                'verified_at', 'verified_by', 'created_by', 'last_login'
            ]);
        });
    }
};