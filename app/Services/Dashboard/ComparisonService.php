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
        return Country::whereIn('id', $countryIds)->get();
    }
}