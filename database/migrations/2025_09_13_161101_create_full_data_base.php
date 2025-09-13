<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Table: users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->enum('role', ['CLIENT', 'DELIVERER', 'COMMERCIAL', 'SUPERVISOR'])->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('account_status', ['PENDING', 'ACTIVE', 'SUSPENDED'])->default('PENDING');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->timestamps();

            $table->index('role', 'idx_users_role');
            $table->index('account_status', 'idx_users_account_status');
            $table->index('created_by', 'idx_users_created_by');
            $table->index('verified_by', 'idx_users_verified_by');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });

        // Table: password_reset_tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Table: sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Table: cache
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // Table: cache_locks
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Table: jobs
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Table: job_batches
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // Table: failed_jobs
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Table: delegations
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['active', 'zone']);
        });

        // Table: user_wallets
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('balance', 10, 3)->default(0.000);
            $table->decimal('pending_amount', 10, 3)->default(0.000);
            $table->decimal('frozen_amount', 10, 3)->default(0.000);
            $table->timestamp('last_transaction_at')->nullable();
            $table->string('last_transaction_id')->nullable();
            $table->timestamps();
            $table->index('user_id', 'user_wallets_user_id_index');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Table: financial_transactions
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->decimal('amount', 10, 3);
            $table->string('status')->default('PENDING');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sequence_number')->nullable();
            $table->decimal('wallet_balance_before', 10, 3)->nullable();
            $table->decimal('wallet_balance_after', 10, 3)->nullable();
            $table->string('checksum')->nullable();
            $table->json('metadata')->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index('user_id', 'financial_transactions_user_id_index');
            $table->index('type', 'financial_transactions_type_index');
            $table->index('status', 'financial_transactions_status_index');
            $table->index('created_at', 'financial_transactions_created_at_index');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Table: client_profiles
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('shop_name')->nullable();
            $table->string('fiscal_number')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('identity_document')->nullable();
            $table->decimal('offer_delivery_price', 8, 3);
            $table->decimal('offer_return_price', 8, 3);
            $table->timestamps();
            $table->unique('user_id');
        });

        // Table: wallet_transaction_backups
        Schema::create('wallet_transaction_backups', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->json('snapshot_data');
            $table->timestamp('backup_at');
            $table->timestamps();
            $table->index('transaction_id');
            $table->index('backup_at');
        });

        // Table: action_logs
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('user_role');
            $table->string('action_type');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'action_type']);
            $table->index(['created_at', 'action_type']);
        });

        // Table: packages
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code')->unique();
            $table->foreignId('sender_id')->constrained('users');
            $table->json('sender_data');
            $table->foreignId('delegation_from')->constrained('delegations');
            $table->json('recipient_data');
            $table->foreignId('delegation_to')->constrained('delegations');
            $table->string('content_description');
            $table->text('notes')->nullable();
            $table->decimal('cod_amount', 10, 3)->default(0);
            $table->decimal('delivery_fee', 8, 3);
            $table->decimal('return_fee', 8, 3);
            $table->enum('status', [
                'CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP',
                'DELIVERED', 'PAID', 'REFUSED', 'RETURNED',
                'UNAVAILABLE', 'VERIFIED', 'CANCELLED'
            ])->default('CREATED');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users');
            $table->timestamp('assigned_at')->nullable();
            $table->integer('delivery_attempts')->default(0);
            $table->boolean('cod_modifiable_by_commercial')->default(true);
            $table->decimal('amount_in_escrow', 10, 3)->default(0);
            $table->timestamps();
            $table->index(['status', 'assigned_deliverer_id']);
            $table->index(['sender_id', 'status']);
            $table->index(['delegation_from', 'delegation_to']);
            $table->index('created_at');
        });

        // Table: complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code')->unique();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users');
            $table->enum('type', [
                'CHANGE_COD', 'DELIVERY_DELAY', 'REQUEST_RETURN',
                'RETURN_DELAY', 'RESCHEDULE_TODAY', 'FOURTH_ATTEMPT', 'CUSTOM'
            ]);
            $table->text('description');
            $table->json('additional_data')->nullable();
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'RESOLVED', 'REJECTED'])->default('PENDING');
            $table->enum('priority', ['LOW', 'NORMAL', 'HIGH', 'URGENT'])->default('NORMAL');
            $table->foreignId('assigned_commercial_id')->nullable()->constrained('users');
            $table->text('resolution_notes')->nullable();
            $table->json('resolution_data')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'priority']);
            $table->index(['client_id', 'status']);
            $table->index(['assigned_commercial_id', 'status']);
            $table->index('created_at');
        });

        // Table: package_status_histories
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
            $table->index(['package_id', 'created_at']);
            $table->index(['changed_by', 'created_at']);
            $table->index('new_status');
        });

        // Table: notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->enum('priority', ['LOW', 'NORMAL', 'HIGH', 'URGENT'])->default('NORMAL');
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('action_url')->nullable();
            $table->string('related_type')->nullable();
            $table->string('related_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read', 'created_at']);
            $table->index(['type', 'priority']);
            $table->index(['related_type', 'related_id']);
            $table->index('expires_at');
        });

        // Table: withdrawal_requests
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
            $table->timestamp('delivered_at')->nullable();
            $table->json('delivery_proof')->nullable();
            $table->text('processing_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'method']);
            $table->index(['client_id', 'status']);
            $table->index(['processed_by_commercial_id', 'status']);
            $table->index('created_at');
        });

        // Table: cod_modifications
        Schema::create('cod_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->decimal('old_amount', 10, 3);
            $table->decimal('new_amount', 10, 3);
            $table->foreignId('modified_by_commercial_id')->constrained('users');
            $table->string('reason');
            $table->foreignId('client_complaint_id')->nullable()->constrained('complaints');
            $table->text('modification_notes')->nullable();
            $table->json('context_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('emergency_modification')->default(false);
            $table->timestamps();
            $table->index(['package_id', 'created_at']);
            $table->index(['modified_by_commercial_id', 'created_at']);
            $table->index('created_at');
        });

        // Table: deliverer_wallet_emptyings
        Schema::create('deliverer_wallet_emptyings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverer_id')->constrained('users');
            $table->foreignId('commercial_id')->constrained('users');
            $table->decimal('wallet_amount', 10, 3);
            $table->decimal('physical_amount', 10, 3);
            $table->decimal('discrepancy_amount', 10, 3)->default(0);
            $table->timestamp('emptying_date');
            $table->text('notes')->nullable();
            $table->boolean('receipt_generated')->default(false);
            $table->string('receipt_path')->nullable();
            $table->json('emptying_details')->nullable();
            $table->boolean('deliverer_acknowledged')->default(false);
            $table->timestamp('deliverer_acknowledged_at')->nullable();
            $table->timestamps();
            $table->index(['deliverer_id', 'emptying_date']);
            $table->index(['commercial_id', 'emptying_date']);
            $table->index('emptying_date');
        });

        // Insertion des wallets pour les utilisateurs existants
        DB::statement("
            INSERT INTO user_wallets (user_id, balance, pending_amount, frozen_amount, created_at, updated_at)
            SELECT id, 0.000, 0.000, 0.000, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM users 
            WHERE role IN ('CLIENT', 'DELIVERER') 
            AND id NOT IN (SELECT user_id FROM user_wallets)
        ");
    }

    public function down()
    {
        Schema::dropIfExists('deliverer_wallet_emptyings');
        Schema::dropIfExists('cod_modifications');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('package_status_histories');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('action_logs');
        Schema::dropIfExists('wallet_transaction_backups');
        Schema::dropIfExists('client_profiles');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('user_wallets');
        Schema::dropIfExists('delegations');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};