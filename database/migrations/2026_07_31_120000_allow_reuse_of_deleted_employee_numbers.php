<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_employee_number_unique');
            $table->string('active_employee_number', 50)
                ->virtualAs('IF(deleted_at IS NULL, employee_number, NULL)')
                ->after('employee_number');
            $table->unique('active_employee_number');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique('employees_active_employee_number_unique');
            $table->dropColumn('active_employee_number');
            $table->unique('employee_number');
        });
    }
};
