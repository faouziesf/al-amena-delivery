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
            $table->unsignedBigInteger('depot_manager_id')->nullable()->after('status');
            $table->string('depot_manager_name')->nullable()->after('depot_manager_id');

            $table->foreign('depot_manager_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['depot_manager_id']);
            $table->dropColumn(['depot_manager_id', 'depot_manager_name']);
        });
    }
};
