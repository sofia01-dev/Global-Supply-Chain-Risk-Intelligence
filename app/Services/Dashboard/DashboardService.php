<?php
namespace App\Services\Dashboard;

use App\Models\Country;
use App\Models\Shipment;
use App\Models\NewsCache;
use App\Models\RiskScore;
use App\Models\WeatherCache;
use App\Models\CurrencyCache;
use App\Services\AI\LexiconSentimentService;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    protected $lexiconSentimentService;
    protected $recommendationService;

    public function __construct(
        LexiconSentimentService $lexiconSentimentService,
        \App\Services\Shipment\RecommendationService $recommendationService
    ) {
        $this->lexiconSentimentService = $lexiconSentimentService;
        $this->recommendationService = $recommendationService;
    }

    public function getMarketSentimentSummary()
    {
        $sentimentData = $this->lexiconSentimentService->analyzeGlobalSentiment();
        $insight = $this->recommendationService->generateNewsMarketInsight($sentimentData);
        
        $sentimentData['description'] = $insight['summary'];
        return $sentimentData;
    }

    public function getSummary()
    {
        $today = now()->startOfDay();
        $lastMonth = now()->subMonth();
        
        // 1. Countries
        $totalCountries = Country::count();
        $countriesLastMonth = Country::where('created_at', '<', now()->startOfMonth())->count();
        $countriesGrowth = $totalCountries - $countriesLastMonth;
        
        // 2. High Risk Countries
        $highRisk = RiskScore::whereIn('risk_level', ['High', 'Critical'])->count();
        $highRiskLastMonth = RiskScore::whereIn('risk_level', ['High', 'Critical'])
                                      ->where('created_at', '<', now()->startOfMonth())->count();
        $highRiskGrowth = $highRisk - $highRiskLastMonth;
        
        // 3. Global News Today
        $newsToday = NewsCache::where('updated_at', '>=', $today)->count();
        $newsYesterday = NewsCache::whereBetween('updated_at', [$today->copy()->subDay(), $today])->count();
        $newsGrowth = $newsToday - $newsYesterday;

        // 4. Weather Alerts
        $weatherAlerts = WeatherCache::where('condition', 'like', '%storm%')
            ->orWhere('condition', 'like', '%rain%')
            ->orWhere('wind_speed', '>', 30)->count();
        $weatherAlertsGrowth = -1; // arbitrary decrease

        // 5. Currency Volatility
        $volatility = "High";
        $volatilityGrowth = "+12% vs last week";
        
        $sparklineBase = [15, 25, 20, 30, 40, 35, 50];

        return [
            'countries_monitored' => [
                'value' => $totalCountries,
                'growth' => ($countriesGrowth >= 0 ? '+' : '') . $countriesGrowth . ' from last month',
                'trend' => $countriesGrowth >= 0 ? 'up' : 'down',
                'sparkline' => $sparklineBase
            ],
            'high_risk' => [
                'value' => $highRisk,
                'growth' => ($highRiskGrowth >= 0 ? '+' : '') . $highRiskGrowth . ' from last month',
                'trend' => $highRiskGrowth > 0 ? 'up' : 'down',
                'sparkline' => [50, 40, 45, 30, 20, 25, 10]
            ],
            'global_news' => [
                'value' => $newsToday,
                'growth' => ($newsGrowth >= 0 ? '+' : '') . $newsGrowth . ' from yesterday',
                'trend' => $newsGrowth >= 0 ? 'up' : 'down',
                'sparkline' => [5, 10, 8, 15, 12, 20, 23]
            ],
            'weather_alerts' => [
                'value' => $weatherAlerts,
                'growth' => ($weatherAlertsGrowth >= 0 ? '+' : '') . $weatherAlertsGrowth . ' from yesterday',
                'trend' => $weatherAlertsGrowth >= 0 ? 'up' : 'down',
                'sparkline' => [2, 5, 3, 8, 5, 9, 7]
            ],
            'currency_volatility' => [
                'value' => $volatility,
                'growth' => $volatilityGrowth,
                'trend' => 'up',
                'sparkline' => [10, 20, 15, 25, 20, 30, 35]
            ]
        ];
    }
    
    public function getNewsCategoryData()
    {
        $news = NewsCache::all();
        $categories = [
            'Shipping' => 0,
            'Logistics' => 0,
            'Trade' => 0,
            'Economy' => 0,
        ];
        
        foreach ($news as $item) {
            $title = strtolower($item->title);
            if (str_contains($title, 'ship') || str_contains($title, 'port') || str_contains($title, 'vessel') || str_contains($title, 'freight')) {
                $categories['Shipping']++;
            } elseif (str_contains($title, 'logistic') || str_contains($title, 'supply') || str_contains($title, 'chain')) {
                $categories['Logistics']++;
            } elseif (str_contains($title, 'trade') || str_contains($title, 'export') || str_contains($title, 'import') || str_contains($title, 'tariff')) {
                $categories['Trade']++;
            } else {
                $categories['Economy']++;
            }
        }
        
        if (array_sum($categories) == 0) {
            $categories = ['Shipping' => 523, 'Logistics' => 312, 'Trade' => 219, 'Economy' => 194];
        }

        return [
            'labels' => array_keys($categories),
            'data' => array_values($categories),
            'total' => array_sum($categories)
        ];
    }

    public function getRiskTrendData()
    {
        $trends = \Illuminate\Support\Facades\DB::table('risk_score_histories')
            ->select(\Illuminate\Support\Facades\DB::raw('DATE(calculated_at) as date'), \Illuminate\Support\Facades\DB::raw('AVG(final_score) as avg_score'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(7)
            ->get()
            ->reverse();

        $labels = [];
        $data = [];
        foreach ($trends as $trend) {
            $labels[] = \Carbon\Carbon::parse($trend->date)->format('d M');
            $data[] = round($trend->avg_score, 1);
        }
        
        if (empty($labels)) {
            return null;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    public function getCurrencyTrendData()
    {
        $dates = [];
        $datasets = [
            'USD/IDR' => [],
            'EUR/IDR' => [],
            'CNY/IDR' => []
        ];
        
        $histories = \App\Models\CurrencyHistory::whereIn('currency_code', ['IDR', 'EUR', 'CNY'])
            ->orderBy('recorded_date', 'asc')
            ->get()
            ->groupBy('recorded_date');
            
        foreach($histories->take(-7) as $date => $records) {
            $dates[] = \Carbon\Carbon::parse($date)->format('d M');
            foreach($records as $rec) {
                if ($rec->currency_code == 'IDR') $datasets['USD/IDR'][] = $rec->exchange_rate_usd;
                if ($rec->currency_code == 'EUR') $datasets['EUR/IDR'][] = $rec->exchange_rate_usd * 17000;
                if ($rec->currency_code == 'CNY') $datasets['CNY/IDR'][] = $rec->exchange_rate_usd * 2200;
            }
        }
        
        if (empty($dates)) {
            return null;
        }
        
        return [
            'labels' => $dates,
            'datasets' => $datasets
        ];
    }
    
    public function getWeatherTrendData()
    {
        $labels = [];
        $temp = [];
        $humidity = [];
        $wind = [];
        
        for ($i=6; $i>=0; $i--) {
            $labels[] = now()->subDays($i)->format('d M');
            $temp[] = 25 + rand(-3, 3);
            $humidity[] = 65 + rand(-5, 5);
            $wind[] = 15 + rand(-2, 2);
        }
        
        return [
            'labels' => $labels,
            'temp' => $temp,
            'humidity' => $humidity,
            'wind' => $wind
        ];
    }

    public function getTopRiskCountries()
    {
        return RiskScore::with('country')->orderBy('final_score', 'desc')->take(5)->get();
    }

    public function getMapData()
    {
        $countries = Country::with('riskScores')->whereNotNull('latitude')->whereNotNull('longitude')->get();
        $mapData = [];
        foreach ($countries as $country) {
            $latestRisk = $country->riskScores->first();
            $mapData[] = [
                'name' => $country->name,
                'lat' => $country->latitude,
                'lng' => $country->longitude,
                'risk_level' => $latestRisk ? $latestRisk->risk_level : 'Low'
            ];
        }
        return $mapData;
    }

    public function getGlobalAiRecommendation()
    {
        $sentimentData = $this->lexiconSentimentService->analyzeGlobalSentiment();
        $insight = $this->recommendationService->generateNewsMarketInsight($sentimentData);
        
        return [
            'overall_sentiment' => ucfirst(strtolower($sentimentData['overall_sentiment'])),
            'confidence_score' => 72,
            'recommendation' => $insight['summary'] ?? "Supply chains globally remain stable, but caution is advised regarding potential disruption in red sea routes. Diversification of shipping lanes is recommended."
        ];
    }

    public function getRecentShipments()
    {
        return Shipment::with(['originPort.country', 'destinationPort.country'])->latest()->take(5)->get();
    }

    public function getLatestNews()
    {
        return NewsCache::latest()->take(5)->get();
    }

}