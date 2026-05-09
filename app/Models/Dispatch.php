<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasProblems;
use App\Models\Traits\HasStateMachine;
use App\StateMachines\DispatchStateMachine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Dispatch extends Model
{
    use HasFactory, HasProblems, HasStateMachine;

    protected string $stateMachineClass = DispatchStateMachine::class;

    protected $fillable = [
        'dispatch_number',
        'origin_id',
        'destination_id',
        'driver_id',
        'status',
        'seal_number',
        'semi_number',
        'chassis_number',
        'cost',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
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

    public function routes(): HasMany
    {
        return $this->hasMany(TransportRoute::class);
    }

    public function shipments(): HasManyThrough
    {
        return $this->hasManyThrough(Shipment::class, TransportRoute::class);
    }
}
