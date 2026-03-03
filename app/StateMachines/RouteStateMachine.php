<?php

declare(strict_types=1);

namespace App\StateMachines;

/**
 * Gestiona el ciclo de vida de una Hoja de Ruta (TransportRoute).
 *
 * Flujo normal:
 *   Cargada → En viaje → Entregada
 *   Cargada → Con problemas (atajo de emergencia)
 */
class RouteStateMachine extends BaseStateMachine
{
    // ── Constantes de estado ─────────────────────────────────
    public const STATUS_CARGADA = 'Cargada';
    public const STATUS_EN_VIAJE = 'En viaje';
    public const STATUS_ENTREGADA = 'Entregada';
    // STATUS_CON_PROBLEMAS eliminado: ahora es un DocumentProblem (flag ortogonal al estado)

    // ── Interfaz ─────────────────────────────────────────────

    public function validStates(): array
    {
        return [
            self::STATUS_CARGADA,
            self::STATUS_EN_VIAJE,
            self::STATUS_ENTREGADA,
        ];
    }

    public function transitions(): array
    {
        return [
            self::STATUS_CARGADA => [self::STATUS_EN_VIAJE],
            self::STATUS_EN_VIAJE => [self::STATUS_ENTREGADA],
            self::STATUS_ENTREGADA => [],
        ];
    }

    // ── Cascada: al cambiar el estado de la Ruta, propaga a sus Guías ──

    protected function afterTransition(string $from, string $to): void
    {
        // Mapeamos el nuevo estado de Ruta → estado correspondiente en Guía
        $shipmentStatusMap = [
            self::STATUS_EN_VIAJE => ShipmentStateMachine::STATUS_EN_TRANSITO,
            self::STATUS_ENTREGADA => ShipmentStateMachine::STATUS_DTO_DESTINO,
        ];

        if (!isset($shipmentStatusMap[$to])) {
            return; // No hay propagación para este estado
        }

        $targetShipmentStatus = $shipmentStatusMap[$to];

        // Cargamos las guías de la ruta y las transitamos individualmente
        // para respetar sus propias reglas de transición
        // withoutGlobalScopes() para evitar filtrado por CompanyScope durante la cascada
        $this->model->shipments()->withoutGlobalScopes()->each(function ($shipment) use ($targetShipmentStatus) {
            $sm = $shipment->stateMachine();
            if ($sm->canTransitionTo($sm->currentStatus(), $targetShipmentStatus)) {
                $sm->transitionTo($targetShipmentStatus, 'Propagado desde cambio de estado en Ruta');
            }
        });
    }
}