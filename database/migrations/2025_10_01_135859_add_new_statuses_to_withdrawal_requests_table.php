<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pour SQLite, nous devons recréer la colonne avec les nouveaux statuts
        DB::statement("
            CREATE TABLE withdrawal_requests_new AS
            SELECT * FROM withdrawal_requests
        ");

        DB::statement("DROP TABLE withdrawal_requests");

        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('client_id')->constrained('users');
            $table->decimal('amount', 10, 3);
            $table->enum('method', ['BANK_TRANSFER', 'CASH_DELIVERY']);
            $table->json('bank_details')->nullable();
            $table->enum('status', [
                'PENDING',
                'APPROVED',
                'PROCESSED',           // Nouveau: pour virements bancaires traités
                'READY_FOR_DELIVERY',  // Nouveau: pour espèces assignées au livreur
                'IN_PROGRESS',
                'DELIVERED',           // Nouveau: statut final après livraison
                'COMPLETED',
                'REJECTED'
            ])->default('PENDING');
            $table->foreignId('processed_by_commercial_id')->nullable()->constrained('users');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users');
            $table->string('delivery_receipt_code')->nullable();
            $table->text('processing_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('delivery_proof')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Restaurer les données
        DB::statement("
            INSERT INTO withdrawal_requests
            SELECT * FROM withdrawal_requests_new
        ");

        DB::statement("DROP TABLE withdrawal_requests_new");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pour rollback, on recrée la table avec les anciens statuts
        DB::statement("
            CREATE TABLE withdrawal_requests_old AS
            SELECT * FROM withdrawal_requests
        ");

        DB::statement("DROP TABLE withdrawal_requests");

        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('client_id')->constrained('users');
            $table->decimal('amount', 10, 3);
            $table->enum('method', ['BANK_TRANSFER', 'CASH_DELIVERY']);
            $table->json('bank_details')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'IN_PROGRESS', 'COMPLETED', 'REJECTED'])->default('PENDING');
            $table->foreignId('processed_by_commercial_id')->nullable()->constrained('users');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users');
            $table->string('delivery_receipt_code')->nullable();
            $table->text('processing_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('delivery_proof')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Restaurer les données en convertissant les nouveaux statuts
        DB::statement("
            INSERT INTO withdrawal_requests
            SELECT
                id, request_code, client_id, amount, method, bank_details,
                CASE
                    WHEN status IN ('PROCESSED', 'READY_FOR_DELIVERY', 'DELIVERED') THEN 'COMPLETED'
                    ELSE status
                END as status,
                processed_by_commercial_id, assigned_deliverer_id, delivery_receipt_code,
                processing_notes, rejection_reason, delivered_at, delivery_proof, processed_at,
                created_at, updated_at
            FROM withdrawal_requests_old
        ");

        DB::statement("DROP TABLE withdrawal_requests_old");
    }
};
