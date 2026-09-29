<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discipline_records', function (Blueprint $table) {
            $table->string('purpose_template_name')->nullable();
            $table->string('reason_1_template_name')->nullable();
            $table->string('reason_2_template_name')->nullable();
            $table->string('reason_3_template_name')->nullable();
            $table->string('decision_template_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('discipline_records', function (Blueprint $table) {
            $table->dropColumn([
                'purpose_template_name',
                'reason_1_template_name',
                'reason_2_template_name',
                'reason_3_template_name',
                'decision_template_name',
            ]);
        });
    }
};
