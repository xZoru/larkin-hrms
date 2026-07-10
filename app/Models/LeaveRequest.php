<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'approved_by',
        'start_date',
        'end_date',
        'days_requested',
        'reason',
        'status',
        'approved_date',
        'rejection_reason'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_date' => 'date'
    ];

    // SOW: Leave Management
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approve($userId)
    {
        $this->status = 'Approved';
        $this->approved_by = $userId;
        $this->approved_date = now();
        $this->save();

        $record = LeaveRecord::firstOrCreate([
            'employee_id' => $this->employee_id,
            'year' => now()->year
        ]);
        $record->leave_taken += $this->days_requested;
        $record->leave_balance = $record->leave_accrued - $record->leave_taken;
        $record->save();
    }

    public function reject($reason, $userId)
    {
        $this->status = 'Rejected';
        $this->approved_by = $userId;
        $this->rejection_reason = $reason;
        $this->approved_date = now();
        $this->save();
    }
}