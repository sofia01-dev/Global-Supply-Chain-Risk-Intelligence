<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskFactor extends Model
{
    protected $fillable = [
        'factor',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];
}