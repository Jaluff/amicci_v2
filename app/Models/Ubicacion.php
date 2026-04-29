<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = [
        'nombre',
        'branch_id',
    ];

    /**
     * Sucursal operativa a la que pertenece esta zona tarifaria.
     * Ej: "Mendoza Este" → Sucursal Mendoza
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
