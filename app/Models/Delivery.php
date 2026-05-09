<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasProblems;
use App\Models\Traits\HasStateMachine;
use App\StateMachines\DeliveryStateMachine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory, HasProblems, HasStateMachine;

    protected string $stateMachineClass = DeliveryStateMachine::class;

    protected $fillable = [
        'company_id',
        'delivery_number',
        'deliverer_id',
        'vehicle_plate',
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(Deliverer::class);
    }

    /**
     * Sucursal donde se realiza el reparto (punto operativo).
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'location_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
