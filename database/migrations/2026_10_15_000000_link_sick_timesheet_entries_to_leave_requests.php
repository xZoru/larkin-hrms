<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->foreignId('source_attendance_log_id')
                ->nullable()
                ->unique()
                ->after('leave_type')
                ->constrained('attendance_logs')
                ->nullOnDelete();
        });

        $now = now();
        DB::table('attendance_logs')
            ->where('attendance_type', 'Sick Leave')
            ->orderBy('id')
            ->chunkById(500, function ($logs) use ($now) {
                foreach ($logs as $log) {
                    DB::table('leave_requests')->updateOrInsert(
                        ['source_attendance_log_id' => $log->id],
                        [
                            'employee_id' => $log->employee_id,
                            'leave_type' => 'Sick',
                            'start_date' => $log->date,
                            'end_date' => $log->date,
                            'days_requested' => 1,
                            'reason' => 'Recorded from timesheet entry',
                            'status' => 'Approved',
                            'approved_date' => $log->date,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            });
    }

    public function down(): void
    {
        DB::table('leave_requests')
            ->whereNotNull('source_attendance_log_id')
            ->delete();

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('source_attendance_log_id');
        });
    }
};
