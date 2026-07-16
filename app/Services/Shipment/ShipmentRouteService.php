<?php
namespace App\Services\Shipment;

class ShipmentRouteService
{
    public function getMapData($shipment)
    {
        // Extract ordered route coordinates for Leaflet
        $mapData = [];
        if ($shipment->originPort) {
            $mapData[] = [
                'type' => 'Origin',
                'name' => $shipment->originPort->name,
                'lat' => $shipment->originPort->latitude,
                'lng' => $shipment->originPort->longitude,
            ];
        }

        foreach ($shipment->routes->sortBy('sequence_order') as $route) {
            $mapData[] = [
                'type' => 'Transit',
                'name' => $route->port->name,
                'lat' => $route->port->latitude,
                'lng' => $route->port->longitude,
            ];
        }

        if ($shipment->destinationPort) {
            $mapData[] = [
                'type' => 'Destination',
                'name' => $shipment->destinationPort->name,
                'lat' => $shipment->destinationPort->latitude,
                'lng' => $shipment->destinationPort->longitude,
            ];
        }

        return $mapData;
    }
}