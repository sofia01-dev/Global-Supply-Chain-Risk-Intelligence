<?php
namespace App\Services\Dashboard;

use App\Models\Country;
use App\Services\Risk\RiskEngineService;
use App\Services\Api\WeatherApiService;
use App\Services\Api\CurrencyApiService;
use App\Services\AI\LexiconSentimentService;
use App\Models\CurrencyHistory;

class ComparisonService
{
    protected $riskEngine;
    protected $weatherApi;
    protected $currencyApi;
    protected $sentimentService;

    public function __construct(
        RiskEngineService $riskEngine,
        WeatherApiService $weatherApi,
        CurrencyApiService $currencyApi,
        LexiconSentimentService $sentimentService
    ) {
        $this->riskEngine = $riskEngine;
        $this->weatherApi = $weatherApi;
        $this->currencyApi = $currencyApi;
        $this->sentimentService = $sentimentService;
    }

    public function getComparisonData($countryAId, $countryBId)
    {
        $countryA = Country::with('economicIndicator')->find($countryAId);
        $countryB = Country::with('economicIndicator')->find($countryBId);

        if (!$countryA || !$countryB) {
            return ['error' => 'Invalid countries selected'];
        }

        $dataA = $this->buildCountryData($countryA);
        $dataB = $this->buildCountryData($countryB);
        $recommendation = $this->generateRecommendation($dataA, $dataB);

        return [
            'countryA' => $dataA,
            'countryB' => $dataB,
            'recommendation' => $recommendation
        ];
    }

    private function buildCountryData(Country $country)
    {
        // 1. Economic Data
        $gdp = $country->economicIndicator ? $country->economicIndicator->gdp : 0;
        $inflation = $country->economicIndicator ? $country->economicIndicator->inflation_rate : 0;

        // 2. Weather Data
        $weather = ['temp' => 0, 'desc' => 'N/A', 'humidity' => 0, 'wind' => 0, 'pressure' => 0, 'rain' => 0];
        try {
            $weatherCache = \App\Models\WeatherCache::where('country_id', $country->id)->first();
            if ($weatherCache) {
                $raw = is_string($weatherCache->raw_data) ? json_decode($weatherCache->raw_data, true) : $weatherCache->raw_data;
                $current = $raw['current'] ?? [];
                
                $weather = [
                    'temp' => $weatherCache->temperature ?? 0,
                    'desc' => $weatherCache->condition ?? 'Unknown',
                    'humidity' => $current['relative_humidity_2m'] ?? 0,
                    'wind' => $weatherCache->wind_speed ?? 0,
                    'pressure' => $current['surface_pressure'] ?? 0,
                    'rain' => $current['precipitation'] ?? 0,
                ];
            }
        } catch (\Exception $e) {}

        // 3. Currency Data
        $currencyRate = 1;
        $currencyChange = 0;
        $currencyHistory = [];
        try {
            $currencyRate = \App\Models\CurrencyCache::where('currency_code', $country->currency_code)->value('exchange_rate_usd') ?? 1;
            $histories = CurrencyHistory::where('currency_code', $country->currency_code)
                ->orderBy('recorded_date', 'desc')->take(7)->get()->reverse()->values();
            
            if ($histories->count() > 1) {
                $today = (float) $histories->last()->exchange_rate_usd;
                $yesterday = (float) $histories[$histories->count()-2]->exchange_rate_usd;
                if ($yesterday > 0) {
                    $currencyChange = round((($today - $yesterday) / $yesterday) * 100, 2);
                }
            }
            $currencyHistory = $histories->pluck('exchange_rate_usd')->toArray();
            if (empty($currencyHistory)) { $currencyHistory = [$currencyRate]; }
        } catch (\Exception $e) {}

        // 4. News Sentiment
        $sentiment = $this->sentimentService->analyzeCountrySentiment($country->id);

        // 5. Risk Scores
        $riskScore = \App\Models\RiskScore::where('country_id', $country->id)->first();
        $overall = $riskScore ? $riskScore->final_score : 50;
        $overall_label = $riskScore ? $riskScore->risk_level : 'Medium';

        // Derive factors since they aren't saved individually
        $weatherRisk = ($weather['temp'] > 35 || $weather['temp'] < 0) ? 'High' : (($weather['temp'] > 30 || $weather['temp'] < 10) ? 'Medium' : 'Low');
        $ecoRisk = ($inflation > 10 || $inflation < 0) ? 'High' : (($inflation > 5) ? 'Medium' : 'Low');
        $newsRisk = ($sentiment['negative_pct'] > 50) ? 'High' : (($sentiment['negative_pct'] > 20) ? 'Medium' : 'Low');
        $currRisk = abs($currencyChange) > 1 ? 'High' : (abs($currencyChange) > 0.5 ? 'Medium' : 'Low');

        return [
            'id' => $country->id,
            'name' => $country->name,
            'flag' => $country->flag_url ?? '',
            'currency_code' => $country->currency_code,
            'gdp' => round($gdp / 1000000000000, 2), 
            'inflation' => $inflation,
            'weather' => $weather,
            'currency' => [
                'rate' => $currencyRate,
                'change' => $currencyChange,
                'history' => $currencyHistory
            ],
            'sentiment' => $sentiment,
            'risk' => [
                'overall' => $overall,
                'overall_label' => $overall_label,
                'factors' => [
                    'weather' => $weatherRisk,
                    'economic' => $ecoRisk,
                    'news' => $newsRisk,
                    'currency' => $currRisk
                ]
            ]
        ];
    }

