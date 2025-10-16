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
        // Core Authentication & User Management
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->enum('role', ['CLIENT', 'DELIVERER', 'COMMERCIAL', 'SUPERVISOR', 'DEPOT_MANAGER'])->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('account_status', ['PENDING', 'ACTIVE', 'SUSPENDED'])->default('PENDING');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('last_login')->nullable();
            
            // Champs spécifiques aux livreurs
            $table->string('assigned_delegation')->nullable();
            $table->enum('deliverer_type', ['DELEGATION', 'JOKER', 'TRANSIT'])->nullable();
            $table->decimal('delegation_latitude', 10, 8)->nullable();
            $table->decimal('delegation_longitude', 11, 8)->nullable();
            $table->integer('delegation_radius_km')->nullable();
            
            // Champs spécifiques aux chefs dépôt
            $table->json('assigned_gouvernorats')->nullable();
            $table->string('depot_name')->nullable();
            $table->text('depot_address')->nullable();
            $table->boolean('is_depot_manager')->default(false);
            
            $table->timestamps();

            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index('role');
            $table->index('account_status');
            $table->index('assigned_delegation');
            $table->index('deliverer_type');
        });

        Schema::create('delegations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->index(['active', 'zone']);
        });

        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('shop_name')->nullable();
            $table->string('fiscal_number')->nullable();
            $table->string('business_sector')->nullable();
            $table->string('identity_document')->nullable();
            $table->decimal('offer_delivery_price', 16, 3);
            $table->decimal('offer_return_price', 16, 3);
            $table->text('internal_notes')->nullable();
            $table->string('validation_status')->default('PENDING');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_notes')->nullable();
            $table->timestamps();
        });
        
        // Laravel System Tables
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->text('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Client & Address Management
        Schema::create('client_pickup_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('address');
            $table->string('phone');
            $table->string('tel2')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('delegation')->nullable();
            $table->string('gouvernorat')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('saved_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'SUPPLIER' or 'CLIENT'
            $table->string('name');
            $table->string('label')->nullable();
            $table->string('phone');
            $table->text('address');
            $table->foreignId('delegation_id')->nullable()->constrained();
            $table->boolean('is_default')->default(false);
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

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

        // Import & Batch Processing
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->foreignId('user_id')->constrained();
            $table->string('filename');
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('successful_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->string('status'); // PENDING, PROCESSING, COMPLETED, FAILED
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('errors')->nullable();
            $table->json('summary')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });

        // Pickup Management
        Schema::create('pickup_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->text('pickup_address');
            $table->string('pickup_phone');
            $table->string('pickup_contact_name')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->unsignedBigInteger('delegation_from')->nullable();
            $table->timestamp('requested_pickup_date')->nullable();
            $table->string('status')->default('pending'); // pending, assigned, picked_up, cancelled
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamps();
        });

        Schema::create('manifests', function (Blueprint $table) {
            $table->id();
            $table->string('manifest_number')->unique();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->json('package_ids')->nullable();
            $table->foreignId('pickup_address_id')->nullable()->constrained('client_pickup_addresses')->onDelete('set null');
            $table->string('pickup_address_name')->nullable();
            $table->string('pickup_phone')->nullable();
            $table->integer('total_packages')->default(0);
            $table->decimal('total_cod_amount', 16, 3)->default(0);
            $table->decimal('total_weight', 16, 3)->nullable();
            $table->foreignId('pickup_request_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('CREATED'); // CREATED, REQUESTED, COLLECTED, CANCELLED
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });

        // Package Management
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code')->unique();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->json('sender_data')->nullable();
            $table->foreignId('delegation_from')->nullable()->constrained('delegations')->onDelete('set null');
            $table->json('recipient_data')->nullable();
            $table->foreignId('delegation_to')->nullable()->constrained('delegations')->onDelete('set null');
            $table->text('content_description')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('cod_amount', 16, 3)->default(0);
            $table->decimal('delivery_fee', 16, 3)->default(0);
            $table->decimal('return_fee', 16, 3)->default(0);
            $table->string('status')->default('CREATED'); // CREATED, AVAILABLE, ACCEPTED, PICKED_UP, DELIVERED, PAID, REFUSED, RETURNED, UNAVAILABLE, VERIFIED, CANCELLED
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->integer('delivery_attempts')->default(0);
            $table->boolean('cod_modifiable_by_commercial')->default(false);
            $table->decimal('amount_in_escrow', 16, 3)->default(0);
            
            // Pickup data
            $table->json('supplier_data')->nullable();
            $table->foreignId('pickup_delegation_id')->nullable()->constrained('delegations')->onDelete('set null');
            $table->text('pickup_address')->nullable();
            $table->string('pickup_phone')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->foreignId('pickup_address_id')->nullable()->constrained('client_pickup_addresses')->onDelete('set null');
            
            // Package details
            $table->decimal('package_weight', 16, 3)->nullable();
            $table->json('package_dimensions')->nullable();
            $table->decimal('package_value', 16, 3)->nullable();
            $table->text('special_instructions')->nullable();
            $table->boolean('is_fragile')->default(false);
            $table->boolean('requires_signature')->default(false);
            $table->boolean('allow_opening')->default(false);
            $table->string('payment_method')->nullable();
            
            // Import & assignment
            $table->foreignId('import_batch_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('reassigned_at')->nullable();
            $table->foreignId('reassigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('reassignment_reason')->nullable();
            
            // Cancellation
            $table->boolean('cancelled_by_client')->default(false);
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('auto_return_reason')->nullable();
            
            // Delivery & payment
            $table->timestamp('delivered_at')->nullable();
            $table->boolean('est_echange')->default(false);
            $table->foreignId('payment_withdrawal_id')->nullable()->constrained('withdrawal_requests')->onDelete('set null');
            $table->decimal('advance_used_for_fees', 16, 3)->default(0);
            $table->decimal('balance_used_for_fees', 16, 3)->default(0);
            $table->string('fee_payment_source')->nullable(); // 'advance', 'balance', 'mixed'
            
            $table->timestamps();
            
            $table->index('status');
            $table->index('sender_id');
            $table->index('assigned_deliverer_id');
            $table->index('delegation_to');
        });

        Schema::create('package_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->string('previous_status');
            $table->string('new_status');
            $table->foreignId('changed_by')->constrained('users');
            $table->string('changed_by_role');
            $table->text('notes')->nullable();
            $table->json('additional_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // Financial Management
        Schema::create('user_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('balance', 16, 3)->default(0);
            $table->decimal('pending_amount', 16, 3)->default(0);
            $table->decimal('frozen_amount', 16, 3)->default(0);
            $table->decimal('advance_balance', 16, 3)->default(0);
            $table->timestamp('advance_last_modified_at')->nullable();
            $table->foreignId('advance_last_modified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_transaction_at')->nullable();
            $table->string('last_transaction_id')->nullable();
            $table->timestamps();
        });

        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // CREDIT, DEBIT, PACKAGE_PAYMENT, DELIVERY_FEE, WITHDRAWAL, ADVANCE_CREDIT, ADVANCE_DEBIT, ADVANCE_USAGE
            $table->decimal('amount', 16, 3);
            $table->string('status'); // PENDING, COMPLETED, FAILED, CANCELLED
            $table->foreignId('package_id')->nullable()->constrained()->onDelete('set null');
            $table->text('description')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->decimal('wallet_balance_before', 16, 3)->nullable();
            $table->decimal('wallet_balance_after', 16, 3)->nullable();
            $table->string('checksum')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
        });

        Schema::create('wallet_transaction_backups', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->json('snapshot_data');
            $table->timestamp('backup_at');
            $table->timestamps();
        });

        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 16, 3);
            $table->string('method'); // BANK_TRANSFER, CASH_DELIVERY
            $table->json('bank_details')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('client_bank_accounts')->onDelete('set null');
            $table->text('reason')->nullable();
            $table->string('status'); // PENDING, APPROVED, PROCESSED, READY_FOR_DELIVERY, IN_PROGRESS, DELIVERED, COMPLETED, REJECTED, CANCELLED
            $table->foreignId('processed_by_commercial_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_deliverer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_depot_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('delivery_receipt_code')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('delivery_proof')->nullable();
            $table->text('processing_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('assigned_package_id')->nullable()->constrained('packages')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('topup_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 16, 3);
            $table->string('method'); // BANK_TRANSFER, BANK_DEPOSIT, CASH
            $table->string('bank_transfer_id')->nullable();
            $table->string('proof_document')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, VALIDATED, REJECTED, CANCELLED
            $table->foreignId('processed_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('validation_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('deliverer_wallet_emptyings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('commercial_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->decimal('wallet_amount', 16, 3)->nullable();
            $table->decimal('physical_amount', 16, 3)->nullable();
            $table->decimal('amount', 16, 3)->nullable(); // For depot manager emptyings
            $table->decimal('discrepancy_amount', 16, 3)->default(0);
            $table->timestamp('emptying_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('receipt_generated')->default(false);
            $table->string('receipt_path')->nullable();
            $table->json('emptying_details')->nullable();
            $table->boolean('deliverer_acknowledged')->default(false);
            $table->timestamp('deliverer_acknowledged_at')->nullable();
            $table->timestamps();
        });

        // Complaints & Support
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_code')->unique();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // CHANGE_COD, DELIVERY_DELAY, REQUEST_RETURN, RETURN_DELAY, RESCHEDULE_TODAY, FOURTH_ATTEMPT, CUSTOM
            $table->text('description');
            $table->json('additional_data')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, IN_PROGRESS, RESOLVED, REJECTED
            $table->string('priority')->default('NORMAL'); // LOW, NORMAL, HIGH, URGENT
            $table->foreignId('assigned_commercial_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->json('resolution_data')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('ticket_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('type'); // COMPLAINT, QUESTION, SUPPORT, OTHER
            $table->boolean('is_complaint')->default(false);
            $table->string('subject');
            $table->text('description')->nullable();
            $table->text('complaint_description')->nullable();
            $table->json('complaint_data')->nullable();
            $table->string('complaint_type')->nullable();
            $table->string('status')->default('OPEN'); // OPEN, IN_PROGRESS, RESOLVED, CLOSED, URGENT
            $table->string('priority')->default('NORMAL'); // LOW, NORMAL, HIGH, URGENT
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreignId('complaint_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('package_id')->nullable()->constrained()->onDelete('set null');
            $table->string('category')->nullable();
            $table->string('source')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->string('sender_type'); // CLIENT, COMMERCIAL, SUPERVISOR
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('path');
            $table->string('url');
            $table->unsignedBigInteger('size');
            $table->string('mime_type');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('uploaded_at');
            $table->timestamps();
        });

        // Notifications & Logs
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->string('priority')->default('NORMAL'); // LOW, NORMAL, HIGH, URGENT
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('action_url')->nullable();
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('read');
        });

        Schema::create('action_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_role')->nullable();
            $table->string('action_type');
            $table->string('target_type')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('additional_data')->nullable();
            $table->timestamps();
        });

        Schema::create('cod_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            $table->decimal('old_amount', 16, 3);
            $table->decimal('new_amount', 16, 3);
            $table->foreignId('modified_by_commercial_id')->constrained('users')->onDelete('cascade');
            $table->string('reason');
            $table->foreignId('client_complaint_id')->nullable()->constrained('complaints')->onDelete('set null');
            $table->text('modification_notes')->nullable();
            $table->json('context_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('emergency_modification')->default(false);
            $table->timestamps();
        });

        // Run Sheets & Routes
        Schema::create('run_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('sheet_code')->unique();
            $table->foreignId('deliverer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('delegation_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->string('status')->default('PENDING'); // PENDING, IN_PROGRESS, COMPLETED, CANCELLED
            $table->json('package_types')->nullable();
            $table->string('sort_criteria')->nullable();
            $table->boolean('include_cod_summary')->default(false);
            $table->json('packages_data')->nullable();
            $table->integer('packages_count')->default(0);
            $table->decimal('total_cod_amount', 16, 3)->default(0);
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('preparation_notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->json('completion_stats')->nullable();
            $table->json('route_optimization')->nullable();
            $table->decimal('estimated_distance', 10, 2)->nullable();
            $table->integer('estimated_duration')->nullable();
            $table->string('pdf_path')->nullable();
            $table->integer('print_count')->default(0);
            $table->json('export_formats')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('transit_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->string('origin_depot');
            $table->string('destination_depot');
            $table->date('date');
            $table->string('status')->default('ASSIGNED'); // ASSIGNED, IN_PROGRESS, COMPLETED
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('transit_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transit_routes')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('destination_governorate');
            $table->integer('packages_count')->default(0);
            $table->string('status')->default('PENDING'); // PENDING, LOADED, DELIVERED
            $table->timestamp('loaded_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->json('package_ids')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transit_boxes');
        Schema::dropIfExists('transit_routes');
        Schema::dropIfExists('run_sheets');
        Schema::dropIfExists('cod_modifications');
        Schema::dropIfExists('action_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('ticket_attachments');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('deliverer_wallet_emptyings');
        Schema::dropIfExists('topup_requests');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('wallet_transaction_backups');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('user_wallets');
        Schema::dropIfExists('package_status_histories');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('manifests');
        Schema::dropIfExists('pickup_requests');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('client_bank_accounts');
        Schema::dropIfExists('saved_addresses');
        Schema::dropIfExists('client_pickup_addresses');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('client_profiles');
        Schema::dropIfExists('delegations');
        Schema::dropIfExists('users');
    }
};