<?php

namespace App\Models;

use App\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Party extends Model
{
    use HasFactory, HasAddresses;

    protected $fillable = [
        'name',
        'address',
        'locality',
        'city',
        'province',
        'postal_code',
        'phone',
        'phone_secondary',
        'email',
        'document',
        'document_type',
        'tax_status',
        'iva_percent',
        'has_insurance',
        'insurance_percent',
    ];



    /**
     * Todas las configuraciones tarifarias del cliente (puede tener varias para distintas rutas).
     */
    public function tariffSettings(): HasMany
    {
        return $this->hasMany(PartyTariffSetting::class);
    }

    /**
     * La configuración tarifaria activa del cliente (usada en el formulario de edición).
     * Si tiene varias, retorna la primera activa por orden de creación.
     */
    public function activeTariffSetting(): HasOne
    {
        return $this->hasOne(PartyTariffSetting::class)
            ->where('valid_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhere('valid_until', '>=', now());
            })
            ->latest();
    }
}