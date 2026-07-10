<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'title',
        'message',
        'type',
        'expiry_date',
        'days_before',
        'is_read',
        'read_at',
        'link',
        'data'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
        'data' => 'array'
    ];

    // SOW: Expiry Notifications (90 days before)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function markAsRead()
    {
        $this->is_read = true;
        $this->read_at = now();
        $this->save();
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeExpiringSoon($query, $days = 90)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                     ->where('expiry_date', '>=', now());
    }
}