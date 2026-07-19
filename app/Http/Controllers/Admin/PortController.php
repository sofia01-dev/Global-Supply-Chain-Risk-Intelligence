<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\Port\PortService;

class PortController extends Controller
{
    protected $portService;

    public function __construct(PortService $portService)
    {
        $this->portService = $portService;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\Port::with('country');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('unlocode', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('country_id') && $request->country_id !== 'all') {
            $query->where('country_id', $request->country_id);
        }

        $ports = $query->paginate(10);
        $countries = \App\Models\Country::orderBy('name')->get();

        // KPIs
        $totalPorts = \App\Models\Port::count();
        $totalCountries = \App\Models\Country::count();
        $portsWithShipments = \App\Models\Shipment::distinct('origin_port_id')->count('origin_port_id');

        return view('admin.ports.index', compact('ports', 'countries', 'totalPorts', 'totalCountries', 'portsWithShipments'));
    }

    public function create()
    {
        $countries = \App\Models\Country::orderBy('name')->get();
        return view('admin.ports.create', compact('countries'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unlocode' => 'required|string|max:10|unique:ports',
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        \App\Models\Port::create([
            'name' => $request->name,
            'unlocode' => strtoupper($request->unlocode),
            'country_id' => $request->country_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('admin.ports.index')->with('success', 'Port added successfully.');
    }

    public function edit(\App\Models\Port $port)
    {
        $countries = \App\Models\Country::orderBy('name')->get();
        return view('admin.ports.edit', compact('port', 'countries'));
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Port $port)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unlocode' => 'required|string|max:10|unique:ports,unlocode,'.$port->id,
            'country_id' => 'required|exists:countries,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $port->update([
            'name' => $request->name,
            'unlocode' => strtoupper($request->unlocode),
            'country_id' => $request->country_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('admin.ports.index')->with('success', 'Port updated successfully.');
    }

    public function destroy(\App\Models\Port $port)
    {
        // Check if port has associated shipments
        if ($port->shipmentsAsOrigin()->count() > 0 || $port->shipmentsAsDestination()->count() > 0) {
            return redirect()->route('admin.ports.index')->with('error', 'Cannot delete this port because it has associated shipment data.');
        }

        $port->delete();
        return redirect()->route('admin.ports.index')->with('success', 'Port deleted successfully.');
    }
}