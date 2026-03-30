<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\DocumentProblem;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Trait HasProblems
 *
 * Agrega gestión de problemas polimórfica a cualquier modelo Eloquent.
 *
 * Uso:
 *   class Dispatch extends Model {
 *       use HasProblems;
 *   }
 *
 *   // Desde el controlador o vista:
 *   $dispatch->hasActiveProblem()      // bool
 *   $dispatch->currentProblem()        // DocumentProblem|null
 *   $dispatch->problems()              // MorphMany (historial completo)
 */
trait HasProblems
{
    /**
     * Historial completo de problemas, más reciente primero.
     */
    public function problems(): MorphMany
    {
        return $this->morphMany(DocumentProblem::class , 'documentable')
            ->orderByDesc('created_at');
    }

    /**
     * El último registro de problema (el estado actual).
     */
    public function currentProblem(): MorphOne
    {
        return $this->morphOne(DocumentProblem::class , 'documentable')
            ->latestOfMany('created_at');
    }

    /**
     * Indica si el documento tiene un problema activo en este momento.
     */
    public function hasActiveProblem(): bool
    {
        $latest = $this->currentProblem;

        return $latest !== null && $latest->is_active;
    }
}