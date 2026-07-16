<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Shipment\ShipmentService;
use App\Services\Shipment\ShipmentRouteService;
use App\Services\Shipment\ShipmentMonitoringService;

class ShipmentController extends Controller
{
    protected $shipmentService, $routeService, $monitoringService, $recommendationService;

    public function __construct(
        ShipmentService $shipmentService,
        ShipmentRouteService $routeService,
        ShipmentMonitoringService $monitoringService
    ) {
        $this->shipmentService = $shipmentService;
        $this->routeService = $routeService;
        $this->monitoringService = $monitoringService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $filters = $request->only(['search', 'status', 'risk_level']);
        
        // Paginated results
        $shipments = $this->shipmentService->getAllForUser($filters);
        
        // Map monitoring data to items
        $shipments->getCollection()->transform(function($shipment) {
            $shipment->monitoring = $this->monitoringService->monitor($shipment);
            return $shipment;
        });

        // Countries list for filter dropdowns
        $countries = \App\Models\Country::orderBy('name')->get();

        return view('user.shipments.index', compact('shipments', 'filters', 'countries'));
    }

    public function show($id)
    {
        $shipment = $this->shipmentService->getByIdForUser($id);
        
        $mapData = $this->routeService->getMapData($shipment);
        
        // Single Source of Truth Monitoring Object
        $monitorObj = $this->monitoringService->monitor($shipment);
        
        // Retain backward compatibility structures for Blade views
        $legacyMonitoringData = $this->monitoringService->calculateMonitoring($shipment);
        $monitoringData = array_merge($legacyMonitoringData, $monitorObj);
        
        $dynamicData = [
            'risk_info' => [
                'score' => $monitorObj['risk_score'],
                'level' => $monitorObj['risk_level'],
            ],
            'recommendations' => [$monitorObj['recommendation']]
        ];

        return view('user.shipments.show', compact('shipment', 'mapData', 'monitoringData', 'dynamicData', 'monitorObj'));
    }

    public function create()
    {
        $ports = \App\Models\Port::with('country')->get();
        return view('user.shipments.create', compact('ports'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'origin_port_id' => 'required|exists:ports,id',
            'destination_port_id' => 'required|exists:ports,id|different:origin_port_id',
            'departure_date' => 'nullable|date',
            'estimated_arrival' => 'nullable|date|after_or_equal:departure_date',
            'current_status' => 'required|string',
        ]);

        $this->shipmentService->createShipment($data);

        return redirect()->route('user.shipments.index')->with('success', 'Shipment created successfully.');
    }

    public function edit($id)
    {
        $shipment = $this->shipmentService->getByIdForUser($id);
        $ports = \App\Models\Port::with('country')->get();
        return view('user.shipments.edit', compact('shipment', 'ports'));
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $data = $request->validate([
            'origin_port_id' => 'required|exists:ports,id',
            'destination_port_id' => 'required|exists:ports,id|different:origin_port_id',
            'departure_date' => 'nullable|date',
            'estimated_arrival' => 'nullable|date|after_or_equal:departure_date',
            'current_status' => 'required|string',
        ]);

        $this->shipmentService->updateShipment($id, $data);

        return redirect()->route('user.shipments.index')->with('success', 'Shipment updated successfully.');
    }

    public function destroy($id)
    {
        $this->shipmentService->deleteShipment($id);
        return redirect()->route('user.shipments.index')->with('success', 'Shipment deleted successfully.');
    }
}