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

        $weatherAlert = ['type' => 'success', 'title' => __('No Alert'), 'message' => __('Weather is calm.')];
        if ($country && $country->weatherCaches->isNotEmpty()) {
            $weather = $country->weatherCaches->first();
            $code = $weather->raw_data['current']['weather_code'] ?? 0;
            
            if ($code >= 95 && $code <= 99) {
                $weatherAlert = ['type' => 'warning', 'title' => __('Thunderstorm'), 'message' => __('Thunderstorms possible in some areas')];
            } elseif (in_array($code, [63, 65, 66, 67, 81, 82])) {
                $weatherAlert = ['type' => 'danger', 'title' => __('Heavy Rain Warning'), 'message' => __('Heavy rainfall expected in several regions')];
            } elseif ($weather->wind_speed > 40) {
                $weatherAlert = ['type' => 'warning', 'title' => __('Strong Wind'), 'message' => __('Strong winds up to :speed km/h expected', ['speed' => ceil($weather->wind_speed)])];
            }
        }

        return view('user.weather', compact('countries', 'country', 'weatherAlert', 'search'));
    }
}