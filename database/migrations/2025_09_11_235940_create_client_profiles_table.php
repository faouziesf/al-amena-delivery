<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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
    }

    public function down()
    {
        Schema::dropIfExists('client_profiles');
    }
};