<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TariffTable extends Model
{
    protected $fillable = [
        'name',
        'origin_id',
        'destination_id',
        'rate_per_ton',
        'rate_per_m3',
        'valid_from',
        'valid_until',
        'is_active',
        'contra_reembolso_percent',
    ];

    protected $casts = [
        'rate_per_ton' => 'decimal:2',
        'rate_per_m3'  => 'decimal:2',
        'valid_from'   => 'date',
        'valid_until'  => 'date',
        'is_active'    => 'boolean',
    ];

    /**
     * Tramos de peso que componen la escala de esta tarifa.
     * Cada cuadro tiene sus propios 19 tramos con distintos valores.
     */
    public function brackets(): HasMany
    {
        return $this->hasMany(TariffBracket::class)->orderBy('weight_from');
    }

    /**
     * Configuraciones tarifarias particulares de clientes para esta ruta.
     */
    public function partySettings(): HasMany
    {
        return $this->hasMany(PartyTariffSetting::class);
    }

    /**
     * Ubicación de origen (zona tarifaria).
     */
    public function origin(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'origin_id');
    }

    /**
     * Ubicación de destino (zona tarifaria).
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'destination_id');
    }

    /**
     * Scope para obtener solo los cuadros activos y vigentes a hoy.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(fn(Builder $q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()));
    }

    /**
     * Nombre descriptivo para mostrar en selects y listados.
     */
    public function getRouteNameAttribute(): string
    {
        $originName = $this->origin?->nombre ?? '?';
        $destName   = $this->destination?->nombre ?? '?';

        return "{$originName} → {$destName}";
    }
}
