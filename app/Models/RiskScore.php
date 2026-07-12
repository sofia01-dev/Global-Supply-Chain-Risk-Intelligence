<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskScore extends Model
{
    protected $fillable = [
        'country_id',
        'shipment_id',
        'final_score',
        'risk_level',
        'calculated_at',
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}