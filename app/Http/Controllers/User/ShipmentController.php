<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\Shipment\ShipmentService;
use App\Services\Shipment\ShipmentMonitoringService;

class ShipmentController extends Controller
{
    protected $shipmentService, $monitoringService, $recommendationService;

    public function __construct(
        ShipmentService $shipmentService,
        ShipmentMonitoringService $monitoringService
    ) {
        $this->shipmentService = $shipmentService;
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

    public function show($id, \Illuminate\Http\Request $request)
    {
        $shipment = $this->shipmentService->getByIdForUser($id);
        
        $mapData = [];
        if ($shipment->originPort) {
            $mapData[] = ['type' => 'Origin', 'name' => $shipment->originPort->name, 'lat' => $shipment->originPort->latitude, 'lng' => $shipment->originPort->longitude];
        }
        if ($shipment->destinationPort) {
            $mapData[] = ['type' => 'Destination', 'name' => $shipment->destinationPort->name, 'lat' => $shipment->destinationPort->latitude, 'lng' => $shipment->destinationPort->longitude];
        }
        
        // Single Source of Truth Monitoring Object
        $monitorObj = $this->monitoringService->monitor($shipment);
        
        // Check if AJAX request for auto-refresh
        if ($request->ajax()) {
            return response()->json([
                'monitorObj' => $monitorObj
            ]);
        }
        
        return view('user.shipments.show', compact('shipment', 'mapData', 'monitorObj'));
    }

    public function create()
    {
        $countries = \App\Models\Country::orderBy('name')->get();
        // Fallback for non-ajax loading
        $ports = \App\Models\Port::with('country')->get();
        return view('user.shipments.create', compact('ports', 'countries'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'shipment_name' => 'required|string|max:255',
            'goods' => 'required|string|max:255',
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
        $countries = \App\Models\Country::orderBy('name')->get();
        // Fallback for non-ajax loading, load all ports
        $ports = \App\Models\Port::with('country')->get();
        return view('user.shipments.edit', compact('shipment', 'ports', 'countries'));
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

    public function getPortsByCountry($country_id)
    {
        $ports = \App\Models\Port::where('country_id', $country_id)->orderBy('name')->get();
        return response()->json($ports);
    }
}