<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        
        //  Hours
        'regular_hours',
        'overtime_hours',
        'sunday_hours',
        'holiday_hours',
        'hours_worked',
        
        //  Rates
        'hourly_rate',
        'overtime_rate',
        
        // Pay components
        'basic_pay',
        'regular_pay',     //  Basic Pay + Tax (gross-up for expats)
        'overtime_pay',
        'sunday_pay',
        'holiday_pay',
        'allowance',
        'gross_wage',
        
        //  Deductions
        'tax',
        'nasfund_ee',
        'nasfund_er',
        'loan_deduction',
        'other_deductions',
        'total_deductions',
        
        //  Net
        'net_pay',
        
        //  Payment
        'payment_method',
        'bank_account',
        
        //  Details
        'details',
    ];

    protected $casts = [
        'details' => 'array',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'sunday_hours' => 'decimal:2',
        'holiday_hours' => 'decimal:2',
        'hours_worked' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'basic_pay' => 'decimal:2', 
        'regular_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'sunday_pay' => 'decimal:2',
        'holiday_pay' => 'decimal:2',
        'allowance' => 'decimal:2',
        'gross_wage' => 'decimal:2',
        'tax' => 'decimal:2',
        'nasfund_ee' => 'decimal:2',
        'nasfund_er' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}