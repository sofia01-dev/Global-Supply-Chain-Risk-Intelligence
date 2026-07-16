<?php
namespace App\Services\Dashboard;
use App\Models\Country;

class ComparisonService
{
    public function getComparisonData(array $countryIds)
    {
        if (empty($countryIds)) {
            return collect();
        }
        return Country::with('economicIndicator')->whereIn('id', $countryIds)->get();
    }
}