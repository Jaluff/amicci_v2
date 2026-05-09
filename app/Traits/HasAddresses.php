<?php

namespace App\Traits;

use App\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasAddresses
{
    /**
     * Get all of the model's addresses.
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the model's primary address.
     */
    public function primaryAddress(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable')
            ->where('is_primary', true)
            ->latestOfMany();
    }
}
