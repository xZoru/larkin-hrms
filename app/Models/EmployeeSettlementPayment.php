<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSettlementPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'employee_id', 'type', 'commenced_at', 'ended_at',
        'service_days', 'service_months', 'hourly_rate', 'hours_per_day',
        'hours_per_week', 'leave_weeks', 'service_fraction', 'amount',
        'issuer_name', 'issuer_position', 'created_by',
    ];

    protected $casts = [
        'commenced_at' => 'date',
        'ended_at' => 'date',
        'service_days' => 'integer',
        'service_months' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'hours_per_day' => 'decimal:2',
        'hours_per_week' => 'decimal:2',
        'leave_weeks' => 'decimal:2',
        'service_fraction' => 'decimal:4',
        'amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
