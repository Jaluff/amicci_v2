<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TariffBracket extends Model
{
    protected $fillable = [
        'tariff_table_id',
        'weight_from',
        'weight_to',
        'rate',
    ];

    protected $casts = [
        'weight_from' => 'integer',
        'weight_to' => 'integer',
        'rate' => 'decimal:2',
    ];

    /**
     * Cuadro tarifario al que pertenece este tramo.
     */
    public function tariffTable(): BelongsTo
    {
        return $this->belongsTo(TariffTable::class);
    }

    /**
     * Etiqueta legible del tramo para mostrar en UI.
     * Ej: "De 1 a 20 Kg."
     */
    public function getLabelAttribute(): string
    {
        return "De {$this->weight_from} a {$this->weight_to} Kg.";
    }
}
