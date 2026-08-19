<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'fortnight_number',
        'period_start',
        'period_end',
        'pay_date',
        'status',
        'total_gross',
        'total_tax',
        'total_net',
        'total_nasfund_ee',
        'total_nasfund_er',
        'total_loan_deductions',
        'total_deductions',
        'total_employees',
        'summary',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
        'approved_at' => 'datetime',
        'summary' => 'array'
    ];

    // SOW: Payroll Management
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /** Human-readable code, with POM reserved for Main Office payruns. */
    public function getDisplayCodeAttribute(): string
    {
        $companyCode = Str::before($this->company?->code ?? 'LKP', '-') ?: 'LKP';
        $branchName = $this->branch?->name;
        $locationCode = $branchName
            ? Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', $branchName), 0, 3))
            : 'POM';

        return sprintf('P-%s-%s-%05d', $companyCode, $locationCode ?: 'BRN', $this->id);
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function loanPayments()
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function nasfundContributions()
    {
        return $this->hasMany(NasfundContribution::class);
    }

    public function calculateTotals()
    {
        // Manual ABA entries are payment instructions only. Keep them attached
        // to the payrun for ABA generation, but exclude them from payroll totals.
        $items = $this->items()->get()->reject(function (PayrollItem $item) {
            $details = $item->details ?? [];

            return is_array($details) && ($details['type'] ?? null) === 'manual_entry';
        });

        $this->total_gross = $items->sum('gross_wage');
        $this->total_tax = $items->sum('tax');
        $this->total_net = $items->sum('net_pay');
        $this->total_nasfund_ee = $items->sum('nasfund_ee');
        $this->total_nasfund_er = $items->sum('nasfund_er');
        $this->total_deductions = $items->sum('loan_deduction') + $items->sum('other_deductions');
        $this->save();
    }

}
