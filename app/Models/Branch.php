<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'ubicacion_id',
        'code',
        'last_shipment_number',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'code' => 'integer',
        'last_shipment_number' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (self $model): void {
            $model->company_id = session('company_id');
        });
    }

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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Genera el número de guía para esta sucursal.
     * Formato: {company.prefix}-{branch.code}-{padded_number}
     * Ej: AM-1-00000001
     */
    public function generateShipmentNumber(string $companyPrefix): string
    {
        return sprintf(
            '%s-%d-%08d',
            $companyPrefix,
            $this->code,
            $this->last_shipment_number
        );
    }
}
