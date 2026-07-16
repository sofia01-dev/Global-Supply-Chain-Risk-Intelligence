<?php
namespace App\Services\Shipment;

use App\Models\RiskScore;
use App\Models\WeatherCache;
use App\Models\CurrencyCache;
use App\Models\NewsCache;

class ShipmentMonitoringService
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->recommendationService = $recommendationService;
    }

    public function monitor($shipment)
    {
        // Destination context
        $destPort = $shipment->destinationPort;
        $destCountry = $destPort ? $destPort->country : null;

        // Origin context
        $originPort = $shipment->originPort;
        $originCountry = $originPort ? $originPort->country : null;

        // Risk Snapshot
        $riskScore = null;
        if ($destCountry) {
            $riskScore = RiskScore::where('country_id', $destCountry->id)->first();
        }

        $scoreVal = $riskScore ? $riskScore->final_score : 0;
        $levelStr = $riskScore ? $riskScore->risk_level : 'Low';

        // Weather Snapshot
        $weather = null;
        $weatherPenalty = 0;
        if ($destCountry) {
            $weather = WeatherCache::where('country_id', $destCountry->id)->first();
            if ($weather && ($weather->wind_speed > 30 || $weather->storm_risk > 50)) {
                $weatherPenalty = 2; // +2 days delay
            }
        }

        // Currency Snapshot
        $currency = null;
        $currencyPenalty = 0;
        if ($destCountry && $destCountry->currency_code) {
            $currency = CurrencyCache::where('currency_code', $destCountry->currency_code)->first();
        }

        // News Snapshot
        $newsPenalty = 0;
        $latestNews = collect();
        if ($destCountry) {
            $latestNews = NewsCache::where('country_id', $destCountry->id)->latest()->take(3)->get();
            $avgNeg = $latestNews->avg('negative_percentage');
            if ($avgNeg > 60) {
                $newsPenalty = 1;
            }
        }

        // Calculate Delay
        $delayDays = $this->calculateEstimatedDelay($levelStr, $weatherPenalty, $newsPenalty);

        // Calculate Recommendation
        $recommendation = $this->recommendationService->generateRecommendation($levelStr, $weatherPenalty, $newsPenalty, $shipment->current_status);

        // Prepare Object
        return [
            'shipment_number' => $shipment->shipment_code,
            'origin' => [
                'port' => $originPort ? $originPort->name : 'Unknown',
                'country' => $originCountry ? $originCountry->name : 'Unknown',
            ],
            'destination' => [
                'port' => $destPort ? $destPort->name : 'Unknown',
                'country' => $destCountry ? $destCountry->name : 'Unknown',
            ],
            'current_status' => $shipment->current_status,
            'risk_score' => $scoreVal,
            'risk_level' => $levelStr,
            'weather_summary' => $weather ? "Temp: {$weather->temperature}°C, Wind: {$weather->wind_speed}km/h" : 'No Data',
            'currency_summary' => $currency ? "Rate: {$currency->exchange_rate_usd} USD" : 'No Data',
            'latest_news_summary' => $latestNews->isNotEmpty() ? $latestNews->first()->headline : 'No Data',
            'estimated_delay' => $delayDays . ' Days',
            'recommendation' => $recommendation,
            'last_updated' => now()->toDateTimeString()
        ];
    }

    private function calculateEstimatedDelay($riskLevel, $weatherPenalty, $newsPenalty)
    {
        $baseDelayMin = 0;
        $baseDelayMax = 1;

        if ($riskLevel === 'Medium') {
            $baseDelayMin = 1; $baseDelayMax = 3;
        } elseif ($riskLevel === 'High') {
            $baseDelayMin = 3; $baseDelayMax = 5;
        } elseif ($riskLevel === 'Critical') {
            $baseDelayMin = 5; $baseDelayMax = 10;
        }

        $min = $baseDelayMin + $weatherPenalty + $newsPenalty;
        $max = $baseDelayMax + $weatherPenalty + $newsPenalty;

        if ($min === $max) {
            return (string)$min;
        }
        return "{$min}-{$max}";
    }

    // Retained for backward compatibility if old views expect it
    public function calculateMonitoring($shipment)
    {
        $histories = $shipment->histories;
        $lastHistory = $histories->first();

        // Calculate progress %
        $totalStops = 2 + $shipment->routes->count(); 
        $completedStops = $histories->whereIn('status', ['Arrived', 'Departed'])->count();
        $progress = $totalStops > 0 ? min(100, round(($completedStops / ($totalStops * 2)) * 100)) : 0;

        $currentLocation = $lastHistory ? $lastHistory->location_desc : 'Unknown';
        $currentTransitPoint = 'None';
        
        if ($shipment->current_status === 'In Transit' || $shipment->current_status === 'Arrived') {
            $currentTransitPoint = $lastHistory ? $lastHistory->location_desc : 'Unknown';
        }

        return [
            'progress_percentage' => $progress,
            'current_location' => $currentLocation,
            'current_transit_point' => $currentTransitPoint,
            'last_known_location' => $currentLocation,
            'current_status' => $lastHistory ? $lastHistory->status : ($shipment->current_status ?? 'Pending'),
        ];
    }
}