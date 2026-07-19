<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskScoreHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'final_score',
        'risk_level',
        'calculated_at'
    ];

    protected $casts = [
        'final_score' => 'float',
        'calculated_at' => 'datetime',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
