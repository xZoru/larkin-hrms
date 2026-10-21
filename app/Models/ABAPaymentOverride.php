<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ABAPaymentOverride extends Model
{
    protected $table = 'aba_payment_overrides';

    protected $fillable = ['payroll_id', 'payroll_item_id', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];
}
