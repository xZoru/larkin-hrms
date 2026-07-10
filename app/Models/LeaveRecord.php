<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'year',
        'leave_balance',
        'leave_taken',
        'leave_accrued',
        'last_accrual_date'
    ];

    protected $casts = [
        'last_accrual_date' => 'date'
    ];

    // SOW: Annual Leave Accrual (1 day per 1.5 months, max 9 days)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function accrueLeave()
    {
        $months = 1.5;
        $daysPerMonth = 1;
        $maxDays = 9;

        $accrued = $this->leave_accrued + ($daysPerMonth / $months);
        $this->leave_accrued = min($accrued, $maxDays);
        $this->leave_balance = $this->leave_accrued - $this->leave_taken;
        $this->last_accrual_date = now();
        $this->save();
    }
}