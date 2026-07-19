<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCache extends Model
{
    protected $fillable = [
        'category',
        'country_id',
        'title',
        'url',
        'image_url',
        'sentiment_label',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}