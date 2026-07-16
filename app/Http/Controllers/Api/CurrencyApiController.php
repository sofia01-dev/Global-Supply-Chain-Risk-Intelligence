<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\CurrencyApiService;

class CurrencyApiController extends Controller
{
    protected $currencyApiService;

    public function __construct(CurrencyApiService $currencyApiService)
    {
        $this->currencyApiService = $currencyApiService;
    }

    public function index()
    {
        $data = $this->currencyApiService->getAllCurrencies();
        
        if ($data->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'No data available.', 'data' => []]);
        }
        return response()->json(['success' => true, 'message' => 'Data retrieved successfully.', 'data' => $data]);
    }
}