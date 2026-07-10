<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->enum('timesheet_status', ['Draft', 'Final', 'Locked'])->default('Draft')->after('notes');
            $table->timestamp('finalized_at')->nullable()->after('timesheet_status');
            $table->timestamp('locked_at')->nullable()->after('finalized_at');
            $table->foreignId('finalized_by')->nullable()->constrained('users')->after('locked_at');
            $table->foreignId('locked_by')->nullable()->constrained('users')->after('finalized_by');
        });
    }

    public function down()
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropForeign(['finalized_by']);
            $table->dropForeign(['locked_by']);
            $table->dropColumn(['timesheet_status', 'finalized_at', 'locked_at', 'finalized_by', 'locked_by']);
        });
    }
};