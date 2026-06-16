<?php

declare(strict_types=1);

namespace App\StateMachines;

use App\Models\StatusHistory;
use Illuminate\Support\Facades\Auth;

class DeliveryStateMachine extends BaseStateMachine
{
    public const READY = 'Listo';

    public const ON_DELIVERY = 'En reparto';

    public const FINISHED = 'Finalizado';

    public function validStates(): array
    {
        return [self::READY, self::ON_DELIVERY, self::FINISHED];
    }

    public function transitions(): array
    {
        return [
            self::READY => [self::ON_DELIVERY],
            self::ON_DELIVERY => [self::FINISHED, self::READY],
            self::FINISHED => [],
        ];
    }

    public function canTransitionTo(string $from, string $to): bool
    {
        if ($to === self::FINISHED) {
            // No permitir finalizar si hay guías con problemas activos aún vinculadas.
            // Deben ser "Devueltas" manualmente a través del modal primero.
            $hasActiveProblems = $this->model->shipments()
                ->whereHas('problems', fn ($q) => $q->where('is_active', true))
                ->exists();

            if ($hasActiveProblems) {
                return false;
            }

            // No permitir finalizar si hay guías que todavía no están entregadas
            $hasPendingShipments = $this->model->shipments()
                ->where('ubicacion_actual', '!=', 'Entregado')
                ->exists();

            if ($hasPendingShipments) {
                return false;
            }
        }

        return parent::canTransitionTo($from, $to);
    }

    protected function afterTransition(string $from, string $to): void
    {
        if ($from === self::READY && $to === self::ON_DELIVERY) {
            // Cascade status change to shipments
            $this->model->shipments->each(function ($shipment) {
                $shipment->update(['ubicacion_actual' => 'En reparto']);
            });

            $shipments = $this->model->shipments()->with(['sender', 'recipient', 'origin', 'destination'])->get();
            app(\App\Services\GroupedNotificationService::class)->sendGroupedNotifications($shipments, 'en_reparto', 'En reparto');
        } elseif ($from === self::ON_DELIVERY && $to === self::READY) {
            // Revert state if necessary
            $this->model->shipments()
                ->where('ubicacion_actual', 'En reparto')
                ->get()
                ->each(function ($shipment) {
                    $shipment->update(['ubicacion_actual' => 'Dto destino']);
                });
        } elseif ($to === self::FINISHED) {
            // Finalizar guías del reparto (Solo las que NO fueron devueltas manualmente)
            foreach ($this->model->shipments as $shipment) {
                // Si llegamos aquí y hay una con problema activo, la ignoramos (no debería pasar por el canTransitionTo)
                if ($shipment->hasActiveProblem()) {
                    continue;
                }

                // Transicionar la guía normal a entregado y registrar la fecha de entrega
                $shipment->update([
                    'ubicacion_actual' => 'Entregado',
                    'fecha_entrega' => now(),
                ]);

                // Registrar en historial para mantener consistencia
                StatusHistory::create([
                    'model_type' => get_class($shipment),
                    'model_id' => $shipment->getKey(),
                    'from_status' => 'En reparto',
                    'to_status' => 'Entregado',
                    'comment' => 'Entregado desde Reparto '.$this->model->delivery_number,
                    'user_id' => Auth::id(),
                    'transitioned_at' => now(),
                ]);

                if (method_exists($shipment, 'logActivity')) {
                    $shipment->logActivity(
                        "Cambio de estado: En reparto ➔ Entregado (Entregado desde Reparto {$this->model->delivery_number})",
                        'status_changed',
                        ['from' => 'En reparto', 'to' => 'Entregado']
                    );
                }
            }
        }
    }
}
