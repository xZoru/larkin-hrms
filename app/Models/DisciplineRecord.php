<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplineRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'memo_template_id',
        'memo_number',
        'offense_description',
        'action_taken',
        'date_issued',
        'issued_by',
        'follow_up_date',
        'remarks',
        'document_path'
    ];

    protected $casts = [
        'date_issued' => 'date',
        'follow_up_date' => 'date'
    ];

    // SOW: Employee Discipline Records
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function template()
    {
        return $this->belongsTo(MemoTemplate::class, 'memo_template_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}