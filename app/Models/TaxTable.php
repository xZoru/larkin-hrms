<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxTable extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'employee_type',
        'min_amount',
        'max_amount',
        'tax_rate',
        'fixed_tax',
        'effective_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date'
    ];

    // SOW: Tax tables configurable by administrators
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // SOW: National Employee Tax calculated directly from wages
    // SOW: Expatriate Employee Tax calculated from net wages and added to gross wage
    public function calculateTax($amount)
    {
        if ($this->max_amount && $amount > $this->max_amount) {
            return null;
        }

        if ($amount < $this->min_amount) {
            return null;
        }

        return ($amount * $this->tax_rate / 100) + $this->fixed_tax;
    }
}