<?php
namespace App\Services\Risk;

use App\Models\Country;
use App\Models\RiskScore;
use Illuminate\Support\Facades\Log;

class RiskEngineService
{
    /**
     * Calculate and sync Risk Scores for all countries based on synchronized API data.
     */
    public function syncRiskScores()
    {
        $countries = Country::with(['weatherCaches', 'newsCaches', 'economicIndicator'])->get();

        $stats = [
            'total_processed' => 0,
            'success_count' => 0,
            'failed_count' => 0,
            'reasons' => [],
            'examples' => []
        ];

        foreach ($countries as $country) {
            $stats['total_processed']++;
            try {
                // Initialize component risks
                $weatherRisk = 0;
                $inflationRisk = 0;
                $currencyRisk = 0;
                $newsRisk = 0;

                // 1. Weather Risk (30%)
                $weather = $country->weatherCaches()->latest()->first();
                if ($weather) {
                    $temp = $weather->temperature ?? 25;
                    $wind = $weather->wind_speed ?? 0;
                    $tempRisk = ($temp > 35 || $temp < 0) ? 80 : (($temp > 30 || $temp < 10) ? 40 : 10);
                    $windRisk = min(($wind / 100) * 100, 100);
                    
                    $weatherRisk = ($tempRisk * 0.5) + ($windRisk * 0.5);
                } else {
                    $weatherRisk = 50; // Fallback average risk
                }

                // 2. Inflation Risk (25%)
                $eco = $country->economicIndicator;
                if ($eco && $eco->inflation_rate !== null) {
                    $inf = $eco->inflation_rate;
                    // Deflasi (negatif) atau inflasi tinggi (>15%) mengandung risiko.
                    if ($inf < 0) {
                        $inflationRisk = min(abs($inf) * 10, 100);
                    } else {
                        $inflationRisk = min(($inf / 20) * 100, 100);
                    }
                } else {
                    $inflationRisk = 50; // Fallback
                }

                // 3. Currency / Exchange Rate Risk (20%)
                $currencyRisk = 50; 

                // 4. News Sentiment Risk (25%)
                $newsList = $country->newsCaches()->latest()->take(5)->get();
                
                // Jika negara tidak memiliki berita khusus, gunakan Berita Global sebagai cadangan 
                if ($newsList->isEmpty()) {
                    $newsList = \App\Models\NewsCache::whereNull('country_id')->latest()->take(5)->get();
                }

                if ($newsList->isNotEmpty()) {
                    $totalRisk = 0;
                    foreach ($newsList as $news) {
                        $score = $news->sentiment_score ?? 0;
                        $label = strtolower($news->sentiment_label ?? 'neutral');
                        
                        if ($label === 'negative' || $score < 0) {
                            $totalRisk += 80 + (abs($score) * 20); // 80-100 risk
                        } elseif ($label === 'positive' || $score > 0) {
                            $totalRisk += 20 - (abs($score) * 20); // 0-20 risk
                        } else {
                            $totalRisk += 50; // Neutral
                        }
                    }
                    $newsRisk = $totalRisk / $newsList->count();
                } else {
                    $newsRisk = 50; // Fallback
                }

                $factors = \App\Models\RiskFactor::pluck('weight', 'factor')->toArray();
                
                $weightWeather = ($factors['Weather'] ?? 30) / 100;
                $weightInflation = ($factors['Inflation'] ?? 20) / 100;
                $weightNews = ($factors['Political News'] ?? 40) / 100;
                $weightCurrency = ($factors['Currency'] ?? 10) / 100;

                // Menggabungkan Skor Risiko Keseluruhan menggunakan Model Risiko Berbobot
                $finalScore = ($weatherRisk * $weightWeather) 
                            + ($inflationRisk * $weightInflation) 
                            + ($currencyRisk * $weightCurrency) 
                            + ($newsRisk * $weightNews);
                
                $finalScore = max(0, min(100, $finalScore)); // Normalize 0-100

                // Map to Risk Level
                // 0–25 Low, 26–50 Medium, 51–75 High, 76–100 Critical
                $riskLevel = 'Low';
                if ($finalScore > 25 && $finalScore <= 50) {
                    $riskLevel = 'Medium';
                } elseif ($finalScore > 50 && $finalScore <= 75) {
                    $riskLevel = 'High';
                } elseif ($finalScore > 75) {
                    $riskLevel = 'Critical';
                }

                \App\Models\RiskScoreHistory::create([
                    'country_id' => $country->id,
                    'final_score' => round($finalScore, 2),
                    'risk_level' => $riskLevel,
                    'calculated_at' => now()
                ]);

                RiskScore::updateOrCreate(
                    ['country_id' => $country->id],
                    [
                        'final_score' => round($finalScore, 2),
                        'risk_level' => $riskLevel,
                        'calculated_at' => now()
                    ]
                );

                $stats['success_count']++;

                if (count($stats['examples']) < 5 && in_array($country->name, ['Germany', 'China', 'Indonesia', 'United States', 'Brazil'])) {
                    $actualLevelStr = ($finalScore > 75) ? 'Critical Risk' : $riskLevel . ' Risk';
                    $stats['examples'][] = "{$country->name} : " . round($finalScore, 2) . " ({$actualLevelStr})";
                }

            } catch (\Throwable $e) {
                $stats['failed_count']++;
                $stats['reasons'][] = "Country ID {$country->id}: " . $e->getMessage();
                Log::error("RiskEngine Error for {$country->name}: " . $e->getMessage());
            }
        }

        return $stats;
    }
}
