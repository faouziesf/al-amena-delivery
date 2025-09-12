<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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

    public function down()
    {
        Schema::dropIfExists('wallet_transaction_backups');
    }
};