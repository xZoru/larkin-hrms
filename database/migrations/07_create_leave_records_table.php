<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->decimal('leave_balance', 5, 1)->default(0);
            $table->decimal('leave_taken', 5, 1)->default(0);
            $table->decimal('leave_accrued', 5, 1)->default(0);
            $table->date('last_accrual_date')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_records');
    }
};