<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            // Multi-bank support fields
            $table->string('bank_code', 3)->nullable()->default('BSP')->after('bank_account_name');
            $table->string('apca_user_id', 6)->nullable()->default('000001')->after('bank_code');
            $table->string('aba_file_format', 20)->nullable()->default('STANDARD')->after('apca_user_id');
        });
    }

    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['bank_code', 'apca_user_id', 'aba_file_format']);
        });
    }
};