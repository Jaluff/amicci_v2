<?php

declare(strict_types=1);

namespace App\StateMachines;

/**
 * Gestiona el ciclo de vida de un Despacho (Dispatch).
 *
 * Flujo normal:
 *   Cargado → En viaje → Arribado
 *
 * Cascada:
 *   → En viaje : propaga a sus Rutas → e indirectamente a sus Guías
 *   → Arribado : propaga a sus Rutas con estado "Entregada"
 */
class DispatchStateMachine extends BaseStateMachine
{
    // ── Constantes de estado ─────────────────────────────────
    public const STATUS_CARGADO = 'Cargado';
    public const STATUS_EN_VIAJE = 'En viaje';
    public const STATUS_ARRIBADO = 'Arribado';

    // ── Interfaz ─────────────────────────────────────────────

    public function validStates(): array
    {
        return [
            self::STATUS_CARGADO,
            self::STATUS_EN_VIAJE,
            self::STATUS_ARRIBADO,
        ];
    }

    public function transitions(): array
    {
        return [
            self::STATUS_CARGADO => [self::STATUS_EN_VIAJE],
            self::STATUS_EN_VIAJE => [self::STATUS_ARRIBADO],
            self::STATUS_ARRIBADO => [],
        ];
    }

    // ── Cascada: Despacho propaga a sus Rutas (y estas a sus Guías) ──

    protected function afterTransition(string $from, string $to): void
    {
        // Mapeamos el nuevo estado de Despacho → estado de Ruta
        $routeStatusMap = [
            self::STATUS_EN_VIAJE => RouteStateMachine::STATUS_EN_VIAJE,
            self::STATUS_ARRIBADO => RouteStateMachine::STATUS_ENTREGADA,
        ];

        if (!isset($routeStatusMap[$to])) {
            return;
        }

        $targetRouteStatus = $routeStatusMap[$to];

        // Propagamos a cada Ruta del Despacho
        // La RouteStateMachine se encargará de propagar a las Guías
        // withoutGlobalScopes() para evitar filtrado por CompanyScope durante la cascada
        $this->model->routes()->withoutGlobalScopes()->each(function ($route) use ($targetRouteStatus) {
            $sm = $route->stateMachine();
            if ($sm->canTransitionTo($sm->currentStatus(), $targetRouteStatus)) {
                $sm->transitionTo($targetRouteStatus, 'Propagado desde cambio de estado en Despacho');
            }
        });
    }
}