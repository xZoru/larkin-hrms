<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('allowance', 10, 2)->default(0)->after('base_salary');
            $table->string('bank_account_number')->nullable()->after('payment_method');
            $table->string('bank_name')->nullable()->after('bank_account_number');
            $table->decimal('monthly_salary', 12, 2)->nullable()->after('allowance');
            $table->integer('fortnight_hours')->default(84)->after('monthly_salary');

        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['allowance', 'bank_account_number', 'bank_name']);
        });
    }
};