<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cod_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->decimal('old_amount', 10, 3);
            $table->decimal('new_amount', 10, 3);
            $table->foreignId('modified_by_commercial_id')->constrained('users');
            $table->string('reason');
            $table->foreignId('client_complaint_id')->nullable()->constrained('complaints');
            $table->text('modification_notes')->nullable();
            $table->json('context_data')->nullable(); // Données contextuelles
            $table->string('ip_address')->nullable();
            $table->boolean('emergency_modification')->default(false);
            $table->timestamps();
            
            // Index
            $table->index(['package_id', 'created_at']);
            $table->index(['modified_by_commercial_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cod_modifications');
    }
};