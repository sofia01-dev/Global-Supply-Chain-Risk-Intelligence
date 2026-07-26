<?php
namespace App\Services\Api;

use App\Models\Country;
use App\Models\WeatherCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherApiService
{
    public function getAllWeatherWithCountries($search = null)
    {
        $query = Country::whereHas('weatherCaches')->with('weatherCaches');
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
        return $query->orderBy('name')->get();
    }

    public function syncWeather()
    {
        $countries = Country::whereNotNull('latitude')->whereNotNull('longitude')->get();
        $syncedData = collect();

        // Sinkronisasi Negara
        foreach ($countries as $country) {
            try {
                usleep(100000); 
                $lat = $country->latitude;
                $lng = $country->longitude;
                
                $response = Http::timeout(15)->retry(2, 100)->get("https://api.open-meteo.com/v1/forecast", [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current' => 'temperature_2m,relative_humidity_2m,surface_pressure,cloud_cover,visibility,wind_speed_10m,weather_code',
                    'hourly' => 'temperature_2m',
                    'forecast_days' => 2,
                    'timezone' => 'auto'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && isset($data['current'])) {
                        $current = $data['current'];
                        $weather = WeatherCache::updateOrCreate(
                            ['country_id' => $country->id, 'port_id' => null],
                            [
                                'temperature' => $current['temperature_2m'] ?? null,
                                'wind_speed' => $current['wind_speed_10m'] ?? null,
                                'condition' => $this->mapWeatherCode($current['weather_code'] ?? 0),
                                'raw_data' => $data,
                                'expires_at' => Carbon::now()->addHours(2),
                            ]
                        );
                        $syncedData->push($weather);
                    }
                } else {
                    Log::warning("Weather API failed for country {$country->name}: " . $response->status());
                }
            } catch (\Throwable $e) {
                Log::error("WeatherApiService Error for {$country->name}: " . $e->getMessage());
            }
        }

        // Sinkronisasi Port
        $activePortIds = \App\Models\Shipment::pluck('destination_port_id')->merge(\App\Models\Shipment::pluck('origin_port_id'))->unique();
        $ports = \App\Models\Port::whereIn('id', $activePortIds)
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->get();
        
        foreach ($ports as $port) {
            try {
                usleep(100000); 
                $lat = $port->latitude;
                $lng = $port->longitude;
                
                $response = Http::timeout(15)->retry(2, 100)->get("https://api.open-meteo.com/v1/forecast", [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'current' => 'temperature_2m,relative_humidity_2m,surface_pressure,cloud_cover,visibility,wind_speed_10m,weather_code',
                    'hourly' => 'temperature_2m',
                    'forecast_days' => 2,
                    'timezone' => 'auto'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && isset($data['current'])) {
                        $current = $data['current'];
                        $weather = WeatherCache::updateOrCreate(
                            ['port_id' => $port->id, 'country_id' => null],
                            [
                                'temperature' => $current['temperature_2m'] ?? null,
                                'wind_speed' => $current['wind_speed_10m'] ?? null,
                                'condition' => $this->mapWeatherCode($current['weather_code'] ?? 0),
                                'raw_data' => $data,
                                'expires_at' => Carbon::now()->addHours(2),
                            ]
                        );
                        $syncedData->push($weather);
                    }
                } else {
                    Log::warning("Weather API failed for port {$port->name}: " . $response->status());
                }
            } catch (\Throwable $e) {
                Log::error("WeatherApiService Error for {$port->name}: " . $e->getMessage());
            }
        }

        return $syncedData;
    }

    private function mapWeatherCode($code)
    {
        if ($code == 0) return 'Clear';
        if ($code >= 1 && $code <= 3) return 'Cloudy';
        if ($code >= 51 && $code <= 67) return 'Rain';
        if ($code >= 71 && $code <= 77) return 'Snow';
        if ($code >= 95 && $code <= 99) return 'Thunderstorm';
        return 'Unknown';
    }
}