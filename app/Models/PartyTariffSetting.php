<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyTariffSetting extends Model
{
    /**
     * Modos de facturación disponibles.
     * Se usan en el GuiaImporteService para determinar qué fórmula aplicar.
     */
    public const BILLING_MODES = [
        'kg' => 'Por Kg (escala tarifaria)',
        'tonelada' => 'Por Tonelada',
        'volumen' => 'Por Volumen (M3)',
        'bultos' => 'Por Bultos',
        'pallets' => 'Por Pallets',
        'bultos_pallets' => 'Por Bultos + Pallets',
        'valor_declarado' => 'Por Valor Declarado (%)',
    ];

    protected $fillable = [
        'party_id',
        'tariff_table_id',
        'billing_mode',
        'minimum_charge',
        'rate_per_ton_custom',
        'rate_per_m3_custom',
        'rate_per_bulto',
        'minimum_per_bulto',   // mínimo propio para bultos
        'rate_per_pallet',
        'minimum_per_pallet',  // mínimo propio para pallets
        'declared_value_pct',
        'valid_from',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'minimum_charge' => 'decimal:2',
        'rate_per_ton_custom' => 'decimal:2',
        'rate_per_m3_custom' => 'decimal:2',
        'rate_per_bulto' => 'decimal:2',
        'minimum_per_bulto' => 'decimal:2',
        'rate_per_pallet' => 'decimal:2',
        'minimum_per_pallet' => 'decimal:2',
        'declared_value_pct' => 'decimal:4',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    /**
     * Cliente al que aplica esta configuración.
     */
    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Cuadro tarifario base al que está vinculado este acuerdo.
     */
    public function tariffTable(): BelongsTo
    {
        return $this->belongsTo(TariffTable::class);
    }

    /**
     * Etiqueta legible del modo de facturación.
     */
    public function getBillingModeLabelAttribute(): string
    {
        return self::BILLING_MODES[$this->billing_mode] ?? $this->billing_mode;
    }

    /**
     * Scope para obtener solo acuerdos vigentes a hoy.
     */
    public function scopeActive($query)
    {
        return $query->where('valid_from', '<=', now())
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()));
    }
}
