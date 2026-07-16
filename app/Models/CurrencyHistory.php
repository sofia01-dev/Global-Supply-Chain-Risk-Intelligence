<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyHistory extends Model
{
    protected $fillable = ['currency_code', 'exchange_rate_usd', 'recorded_date'];
    
    protected $casts = [
        'exchange_rate_usd' => 'decimal:6',
        'recorded_date' => 'date',
    ];
}