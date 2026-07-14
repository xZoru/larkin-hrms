<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('aba_batches', function (Blueprint $table) {
            $table->string('bank_code', 3)->nullable()->after('bank_name');
            $table->string('apca_user_id', 6)->nullable()->after('bank_code');
        });
    }

    public function down()
    {
        Schema::table('aba_batches', function (Blueprint $table) {
            $table->dropColumn(['bank_code', 'apca_user_id']);
        });
    }
};