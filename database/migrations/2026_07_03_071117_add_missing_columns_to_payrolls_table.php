<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Add total_loan_deductions if it doesn't exist
            if (!Schema::hasColumn('payrolls', 'total_loan_deductions')) {
                $table->decimal('total_loan_deductions', 15, 2)->default(0)->after('total_nasfund_er');
            }
            
            // Add total_employees if it doesn't exist
            if (!Schema::hasColumn('payrolls', 'total_employees')) {
                $table->integer('total_employees')->default(0)->after('total_deductions');
            }
            
            // Add total_deductions if it doesn't exist
            if (!Schema::hasColumn('payrolls', 'total_deductions')) {
                $table->decimal('total_deductions', 15, 2)->default(0)->after('total_loan_deductions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'total_loan_deductions')) {
                $table->dropColumn('total_loan_deductions');
            }
            
            if (Schema::hasColumn('payrolls', 'total_employees')) {
                $table->dropColumn('total_employees');
            }
            
            if (Schema::hasColumn('payrolls', 'total_deductions')) {
                $table->dropColumn('total_deductions');
            }
        });
    }
};