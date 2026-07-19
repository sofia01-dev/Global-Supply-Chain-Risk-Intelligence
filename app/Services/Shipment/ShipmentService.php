<?php
namespace App\Services\Shipment;

use App\Models\Shipment;
use Illuminate\Support\Facades\Auth;

class ShipmentService
{
    public function getAllForUser($filters = [])
    {
        $query = Shipment::with(['originPort.country', 'destinationPort.country'])
            ->where('user_id', Auth::id());

        // 1. Filter by Status
        if (!empty($filters['status']) && $filters['status'] !== 'All') {
            $query->where('current_status', $filters['status']);
        }

        // 2. Filter by Origin Country
        if (!empty($filters['origin_country_id'])) {
            $query->whereHas('originPort', function($q) use ($filters) {
                $q->where('country_id', $filters['origin_country_id']);
            });
        }

        // 3. Filter by Destination Country
        if (!empty($filters['destination_country_id'])) {
            $query->whereHas('destinationPort', function($q) use ($filters) {
                $q->where('country_id', $filters['destination_country_id']);
            });
        }

        // 4. Filter by Risk Level (Destination Country)
        if (!empty($filters['risk_level']) && $filters['risk_level'] !== 'All') {
            $query->whereHas('destinationPort.country.riskScores', function($q) use ($filters) {
                $q->where('risk_level', $filters['risk_level']);
            });
        }

        // 5. Search by Code, Origin/Dest Country, Origin/Dest Port
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('shipment_code', 'like', $searchTerm)
                  ->orWhereHas('originPort', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm)
                           ->orWhereHas('country', function($subQ2) use ($searchTerm) {
                               $subQ2->where('name', 'like', $searchTerm);
                           });
                  })
                  ->orWhereHas('destinationPort', function($subQ) use ($searchTerm) {
                      $subQ->where('name', 'like', $searchTerm)
                           ->orWhereHas('country', function($subQ2) use ($searchTerm) {
                               $subQ2->where('name', 'like', $searchTerm);
                           });
                  });
            });
        }

        // Order by latest and paginate
        return $query->latest()->paginate(10);
    }

    public function getByIdForUser($id)
    {
        return Shipment::with([
            'originPort.country',
            'destinationPort.country',
            'histories' => function($q) { $q->orderBy('timestamp', 'desc'); }
        ])->where('user_id', Auth::id())->findOrFail($id);
    }

    public function createShipment($data)
    {
        $data['user_id'] = Auth::id();
        $data['shipment_code'] = $this->generateShipmentCode();
        return Shipment::create($data);
    }

    public function updateShipment($id, $data)
    {
        $shipment = Shipment::where('user_id', Auth::id())->findOrFail($id);
        $oldStatus = $shipment->current_status;
        $shipment->update($data);
        
        // Log history if status changed
        if (isset($data['current_status']) && $oldStatus !== $data['current_status']) {
            $locationDesc = 'Updated Location';
            $statusLabel = 'Status Update';
            
            if ($data['current_status'] === 'transit') {
                $statusLabel = 'Departed';
                $locationDesc = $shipment->originPort ? $shipment->originPort->name : 'Origin Port';
            } elseif ($data['current_status'] === 'arrived') {
                $statusLabel = 'Arrived';
                $locationDesc = $shipment->destinationPort ? $shipment->destinationPort->name : 'Destination Port';
            } elseif ($data['current_status'] === 'delivered') {
                $statusLabel = 'Delivered';
                $locationDesc = 'Final Destination';
            } elseif ($data['current_status'] === 'delayed') {
                $statusLabel = 'Delayed';
                $locationDesc = 'En Route';
            }
            
            \App\Models\ShipmentHistory::create([
                'shipment_id' => $shipment->id,
                'status' => $statusLabel,
                'location_desc' => $locationDesc,
                'timestamp' => now()
            ]);
        }
        
        return $shipment;
    }

    public function deleteShipment($id)
    {
        $shipment = Shipment::where('user_id', Auth::id())->findOrFail($id);
        return $shipment->delete();
    }

    private function generateShipmentCode()
    {
        $date = now()->format('Ymd');
        $count = Shipment::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'SHP-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}