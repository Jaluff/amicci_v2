<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'party_id',
        'numero',
        'fecha_factura',
        'numero_recibo',
        'total',
        'cobrada',
        'fecha_cobro',
        'notas',
    ];

    protected $casts = [
        'fecha_factura' => 'date',
        'fecha_cobro'   => 'date',
        'cobrada'       => 'boolean',
        'total'         => 'decimal:2',
        'party_id'      => 'integer',
        'company_id'    => 'integer',
    ];

    // Sin global scopes: el filtrado por empresa se hace de forma explícita

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeCobradas(Builder $query): Builder
    {
        return $query->where('cobrada', true);
    }

    public function scopePendientes(Builder $query): Builder
    {
        return $query->where('cobrada', false);
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * El cliente al que se le emite la factura.
     * En el futuro contendrá CUIT, condición IVA, etc. para facturación electrónica.
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
