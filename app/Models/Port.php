<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Port extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'unlocode',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function shipmentsAsOrigin()
    {
        return $this->hasMany(Shipment::class, 'origin_port_id');
    }

    public function shipmentsAsDestination()
    {
        return $this->hasMany(Shipment::class, 'destination_port_id');
    }



    public function weatherCaches()
    {
        return $this->hasMany(WeatherCache::class);
    }
}