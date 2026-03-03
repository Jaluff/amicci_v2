<?php

declare(strict_types=1);

namespace App\StateMachines;

use App\Models\StatusHistory;
use App\StateMachines\Contracts\StateMachineInterface;
use App\StateMachines\Exceptions\InvalidTransitionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

abstract class BaseStateMachine implements StateMachineInterface
{
    public function __construct(protected Model $model)
    {
    }

    // ─────────────────────────────────────────────
    // Implementaciones de la interfaz
    // ─────────────────────────────────────────────

    public function canTransitionTo(string $from, string $to): bool
    {
        $transitions = $this->transitions();

        return isset($transitions[$from]) && in_array($to, $transitions[$from], true);
    }

    public function currentStatus(): string
    {
        return $this->model->{ $this->statusField()};
    }

    /**
     * Punto de entrada principal. Ejecuta la transición dentro de una transacción.
     * Valida → Actualiza modelo → Guarda historial → Ejecuta cascada.
     *
     * @throws InvalidTransitionException
     */
    public function transitionTo(string $targetStatus, ?string $comment = null): Model
    {
        $from = $this->currentStatus();

        if (!$this->canTransitionTo($from, $targetStatus)) {
            throw new InvalidTransitionException($from, $targetStatus, get_class($this->model));
        }

        return DB::transaction(function () use ($from, $targetStatus, $comment) {
            // 1. Actualizar el campo de estado en el modelo
            $this->model->update([$this->statusField() => $targetStatus]);

            // 2. Registrar en el historial de auditoría
            StatusHistory::create([
                'model_type' => get_class($this->model),
                'model_id' => $this->model->getKey(),
                'from_status' => $from,
                'to_status' => $targetStatus,
                'comment' => $comment,
                'user_id' => Auth::id(),
                'transitioned_at' => now(),
            ]);

            // 3. Hook de cascada (override en subclases si es necesario)
            $this->afterTransition($from, $targetStatus);

            return $this->model->fresh();
        });
    }

    // ─────────────────────────────────────────────
    // Métodos que las subclases DEBEN o PUEDEN implementar
    // ─────────────────────────────────────────────

    /**
     * Define el campo del modelo que almacena el status.
     * Por defecto 'status'. Override si el modelo usa otro campo (ej. 'ubicacion_actual').
     */
    protected function statusField(): string
    {
        return 'status';
    }

    /**
     * Hook post-transición para efectos en cascada.
     * Las subclases lo sobreescriben para propagar cambios a documentos hijo.
     * Por defecto no hace nada.
     */
    protected function afterTransition(string $from, string $to): void
    {
    // Sin cascada por defecto
    }
}