<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'slug',
        'image',
        'category',
        'tags',
        'content',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'tags' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}