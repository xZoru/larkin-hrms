<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aba_batches', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            
            // Batch identification
            $table->string('batch_number', 50)->unique();
            $table->string('filename', 255);
            $table->string('file_path', 500)->nullable();
            
            // Bank details from company
            $table->string('bank_name', 100);
            $table->string('bsb_number', 7);
            $table->string('account_number', 20);
            $table->string('account_name', 100);
            
            // Processing details
            $table->date('processing_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            
            // Status tracking
            $table->enum('status', [
                'generated', 
                'downloaded', 
                'submitted', 
                'cancelled'
            ])->default('generated');
            
            // Additional data
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            
            // Audit fields
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['payroll_id']);
            $table->index(['batch_number']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aba_batches');
    }
};