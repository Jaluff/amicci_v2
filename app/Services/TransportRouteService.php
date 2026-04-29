<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TransportRoute;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransportRouteService
{
    public function createRoute(array $data): TransportRoute
    {
        return DB::transaction(function () use ($data) {
            // Se usa la empresa enviada en los datos para la numeración
            $company = \App\Models\Company::lockForUpdate()->findOrFail($data['company_id']);

            $company->last_route_number++;
            $company->save();

            $data['route_number'] = $company->prefix . '-R' . str_pad((string) $company->last_route_number, 8, '0', STR_PAD_LEFT);

            $route = TransportRoute::create($data);
            if (isset($data['shipments'])) {
                $this->assignShipments($route, $data['shipments']);
            }
            return $route->loadCount('shipments');
        });
    }

    public function updateRoute(TransportRoute $route, array $data): TransportRoute
    {
        return DB::transaction(function () use ($route, $data) {
            $currentStatus = $route->status;
            $newStatus = $data['status'] ?? null;
            
            // Remove 'status' from data to update other fields cleanly,
            // we will transition explicitly using the state machine.
            if (array_key_exists('status', $data)) {
                unset($data['status']);
            }
            
            $route->update($data);
            
            if (isset($data['shipments']) && $currentStatus === 'Cargada') {
                $this->assignShipments($route, $data['shipments']);
            }
            
            // Execute state transition if a new status was requested and differs.
            if ($newStatus && $newStatus !== $currentStatus) {
                $route->stateMachine()->transitionTo(
                    $newStatus,
                    'Actualizado desde formulario'
                );
            }
            
            return $route->loadCount('shipments');
        });
    }

    private function assignShipments(TransportRoute $route, array $shipmentIds): void
    {
        // Limpiamos los anteriores relacionados a esta ruta que no están en la lista
        $removedShipments = Shipment::where('transport_route_id', $route->id)
            ->whereNotIn('id', $shipmentIds)
            ->get();

        foreach ($removedShipments as $s) {
            $s->update(['transport_route_id' => null]);
            $s->logActivity("Desvinculada de la ruta {$route->route_number}", 'unassigned_route');
        }

        // Si hay algun ID seteamos para esta ruta
        if (!empty($shipmentIds)) {
            $addedShipments = Shipment::whereIn('id', $shipmentIds)
                ->where('transport_route_id', '!=', $route->id)->orWhereNull('transport_route_id')
                ->get();

            // Mass update para asegurar que todos queden referenciados
            Shipment::whereIn('id', $shipmentIds)->update(['transport_route_id' => $route->id]);

            foreach ($addedShipments as $s) {
                // Solo registramos el log a los nuevos vinculados
                $s->logActivity("Asignada a la ruta {$route->route_number}", 'assigned_route');
            }
        }
    }


}