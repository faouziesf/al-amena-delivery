<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('package_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->string('previous_status');
            $table->string('new_status');
            $table->foreignId('changed_by')->constrained('users');
            $table->string('changed_by_role');
            $table->text('notes')->nullable();
            $table->json('additional_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Index pour performances
            $table->index(['package_id', 'created_at']);
            $table->index(['changed_by', 'created_at']);
            $table->index('new_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('package_status_histories');
    }
};