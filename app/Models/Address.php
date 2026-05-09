<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'type',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip_code',
        'phone',
        'email',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Obtener el modelo padre (Company, Party, Driver, etc.) al que pertenece esta dirección.
     */
    public function addressable()
    {
        return $this->morphTo();
    }
}
