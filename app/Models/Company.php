<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'email',
        'tax_id',
        'logo_path',
        'is_active',
        'settings',
        'regular_hours',
        'bank_name',
        'bsb_code',
        'bank_account_number',
        'bank_account_name',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean'
    ];

    // SOW: Multi-Company Support
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function taxTables()
    {
        return $this->hasMany(TaxTable::class);
    }
}