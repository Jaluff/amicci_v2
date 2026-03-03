<?php

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

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\CompanyScope);

        static::creating(function ($model) {
            if (!$model->company_id) {
                $model->company_id = session('company_id');
            }
        });
    }


    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(Dispatch::class);
    }
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
    public function origin(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class , 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class , 'destination_id');
    }
}