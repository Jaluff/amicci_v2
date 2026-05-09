<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'subject_type',
        'subject_id',
        'description',
        'event',
        'properties',
        'causer_type',
        'causer_id',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * El modelo sobre el cual se realiza la actividad (Ej: Guía, Despacho, etc)
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * El usuario o sistema que causó la actividad.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
