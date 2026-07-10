<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'content',
        'placeholders',
        'category',
        'is_active'
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean'
    ];

    // SOW: Memos generated from templates
    public function generateMemo($data)
    {
        $content = $this->content;
        foreach ($data as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        return $content;
    }
}