<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyCache extends Model
{
    protected $fillable = [
        'currency_code',
        'exchange_rate_usd',
        'expires_at',
    ];

    protected $casts = [
        'exchange_rate_usd' => 'decimal:6',
        'expires_at' => 'datetime',
    ];
}