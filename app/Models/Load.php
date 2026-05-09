<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasStateMachine;
use App\StateMachines\LoadStateMachine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Load extends Model
{
    use HasFactory, HasStateMachine, SoftDeletes;

    protected string $stateMachineClass = LoadStateMachine::class;

    protected $fillable = [
        'company_id',
        'numero',
        'remitente_id',
        'destinatario_id',
        'origen_id',
        'destino_id',
        'driver_id',
        'fecha_carga',
        'fecha_descarga',
        'remito',
        'observaciones',
        'estado',
        'fecha_factura',
        'numero_factura',
        'importe_factura',
        'fecha_recibo',
        'numero_recibo',
    ];

    protected $casts = [
        'fecha_carga' => 'date',
        'fecha_descarga' => 'date',
        'fecha_factura' => 'date',
        'fecha_recibo' => 'date',
        'importe_factura' => 'decimal:2',
    ];

    /**
     * Accesores virtuales para estado de facturación y cobro.
     */
    public function getFacturadaAttribute(): bool
    {
        return ! empty($this->numero_factura);
    }

    public function getCobradaAttribute(): bool
    {
        return ! empty($this->numero_recibo);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'remitente_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(Party::class, 'destinatario_id');
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'origen_id');
    }

    public function destino(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destino_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }
}
