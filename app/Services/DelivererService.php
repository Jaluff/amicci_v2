<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Delivery;
use App\Models\Shipment;
use App\Models\StatusHistory;
use App\Models\User;
use App\StateMachines\DeliveryStateMachine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Encapsula la lógica de negocio del panel de repartidores.
 *
 * Usado tanto por el DelivererPanelController (web) como por el
 * Api\DeliveryController (PWA/móvil).
 */
class DelivererService
{
    /**
     * Obtiene los repartos activos (En reparto) para un usuario.
     * Si el usuario es admin/supervisor, devuelve todos.
     */
    public function getActiveDeliveries(User $user): Collection
    {
        if ($user->hasAnyRole(['admin', 'supervisor'])) {
            return Delivery::where('status', DeliveryStateMachine::ON_DELIVERY)
                ->with(['deliverer', 'location'])
                ->orderByDesc('created_at')
                ->get();
        }

        $deliverer = $user->deliverer;

        if (! $deliverer) {
            return new Collection();
        }

        return Delivery::where('deliverer_id', $deliverer->id)
            ->where('status', DeliveryStateMachine::ON_DELIVERY)
            ->with(['deliverer', 'location'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Carga un reparto con sus guías, items, remitente y destinatario.
     */
    public function loadDeliveryWithShipments(Delivery $delivery): Delivery
    {
        $delivery->load(['deliverer', 'location', 'shipments' => function ($q) {
            $q->with(['sender.primaryAddress', 'recipient.primaryAddress', 'origin', 'destination'])
                ->withCount(['items as bultos' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(cantidad), 0)'));
                }]);
        }]);

        return $delivery;
    }

    /**
     * Verifica que el usuario tenga acceso al reparto dado.
     *
     * @return string|null Mensaje de error, o null si tiene acceso.
     */
    public function authorizeDeliveryAccess(User $user, Delivery $delivery): ?string
    {
        $isAdminOrSupervisor = $user->hasAnyRole(['admin', 'supervisor']);

        if (! $isAdminOrSupervisor) {
            $deliverer = $user->deliverer;
            if (! $deliverer || $delivery->deliverer_id !== $deliverer->id) {
                return 'No tienes permiso para ver este reparto.';
            }
        }

        if ($delivery->status !== DeliveryStateMachine::ON_DELIVERY && ! $isAdminOrSupervisor) {
            return 'El reparto debe estar en estado "En reparto" para gestionar sus entregas.';
        }

        return null;
    }

    /**
     * Confirma las entregas de guías de un reparto.
     *
     * Las guías cuyos IDs estén en $checkedIds se marcan como "Entregado".
     * Las que estaban "Entregado" y no están en $checkedIds se revierten a "En reparto".
     *
     * @param  array<int>  $checkedIds  IDs de las guías marcadas como entregadas.
     * @return array{success: bool, message: string}
     */
    public function confirmDeliveries(Delivery $delivery, array $checkedIds, User $user): array
    {
        if ($delivery->status !== DeliveryStateMachine::ON_DELIVERY) {
            return [
                'success' => false,
                'message' => 'El reparto no se encuentra en estado "En reparto".',
            ];
        }

        // Verificar que todos los IDs marcados pertenecen al reparto
        if (! empty($checkedIds)) {
            $verifyCount = Shipment::whereIn('id', $checkedIds)
                ->where('delivery_id', $delivery->id)
                ->count();

            if ($verifyCount !== count($checkedIds)) {
                return [
                    'success' => false,
                    'message' => 'Algunas de las guías seleccionadas no pertenecen a este reparto.',
                ];
            }
        }

        $allShipments = Shipment::where('delivery_id', $delivery->id)->get();

        DB::transaction(function () use ($allShipments, $checkedIds, $user) {
            foreach ($allShipments as $shipment) {
                $isChecked = in_array($shipment->id, $checkedIds, false);

                if ($isChecked && $shipment->ubicacion_actual !== 'Entregado') {
                    $shipment->update([
                        'ubicacion_actual' => 'Entregado',
                        'fecha_entrega' => now(),
                    ]);

                    StatusHistory::create([
                        'model_type' => Shipment::class,
                        'model_id' => $shipment->id,
                        'from_status' => 'En reparto',
                        'to_status' => 'Entregado',
                        'comment' => 'Entregado por Repartidor desde Planilla Móvil',
                        'user_id' => $user->id,
                        'transitioned_at' => now(),
                    ]);

                    if (method_exists($shipment, 'logActivity')) {
                        $shipment->logActivity(
                            'Cambio de estado: En reparto ➔ Entregado (Entregado por repartidor)',
                            'status_changed',
                            ['from' => 'En reparto', 'to' => 'Entregado']
                        );
                    }
                } elseif (! $isChecked && $shipment->ubicacion_actual === 'Entregado') {
                    $shipment->update([
                        'ubicacion_actual' => 'En reparto',
                        'fecha_entrega' => null,
                    ]);

                    StatusHistory::create([
                        'model_type' => Shipment::class,
                        'model_id' => $shipment->id,
                        'from_status' => 'Entregado',
                        'to_status' => 'En reparto',
                        'comment' => 'Entrega revertida a En reparto por Repartidor desde Planilla Móvil',
                        'user_id' => $user->id,
                        'transitioned_at' => now(),
                    ]);

                    if (method_exists($shipment, 'logActivity')) {
                        $shipment->logActivity(
                            'Cambio de estado: Entregado ➔ En reparto (Entrega revertida por repartidor)',
                            'status_changed',
                            ['from' => 'Entregado', 'to' => 'En reparto']
                        );
                    }
                }
            }
        });

        return [
            'success' => true,
            'message' => 'Cambios guardados correctamente.',
        ];
    }
}
