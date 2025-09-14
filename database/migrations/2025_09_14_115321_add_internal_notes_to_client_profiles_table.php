<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->text('internal_notes')->nullable()->after('offer_return_price');
        });
    }

    public function down()
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn('internal_notes');
        });
    }
};