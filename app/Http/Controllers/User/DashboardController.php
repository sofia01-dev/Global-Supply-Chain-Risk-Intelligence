<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\Services\Shipment\ShipmentMonitoringService;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $monitoringService;

    public function __construct(DashboardService $dashboardService, ShipmentMonitoringService $monitoringService)
    {
        $this->dashboardService = $dashboardService;
        $this->monitoringService = $monitoringService;
    }

    public function index()
    {
        // Fitur Pembaruan Otomatis (Hanya untuk Development):
        // Cek apakah data historis tertinggal dari tanggal hari ini. Jika ya, jalankan ulang seeder.
        $lastRiskDate = \Illuminate\Support\Facades\DB::table('risk_score_histories')->max('calculated_at');
        if (!$lastRiskDate || \Carbon\Carbon::parse($lastRiskDate)->startOfDay()->lt(now()->startOfDay())) {
            \Illuminate\Support\Facades\Artisan::call('db:seed', [
                '--class' => 'DashboardHistoricalSeeder',
                '--force' => true
            ]);
        }

        $recentShipments = $this->dashboardService->getRecentShipments()->map(function($shipment) {
            $monitorObj = $this->monitoringService->monitor($shipment);
            $shipment->monitoring = $monitorObj;
            return $shipment;
        });

        // Dapatkan 10 pengiriman aktif berisiko teratas untuk Modal Rekomendasi AI.
        $aiRecommendationsList = \App\Models\Shipment::whereNotIn('current_status', ['Delivered', 'Cancelled'])
            ->get()
            ->map(function($shipment) {
                return $this->monitoringService->monitor($shipment);
            })
            ->sortByDesc('risk_score')
            ->take(10)
            ->values()
            ->all();

        return view('user.dashboard', [
            'summary' => $this->dashboardService->getSummary(),
            'recentShipments' => $recentShipments,
            'topRiskCountries' => $this->dashboardService->getTopRiskCountries(),
            'latestNews' => $this->dashboardService->getLatestNews(),
            'riskTrendData' => $this->dashboardService->getRiskTrendData(),
            'currencyTrendData' => $this->dashboardService->getCurrencyTrendData(),
            'weatherTrendData' => $this->dashboardService->getWeatherTrendData(),
            'gdpTrendData' => $this->dashboardService->getGdpTrendData(),
            'inflationTrendData' => $this->dashboardService->getInflationTrendData(),
            'newsCategoryData' => $this->dashboardService->getNewsCategoryData(),
            'mapData' => $this->dashboardService->getMapData(),
            'aiRecommendation' => $this->dashboardService->getGlobalAiRecommendation(),
            'aiRecommendationsList' => $aiRecommendationsList,
            'marketSentiment' => $this->dashboardService->getMarketSentimentSummary(),
            'adminArticles' => \App\Models\Article::where('is_published', true)->latest()->take(4)->get(),
        ]);
    }

    public function syncData()
    {
        return response()->json([
            'summary' => $this->dashboardService->getSummary(),
            'topRiskCountries' => $this->dashboardService->getTopRiskCountries(),
            'latestNews' => $this->dashboardService->getLatestNews(),
            'riskTrendData' => $this->dashboardService->getRiskTrendData(),
            'currencyTrendData' => $this->dashboardService->getCurrencyTrendData(),
            'weatherTrendData' => $this->dashboardService->getWeatherTrendData(),
            'gdpTrendData' => $this->dashboardService->getGdpTrendData(),
            'inflationTrendData' => $this->dashboardService->getInflationTrendData(),
            'newsCategoryData' => $this->dashboardService->getNewsCategoryData(),
            'mapData' => $this->dashboardService->getMapData(),
            'aiRecommendation' => $this->dashboardService->getGlobalAiRecommendation(),
            'timestamp' => now()->format('d M Y H:i:s') . ' WIB'
        ]);
    }
}