<?php

namespace App\Models;

use App\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deliverer extends Model
{
    use HasAddresses, HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'license_number',
        'dni',
        'email',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
