<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'country_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function watchlists()
    {
        return $this->hasMany(Watchlist::class);
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'admin_id');
    }
}