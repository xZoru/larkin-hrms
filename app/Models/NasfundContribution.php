<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NasfundContribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payroll_id',
        'ee_contribution',
        'er_contribution',
        'base_salary',
        'contribution_date',
        'fortnight_number'
    ];

    protected $casts = [
        'contribution_date' => 'date'
    ];

    // SOW: NASFUND Management (EE 6%, ER 8.4%)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function calculateEE()
    {
        return $this->base_salary * 0.06; // 6%
    }

    public function calculateER()
    {
        return $this->base_salary * 0.084; // 8.4%
    }
}