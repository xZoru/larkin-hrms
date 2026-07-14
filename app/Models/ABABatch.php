<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ABABatch extends Model
{
    use HasFactory;

    protected $table = 'aba_batches';

    protected $fillable = [
        'company_id',
        'payroll_id',
        'batch_number',
        'filename',
        'file_path',
        'bank_name',
        'bank_code',
        'apca_user_id',
        'bsb_number',
        'account_number',
        'account_name',
        'processing_date',
        'total_amount',
        'total_transactions',
        'status',
        'metadata',
        'notes',
        'generated_by',
        'downloaded_at',
        'submitted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'processing_date' => 'date',
        'downloaded_at' => 'datetime',
        'submitted_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // Scopes
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'K' . number_format($this->total_amount, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'generated' => 'badge-primary',
            'downloaded' => 'badge-info',
            'submitted' => 'badge-success',
            'cancelled' => 'badge-danger',
        ];

        return $badges[$this->status] ?? 'badge-secondary';
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }
}