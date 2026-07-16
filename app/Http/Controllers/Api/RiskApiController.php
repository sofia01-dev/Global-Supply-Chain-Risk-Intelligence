<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiskScore;

class RiskApiController extends Controller
{
    public function index()
    {
        // Fetch all current snapshots with eager loaded country data
        $data = RiskScore::with('country')->get();
        if ($data->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'No data available.', 'data' => []]);
        }
        return response()->json(['success' => true, 'message' => 'Data retrieved successfully.', 'data' => $data]);
    }
}