<?php

declare(strict_types=1);

namespace App\StateMachines;

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

    protected function afterTransition(string $from, string $to): void
    {
        if ($from === self::READY && $to === self::ON_DELIVERY) {
            // Cascade status change to shipments
            $this->model->shipments()->update(['ubicacion_actual' => 'En reparto']);
        }
        elseif ($from === self::ON_DELIVERY && $to === self::READY) {
            // Revert state if necessary
            $this->model->shipments()
                ->where('ubicacion_actual', 'En reparto')
                ->update(['ubicacion_actual' => 'Dto destino']);
        }
        elseif ($to === self::FINISHED) {
            // Finalizar guías del reparto
            foreach ($this->model->shipments as $shipment) {
                // Transicionar la guía a entregado
                $shipment->update(['ubicacion_actual' => 'Entregado']);

                // Registrar en historial para mantener consistencia
                \App\Models\StatusHistory::create([
                    'model_type' => get_class($shipment),
                    'model_id' => $shipment->getKey(),
                    'from_status' => 'En reparto',
                    'to_status' => 'Entregado',
                    'comment' => 'Entregado desde Reparto ' . $this->model->delivery_number,
                    'user_id' => \Illuminate\Support\Facades\Auth::id(),
                    'transitioned_at' => now(),
                ]);

                // Resolver problemas activos
                if ($shipment->hasActiveProblem()) {
                    $problem = $shipment->currentProblem;
                    if ($problem) {
                        $problem->update(['is_active' => false]);
                    }
                }
            }
        }
    }
}