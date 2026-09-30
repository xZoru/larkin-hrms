<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('letter_issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('memo_template_id')->nullable()->constrained('memo_templates')->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('letter_number')->unique();
            $table->string('letter_type');
            $table->string('template_name');
            $table->date('date_issued');
            $table->string('addressed_to')->default('To Whom It May Concern');
            $table->longText('body');
            $table->string('signatory_name');
            $table->string('signatory_title')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('letter_issuances'); }
};
