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
            // Drop incorrect columns from your previous table structure
            if (Schema::hasColumn('payrolls', 'first_name')) {
                $table->dropColumn('first_name');
            }
            
            if (Schema::hasColumn('payrolls', 'last_name')) {
                $table->dropColumn('last_name');
            }
            
            if (Schema::hasColumn('payrolls', 'gender')) {
                $table->dropColumn('gender');
            }
            
            if (Schema::hasColumn('payrolls', 'tax')) {
                $table->dropColumn('tax');
            }
            
            if (Schema::hasColumn('payrolls', 'total_nafsund_e')) {
                $table->dropColumn('total_nafsund_e');
            }
            
            if (Schema::hasColumn('payrolls', 'total_nafsund_er')) {
                $table->dropColumn('total_nafsund_er');
            }
            
            if (Schema::hasColumn('payrolls', 'total_loan_deductions')) {
                $table->dropColumn('total_loan_deductions');
            }
            
            if (Schema::hasColumn('payrolls', 'total_employees')) {
                $table->dropColumn('total_employees');
            }
            
            // Add any missing columns that your model expects
            if (!Schema::hasColumn('payrolls', 'total_nasfund_ee')) {
                $table->decimal('total_nasfund_ee', 15, 2)->default(0)->after('total_tax');
            }
            
            if (!Schema::hasColumn('payrolls', 'total_nasfund_er')) {
                $table->decimal('total_nasfund_er', 15, 2)->default(0)->after('total_nasfund_ee');
            }
            
            if (!Schema::hasColumn('payrolls', 'total_deductions')) {
                $table->decimal('total_deductions', 15, 2)->default(0)->after('total_nasfund_er');
            }
            
            if (!Schema::hasColumn('payrolls', 'total_loan_deductions')) {
                $table->decimal('total_loan_deductions', 15, 2)->default(0)->after('total_deductions');
            }
            
            // Add indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['period_start', 'period_end']);
            $table->index(['pay_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Re-add columns if rolling back
            if (!Schema::hasColumn('payrolls', 'first_name')) {
                $table->string('first_name', 10)->nullable();
            }
            
            if (!Schema::hasColumn('payrolls', 'last_name')) {
                $table->string('last_name', 10)->nullable();
            }
            
            if (!Schema::hasColumn('payrolls', 'gender')) {
                $table->date('gender')->nullable();
            }
            
            if (!Schema::hasColumn('payrolls', 'tax')) {
                $table->decimal('tax', 15, 2)->default(0);
            }
            
            if (!Schema::hasColumn('payrolls', 'total_nafsund_e')) {
                $table->decimal('total_nafsund_e', 15, 2)->default(0);
            }
            
            if (!Schema::hasColumn('payrolls', 'total_nafsund_er')) {
                $table->decimal('total_nafsund_er', 15, 2)->default(0);
            }
            
            if (!Schema::hasColumn('payrolls', 'total_loan_deductions')) {
                $table->decimal('total_loan_deductions', 15, 2)->default(0);
            }
            
            if (!Schema::hasColumn('payrolls', 'total_employees')) {
                $table->integer('total_employees')->default(0);
            }
            
            // Drop columns added
            if (Schema::hasColumn('payrolls', 'total_nasfund_ee')) {
                $table->dropColumn('total_nasfund_ee');
            }
            
            if (Schema::hasColumn('payrolls', 'total_nasfund_er')) {
                $table->dropColumn('total_nasfund_er');
            }
            
            if (Schema::hasColumn('payrolls', 'total_deductions')) {
                $table->dropColumn('total_deductions');
            }
            
            if (Schema::hasColumn('payrolls', 'total_loan_deductions')) {
                $table->dropColumn('total_loan_deductions');
            }
            
            // Drop indexes
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['period_start', 'period_end']);
            $table->dropIndex(['pay_date']);
        });
    }
};