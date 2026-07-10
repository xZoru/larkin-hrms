<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('loan_type', ['Cash Advance', 'Loan', 'Company Deductions'])->default('Loan');
            $table->decimal('amount', 15, 2);
            $table->decimal('deduction_per_cutoff', 15, 2)->nullable();
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->integer('installment_count')->default(0);
            $table->integer('payments_made')->default(0);
            $table->text('reason')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Released', 'On-Hold', 'Rejected', 'Completed'])->default('Pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_date')->nullable();
            $table->foreignId('released_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('released_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans');
    }
};