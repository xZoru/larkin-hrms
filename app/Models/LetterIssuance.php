<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterIssuance extends Model
{
    protected $fillable = ['employee_id', 'memo_template_id', 'issued_by', 'letter_number', 'letter_type', 'template_name', 'date_issued', 'addressed_to', 'body', 'signatory_name', 'signatory_title', 'document_path'];
    protected $casts = ['date_issued' => 'date'];
    public function employee() { return $this->belongsTo(Employee::class); }
    public function template() { return $this->belongsTo(MemoTemplate::class, 'memo_template_id'); }
    public function issuedBy() { return $this->belongsTo(User::class, 'issued_by'); }
}
