<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discipline_records', function (Blueprint $table) {
            $table->text('purpose')->nullable();
            $table->text('reason_1')->nullable();
            $table->text('reason_2')->nullable();
            $table->text('reason_3')->nullable();
            $table->text('decision')->nullable();
            $table->date('effectivity_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('discipline_records', function (Blueprint $table) {
            $table->dropColumn(['purpose', 'reason_1', 'reason_2', 'reason_3', 'decision', 'effectivity_date']);
        });
    }
};
