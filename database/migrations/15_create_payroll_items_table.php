<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');

            //  Hours (from attendance summary)
            $table->decimal('regular_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('sunday_hours', 8, 2)->default(0);
            $table->decimal('holiday_hours', 8, 2)->default(0);
            $table->decimal('hours_worked', 8, 2)->default(0);

            //  Rates
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('overtime_rate', 10, 2)->default(0);

            // Pay components
            $table->decimal('regular_pay', 12, 2)->default(0);   // Basic Pay
            $table->decimal('overtime_pay', 12, 2)->default(0);
            $table->decimal('sunday_pay', 12, 2)->default(0);
            $table->decimal('holiday_pay', 12, 2)->default(0);
            $table->decimal('allowance', 12, 2)->default(0);
            $table->decimal('gross_wage', 12, 2)->default(0);

            //  Deductions
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('nasfund_ee', 12, 2)->default(0);
            $table->decimal('nasfund_er', 12, 2)->default(0);
            $table->decimal('loan_deduction', 12, 2)->default(0);
            $table->decimal('other_deductions', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);

            //  Net
            $table->decimal('net_pay', 12, 2)->default(0);

            //  Payment
            $table->enum('payment_method', ['Bank Transfer', 'Cash'])->default('Bank Transfer');
            $table->string('bank_account')->nullable();

            //  Extra
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }
};