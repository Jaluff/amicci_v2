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
            $company = \App\Models\Company::lockForUpdate()->findOrFail(session('company_id'));

            $company->last_route_number++;
            $company->save();

            $data['route_number'] = $company->prefix . '-R' . str_pad((string)$company->last_route_number, 8, '0', STR_PAD_LEFT);
            $data['company_id'] = $company->id;

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
            if (isset($data['status']) && $data['status'] === 'Entregada') {
                $this->validateDeliveryStatus($route);
            }
            $route->update($data);
            if (isset($data['shipments'])) {
                $this->assignShipments($route, $data['shipments']);
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
            
        foreach($removedShipments as $s) {
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
            
            foreach($addedShipments as $s) {
                // Solo registramos el log a los nuevos vinculados
                $s->logActivity("Asignada a la ruta {$route->route_number}", 'assigned_route');
            }
        }
    }
    private function validateDeliveryStatus(TransportRoute $route): void
    {
        // Validación de negocio: No permitir cerrar si hay guías pendientes o en estado temporal.
        // Se asume la existencia de un estado referencial. Se puede ajustar según la tabla shipments real.
        $pendingShipments = $route->shipments()->where('status', '!=', 'Entregada')->count();
        if ($pendingShipments > 0) {
            throw new InvalidArgumentException('No se puede marcar la ruta como "Entregada" si tiene guías pendientes de entrega u otro estado sin finalizar.');
        }
    }
}