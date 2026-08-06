<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('leave_pay', 12, 2)->default(0)->after('holiday_pay');
            $table->decimal('ncsl', 12, 2)->default(0)->after('nasfund_er');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['leave_pay', 'ncsl']);
        });
    }
};
