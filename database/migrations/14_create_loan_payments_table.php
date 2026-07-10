<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->enum('payment_type', ['auto', 'manual'])->default('auto');
            $table->foreignId('processed_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'payroll_id']);
            $table->index('payment_type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_payments');
    }
};