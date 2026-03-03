<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'prefix',
        'last_shipment_number',
        'last_route_number',
        'last_dispatch_number',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}