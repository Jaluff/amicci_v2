<?php

namespace App\Models;

use App\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasAddresses;

    protected $fillable = [
        'name',
        'prefix',
        'last_shipment_number',
        'last_route_number',
        'last_dispatch_number',
        'contra_reembolso_percent',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}