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
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('is_complaint')->default(false)->after('type');
            $table->text('complaint_description')->nullable()->after('description');
            $table->json('complaint_data')->nullable()->after('complaint_description');
            $table->string('complaint_type')->nullable()->after('complaint_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['is_complaint', 'complaint_description', 'complaint_data', 'complaint_type']);
        });
    }
};
