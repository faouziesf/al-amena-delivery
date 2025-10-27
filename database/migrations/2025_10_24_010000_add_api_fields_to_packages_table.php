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
            $table->string('external_reference', 255)->nullable()->after('comment');
            $table->string('created_via', 20)->default('WEB')->after('external_reference');
            
            $table->index('external_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['external_reference']);
            $table->dropColumn(['external_reference', 'created_via']);
        });
    }
};
