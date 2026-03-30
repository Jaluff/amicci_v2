<?php

declare(strict_types=1);

namespace App\StateMachines;

/**
 * Gestiona el ciclo de vida de una Guía (Shipment).
 * El estado se almacena en el campo 'ubicacion_actual'.
 *
 * Flujo normal:
 *   Dto origen → En transito → Dto destino → En reparto → Entregado
 */
class ShipmentStateMachine extends BaseStateMachine
{
    // ── Constantes de estado ─────────────────────────────────
    public const STATUS_DTO_ORIGEN = 'Dto origen';
    public const STATUS_EN_TRANSITO = 'En transito';
    public const STATUS_DTO_DESTINO = 'Dto destino';
    public const STATUS_EN_REPARTO = 'En reparto';
    public const STATUS_ENTREGADO = 'Entregado';

    // ── Interfaz ─────────────────────────────────────────────

    public function validStates(): array
    {
        return [
            self::STATUS_DTO_ORIGEN,
            self::STATUS_EN_TRANSITO,
            self::STATUS_DTO_DESTINO,
            self::STATUS_EN_REPARTO,
            self::STATUS_ENTREGADO,
        ];
    }

    public function transitions(): array
    {
        return [
            self::STATUS_DTO_ORIGEN => [self::STATUS_EN_TRANSITO],
            self::STATUS_EN_TRANSITO => [self::STATUS_DTO_DESTINO],
            self::STATUS_DTO_DESTINO => [self::STATUS_EN_REPARTO],
            self::STATUS_EN_REPARTO => [self::STATUS_ENTREGADO],
            // Estado terminal — sin transiciones salientes
            self::STATUS_ENTREGADO => [],
        ];
    }

    // ── El estado de Guía vive en 'ubicacion_actual', no en 'status' ──

    protected function statusField(): string
    {
        return 'ubicacion_actual';
    }

// ── Las Guías no propagan cascada hacia abajo (son el documento base) ──
// afterTransition() hereda el default (vacío) de BaseStateMachine
}