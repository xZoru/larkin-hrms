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
        $this->refresh();

        $amount = (float) $amount;
        $balanceBefore = (float) $this->remaining_balance;
        $amount = min($amount, $balanceBefore);
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

        $this->update([
            'remaining_balance' => $balanceAfter,
            'total_paid' => (float) $this->total_paid + $amount,
            'payments_made' => $this->payments()->count(),
            'status' => $balanceAfter <= 0 ? 'Completed' : $this->status,
        ]);

        return $payment;
    }

    /**
     * Restore the stored balance fields from the payment ledger.
     *
     * This is used when a draft payroll is deleted and its automatic loan
     * payments are removed. Keeping the ledger as the source of truth also
     * preserves any valid manual or earlier payroll payments.
     */
    public function recalculatePaymentBalance(): void
    {
        $totalPaid = min(
            (float) $this->amount,
            (float) $this->payments()->sum('amount')
        );
        $remainingBalance = max(0, (float) $this->amount - $totalPaid);

        $this->update([
            'total_paid' => $totalPaid,
            'remaining_balance' => $remainingBalance,
            'payments_made' => $this->payments()->count(),
            // Only a payment can move a released loan to Completed. If the
            // deleted draft payment reopens it, it must be deductible again.
            'status' => $remainingBalance > 0 && $this->status === 'Completed'
                ? 'Released'
                : $this->status,
        ]);
    }
}
