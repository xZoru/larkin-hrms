<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'user_type',
        'password',
        'employee_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ============ RELATIONSHIPS ============
    
    // ✅ MULTIPLE COMPANIES - Many-to-Many
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    // ✅ Default company (for backward compatibility)
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // ============ HELPER METHODS ============

    public function isActive()
    {
        return $this->is_active;
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('Super Admin');
    }

    public function isHRManager()
    {
        return $this->hasRole('HR Manager');
    }

    public function isPayrollOfficer()
    {
        return $this->hasRole('Payroll Officer');
    }

    // ✅ Get all company IDs the user belongs to
    public function getCompanyIdsAttribute()
    {
        return $this->companies->pluck('id')->toArray();
    }

    // ✅ Check if user belongs to a company
    public function belongsToCompany($companyId)
    {
        return $this->companies()->where('company_id', $companyId)->exists();
    }

    // ✅ Get default company
    public function getDefaultCompanyAttribute()
    {
        $default = $this->companies()->wherePivot('is_default', true)->first();
        return $default ?? $this->companies()->first();
    }

    // ✅ Set a company as default
    public function setDefaultCompany($companyId)
    {
        // Reset all defaults
        $this->companies()->updateExistingPivot($this->companies->pluck('id')->toArray(), ['is_default' => false]);
        
        // Set the new default
        if ($this->belongsToCompany($companyId)) {
            $this->companies()->updateExistingPivot($companyId, ['is_default' => true]);
        }
    }

    // User type helpers
    public function getUserTypeLabelAttribute()
    {
        $labels = [
            'national' => 'National',
            'expatriate' => 'Expatriate',
            'all' => 'All Employees',
        ];
        return $labels[$this->user_type] ?? 'All Employees';
    }

    public function getUserTypeBadgeAttribute()
    {
        $badges = [
            'national' => 'badge-type national',
            'expatriate' => 'badge-type expatriate',
            'all' => 'badge-type all',
        ];
        return $badges[$this->user_type] ?? 'badge-type all';
    }

    public function canViewNational()
    {
        return in_array($this->user_type, ['national', 'all']);
    }

    public function canViewExpatriate()
    {
        return in_array($this->user_type, ['expatriate', 'all']);
    }
    
    public function getAllowedEmployeeTypes()
    {
        switch ($this->user_type) {
            case 'national':
                return ['National'];
            case 'expatriate':
                return ['Expatriate'];
            case 'all':
            default:
                return ['National', 'Expatriate'];
        }
    }

    public function canViewEmployee($employee)
    {
        if (!$employee) return false;
        
        $allowedTypes = $this->getAllowedEmployeeTypes();
        return in_array($employee->employee_type, $allowedTypes);
    }

    public function scopeViewableEmployees($query)
    {
        $allowedTypes = $this->getAllowedEmployeeTypes();
        return $query->whereIn('employee_type', $allowedTypes);
    }

    public function getCurrentCompanyId()
    {
        // 1. Check session first
        $companyId = session('current_company_id');
        
        if ($companyId) {
            return $companyId;
        }
        
        // 2. Try default company
        $default = $this->default_company;
        if ($default) {
            session(['current_company_id' => $default->id]);
            session(['current_company_name' => $default->name]);
            return $default->id;
        }
        
        // 3. Try first company
        $first = $this->companies()->first();
        if ($first) {
            $this->setDefaultCompany($first->id);
            session(['current_company_id' => $first->id]);
            session(['current_company_name' => $first->name]);
            return $first->id;
        }
        
        return null;
    }

    /**
     * Get the current company name from session
     */
    public function getCurrentCompanyName()
    {
        return session('current_company_name', 'No Company');
    }
}