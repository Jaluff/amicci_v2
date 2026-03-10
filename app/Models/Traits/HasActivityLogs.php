<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivityLogs
{
    /**
     * Obtiene el historial de actividades del modelo.
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject')->latest();
    }

    /**
     * Registra una nueva actividad para este modelo.
     *
     * @param string $description Una descripción legible ("Guía creada", "Asignada al reparto #1")
     * @param string|null $event Un evento corto opcional ("created", "updated", "assigned")
     * @param array $properties Datos extra opcionales estructurados ['old' => ..., 'new' => ...]
     * @param \Illuminate\Database\Eloquent\Model|null $causer El usuario que disparó la acción (si es nulo, toma Auth::user())
     * @return ActivityLog
     */
    public function logActivity(string $description, ?string $event = null, array $properties = [], $causer = null): ActivityLog
    {
        $causer = $causer ?? auth()->user();

        return $this->activityLogs()->create([
            'description' => $description,
            'event' => $event,
            'properties' => $properties,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer ? $causer->id : null,
        ]);
    }
}
