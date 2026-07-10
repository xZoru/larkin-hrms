<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'company_id',
        'amount',
        'reason',
        'installment_count',
        'type',
        'status',
        'rejection_reason',
        'interest_rate',
        'request_date',
        'approved_date',
        'approved_by'
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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

        $loan = Loan::create([
            'employee_id' => $this->employee_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'balance' => $this->amount,
            'interest_rate' => $this->interest_rate,
            'start_date' => now(),
            'end_date' => now()->addMonths($this->installment_count),
            'installment_count' => $this->installment_count,
            'installment_amount' => $this->installment_count > 0 
                ? round($this->amount / $this->installment_count, 2) 
                : $this->amount,
            'description' => $this->reason,
            'status' => 'Active',
            'approved_by' => $userId,
        ]);

        return $loan;
    }

    public function reject($reason, $userId)
    {
        $this->status = 'Rejected';
        $this->rejection_reason = $reason;
        $this->approved_by = $userId;
        $this->approved_date = now();
        $this->save();
    }
}