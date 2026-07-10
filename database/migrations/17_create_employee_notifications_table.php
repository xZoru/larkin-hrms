<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 255);
            $table->text('message');
            $table->string('type', 100);
            $table->date('expiry_date')->nullable();
            $table->integer('days_before')->default(90);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('link')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_notifications');
    }
};