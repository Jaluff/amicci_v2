<?php

namespace App\Models;

use App\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasAddresses;

    protected $fillable = [
        'name',
        'prefix',
        'color',
        'last_shipment_number',
        'last_route_number',
        'last_dispatch_number',
        'last_load_number',
        'last_delivery_number',
        'contra_reembolso_percent',
        'active',
        'legal_name',
        'cuit',
        'gross_income',
        'establishment',
        'stamping_headquarters',
        'start_of_activities',
        'address_line1',
        'phone',
        'email',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_company')
            ->withPivot('last_shipment_number');
    }

    public function transportRoutes(): HasMany
    {
        return $this->hasMany(TransportRoute::class);
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Returns a hex color safe to use (fallback to indigo if not set).
     */
    public function getColorAttribute(?string $value): string
    {
        return $value ?: '#6366f1';
    }
}
