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
        // Table: users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['ADMIN', 'COMMERCIAL', 'CLIENT', 'DELIVERER', 'DEPOT_MANAGER'])->default('CLIENT');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('delegation')->nullable();
            $table->string('delegation_from')->nullable();
            $table->string('delegation_to')->nullable();
            $table->enum('deliverer_type', ['INTERNAL', 'EXTERNAL'])->nullable();
            $table->foreignId('assigned_depot_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        // Table: client_profiles
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name')->nullable();
            $table->string('company_type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('business_address')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('website')->nullable();
            $table->text('business_description')->nullable();
            $table->decimal('delivery_fee_rate', 8, 3)->default(0);
            $table->decimal('cod_fee_percentage', 5, 2)->default(0);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Table: packages
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code')->unique();
            $table->string('tracking_number')->unique()->nullable();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->json('sender_data')->nullable();
            $table->string('delegation_from');
            $table->json('recipient_data')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('recipient_address')->nullable();
            $table->string('recipient_city')->nullable();
            $table->string('delegation_to');
            $table->text('content_description')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('cod_amount', 10, 3)->default(0);
            $table->decimal('delivery_fee', 10, 3)->default(0);
            $table->decimal('return_fee', 10, 3)->default(0);
            $table->enum('status', [
                'CREATED', 'AVAILABLE', 'ACCEPTED', 'PICKED_UP', 
                'IN_TRANSIT', 'DELIVERED', 'RETURNED', 'CANCELLED', 
                'UNAVAILABLE', 'PAID'
            ])->default('CREATED');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('delivery_attempts')->default(0);
            $table->boolean('cod_modifiable_by_commercial')->default(false);
            $table->decimal('amount_in_escrow', 10, 3)->default(0);
            $table->string('delivery_signature')->nullable();
            $table->foreignId('pickup_request_id')->nullable()->constrained('pickup_requests')->onDelete('set null');
            $table->boolean('est_echange')->default(false);
            $table->foreignId('previous_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reassigned_at')->nullable();
            $table->text('reassignment_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['client_id', 'status']);
            $table->index(['assigned_deliverer_id', 'status']);
            $table->index('delegation_from');
            $table->index('delegation_to');
        });

        // Table: pickup_requests
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('pickup_code')->unique()->nullable();
            $table->string('pickup_address');
            $table->string('pickup_contact');
            $table->string('pickup_phone');
            $table->string('pickup_contact_name')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->string('delegation_from');
            $table->timestamp('requested_pickup_date')->nullable();
            $table->enum('status', ['pending', 'assigned', 'picked_up', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'status']);
            $table->index('assigned_deliverer_id');
        });

        // Table: user_wallets
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 15, 3)->default(0);
            $table->decimal('available_balance', 15, 3)->default(0);
            $table->decimal('pending_amount', 15, 3)->default(0);
            $table->decimal('advance_balance', 15, 3)->default(0);
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Table: financial_transactions
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['CREDIT', 'DEBIT', 'TRANSFER', 'FEE', 'COD', 'REFUND', 'WITHDRAWAL', 'TOPUP', 'ADVANCE'])->default('CREDIT');
            $table->decimal('amount', 15, 3);
            $table->decimal('balance_after', 15, 3);
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'CANCELLED'])->default('COMPLETED');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        // Table: withdrawal_requests
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 3);
            $table->enum('payment_method', ['BANK_TRANSFER', 'CASH', 'CHECK', 'MOBILE_MONEY'])->default('BANK_TRANSFER');
            $table->json('payment_details')->nullable();
            $table->enum('status', [
                'PENDING', 'APPROVED', 'REJECTED', 'PROCESSING', 
                'COMPLETED', 'READY_FOR_DELIVERY', 'IN_PROGRESS', 
                'DELIVERED', 'FAILED'
            ])->default('PENDING');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('depot_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->index(['client_id', 'status']);
        });

        // Table: topup_requests
        Schema::create('topup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 3);
            $table->enum('payment_method', ['BANK_TRANSFER', 'CASH', 'CHECK', 'MOBILE_MONEY', 'CARD'])->default('BANK_TRANSFER');
            $table->json('payment_details')->nullable();
            $table->string('receipt_image')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED', 'COMPLETED'])->default('PENDING');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });

        // Table: cod_modifications
        Schema::create('cod_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->decimal('old_amount', 10, 3);
            $table->decimal('new_amount', 10, 3);
            $table->text('reason')->nullable();
            $table->foreignId('modified_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Table: package_status_histories
        Schema::create('package_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('old_status');
            $table->string('new_status');
            $table->foreignId('changed_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['package_id', 'created_at']);
        });

        // Table: delegations
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('gouvernorat');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table: client_pickup_addresses
        Schema::create('client_pickup_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('tel2')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('delegation');
            $table->string('gouvernorat')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Table: client_bank_accounts
        Schema::create('client_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('bank_name');
            $table->string('account_holder_name');
            $table->string('iban');
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        // Table: saved_addresses
        Schema::create('saved_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->string('address');
            $table->string('city');
            $table->string('delegation');
            $table->text('notes')->nullable();
            $table->boolean('is_favorite')->default(false);
            $table->timestamps();
        });

        // Table: tickets
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['LOW', 'NORMAL', 'HIGH', 'URGENT'])->default('NORMAL');
            $table->enum('status', ['OPEN', 'IN_PROGRESS', 'RESOLVED', 'CLOSED'])->default('OPEN');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_complaint')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });

        // Table: ticket_messages
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();
        });

        // Table: ticket_attachments
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_message_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedInteger('file_size');
            $table->timestamps();
        });

        // Table: complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('subject');
            $table->text('description');
            $table->enum('type', ['DELIVERY_DELAY', 'DAMAGED_PACKAGE', 'LOST_PACKAGE', 'WRONG_DELIVERY', 'POOR_SERVICE', 'OTHER'])->default('OTHER');
            $table->enum('status', ['PENDING', 'INVESTIGATING', 'RESOLVED', 'CLOSED'])->default('PENDING');
            $table->text('resolution')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // Table: run_sheets
        Schema::create('run_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('run_sheet_number')->unique();
            $table->foreignId('deliverer_id')->constrained('users')->onDelete('cascade');
            $table->date('run_date');
            $table->string('sector')->nullable();
            $table->enum('status', ['PLANNED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('PLANNED');
            $table->json('package_ids')->nullable();
            $table->integer('total_packages')->default(0);
            $table->integer('delivered_packages')->default(0);
            $table->decimal('total_cod', 15, 3)->default(0);
            $table->decimal('collected_cod', 15, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Table: manifests
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number')->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('from_location');
            $table->string('to_location');
            $table->date('manifest_date');
            $table->enum('status', ['PENDING', 'IN_TRANSIT', 'RECEIVED', 'CANCELLED'])->default('PENDING');
            $table->json('package_ids');
            $table->integer('total_packages')->default(0);
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Table: transit_routes
        Schema::create('transit_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_code')->unique();
            $table->string('origin_delegation');
            $table->string('destination_delegation');
            $table->decimal('estimated_duration_hours', 5, 2)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table: transit_boxes
        Schema::create('transit_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('box_code')->unique();
            $table->foreignId('route_id')->constrained('transit_routes')->onDelete('cascade');
            $table->enum('status', ['IN_PREPARATION', 'IN_TRANSIT', 'ARRIVED', 'DISTRIBUTED'])->default('IN_PREPARATION');
            $table->json('package_ids')->nullable();
            $table->integer('package_count')->default(0);
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamps();
        });

        // Table: deliverer_wallet_emptyings
        Schema::create('deliverer_wallet_emptyings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 3);
            $table->enum('status', ['PENDING', 'APPROVED', 'COMPLETED', 'REJECTED'])->default('PENDING');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Table: import_batches
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('total_rows')->default(0);
            $table->integer('successful_imports')->default(0);
            $table->integer('failed_imports')->default(0);
            $table->json('errors')->nullable();
            $table->enum('status', ['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED'])->default('PENDING');
            $table->timestamps();
        });

        // Table: action_logs
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
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
            
            $table->index(['user_id', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });

        // Table: notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['notifiable_type', 'notifiable_id']);
        });

        // Table: wallet_transaction_backups
        Schema::create('wallet_transaction_backups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->decimal('amount', 15, 3);
            $table->decimal('balance_after', 15, 3);
            $table->text('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('related_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('backed_up_at');
            $table->timestamps();
        });

        // Table: transactions_table_alias (if needed)
        Schema::create('transactions_table_alias', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('type');
            $table->decimal('amount', 15, 3);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Failed jobs
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Job batches
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

        // Jobs
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Personal access tokens
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Cache
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // Cache locks
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('transactions_table_alias');
        Schema::dropIfExists('wallet_transaction_backups');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('action_logs');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('deliverer_wallet_emptyings');
        Schema::dropIfExists('transit_boxes');
        Schema::dropIfExists('transit_routes');
        Schema::dropIfExists('manifests');
        Schema::dropIfExists('run_sheets');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('saved_addresses');
        Schema::dropIfExists('client_bank_accounts');
        Schema::dropIfExists('client_pickup_addresses');
        Schema::dropIfExists('delegations');
        Schema::dropIfExists('package_status_histories');
        Schema::dropIfExists('cod_modifications');
        Schema::dropIfExists('topup_requests');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('user_wallets');
        Schema::dropIfExists('pickup_requests');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('client_profiles');
        Schema::dropIfExists('users');
    }
};
