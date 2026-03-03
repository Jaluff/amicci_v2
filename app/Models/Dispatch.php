<?php

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
    use HasFactory, HasStateMachine, HasProblems;

    protected string $stateMachineClass = DispatchStateMachine::class;

    protected $fillable = [
        'company_id',
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

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Models\Scopes\CompanyScope);

        static::creating(function ($model) {
            if (!$model->company_id) {
                $model->company_id = session('company_id');
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class , 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class , 'destination_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(TransportRoute::class);
    }

    public function shipments(): HasManyThrough
    {
        return $this->hasManyThrough(Shipment::class , TransportRoute::class);
    }
}