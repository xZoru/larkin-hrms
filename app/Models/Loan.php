<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Loan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'loan_type',
        'amount',
        'deduction_per_cutoff',
        'remaining_balance',
        'total_paid',
        'installment_count',
        'payments_made',
        'reason',
        'status',
        'approved_by',
        'approved_date',
        'released_by',
        'released_date',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'deduction_per_cutoff' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'approved_date' => 'datetime',
        'released_date' => 'datetime',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function releaser()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class);
    }

    // Accessors
    public function getRemainingBalanceAttribute()
    {
        if ($this->payments->isEmpty()) {
            return $this->amount;
        }
        return $this->amount - $this->payments->sum('amount');
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments->sum('amount');
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->amount == 0) return 0;
        return round(($this->total_paid / $this->amount) * 100, 2);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopeReleased($query)
    {
        return $query->where('status', 'Released');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['Approved', 'Released']);
    }

    // Methods
    public function isFullyPaid()
    {
        return $this->remaining_balance <= 0;
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['Pending', 'On-Hold']);
    }

    public function canBeApproved()
    {
        return $this->status === 'Pending';
    }

    public function canBeReleased()
    {
        return $this->status === 'Approved';
    }

    public function canBeRejected()
    {
        return $this->status === 'Pending';
    }

    public function canBePutOnHold()
    {
        return in_array($this->status, ['Pending', 'Approved']);
    }

    // UPDATED: Add payment with optional processed_by parameter
    public function addPayment($amount, $payrollId = null, $notes = null, $processedBy = null)
    {
        $balanceBefore = $this->remaining_balance;
        $balanceAfter = max(0, $balanceBefore - $amount);

        // If processed_by not provided, try auth()->id(), fallback to 1 (admin)
        if ($processedBy === null) {
            $processedBy = auth()->check() ? auth()->id() : 1;
        }

        $payment = LoanPayment::create([
            'loan_id' => $this->id,
            'payroll_id' => $payrollId,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'payment_type' => $payrollId ? 'auto' : 'manual',
            'processed_by' => $processedBy,
            'notes' => $notes
        ]);

        // Update payments_made count
        $this->payments_made = $this->payments()->count();
        $this->save();

        // Update loan status if fully paid
        if ($this->remaining_balance <= 0) {
            $this->update(['status' => 'Completed']);
        }

        return $payment;
    }
}