<?php
namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Country;
use App\Models\Port;
use App\Models\Shipment;
use App\Models\Article;
use App\Models\NewsCache;

class DashboardService
{
    public function getAdminSummary(): array
    {
        return [
            'total_users' => User::count(),
            'total_countries' => Country::count(),
            'total_ports' => Port::count(),
            'total_shipments' => Shipment::count(),
            'total_articles' => Article::count(),
        ];
    }

    public function getUserSummary(): array
    {
        return [
            'total_countries_monitored' => Country::count(),
            'active_shipments' => Shipment::whereNotIn('current_status', ['arrived', 'cancelled'])->count(),
            'high_risk_countries' => 0, // Placeholder mapping to risk_scores later
            'latest_global_news' => NewsCache::latest('published_at')->take(5)->get(),
        ];
    }
}