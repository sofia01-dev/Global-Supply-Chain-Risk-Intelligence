<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherCache extends Model
{
    protected $fillable = [
        'country_id',
        'port_id',
        'condition',
        'temperature',
        'wind_speed',
        'raw_data',
        'expires_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'raw_data' => 'array',
        'expires_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }
}