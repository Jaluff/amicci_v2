<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'ubicacion_id',
        'code',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'code'   => 'integer',
    ];

    /**
     * Scope: filtra las sucursales a las que el usuario tiene acceso.
     * Admins sin sucursales asignadas ven todas.
     */
    public function scopePermitted($query)
    {
        $user = auth()->user();
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        $branchIds = $user->branches()->pluck('branches.id')->toArray();

        if (!empty($branchIds)) {
            return $query->whereIn('id', $branchIds);
        }

        if ($user->hasRole('admin')) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Ubicación principal a la que pertenece esta sucursal.
     */
    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    /**
     * Todas las zonas tarifarias que agrupa esta sucursal.
     * Ej: Sucursal Mendoza agrupa "Mendoza", "Mendoza Este", "Mendoza Sur".
     */
    public function ubicaciones(): HasMany
    {
        return $this->hasMany(Ubicacion::class);
    }

    /**
     * Usuarios asignados a esta sucursal.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Empresas asociadas con sus contadores de numeración.
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'branch_company')
            ->withPivot('last_shipment_number');
    }

    /**
     * Guías emitidas desde esta sucursal.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Genera el número de guía para esta sucursal y empresa.
     * Formato: {companyPrefix}-{branch.code}-{padded_number}
     * Ej: AM-1-00000001
     */
    public function generateShipmentNumber(string $companyPrefix, int $lastNumber): string
    {
        return sprintf(
            '%s-%d-%08d',
            $companyPrefix,
            $this->code,
            $lastNumber
        );
    }
}
