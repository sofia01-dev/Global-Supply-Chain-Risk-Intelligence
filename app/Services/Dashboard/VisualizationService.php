<?php
namespace App\Services\Dashboard;
use App\Models\Country;
use App\Models\CurrencyCache;

class VisualizationService
{
    public function getGdpTrend() { return collect(); }
    public function getInflationTrend() { return collect(); }
    public function getCurrencyTrend() { return CurrencyCache::latest()->take(30)->get(); }
    public function getRiskTrend() { return collect(); }
}