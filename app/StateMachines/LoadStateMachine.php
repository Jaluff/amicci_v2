<?php

declare(strict_types=1);

namespace App\StateMachines;

/**
 * Gestiona el ciclo de vida de una Carga (Load).
 */
class LoadStateMachine extends BaseStateMachine
{
    // ── Constantes de estado ─────────────────────────────────
    public const STATUS_PREPARADO = 'Preparado';
    public const STATUS_EN_VIAJE = 'En viaje';
    public const STATUS_ARRIBADO = 'Arribado';

    // ── Interfaz ─────────────────────────────────────────────

    public function validStates(): array
    {
        return [
            self::STATUS_PREPARADO,
            self::STATUS_EN_VIAJE,
            self::STATUS_ARRIBADO,
        ];
    }

    public function transitions(): array
    {
        return [
            self::STATUS_PREPARADO => [self::STATUS_EN_VIAJE],
            self::STATUS_EN_VIAJE => [self::STATUS_ARRIBADO, self::STATUS_PREPARADO], // Rollback permitido
            self::STATUS_ARRIBADO => [],
        ];
    }

    protected function afterTransition(string $from, string $to): void
    {
        if ($to === self::STATUS_ARRIBADO && !$this->model->fecha_descarga) {
            $this->model->update(['fecha_descarga' => now()]);
        }
    }

    protected function statusField(): string
    {
        return 'estado';
    }
}
