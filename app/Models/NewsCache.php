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
        'positive_percentage',
        'neutral_percentage',
        'negative_percentage',
        'sentiment_score',
        'sentiment_label',
        'published_at',
    ];

    protected $casts = [
        'positive_percentage' => 'decimal:2',
        'neutral_percentage' => 'decimal:2',
        'negative_percentage' => 'decimal:2',
        'sentiment_score' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}