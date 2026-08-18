<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'branch_id', 'from_date', 'to_date', 'is_temporary', 'notes', 'assigned_by'];
    protected $casts = ['from_date' => 'date', 'to_date' => 'date', 'is_temporary' => 'boolean'];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function assignedBy() { return $this->belongsTo(User::class, 'assigned_by'); }
}
