<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
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
    ];

    /* protected static function booted()
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model) {
            if (session()->has('company_id')) {
                $model->company_id = session('company_id');
            }
        });
    } */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
