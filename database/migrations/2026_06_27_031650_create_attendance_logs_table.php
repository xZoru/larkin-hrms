<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->boolean('has_break')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_sunday')->default(false);
            $table->string('attendance_type')->default('Work');      
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('fortnight_number', 10);
            $table->timestamps();
            
            $table->unique(['employee_id', 'date']);
            $table->index('fortnight_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_logs');
    }
};