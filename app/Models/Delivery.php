<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\HasProblems;
use App\Models\Traits\HasStateMachine;
use App\StateMachines\DeliveryStateMachine;

class Delivery extends Model
{
    use HasProblems, HasStateMachine;

    protected string $stateMachineClass = DeliveryStateMachine::class;

    protected $fillable = [
        'company_id',
        'delivery_number',
        'deliverer_id',
        'location_id',
        'guide_count',
        'package_count',
        'load_date',
        'dispatch_date',
        'status',
    ];

    protected $casts = [
        'load_date' => 'date',
        'dispatch_date' => 'date',
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

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(Deliverer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class , 'location_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}