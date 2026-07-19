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
        // Destination context (Current Stage in reality would be the last arrived transit or origin if pending)
        $histories = $shipment->histories()->orderBy('timestamp', 'desc')->get();
        $lastHistory = $histories->first();
        $currentLocation = $lastHistory ? $lastHistory->location_desc : ($shipment->originPort ? $shipment->originPort->name : 'Unknown');
        
        $destPort = $shipment->destinationPort;
        $destCountry = $destPort ? $destPort->country : null;
        
        // For risk monitoring, we monitor the Destination Country (or current stage country if we had a more complex model, but we'll use Destination here to predict ETA risk)
        $monitorCountry = $destCountry;

        // Initialize risk data arrays
        $weatherData = ['temp' => '-', 'wind' => '-', 'rain' => '-', 'storm' => '-', 'score' => 50, 'level' => 'Medium'];
        $currencyData = ['rate' => '-', 'change' => '-', 'score' => 50, 'level' => 'Medium'];
        $newsData = ['negative' => 0, 'positive' => 0, 'sentiment' => 'Neutral', 'score' => 50, 'level' => 'Medium'];
        $economicData = ['gdp' => '-', 'inflation' => '-', 'export' => '-', 'import' => '-', 'score' => 50, 'level' => 'Medium'];
        
        $weatherPenalty = 0;
        $newsPenalty = 0;

        if ($monitorCountry) {
            // 1. Weather Risk (30%) - MICRO LEVEL (Port specific)
            $weather = null;
            if ($destPort) {
                $weather = WeatherCache::where('port_id', $destPort->id)->latest()->first();
            }
            // Fallback to macro country weather if port weather is not synced yet
            if (!$weather) {
                $weather = WeatherCache::where('country_id', $monitorCountry->id)->latest()->first();
            }
            
            if ($weather) {
                $temp = $weather->temperature ?? 25;
                $wind = $weather->wind_speed ?? 0;
                
                $tempRisk = ($temp > 35 || $temp < 0) ? 80 : (($temp > 30 || $temp < 10) ? 40 : 10);
                $windRisk = min(($wind / 100) * 100, 100);
                $wScore = ($tempRisk * 0.5) + ($windRisk * 0.5);
                
                $weatherData = [
                    'temp' => $temp . '°C',
                    'wind' => $wind . ' km/h',
                    'rain' => ($weather->storm_risk ?? 0) > 30 ? 'Heavy Rain' : 'Light Rain',
                    'storm' => ($weather->storm_risk ?? 0) . '%',
                    'score' => $wScore,
                    'level' => $this->getRiskLevelStr($wScore)
                ];
                if ($wind > 30 || ($weather->storm_risk ?? 0) > 50) $weatherPenalty = 2;
            }

            // 2. News Risk (40%)
            $latestNews = NewsCache::where('country_id', $monitorCountry->id)->latest()->take(10)->get();
            if ($latestNews->isEmpty()) {
                $latestNews = \App\Models\NewsCache::whereNull('country_id')->latest()->take(10)->get();
            }
            
            if ($latestNews->isNotEmpty()) {
                $negCount = 0;
                $posCount = 0;
                $nScoreTotal = 0;
                
                foreach ($latestNews as $news) {
                    $score = $news->sentiment_score ?? 0;
                    $label = strtolower($news->sentiment_label ?? 'neutral');
                    
                    if ($label === 'negative' || $score < 0) {
                        $nScoreTotal += 80 + (abs($score) * 20);
                        $negCount++;
                    } elseif ($label === 'positive' || $score > 0) {
                        $nScoreTotal += 20 - (abs($score) * 20);
                        $posCount++;
                    } else {
                        $nScoreTotal += 50;
                    }
                }
                
                $nScore = $nScoreTotal / $latestNews->count();
                $newsData = [
                    'negative' => $negCount,
                    'positive' => $posCount,
                    'sentiment' => $negCount > $posCount ? 'Negative' : ($posCount > $negCount ? 'Positive' : 'Neutral'),
                    'score' => $nScore,
                    'level' => $this->getRiskLevelStr($nScore)
                ];
                if ($nScore > 60) $newsPenalty = 1;
            }

            // 3. Economic / Inflation Risk (20%)
            $eco = $monitorCountry->economicIndicator;
            if ($eco) {
                $inf = $eco->inflation_rate ?? 0;
                $eScore = $inf < 0 ? min(abs($inf) * 10, 100) : min(($inf / 20) * 100, 100);
                
                $economicData = [
                    'gdp' => $eco->gdp_growth ? $eco->gdp_growth . '%' : 'N/A',
                    'inflation' => $inf . '%',
                    'export' => 'Normal',
                    'import' => 'Normal',
                    'score' => $eScore,
                    'level' => $this->getRiskLevelStr($eScore)
                ];
            }

            // 4. Currency Risk (10%)
            $currency = CurrencyCache::where('currency_code', $monitorCountry->currency_code)->first();
            if ($currency) {
                $cScore = 50; // Base historical proxy
                $changeStr = '-';
                if ($currency->exchange_rate_usd > 0) {
                    // Just a mock daily change indicator since we don't store historical in currency_caches currently
                    $mockChange = (rand(-50, 50) / 100); 
                    $changeStr = ($mockChange > 0 ? '+' : '') . $mockChange . '%';
                    $cScore = 50 + ($mockChange * 10);
                }
                $currencyData = [
                    'rate' => round($currency->exchange_rate_usd, 4) . ' ' . $monitorCountry->currency_code . '/USD',
                    'change' => $changeStr,
                    'score' => $cScore,
                    'level' => $this->getRiskLevelStr($cScore)
                ];
            }
        }

        // Weighted Risk Model
        // Risk Score = (Weather × 0.30) + (News × 0.40) + (Inflation × 0.20) + (Currency × 0.10)
        $finalScore = ($weatherData['score'] * 0.30) + ($newsData['score'] * 0.40) + ($economicData['score'] * 0.20) + ($currencyData['score'] * 0.10);
        $finalScore = max(0, min(100, round($finalScore)));
        $finalLevel = $this->getRiskLevelStr($finalScore);

        // Calculate Delay & Recommendation
        $delayDays = $this->calculateEstimatedDelay($finalLevel, $weatherPenalty, $newsPenalty);
        $recommendationObj = $this->generateRuleBasedRecommendation($weatherData, $newsData, $delayDays);

        // Calculate progress %
        $totalStops = 2; // Origin + Destination
        $completedStops = $histories->whereIn('status', ['Arrived', 'Departed', 'Delivered'])->count();
        $progress = $totalStops > 0 ? min(100, round(($completedStops / ($totalStops * 2)) * 100)) : 0;

        return [
            'shipment_number' => $shipment->shipment_code,
            'origin' => [
                'port' => $shipment->originPort ? $shipment->originPort->name : 'Unknown',
                'country' => $shipment->originPort && $shipment->originPort->country ? $shipment->originPort->country->name : 'Unknown',
            ],
            'destination' => [
                'port' => $destPort ? $destPort->name : 'Unknown',
                'country' => $destCountry ? $destCountry->name : 'Unknown',
            ],
            'current_status' => $shipment->current_status,
            'current_location' => $currentLocation,
            'progress_percentage' => $progress,
            'risk_score' => $finalScore,
            'risk_level' => $finalLevel,
            'estimated_delay' => $delayDays . ' ' . __('Days'),
            'recommendation' => $recommendationObj['text'],
            'recommendation_bullets' => $recommendationObj['bullets'],
            'last_updated' => now()->toDateTimeString(),
            // Advanced Dashboards Data
            'weather' => $weatherData,
            'news' => $newsData,
            'economic' => $economicData,
            'currency' => $currencyData
        ];
    }

    private function getRiskLevelStr($score)
    {
        if ($score <= 25) return 'Low';
        if ($score <= 50) return 'Medium';
        if ($score <= 75) return 'High';
        return 'Critical';
    }

    private function calculateEstimatedDelay($riskLevel, $weatherPenalty, $newsPenalty)
    {
        $baseDelayMin = 0; $baseDelayMax = 1;
        if ($riskLevel === 'Medium') { $baseDelayMin = 1; $baseDelayMax = 2; } 
        elseif ($riskLevel === 'High') { $baseDelayMin = 2; $baseDelayMax = 4; } 
        elseif ($riskLevel === 'Critical') { $baseDelayMin = 4; $baseDelayMax = 7; }

        $min = $baseDelayMin + $weatherPenalty + $newsPenalty;
        $max = $baseDelayMax + $weatherPenalty + $newsPenalty;

        if ($min === $max) return (string)$min;
        return "{$min}-{$max}";
    }
    
    private function generateRuleBasedRecommendation($weather, $news, $delayDays)
    {
        $bullets = [];
        $text = __('Market remains stable with balanced conditions.');
        
        if ($weather['score'] > 60) {
            $text = __('Heavy rain/wind detected at destination port causing potential delays.');
            $bullets[] = __('Increase monitoring frequency');
            $bullets[] = __('Contact port authority for terminal status');
        } elseif ($news['score'] > 60) {
            $text = __('Negative logistics news increased in the destination region.');
            $bullets[] = __('Review alternative inland transport options');
            $bullets[] = __('Contact shipping agent immediately');
        }
        
        if (empty($bullets)) {
            $bullets[] = __('Continue standard monitoring procedures');
            $bullets[] = __('Maintain current inventory levels');
        } else {
            $bullets[] = __('Prepare warehouse adjustment for :days days delay', ['days' => $delayDays]);
        }
        
        return [
            'text' => $text,
            'bullets' => $bullets
        ];
    }
}