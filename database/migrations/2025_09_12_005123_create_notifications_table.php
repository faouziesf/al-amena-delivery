<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // COMPLAINT_NEW, WITHDRAWAL_REQUEST, WALLET_HIGH, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Données contextuelles
            $table->enum('priority', ['LOW', 'NORMAL', 'HIGH', 'URGENT'])->default('NORMAL');
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('action_url')->nullable(); // URL pour action directe
            $table->string('related_type')->nullable(); // Type d'entité liée (Package, Complaint, etc.)
            $table->string('related_id')->nullable(); // ID de l'entité liée
            $table->timestamps();
            
            // Index pour performances
            $table->index(['user_id', 'read', 'created_at']);
            $table->index(['type', 'priority']);
            $table->index(['related_type', 'related_id']);
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};