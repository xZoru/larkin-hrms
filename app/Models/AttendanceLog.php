<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'hours_worked',
        'attendance_type',
        'notes',
        'timesheet_status',
        'finalized_at',
        'locked_at',
        'finalized_by',
        'locked_by',
        'has_break',
        'is_sunday',
        'is_holiday',
        'fortnight_number',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_sunday' => 'boolean',
        'is_holiday' => 'boolean',
        'has_break' => 'boolean',
        'finalized_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function finalizedBy()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
}