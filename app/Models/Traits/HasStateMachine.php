<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\StatusHistory;
use App\StateMachines\BaseStateMachine;
use App\StateMachines\Contracts\StateMachineInterface;
use LogicException;

/**
 * Trait para agregar capacidades de StateMachine a cualquier modelo Eloquent.
 *
 * Uso:
 *   class Dispatch extends Model {
 *       use HasStateMachine;
 *       protected string $stateMachineClass = DispatchStateMachine::class;
 *   }
 *   
 *   // Desde el controlador:
 *   $dispatch->stateMachine()->transitionTo('En viaje');
 */
trait HasStateMachine
{
    /**
     * Retorna una instancia fresh de la StateMachine del modelo.
     * El modelo hijo DEBE definir protected string $stateMachineClass
     */
    public function stateMachine(): StateMachineInterface
    {
        if (!isset($this->stateMachineClass)) {
            throw new LogicException(
                get_class($this) . ' debe definir $stateMachineClass para usar HasStateMachine.'
                );
        }

        /** @var BaseStateMachine */
        return new $this->stateMachineClass($this);
    }

    /**
     * Relación polimórfica al historial de estados.
     */
    public function statusHistories()
    {
        return $this->morphMany(StatusHistory::class , 'model')->orderByDesc('transitioned_at');
    }

    /**
     * Retorna el último registro del historial.
     */
    public function latestStatusHistory()
    {
        return $this->morphOne(StatusHistory::class , 'model')->latestOfMany('transitioned_at');
    }
}