<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Api\WeatherApiService;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    protected $weatherApiService;
    public function __construct(WeatherApiService $weatherApiService) {
        $this->weatherApiService = $weatherApiService;
    }
    public function index(Request $request) {
        $search = $request->query('search');
        $countries = $this->weatherApiService->getAllWeatherWithCountries($search);
        
        $country = null;
        if ($request->has('country_id')) {
            $country = $countries->where('id', $request->country_id)->first();
        }
        
        if (!$country && $countries->isNotEmpty()) {
            $country = $countries->first();
        }

        $weatherAlerts = [];
        if ($country && $country->weatherCaches->isNotEmpty()) {
            $weather = $country->weatherCaches->first();
            $code = $weather->raw_data['current']['weather_code'] ?? 0;
            
            if (in_array($code, [63, 65, 66, 67, 81, 82])) {
                $weatherAlerts[] = ['type' => 'danger', 'icon' => 'bi-exclamation-triangle-fill', 'title' => __('Heavy Rain Warning'), 'message' => __('Heavy rainfall expected in several regions'), 'badge' => 'High'];
            }
            if ($weather->wind_speed > 40) {
                $weatherAlerts[] = ['type' => 'warning', 'icon' => 'bi-wind', 'title' => __('Strong Wind'), 'message' => __('Strong winds up to :speed km/h expected', ['speed' => ceil($weather->wind_speed)]), 'badge' => 'Medium'];
            }
            if ($code >= 95 && $code <= 99) {
                $weatherAlerts[] = ['type' => 'warning', 'icon' => 'bi-lightning-fill', 'title' => __('Thunderstorm'), 'message' => __('Thunderstorms possible in some areas'), 'badge' => 'Medium'];
            }
        }
        
        $weatherAlerts[] = ['type' => 'success', 'icon' => 'bi-check-circle-fill', 'title' => __('No Flood Alert'), 'message' => __('No flood risk in your area'), 'badge' => 'Low'];
        
        if (count($weatherAlerts) === 1) { // Only No Flood Alert
            array_unshift($weatherAlerts, ['type' => 'success', 'icon' => 'bi-check-circle-fill', 'title' => __('No Alert'), 'message' => __('Weather is calm.'), 'badge' => 'Low']);
        }

        return view('user.weather', compact('countries', 'country', 'weatherAlerts', 'search'));
    }
}