<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayIncreaseHistory extends Model
{
    use HasFactory;

    protected $table = 'pay_increase_history';

    protected $fillable = [
        'employee_id',
        'previous_rate',
        'new_rate',
        'increase_percentage',
        'increase_date',
        'reason',
        'approved_by'
    ];

    protected $casts = [
        'increase_date' => 'date',
        'previous_rate' => 'decimal:2',
        'new_rate' => 'decimal:2',
        'increase_percentage' => 'decimal:2'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}