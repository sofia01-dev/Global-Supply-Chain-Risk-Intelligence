<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NegativeWord extends Model
{
    protected $fillable = [
        'word',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];
}