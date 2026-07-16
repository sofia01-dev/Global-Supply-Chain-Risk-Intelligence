<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\NewsApiService;

class NewsApiController extends Controller
{
    protected $newsApiService;

    public function __construct(NewsApiService $newsApiService)
    {
        $this->newsApiService = $newsApiService;
    }

    public function index()
    {
        $data = $this->newsApiService->getAllNews();
        
        if ($data->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'No data available.', 'data' => []]);
        }
        return response()->json(['success' => true, 'message' => 'Data retrieved successfully.', 'data' => $data]);
    }
}