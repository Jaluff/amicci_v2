<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasProblems;
use App\Models\Traits\HasStateMachine;
use App\StateMachines\RouteStateMachine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRoute extends Model
{
    use HasFactory, HasStateMachine, HasProblems;

    protected string $stateMachineClass = RouteStateMachine::class;

    protected $fillable = [
        'company_id',
        'dispatch_id',
        'route_number',
        'origin_id',
        'destination_id',
        'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Sucursal de origen (punto operativo).
     */
    public function origin(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origin_id');
    }

    /**
     * Sucursal de destino (punto operativo).
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_id');
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}