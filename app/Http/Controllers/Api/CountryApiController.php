<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\CountryApiService;

class CountryApiController extends Controller
{
    protected $countryApiService;

    public function __construct(CountryApiService $countryApiService)
    {
        $this->countryApiService = $countryApiService;
    }

    public function index()
    {
        $data = $this->countryApiService->getAllCountries();
        
        if ($data->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'No data available.', 'data' => []]);
        }
        return response()->json(['success' => true, 'message' => 'Data retrieved successfully.', 'data' => $data]);
    }
}