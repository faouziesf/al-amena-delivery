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
        // Table: users
        if (!Schema::hasTable('users')) {
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
                $table->timestamps();

                $table->index('role', 'idx_users_role');
                $table->index('account_status', 'idx_users_account_status');
                $table->index('created_by', 'idx_users_created_by');
                $table->index('verified_by', 'idx_users_verified_by');
                $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // Table: password_reset_tokens
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Table: sessions
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        // Table: cache
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        }

        // Table: cache_locks
        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        }

        // Table: jobs
        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        // Table: job_batches
        if (!Schema::hasTable('job_batches')) {
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
        }

        // Table: failed_jobs
        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        // Table: delegations
        if (!Schema::hasTable('delegations')) {
            Schema::create('delegations', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('zone')->nullable();
                $table->boolean('active')->default(true);
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
                $table->index(['active', 'zone']);
            });
        }

        // Table: user_wallets
        if (!Schema::hasTable('user_wallets')) {
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
        }

        // Table: financial_transactions
        if (!Schema::hasTable('financial_transactions')) {
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
        }

        // Table: client_profiles
        if (!Schema::hasTable('client_profiles')) {
            Schema::create('client_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('shop_name')->nullable();
                $table->string('fiscal_number')->nullable();
                $table->string('business_sector')->nullable();
                $table->string('identity_document')->nullable();
                $table->decimal('offer_delivery_price', 8, 3);
                $table->decimal('offer_return_price', 8, 3);
                $table->text('internal_notes')->nullable();
                $table->timestamps();
                $table->unique('user_id');
            });
        } else {
            // Ajouter la colonne internal_notes si elle n'existe pas
            if (!Schema::hasColumn('client_profiles', 'internal_notes')) {
                Schema::table('client_profiles', function (Blueprint $table) {
                    $table->text('internal_notes')->nullable()->after('offer_return_price');
                });
            }
        }

        // Table: wallet_transaction_backups
        if (!Schema::hasTable('wallet_transaction_backups')) {
            Schema::create('wallet_transaction_backups', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_id');
                $table->json('snapshot_data');
                $table->timestamp('backup_at');
                $table->timestamps();
                $table->index('transaction_id');
                $table->index('backup_at');
            });
        }

        // Table: action_logs
        if (!Schema::hasTable('action_logs')) {
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
        }

        // Table: saved_addresses
        if (!Schema::hasTable('saved_addresses')) {
            Schema::create('saved_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['SUPPLIER', 'CLIENT'])->default('CLIENT');
                $table->string('name');
                $table->string('label')->nullable();
                $table->string('phone');
                $table->text('address');
                $table->foreignId('delegation_id')->constrained()->onDelete('cascade');
                $table->boolean('is_default')->default(false);
                $table->integer('usage_count')->default(0);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'type']);
                $table->index(['user_id', 'is_default']);
                $table->index(['user_id', 'type', 'is_default']);
            });
        }

        // Table: import_batches
        if (!Schema::hasTable('import_batches')) {
            Schema::create('import_batches', function (Blueprint $table) {
                $table->id();
                $table->string('batch_code')->unique();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('filename');
                $table->integer('total_rows')->default(0);
                $table->integer('processed_rows')->default(0);
                $table->integer('successful_rows')->default(0);
                $table->integer('failed_rows')->default(0);
                $table->enum('status', ['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'CANCELLED'])->default('PENDING');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('errors')->nullable();
                $table->json('summary')->nullable();
                $table->string('file_path')->nullable();
                $table->timestamps();
                
                $table->index(['user_id', 'status']);
                $table->index(['status', 'created_at']);
                $table->index('batch_code');
            });
        }

        // Table: packages
        if (!Schema::hasTable('packages')) {
            Schema::create('packages', function (Blueprint $table) {
                $table->id();
                $table->string('package_code')->unique();
                $table->foreignId('sender_id')->constrained('users');
                $table->json('sender_data');
                $table->json('supplier_data')->nullable();
                $table->foreignId('delegation_from')->constrained('delegations');
                $table->foreignId('pickup_delegation_id')->nullable()->constrained('delegations');
                $table->text('pickup_address')->nullable();
                $table->string('pickup_phone')->nullable();
                $table->text('pickup_notes')->nullable();
                $table->json('recipient_data');
                $table->foreignId('delegation_to')->constrained('delegations');
                $table->string('content_description');
                $table->text('notes')->nullable();
                $table->decimal('cod_amount', 10, 3)->default(0);
                $table->decimal('package_weight', 8, 3)->nullable();
                $table->json('package_dimensions')->nullable();
                $table->decimal('package_value', 10, 3)->nullable();
                $table->decimal('delivery_fee', 8, 3);
                $table->decimal('return_fee', 8, 3);
                $table->text('special_instructions')->nullable();
                $table->boolean('is_fragile')->default(false);
                $table->boolean('requires_signature')->default(false);
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
                $table->foreignId('import_batch_id')->nullable()->constrained('import_batches')->onDelete('set null');
                $table->timestamps();
                
                $table->index(['status', 'assigned_deliverer_id']);
                $table->index(['sender_id', 'status']);
                $table->index(['delegation_from', 'delegation_to']);
                $table->index('created_at');
                $table->index('pickup_delegation_id');
                $table->index('import_batch_id');
                $table->index(['sender_id', 'import_batch_id']);
            });
        } else {
            // Ajouter les nouvelles colonnes si elles n'existent pas
            Schema::table('packages', function (Blueprint $table) {
                if (!Schema::hasColumn('packages', 'supplier_data')) {
                    $table->json('supplier_data')->nullable()->after('sender_data');
                }
                if (!Schema::hasColumn('packages', 'pickup_delegation_id')) {
                    $table->foreignId('pickup_delegation_id')->nullable()->constrained('delegations')->after('delegation_from');
                }
                if (!Schema::hasColumn('packages', 'pickup_address')) {
                    $table->text('pickup_address')->nullable()->after('pickup_delegation_id');
                }
                if (!Schema::hasColumn('packages', 'pickup_phone')) {
                    $table->string('pickup_phone')->nullable()->after('pickup_address');
                }
                if (!Schema::hasColumn('packages', 'pickup_notes')) {
                    $table->text('pickup_notes')->nullable()->after('pickup_phone');
                }
                if (!Schema::hasColumn('packages', 'package_weight')) {
                    $table->decimal('package_weight', 8, 3)->nullable()->after('cod_amount');
                }
                if (!Schema::hasColumn('packages', 'package_dimensions')) {
                    $table->json('package_dimensions')->nullable()->after('package_weight');
                }
                if (!Schema::hasColumn('packages', 'package_value')) {
                    $table->decimal('package_value', 10, 3)->nullable()->after('package_dimensions');
                }
                if (!Schema::hasColumn('packages', 'special_instructions')) {
                    $table->text('special_instructions')->nullable()->after('notes');
                }
                if (!Schema::hasColumn('packages', 'is_fragile')) {
                    $table->boolean('is_fragile')->default(false)->after('special_instructions');
                }
                if (!Schema::hasColumn('packages', 'requires_signature')) {
                    $table->boolean('requires_signature')->default(false)->after('is_fragile');
                }
                if (!Schema::hasColumn('packages', 'import_batch_id')) {
                    $table->foreignId('import_batch_id')->nullable()->constrained('import_batches')->onDelete('set null')->after('amount_in_escrow');
                }
            });
        }

        // Table: complaints
        if (!Schema::hasTable('complaints')) {
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
        }

        // Table: package_status_histories
        if (!Schema::hasTable('package_status_histories')) {
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
        }

        // Table: notifications
        if (!Schema::hasTable('notifications')) {
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
        }

        // Table: withdrawal_requests
        if (!Schema::hasTable('withdrawal_requests')) {
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
        }

        // Table: cod_modifications
        if (!Schema::hasTable('cod_modifications')) {
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
        }

        // Table: deliverer_wallet_emptyings
        if (!Schema::hasTable('deliverer_wallet_emptyings')) {
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
        }

        // Table: topup_requests
        if (!Schema::hasTable('topup_requests')) {
            Schema::create('topup_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_code')->unique();
                $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
                $table->decimal('amount', 10, 3);
                $table->enum('method', ['BANK_TRANSFER', 'BANK_DEPOSIT', 'CASH']);
                $table->string('bank_transfer_id')->nullable()->unique();
                $table->string('proof_document')->nullable();
                $table->text('notes')->nullable();
                $table->enum('status', ['PENDING', 'VALIDATED', 'REJECTED', 'CANCELLED'])->default('PENDING');
                $table->foreignId('processed_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('processed_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('validation_notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['client_id', 'status']);
                $table->index(['status', 'method']);
                $table->index(['processed_by_id', 'status']);
                $table->index('created_at');
                $table->index('bank_transfer_id');
            });
        }

        // Créer des wallets pour tous les utilisateurs CLIENT et DELIVERER qui n'en ont pas
        $usersWithoutWallets = DB::table('users')
            ->leftJoin('user_wallets', 'users.id', '=', 'user_wallets.user_id')
            ->whereNull('user_wallets.user_id')
            ->whereIn('users.role', ['CLIENT', 'DELIVERER'])
            ->select('users.id')
            ->get();

        foreach ($usersWithoutWallets as $user) {
            DB::table('user_wallets')->insert([
                'user_id' => $user->id,
                'balance' => 0.000,
                'pending_amount' => 0.000,
                'frozen_amount' => 0.000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "✅ Base de données créée avec succès.\n";
        echo "✅ Créé " . count($usersWithoutWallets) . " wallets manquants.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer toutes les tables dans l'ordre inverse des dépendances
        Schema::dropIfExists('topup_requests');
        Schema::dropIfExists('deliverer_wallet_emptyings');
        Schema::dropIfExists('cod_modifications');
        Schema::dropIfExists('withdrawal_requests');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('package_status_histories');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('packages');
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('saved_addresses');
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