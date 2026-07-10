<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('discipline_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('memo_template_id')->nullable()->index();
            $table->string('memo_number', 50)->unique();
            $table->text('offense_description');
            $table->text('action_taken');
            $table->date('date_issued');
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->date('follow_up_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('discipline_records');
    }
};