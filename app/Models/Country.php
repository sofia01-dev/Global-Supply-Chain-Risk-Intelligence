<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'iso2_code',
        'name',
        'capital',
        'region',
        'currency_code',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function ports()
    {
        return $this->hasMany(Port::class);
    }

    public function riskScores()
    {
        return $this->hasMany(RiskScore::class);
    }

    public function riskScoreHistories()
    {
        return $this->hasMany(RiskScoreHistory::class);
    }

    public function weatherCaches()
    {
        return $this->hasMany(WeatherCache::class);
    }

    public function newsCaches()
    {
        return $this->hasMany(NewsCache::class);
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function economicIndicator()
    {
        return $this->hasOne(EconomicIndicator::class);
    }

    public function getGdpAttribute($value)
    {
        return $this->economicIndicator->gdp ?? $value;
    }

    public function getInflationRateAttribute($value)
    {
        return $this->economicIndicator->inflation_rate ?? $value;
    }

    public function getPopulationAttribute($value)
    {
        return $this->economicIndicator->population ?? $value;
    }
}