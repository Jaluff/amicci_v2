<?php

declare(strict_types=1);

namespace App\StateMachines\Contracts;

use Illuminate\Database\Eloquent\Model;

interface StateMachineInterface
{
    /**
     * Retorna todos los estados válidos del documento.
     * @return array<string>
     */
    public function validStates(): array;

    /**
     * Retorna el mapa de transiciones válidas.
     * Formato: ['estado-origen' => ['estado-destino-1', 'estado-destino-2']]
     * @return array<string, array<string>>
     */
    public function transitions(): array;

    /**
     * Verifica si la transición de $from a $to es válida.
     */
    public function canTransitionTo(string $from, string $to): bool;

    /**
     * Ejecuta la transición de estado con auditoría y cascada.
     * @throws \App\StateMachines\Exceptions\InvalidTransitionException
     */
    public function transitionTo(string $targetStatus, ?string $comment = null): Model;

    /**
     * Retorna el estado actual del modelo.
     */
    public function currentStatus(): string;
}