<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Position;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // SOW: National & Expatriate Fields
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'extension_name',
        'full_name',
        'position_id',
        'position',
        'workshift',
        'gender',
        'marital_status',
        'employee_type',
        'department_id',
        'photo_path',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'joining_date',
        'end_date',
        'deployment_date',
        
        // SOW: Expatriate Documents
        'passport_number',
        'passport_expiry',
        'work_permit_number',
        'work_permit_expiry',
        'visa_number',
        'visa_expiry',
        
        // SOW: NASFUND
        'nasfund_number',
        'nasfund_dependents',
        'nasfund_allocation_percentage',
        
        // SOW: Payroll
        'hourly_rate',
        'monthly_salary',
        'fortnight_hours',
        'base_salary',
        'allowance',
        'payment_method',
        
        // SOW: Status
        'status',
        
        // SOW: Multi-Company
        'company_id'
    ];


    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'end_date' => 'date',
        'deployment_date' => 'date',
        'passport_expiry' => 'date',
        'work_permit_expiry' => 'date',
        'visa_expiry' => 'date',
        'nasfund_allocation_percentage' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
    ];

    /**
     * The employee index is the only screen that intentionally includes
     * inactive records. Use this scope for operational employee lists.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'Active');
    }

    /**
     * Serve uploaded photos through the application instead of relying on a
     * public/storage symlink or the APP_URL value. This works on Laravel Cloud
     * as well as local installations.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? route('employees.photo', $this, absolute: false)
            : null;
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function getPositionNameAttribute()
    {
        return $this->attributes['position'] ?? $this->position()->value('name');
    }

    // SOW: Age (auto-calculated)
    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    /**
     * The effective fortnight regular-hours cap for this employee.
     * Precedence: an explicit per-employee override (fortnight_hours),
     * then the employee's company default (companies.regular_hours,
     * e.g. 144 for YellowJacket Security), then 84 as the final fallback.
     *
     * This is the single source of truth for the 84/144-hour logic and
     * should be used anywhere that previously hardcoded 84.
     */
    public function getRegularHoursLimitAttribute()
    {
        return (int) ($this->fortnight_hours ?? ($this->company?->regular_hours ?? 84));
    }

    /**
     * Yellow Jacket, Wave, and Caroline's employees do not receive a Sunday
     * premium. Their Sunday work counts toward the normal regular-hours cap,
     * then becomes standard overtime once that cap has been reached.
     */
    public function isSundayOvertimeExempt(): bool
    {
        return in_array(strtoupper((string) ($this->company?->code ?? '')), [
            'YJS-POM',
            'YJS-LAE',
            'WAVE',
            'CARO',
        ], true);
    }

    // SOW: Employee belongs to Company
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // SOW: Employee belongs to Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function assignments()
    {
        return $this->hasMany(EmployeeAssignment::class)->orderByDesc('from_date');
    }

    /** Assignment effective on a given date (today by default). */
    public function assignmentOn($date = null)
    {
        $date = Carbon::parse($date ?? now())->toDateString();
        return $this->assignments()
            ->with('branch')
            ->whereDate('from_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('to_date')->orWhereDate('to_date', '>=', $date);
            })
            ->first();
    }

    public function scopeAtBranch(Builder $query, $branchId, $date = null): Builder
    {
        $date = Carbon::parse($date ?? now())->toDateString();
        return $query->whereHas('assignments', function ($assignment) use ($branchId, $date) {
            $assignment->where('branch_id', $branchId)
                ->whereDate('from_date', '<=', $date)
                ->where(function ($dates) use ($date) {
                    $dates->whereNull('to_date')->orWhereDate('to_date', '>=', $date);
                });
        });
    }

    // SOW: Up to 2 Bank Accounts
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    // SOW: Employee Documents (Upload, Images, Contracts)
    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    // SOW: Leave Management
    public function leaveRecords()
    {
        return $this->hasMany(LeaveRecord::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // SOW: Loans & Cash Advances
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    // SOW: Discipline Records
    public function disciplineRecords()
    {
        return $this->hasMany(DisciplineRecord::class);
    }

    // Pay Increase History - Explicit table reference
    public function payIncreaseHistory()
    {
        return $this->hasMany(PayIncreaseHistory::class, 'employee_id');
    }

    // SOW: NASFUND
    public function nasfundContributions()
    {
        return $this->hasMany(NasfundContribution::class);
    }

    // SOW: Payroll
    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function getAllowanceAttribute()
    {
        return $this->attributes['allowance'] ?? 0;
    }

    // SOW: Expiry Notifications (90 days before)
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function getBankAccountDetails()
    {
        $account = $this->bankAccounts()->where('is_preferred', true)->first();
        if ($account) {
            return [
                'account_name' => $account->account_name,
                'account_number' => $account->account_number,
                'bank_name' => $account->bank_name,
                'bsb_code' => $account->bsb_code,
            ];
        }
        return null;
    }
    // SOW: Preferred payment account
    public function getPreferredBankAccount()
    {
        return $this->bankAccounts()->where('is_preferred', true)->first();
    }

    // SOW: Employee Classification Helpers
    public function isNational()
    {
        return $this->employee_type === 'National';
    }

    public function isExpatriate()
    {
        return $this->employee_type === 'Expatriate';
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function attendanceSummaries()
    {
        return $this->hasMany(AttendanceSummary::class);
    }

    public function getAttendanceSummary($fortnightNumber)
    {
        return $this->attendanceSummaries()
            ->where('fortnight_number', $fortnightNumber)
            ->first();
    }
}
