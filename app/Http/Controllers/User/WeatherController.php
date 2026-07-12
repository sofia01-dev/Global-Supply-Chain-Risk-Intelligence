<?php
namespace App\Http\Controllers\User;
use App\Http\Controllers\Controller;
use App\Services\Api\WeatherApiService;

class WeatherController extends Controller
{
    protected $weatherApiService;
    public function __construct(WeatherApiService $weatherApiService) {
        $this->weatherApiService = $weatherApiService;
    }
    public function index() {
        $weatherData = $this->weatherApiService->getAllWeather();
        return view('user.weather', compact('weatherData'));
    }
}