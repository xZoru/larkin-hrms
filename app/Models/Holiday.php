<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'is_global',
        'name',
        'date',
        'description',
        'is_recurring',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean',
        'is_global' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public static function isHoliday($companyId, $date)
    {
        return self::where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhere('is_global', true);
            })
            ->where('is_active', true)
            ->whereDate('date', $date)
            ->exists();
    }

    public static function getHolidayName($companyId, $date)
    {
        $holiday = self::where(function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->orWhere('is_global', true);
            })
            ->where('is_active', true)
            ->whereDate('date', $date)
            ->first();

        return $holiday ? $holiday->name : null;
    }
}
