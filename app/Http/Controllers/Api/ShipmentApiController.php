<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Services\Shipment\ShipmentMonitoringService;
use Illuminate\Http\Request;

class ShipmentApiController extends Controller
{
    protected $monitoringService;

    public function __construct(ShipmentMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function index()
    {
        $shipments = Shipment::with(['originPort.country', 'destinationPort.country', 'histories'])->get();
        
        $data = $shipments->map(function ($shipment) {
            return $this->monitoringService->monitor($shipment);
        });

        return response()->json([
            'success' => true,
            'message' => 'Shipments retrieved successfully',
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $shipment = Shipment::with(['originPort.country', 'destinationPort.country', 'histories'])->find($id);

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found'
            ], 404);
        }

        $data = $this->monitoringService->monitor($shipment);

        return response()->json([
            'success' => true,
            'message' => 'Shipment details retrieved successfully',
            'data' => $data
        ]);
    }
}
