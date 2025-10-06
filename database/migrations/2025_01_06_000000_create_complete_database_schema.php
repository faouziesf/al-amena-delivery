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
            $table->string('remember_token')->nullable();
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('account_status')->default('PENDING');
            $table->timestamp('verified_at')->nullable();
            $table->integer('verified_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->string('assigned_delegation')->nullable();
            $table->decimal('delegation_latitude', 15, 3)->nullable();
            $table->decimal('delegation_longitude', 15, 3)->nullable();
            $table->integer('delegation_radius_km')->default('10');
            $table->string('deliverer_type')->default('DELEGATION');
            $table->string('assigned_gouvernorats')->nullable();
            $table->string('depot_name')->nullable();
            $table->string('depot_address')->nullable();
            $table->boolean('is_depot_manager')->default(0);
            $table->timestamps();

            $table->index(['role', 'deliverer_type']);
            $table->index(['assigned_delegation']);
            $table->index(['role', 'assigned_delegation']);
            $table->index(['verified_by']);
            $table->index(['created_by']);
            $table->index(['account_status']);
            $table->index(['role']);
        });

        // Table: delegations
        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone')->nullable();
            $table->boolean('active')->default(1);
            $table->integer('created_by');
            $table->timestamps();

            $table->index(['active', 'zone']);
        });

        // Table: client_profiles
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('shop_name')->nullable();
            $table->string('fiscal_number')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('identity_document')->nullable();
            $table->decimal('offer_delivery_price', 15, 3);
            $table->decimal('offer_return_price', 15, 3);
            $table->string('internal_notes')->nullable();
            $table->string('validation_status')->default('PENDING');
            $table->integer('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->string('validation_notes')->nullable();
            $table->timestamps();

        });

        // Table: client_bank_accounts
        Schema::create('client_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('bank_name');
            $table->string('account_holder_name');
            $table->string('iban');
            $table->boolean('is_default')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'last_used_at']);
            $table->index(['client_id', 'is_default']);
        });

        // Table: client_pickup_addresses
        Schema::create('client_pickup_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('name');
            $table->string('address');
            $table->string('phone')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('delegation');
            $table->string('notes')->nullable();
            $table->boolean('is_default')->default(0);
            $table->string('gouvernorat')->nullable();
            $table->string('tel2')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'is_default']);
        });

        // Table: saved_addresses
        Schema::create('saved_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type')->default('CLIENT');
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('phone');
            $table->string('address');
            $table->unsignedBigInteger('delegation_id');
            $table->boolean('is_default')->default(0);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'is_default']);
            $table->index(['user_id', 'is_default']);
            $table->index(['user_id', 'type']);
        });

        // Table: user_wallets
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('balance', 15, 3)->default(0);
            $table->decimal('pending_amount', 15, 3)->default(0);
            $table->decimal('frozen_amount', 15, 3)->default(0);
            $table->timestamp('last_transaction_at')->nullable();
            $table->string('last_transaction_id')->nullable();
            $table->decimal('advance_balance', 15, 3)->default(0);
            $table->timestamp('advance_last_modified_at')->nullable();
            $table->integer('advance_last_modified_by')->nullable();
            $table->timestamps();

            $table->index(['user_id']);
        });

        // Table: financial_transactions
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->decimal('amount', 15, 3);
            $table->string('status')->default('PENDING');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('description')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->decimal('wallet_balance_before', 15, 3)->nullable();
            $table->decimal('wallet_balance_after', 15, 3)->nullable();
            $table->string('checksum')->nullable();
            $table->string('metadata')->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['status']);
            $table->index(['type']);
            $table->index(['user_id']);
        });

        // Table: withdrawal_requests
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code');
            $table->unsignedBigInteger('client_id');
            $table->decimal('amount', 15, 3);
            $table->string('method');
            $table->string('bank_details')->nullable();
            $table->string('status')->default('PENDING');
            $table->unsignedBigInteger('processed_by_commercial_id')->nullable();
            $table->unsignedBigInteger('assigned_deliverer_id')->nullable();
            $table->string('delivery_receipt_code')->nullable();
            $table->string('processing_notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivery_proof')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('assigned_package_id')->nullable();
            $table->unsignedBigInteger('assigned_depot_manager_id')->nullable();
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['assigned_depot_manager_id']);
        });

        // Table: topup_requests
        Schema::create('topup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code');
            $table->unsignedBigInteger('client_id');
            $table->decimal('amount', 15, 3);
            $table->string('method');
            $table->string('bank_transfer_id')->nullable();
            $table->string('proof_document')->nullable();
            $table->string('notes')->nullable();
            $table->string('status')->default('PENDING');
            $table->unsignedBigInteger('processed_by_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('validation_notes')->nullable();
            $table->string('metadata')->nullable();
            $table->timestamps();

            $table->index(['bank_transfer_id']);
            $table->index(['created_at']);
            $table->index(['processed_by_id', 'status']);
            $table->index(['status', 'method']);
            $table->index(['client_id', 'status']);
        });

        // Table: deliverer_wallet_emptyings
        Schema::create('deliverer_wallet_emptyings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deliverer_id');
            $table->unsignedBigInteger('commercial_id');
            $table->decimal('wallet_amount', 15, 3);
            $table->decimal('physical_amount', 15, 3);
            $table->decimal('discrepancy_amount', 15, 3)->default(0);
            $table->timestamp('emptying_date');
            $table->string('notes')->nullable();
            $table->boolean('receipt_generated')->default(0);
            $table->string('receipt_path')->nullable();
            $table->string('emptying_details')->nullable();
            $table->boolean('deliverer_acknowledged')->default(0);
            $table->timestamp('deliverer_acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['emptying_date']);
            $table->index(['commercial_id', 'emptying_date']);
            $table->index(['deliverer_id', 'emptying_date']);
        });

        // Table: pickup_requests
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('pickup_address');
            $table->string('pickup_phone')->nullable();
            $table->string('pickup_contact_name')->nullable();
            $table->string('pickup_notes')->nullable();
            $table->string('delegation_from');
            $table->timestamp('requested_pickup_date');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('assigned_deliverer_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamps();

            $table->index(['delegation_from', 'status']);
            $table->index(['assigned_deliverer_id', 'status']);
            $table->index(['client_id', 'status']);
        });

        // Table: packages
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code');
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_data');
            $table->string('supplier_data')->nullable();
            $table->integer('delegation_from');
            $table->unsignedBigInteger('pickup_delegation_id')->nullable();
            $table->string('pickup_address')->nullable();
            $table->string('pickup_phone')->nullable();
            $table->string('pickup_notes')->nullable();
            $table->string('recipient_data');
            $table->integer('delegation_to');
            $table->string('content_description');
            $table->string('notes')->nullable();
            $table->decimal('cod_amount', 15, 3)->default(0);
            $table->decimal('package_weight', 15, 3)->nullable();
            $table->string('package_dimensions')->nullable();
            $table->decimal('package_value', 15, 3)->nullable();
            $table->decimal('delivery_fee', 15, 3);
            $table->decimal('return_fee', 15, 3);
            $table->string('special_instructions')->nullable();
            $table->boolean('is_fragile')->default(0);
            $table->boolean('requires_signature')->default(0);
            $table->string('status')->default('CREATED');
            $table->unsignedBigInteger('assigned_deliverer_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->integer('delivery_attempts')->default(0);
            $table->boolean('cod_modifiable_by_commercial')->default(1);
            $table->decimal('amount_in_escrow', 15, 3)->default(0);
            $table->unsignedBigInteger('import_batch_id')->nullable();
            $table->unsignedBigInteger('pickup_request_id')->nullable();
            $table->decimal('advance_used_for_fees', 15, 3)->default(0);
            $table->decimal('balance_used_for_fees', 15, 3)->default(0);
            $table->string('fee_payment_source')->nullable();
            $table->boolean('allow_opening')->default(0);
            $table->string('payment_method')->default('cash_only');
            $table->unsignedBigInteger('pickup_address_id')->nullable();
            $table->timestamp('reassigned_at')->nullable();
            $table->string('reassignment_reason')->nullable();
            $table->boolean('cancelled_by_client')->default(0);
            $table->string('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->integer('cancelled_by')->nullable();
            $table->string('auto_return_reason')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedBigInteger('payment_withdrawal_id')->nullable();
            $table->boolean('est_echange')->default(0);
            $table->timestamps();

            $table->index(['status', 'assigned_deliverer_id']);
            $table->index(['sender_id', 'status']);
            $table->index(['sender_id', 'import_batch_id']);
            $table->index(['pickup_request_id']);
            $table->index(['pickup_delegation_id']);
            $table->index(['import_batch_id']);
            $table->index(['delegation_from', 'delegation_to']);
            $table->index(['created_at']);
            $table->index(['cancelled_by']);
            $table->index(['cancelled_by_client']);
            $table->index(['cancelled_at']);
        });

        // Table: package_status_histories
        Schema::create('package_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->string('previous_status');
            $table->string('new_status');
            $table->integer('changed_by');
            $table->string('changed_by_role');
            $table->string('notes')->nullable();
            $table->string('additional_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['new_status']);
            $table->index(['changed_by', 'created_at']);
            $table->index(['package_id', 'created_at']);
        });

        // Table: cod_modifications
        Schema::create('cod_modifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id');
            $table->decimal('old_amount', 15, 3);
            $table->decimal('new_amount', 15, 3);
            $table->unsignedBigInteger('modified_by_commercial_id');
            $table->string('reason');
            $table->unsignedBigInteger('client_complaint_id')->nullable();
            $table->string('modification_notes')->nullable();
            $table->string('context_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('emergency_modification')->default(0);
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['modified_by_commercial_id', 'created_at']);
            $table->index(['package_id', 'created_at']);
        });

        // Table: import_batches
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code');
            $table->unsignedBigInteger('user_id');
            $table->string('filename');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->string('status')->default('PENDING');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('errors')->nullable();
            $table->string('summary')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['batch_code']);
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'status']);
        });

        // Table: run_sheets
        Schema::create('run_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('sheet_code');
            $table->unsignedBigInteger('deliverer_id');
            $table->unsignedBigInteger('delegation_id');
            $table->text('date');
            $table->string('status')->default('PENDING');
            $table->string('package_types');
            $table->string('sort_criteria')->default('address');
            $table->boolean('include_cod_summary')->default(0);
            $table->string('packages_data');
            $table->integer('packages_count')->default(0);
            $table->decimal('total_cod_amount', 15, 3)->default(0);
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('preparation_notes')->nullable();
            $table->string('completion_notes')->nullable();
            $table->string('completion_stats')->nullable();
            $table->string('route_optimization')->nullable();
            $table->decimal('estimated_distance', 15, 3)->nullable();
            $table->integer('estimated_duration')->nullable();
            $table->string('pdf_path')->nullable();
            $table->integer('print_count')->default(0);
            $table->string('export_formats')->nullable();
            $table->string('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['sheet_code']);
            $table->index(['date']);
            $table->index(['deliverer_id', 'status']);
            $table->index(['status', 'date']);
            $table->index(['delegation_id', 'date']);
            $table->index(['deliverer_id', 'date']);
        });

        // Table: manifests
        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number');
            $table->unsignedBigInteger('sender_id');
            $table->string('package_ids');
            $table->unsignedBigInteger('pickup_address_id');
            $table->string('pickup_address_name');
            $table->string('pickup_phone');
            $table->integer('total_packages');
            $table->decimal('total_cod_amount', 15, 3)->default(0);
            $table->decimal('total_weight', 15, 3)->nullable();
            $table->unsignedBigInteger('pickup_request_id')->nullable();
            $table->string('status')->default('CREATED');
            $table->timestamp('generated_at');
            $table->timestamps();

        });

        // Table: transit_routes
        Schema::create('transit_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('origin_depot');
            $table->string('destination_depot');
            $table->text('date');
            $table->string('status')->default('ASSIGNED');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['driver_id', 'date']);
        });

        // Table: transit_boxes
        Schema::create('transit_boxes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id');
            $table->string('code');
            $table->string('destination_governorate');
            $table->integer('packages_count')->default(0);
            $table->string('status')->default('PENDING');
            $table->timestamp('loaded_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('package_ids')->nullable();
            $table->string('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->index(['code']);
            $table->index(['destination_governorate']);
            $table->index(['route_id', 'status']);
        });

        // Table: tickets
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number');
            $table->string('type')->default('QUESTION');
            $table->string('subject');
            $table->string('description');
            $table->string('status')->default('OPEN');
            $table->string('priority')->default('NORMAL');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('assigned_to_id')->nullable();
            $table->unsignedBigInteger('complaint_id')->nullable();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->string('metadata')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->boolean('is_complaint')->default(0);
            $table->string('complaint_description')->nullable();
            $table->string('complaint_data')->nullable();
            $table->string('complaint_type')->nullable();
            $table->string('category')->default('GENERAL');
            $table->string('source')->default('CLIENT_PORTAL');
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['type']);
            $table->index(['status', 'priority']);
            $table->index(['assigned_to_id', 'status']);
            $table->index(['client_id', 'status']);
        });

        // Table: ticket_messages
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_type');
            $table->string('message');
            $table->string('attachments')->nullable();
            $table->boolean('is_internal')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->string('metadata')->nullable();
            $table->timestamps();

            $table->index(['is_internal']);
            $table->index(['sender_type']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['ticket_id', 'created_at']);
        });

        // Table: ticket_attachments
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path');
            $table->string('url')->nullable();
            $table->integer('size');
            $table->string('mime_type');
            $table->integer('uploaded_by');
            $table->timestamp('uploaded_at');
            $table->timestamps();

            $table->index(['ticket_id', 'uploaded_by']);
        });

        // Table: complaints
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code');
            $table->unsignedBigInteger('package_id');
            $table->unsignedBigInteger('client_id');
            $table->string('type');
            $table->string('description');
            $table->string('additional_data')->nullable();
            $table->string('status')->default('PENDING');
            $table->string('priority')->default('NORMAL');
            $table->unsignedBigInteger('assigned_commercial_id')->nullable();
            $table->string('resolution_notes')->nullable();
            $table->string('resolution_data')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->timestamps();

            $table->index(['ticket_id']);
            $table->index(['status', 'priority']);
            $table->index(['created_at']);
            $table->index(['client_id', 'status']);
            $table->index(['assigned_commercial_id', 'status']);
        });

        // Table: notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->string('title');
            $table->string('message');
            $table->string('data')->nullable();
            $table->string('priority')->default('NORMAL');
            $table->boolean('read')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('action_url')->nullable();
            $table->string('related_type')->nullable();
            $table->string('related_id')->nullable();
            $table->timestamps();

            $table->index(['expires_at']);
            $table->index(['related_type', 'related_id']);
            $table->index(['type', 'priority']);
            $table->index(['user_id', 'read', 'created_at']);
        });

        // Table: action_logs
        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('user_role');
            $table->string('action_type');
            $table->string('target_type')->nullable();
            $table->string('target_id')->nullable();
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('additional_data')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'action_type']);
            $table->index(['user_id', 'action_type']);
        });

        // Table: wallet_transaction_backups
        Schema::create('wallet_transaction_backups', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('snapshot_data');
            $table->timestamp('backup_at');
            $table->timestamps();

            $table->index(['backup_at']);
            $table->index(['transaction_id']);
        });

        // Table: transactions_table_alias
        Schema::create('transactions_table_alias', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        // Table: password_reset_tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email');
            $table->string('token');

        });

        // Table: sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('payload');
            $table->integer('last_activity');

            $table->index(['last_activity']);
            $table->index(['user_id']);
        });

        // Table: failed_jobs
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('connection');
            $table->string('queue');
            $table->string('payload');
            $table->string('exception');
            $table->timestamp('failed_at')->default('CURRENT_TIMESTAMP');

        });

        // Table: job_batches
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id');
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->string('failed_job_ids');
            $table->string('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('finished_at')->nullable();

        });

        // Table: jobs
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue');
            $table->string('payload');
            $table->integer('attempts');
            $table->integer('reserved_at')->nullable();
            $table->integer('available_at');

            $table->index(['queue']);
        });

        // Table: personal_access_tokens
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type');
            $table->unsignedBigInteger('tokenable_id');
            $table->string('name');
            $table->string('token');
            $table->string('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['expires_at']);
            $table->index(['tokenable_type', 'tokenable_id']);
        });

        // Table: cache
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key');
            $table->string('value');
            $table->integer('expiration');

        });

        // Table: cache_locks
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key');
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
        Schema::dropIfExists('action_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('transit_boxes');
        Schema::dropIfExists('transit_routes');
        Schema::dropIfExists('manifests');
        Schema::dropIfExists('run_sheets');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('cod_modifications');
        Schema::dropIfExists('package_status_histories');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('pickup_requests');
        Schema::dropIfExists('deliverer_wallet_emptyings');
        Schema::dropIfExists('topup_requests');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('user_wallets');
        Schema::dropIfExists('saved_addresses');
        Schema::dropIfExists('client_pickup_addresses');
        Schema::dropIfExists('client_bank_accounts');
        Schema::dropIfExists('client_profiles');
        Schema::dropIfExists('delegations');
        Schema::dropIfExists('users');
    }
};
