<?php
namespace App\Services\Dashboard;
use App\Models\Country;

class CountryDashboardService
{
    public function getCountryDetail($id)
    {
        return Country::with(['riskScores', 'weatherCaches', 'newsCaches'])->find($id);
    }
}