<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_settlement_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->date('commenced_at');
            $table->date('ended_at');
            $table->unsignedInteger('service_days');
            $table->decimal('service_months', 8, 2)->default(0);
            $table->decimal('hourly_rate', 12, 2);
            $table->decimal('hours_per_day', 8, 2)->nullable();
            $table->decimal('hours_per_week', 8, 2)->nullable();
            $table->decimal('leave_weeks', 8, 2)->nullable();
            $table->decimal('service_fraction', 8, 4)->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('issuer_name');
            $table->string('issuer_position');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['company_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_settlement_payments');
    }
};
