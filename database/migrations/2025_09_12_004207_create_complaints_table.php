<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code')->unique();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users');
            
            // Type et détails de la réclamation
            $table->enum('type', [
                'CHANGE_COD', 'DELIVERY_DELAY', 'REQUEST_RETURN', 
                'RETURN_DELAY', 'RESCHEDULE_TODAY', 'FOURTH_ATTEMPT', 'CUSTOM'
            ]);
            $table->text('description');
            $table->json('additional_data')->nullable(); // Pour stocker des données spécifiques par type
            
            // Gestion et résolution
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'RESOLVED', 'REJECTED'])->default('PENDING');
            $table->enum('priority', ['LOW', 'NORMAL', 'HIGH', 'URGENT'])->default('NORMAL');
            $table->foreignId('assigned_commercial_id')->nullable()->constrained('users');
            $table->text('resolution_notes')->nullable();
            $table->json('resolution_data')->nullable(); // Actions prises, montants modifiés, etc.
            
            // Horodatage
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            // Index
            $table->index(['status', 'priority']);
            $table->index(['client_id', 'status']);
            $table->index(['assigned_commercial_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('complaints');
    }
};