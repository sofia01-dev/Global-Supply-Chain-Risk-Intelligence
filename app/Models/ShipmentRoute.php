<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentRoute extends Model
{
    protected $fillable = [
        'shipment_id',
        'port_id',
        'sequence_order',
        'estimated_arrival',
        'actual_arrival',
    ];

    protected $casts = [
        'estimated_arrival' => 'datetime',
        'actual_arrival' => 'datetime',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function port()
    {
        return $this->belongsTo(Port::class);
    }
}