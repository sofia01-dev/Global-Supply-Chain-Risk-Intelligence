<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'user_id',
        'shipment_name',
        'goods',
        'shipment_code',
        'origin_port_id',
        'destination_port_id',
        'current_status',
        'departure_date',
        'estimated_arrival',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'estimated_arrival' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function originPort()
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    public function destinationPort()
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }



    public function histories()
    {
        return $this->hasMany(ShipmentHistory::class);
    }

    public function riskScores()
    {
        return $this->hasMany(RiskScore::class);
    }
}