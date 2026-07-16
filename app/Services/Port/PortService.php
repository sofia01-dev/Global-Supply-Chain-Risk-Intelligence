<?php
namespace App\Services\Port;

use App\Models\Port;

class PortService
{
    public function getAllPorts()
    {
        return Port::with('country')->get();
    }

    public function getPortById($id)
    {
        return Port::with('country')->find($id);
    }

    public function getPortsByCountry($countryId)
    {
        return Port::with('country')->where('country_id', $countryId)->get();
    }
}