<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number', 50)->unique();
            $table->string('full_name', 255);
            $table->string('position', 255);
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable();
            $table->enum('employee_type', ['National', 'Expatriate'])->default('National');
            $table->date('date_of_birth');
            $table->string('photo_path')->nullable();
            $table->date('joining_date');
            $table->date('end_date')->nullable();
            $table->date('deployment_date')->nullable();
            $table->string('passport_number')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('work_permit_number')->nullable();
            $table->date('work_permit_expiry')->nullable();
            $table->string('visa_number')->nullable();
            $table->date('visa_expiry')->nullable();
            $table->string('nasfund_number', 50)->nullable();
            $table->integer('nasfund_dependents')->default(0);
            $table->decimal('nasfund_allocation_percentage', 5, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->enum('payment_method', ['Bank Transfer', 'Cash'])->default('Bank Transfer');
            $table->enum('status', ['Active', 'Inactive', 'Terminated', 'Resigned'])->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
};