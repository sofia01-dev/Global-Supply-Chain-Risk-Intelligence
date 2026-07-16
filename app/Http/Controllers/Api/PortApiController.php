<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Port\PortService;

class PortApiController extends Controller
{
    protected $portService;

    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    public function index()
    {
        $data = $this->portService->getAllPorts();
        
        if ($data->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'No data available.', 'data' => []]);
        }
        return response()->json(['success' => true, 'message' => 'Data retrieved successfully.', 'data' => $data]);
    }
}