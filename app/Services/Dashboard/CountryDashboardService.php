<?php
namespace App\Services\Dashboard;

use App\Models\Country;
use App\Models\RiskScore;
use App\Models\NewsCache;

class CountryDashboardService
{
    public function getAllCountriesList($search = null)
    {
        $query = Country::with(['riskScores' => function($q) {
            $q->latest();
        }]);

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->orderBy('name')->get();
    }

    public function getCountryDetail($id)
    {
        $country = Country::with(['weatherCaches', 'newsCaches', 'economicIndicator', 'riskScores'])->find($id);
        if (!$country) return null;

        // Fetch currency cache
        $currency = null;
        if ($country->currency_code) {
            $currency = \App\Models\CurrencyCache::where('currency_code', $country->currency_code)->first();
        }
        $country->currentCurrency = $currency;

        // Fetch fallback news analysis for sentiment (if we want to mimic the old sentiment_analysis shape)
        if ($country->newsCaches && $country->newsCaches->isNotEmpty()) {
            $newsList = $country->newsCaches;
        } else {
            $newsList = NewsCache::latest()->take(5)->get();
        }
        
        $country->globalNewsFallback = $newsList;

        $riskScore = $country->riskScores->first();
        
        $riskData = [
            'score' => $riskScore ? $riskScore->final_score : 0,
            'level' => $riskScore ? $riskScore->risk_level . ' Risk' : 'No Data',
            'category' => 'General',
            'delay_reasons' => ['System Automated Audit'],
            'sentiment_analysis' => ['sentiment' => 'Neutral', 'positive_pct' => 0, 'negative_pct' => 0, 'neutral_pct' => 100]
        ];

        // Fetch fallback news analysis for sentiment (if we want to mimic the old sentiment_analysis shape)
        if ($country->newsCaches->isNotEmpty()) {
            $newsList = $country->newsCaches;
        } else {
            $newsList = NewsCache::whereNull('country_id')->latest()->take(5)->get();
        }
        
        if ($newsList->isNotEmpty()) {
            $pos = $newsList->avg('positive_percentage') ?? 0;
            $neg = $newsList->avg('negative_percentage') ?? 0;
            $neu = $newsList->avg('neutral_percentage') ?? 0;
            $sentiment = $pos > $neg ? 'Positive' : ($neg > $pos ? 'Negative' : 'Neutral');
            
            $riskData['sentiment_analysis'] = [
                'sentiment' => $sentiment,
                'positive_pct' => round($pos, 2),
                'negative_pct' => round($neg, 2),
                'neutral_pct' => round($neu, 2)
            ];
        }

        $country->riskData = $riskData;

        return $country;
    }
}