<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'document_name',
        'document_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'description',
        'expiry_date',
        'uploaded_by'
    ];

    protected $casts = [
        'expiry_date' => 'date'
    ];

    // SOW: Employee Documents (Upload, Images, Contracts)
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}