    private function getWeatherDesc($code) {
        $map = [
            0 => 'Clear sky',
            1 => 'Mainly clear', 2 => 'Partly cloudy', 3 => 'Overcast',
            45 => 'Fog', 48 => 'Depositing rime fog',
            51 => 'Light drizzle', 53 => 'Moderate drizzle', 55 => 'Dense drizzle',
            61 => 'Slight rain', 63 => 'Moderate rain', 65 => 'Heavy rain',
            71 => 'Slight snow', 73 => 'Moderate snow', 75 => 'Heavy snow',
            95 => 'Thunderstorm'
        ];
        return $map[$code] ?? 'Unknown';
    }

    private function generateRecommendation($dataA, $dataB)
    {
        $overallText = "";
        if ($dataA['risk']['overall'] < $dataB['risk']['overall']) {
            $overallText = __(':nameA currently has lower overall risk compared to :nameB.', ['nameA' => $dataA['name'], 'nameB' => $dataB['name']]);
        } elseif ($dataA['risk']['overall'] > $dataB['risk']['overall']) {
            $overallText = __(':nameA currently has lower overall risk compared to :nameB.', ['nameA' => $dataB['name'], 'nameB' => $dataA['name']]);
        } else {
            $overallText = __('Both :nameA and :nameB share a similar overall risk profile.', ['nameA' => $dataA['name'], 'nameB' => $dataB['name']]);
        }

        $insights = [];
        if ($dataA['inflation'] < $dataB['inflation']) {
            $insights[] = __('Inflation in :nameA is more stable (:infA%) than :nameB (:infB%).', ['nameA' => $dataA['name'], 'infA' => $dataA['inflation'], 'nameB' => $dataB['name'], 'infB' => $dataB['inflation']]);
        } else {
            $insights[] = __('Inflation in :nameA is more stable (:infA%) than :nameB (:infB%).', ['nameA' => $dataB['name'], 'infA' => $dataB['inflation'], 'nameB' => $dataA['name'], 'infB' => $dataA['inflation']]);
        }

        if ($dataA['gdp'] > $dataB['gdp']) {
            $insights[] = __(':nameA has a larger economic size (:gdpAT USD) compared to :nameB.', ['nameA' => $dataA['name'], 'gdpA' => $dataA['gdp'], 'nameB' => $dataB['name']]);
        } else {
            $insights[] = __(':nameA has a larger economic size (:gdpAT USD) compared to :nameB.', ['nameA' => $dataB['name'], 'gdpA' => $dataB['gdp'], 'nameB' => $dataA['name']]);
        }

        if ($dataA['sentiment']['negative_pct'] > $dataB['sentiment']['negative_pct']) {
            $insights[] = __(':nameA has higher negative news sentiment (:pct%) which may indicate logistics issues.', ['nameA' => $dataA['name'], 'pct' => $dataA['sentiment']['negative_pct']]);
        } elseif ($dataB['sentiment']['negative_pct'] > $dataA['sentiment']['negative_pct']) {
            $insights[] = __(':nameA has higher negative news sentiment (:pct%) which may indicate logistics issues.', ['nameA' => $dataB['name'], 'pct' => $dataB['sentiment']['negative_pct']]);
        }

        $safer = $dataA['risk']['overall'] < $dataB['risk']['overall'] ? $dataA['name'] : $dataB['name'];
        $riskier = $dataA['risk']['overall'] < $dataB['risk']['overall'] ? $dataB['name'] : $dataA['name'];
        
        $recommendation = __('If your supply chain involves high-value shipments, :safer currently offers lower operational risk. Continue monitoring :riskier logistics conditions due to their risk factors.', ['safer' => $safer, 'riskier' => $riskier]);

        return [
            'overall' => $overallText,
            'insights' => $insights,
            'recommendation' => $recommendation
        ];
    }
}