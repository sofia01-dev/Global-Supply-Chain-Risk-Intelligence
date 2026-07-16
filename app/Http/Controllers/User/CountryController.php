<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\CountryDashboardService;
use App\Services\Shipment\RecommendationService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected $countryDashboardService;
    protected $recommendationService;

    public function __construct(CountryDashboardService $countryDashboardService, RecommendationService $recommendationService) {
        $this->countryDashboardService = $countryDashboardService;
        $this->recommendationService = $recommendationService;
    }

    public function index(Request $request) {
        $search = $request->query('search');
        $countries = $this->countryDashboardService->getAllCountriesList($search);
        
        $country = null;
        $aiRecommendation = null;

        if ($countries->isNotEmpty()) {
            $country = $this->countryDashboardService->getCountryDetail($countries->first()->id);
            if ($country && isset($country->riskData)) {
                $recMessage = $this->recommendationService->generateRecommendation(
                    str_replace(' Risk', '', $country->riskData['level'])
                );
                $aiRecommendation = [
                    'status' => $country->riskData['level'],
                    'message' => __('Based on current conditions: ') . $recMessage,
                    'details' => [
                        __('Weather conditions are stable'),
                        __('Economic indicators are within normal range'),
                        __('News sentiment is :sentiment', ['sentiment' => __($country->riskData['sentiment_analysis']['sentiment'] ?? 'Neutral')])
                    ]
                ];
            }
        }
        
        return view('user.country', compact('countries', 'country', 'aiRecommendation', 'search'));
    }

    public function show($id) {
        $search = request()->query('search');
        $countries = $this->countryDashboardService->getAllCountriesList($search);
        $country = $this->countryDashboardService->getCountryDetail($id);
        
        $aiRecommendation = null;
        if ($country && isset($country->riskData)) {
            $recMessage = $this->recommendationService->generateRecommendation(
                str_replace(' Risk', '', $country->riskData['level'])
            );
            $aiRecommendation = [
                'status' => $country->riskData['level'],
                'message' => __('Based on current conditions: ') . $recMessage,
                'details' => [
                    __('Weather is monitored'),
                    __('Economic parameters are tracked'),
                    __('News sentiment is :sentiment', ['sentiment' => __($country->riskData['sentiment_analysis']['sentiment'] ?? 'Neutral')])
                ]
            ];
        }

        return view('user.country', compact('countries', 'country', 'aiRecommendation', 'search'));
    }
}