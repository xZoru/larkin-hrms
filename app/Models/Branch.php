<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'code', 'type', 'address', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function company() { return $this->belongsTo(Company::class); }
    public function assignments() { return $this->hasMany(EmployeeAssignment::class); }
}
