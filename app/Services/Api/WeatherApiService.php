<?php
namespace App\Services\Api;
use App\Models\WeatherCache;

class WeatherApiService
{
    public function getAllWeather()
    {
        return WeatherCache::with(['country', 'port'])->get();
    }
}