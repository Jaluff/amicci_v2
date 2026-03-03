<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'address',
        'phone',
        'license_number',
        'dni',
    ];
    public function transportRoutes(): HasMany
    {
        return $this->hasMany(TransportRoute::class);
    }
}