<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\CountryDashboardService;

class CountryController extends Controller
{
    protected $countryDashboardService;
    public function __construct(CountryDashboardService $countryDashboardService) {
        $this->countryDashboardService = $countryDashboardService;
    }
    public function show($id) {
        $country = $this->countryDashboardService->getCountryDetail($id);
        return view('user.country', compact('country'));
    }
}