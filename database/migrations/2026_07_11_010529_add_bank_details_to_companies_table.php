<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('settings');
            $table->string('bsb_code')->nullable()->after('bank_name');
            $table->string('bank_account_number')->nullable()->after('bsb_code');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bsb_code', 'bank_account_number', 'bank_account_name']);
        });
    }
};