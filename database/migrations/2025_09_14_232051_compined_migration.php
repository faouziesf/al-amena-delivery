<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Creating saved_addresses table
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

        // Creating import_batches table
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

        // Modifying packages table
        Schema::table('packages', function (Blueprint $table) {
            $table->json('supplier_data')->nullable()->after('sender_data');
            $table->foreignId('pickup_delegation_id')->nullable()->constrained('delegations')->after('delegation_from');
            $table->text('pickup_address')->nullable()->after('pickup_delegation_id');
            $table->string('pickup_phone')->nullable()->after('pickup_address');
            $table->text('pickup_notes')->nullable()->after('pickup_phone');
            $table->decimal('package_weight', 8, 3)->nullable()->after('cod_amount');
            $table->json('package_dimensions')->nullable()->after('package_weight');
            $table->decimal('package_value', 10, 3)->nullable()->after('package_dimensions');
            $table->text('special_instructions')->nullable()->after('notes');
            $table->boolean('is_fragile')->default(false)->after('special_instructions');
            $table->boolean('requires_signature')->default(false)->after('is_fragile');
            $table->foreignId('import_batch_id')->nullable()->constrained('import_batches')->onDelete('set null')->after('amount_in_escrow');
            
            $table->index('pickup_delegation_id');
            $table->index('import_batch_id');
            $table->index(['sender_id', 'import_batch_id']);
        });
    }

    public function down(): void
    {
        // Dropping packages table modifications
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['pickup_delegation_id']);
            $table->dropForeign(['import_batch_id']);
            
            $table->dropColumn([
                'supplier_data',
                'pickup_delegation_id',
                'pickup_address',
                'pickup_phone',
                'pickup_notes',
                'package_weight',
                'package_dimensions',
                'package_value',
                'special_instructions',
                'is_fragile',
                'requires_signature',
                'import_batch_id'
            ]);
        });

        // Dropping import_batches table
        Schema::dropIfExists('import_batches');

        // Dropping saved_addresses table
        Schema::dropIfExists('saved_addresses');
    }
};