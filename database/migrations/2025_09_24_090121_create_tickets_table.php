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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Numéro unique du ticket (ex: TKT_20241024_001)
            $table->enum('type', ['COMPLAINT', 'QUESTION', 'SUPPORT', 'OTHER'])->default('QUESTION');
            $table->string('subject'); // Sujet du ticket
            $table->text('description'); // Description initiale
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED', 'URGENT'])->default('OPEN');
            $table->enum('priority', ['LOW', 'NORMAL', 'HIGH', 'URGENT'])->default('NORMAL');

            // Relations
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->onDelete('set null'); // Commercial assigné
            $table->foreignId('complaint_id')->nullable()->constrained('complaints')->onDelete('set null'); // Si créé depuis réclamation
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null'); // Package lié si applicable

            // Méta-données
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->timestamp('first_response_at')->nullable(); // Première réponse du commercial
            $table->timestamp('last_activity_at')->nullable(); // Dernière activité
            $table->timestamp('resolved_at')->nullable(); // Date de résolution
            $table->timestamp('closed_at')->nullable(); // Date de fermeture
            $table->timestamps();

            // Index
            $table->index(['client_id', 'status']);
            $table->index(['assigned_to_id', 'status']);
            $table->index(['status', 'priority']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
