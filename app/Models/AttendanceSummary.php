<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'fortnight_number',
        'period_start',
        'period_end',
        'regular_hours',
        'overtime_hours',
        'sunday_hours',
        'holiday_hours',
        'total_hours',
        'total_days',
        'present_days',
        'absent_days'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}