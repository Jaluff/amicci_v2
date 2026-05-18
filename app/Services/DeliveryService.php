<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\Delivery;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliveryService
{
    public function createDelivery(array $data): Delivery
    {
        return DB::transaction(function () use ($data) {
            // Se usa la empresa enviada en los datos para la numeración
            $company = Company::lockForUpdate()->findOrFail($data['company_id']);
            $company->last_delivery_number++;
            $company->save();

            $branchId = $data['location_id'] ?? 0;
            $prefix = substr($company->prefix, 0, 1);
            $data['delivery_number'] = sprintf('%s%dE-%06d', $prefix, $branchId, $company->last_delivery_number);

            $shipments = [];
            if (! empty($data['shipments'])) {
                $shipments = Shipment::query()->with('items')->whereIn('id', $data['shipments'])
                    ->where('ubicacion_actual', '=', 'Dto destino')
                    ->whereNull('delivery_id')
                    ->get();
            }

            $data['guide_count'] = count($shipments);
            $data['package_count'] = collect($shipments)->sum(fn ($s) => $s->items->sum('cantidad'));
            $data['dispatch_date'] = $data['dispatch_date'] ?? null;

            $delivery = Delivery::create($data);

            if (count($shipments) > 0) {
                Shipment::whereIn('id', $shipments->pluck('id'))
                    ->update([
                        'delivery_id' => $delivery->id,
                    ]);

                foreach ($shipments as $s) {
                    $s->logActivity("Asignada al reparto {$delivery->delivery_number}", 'assigned_delivery');
                }
            }

            return $delivery;
        });
    }

    public function updateDelivery(Delivery $delivery, array $data): Delivery
    {
        return DB::transaction(function () use ($delivery, $data) {
            // Connect / Disconnect only if status is 'Listo'
            if ($delivery->status === 'Listo') {
                $shipmentIds = $data['shipments'] ?? [];

                // Desconectar las guías viejas que no están en la lista
                $removedShipments = Shipment::where('delivery_id', $delivery->id)
                    ->whereNotIn('id', $shipmentIds)
                    ->get();

                foreach ($removedShipments as $s) {
                    $s->update([
                        'delivery_id' => null,
                        'ubicacion_actual' => 'Dto destino',
                    ]);
                    $s->logActivity("Desvinculada del reparto {$delivery->delivery_number}", 'unassigned_delivery');
                }

                // Connect new ones
                if (! empty($shipmentIds)) {
                    $shipments = Shipment::query()->with('items')->whereIn('id', $shipmentIds)
                        ->where(function ($q) use ($delivery) {
                            $q->whereNull('delivery_id')
                                ->orWhere('delivery_id', $delivery->id);
                        })
                        ->get();

                    Shipment::whereIn('id', $shipmentIds)->update([
                        'delivery_id' => $delivery->id,
                    ]);

                    foreach ($shipments as $s) {
                        if ($s->getOriginal('delivery_id') != $delivery->id) {
                            $s->logActivity("Asignada al reparto {$delivery->delivery_number}", 'assigned_delivery');
                        }
                    }

                    $data['guide_count'] = count($shipments);
                    $data['package_count'] = collect($shipments)->sum(fn ($s) => $s->items->sum('cantidad'));
                } else {
                    $data['guide_count'] = 0;
                    $data['package_count'] = 0;
                }
            } else {
                // Si no es Listo, ignoramos cualquier cambio en el array de shipments
                unset($data['shipments']);
            }

            $delivery->update($data);

            return $delivery;
        });
    }

    public function returnShipmentFromDelivery(Delivery $delivery, Shipment $shipment): void
    {
        if ($shipment->delivery_id !== $delivery->id) {
            throw new InvalidArgumentException('La guía no pertenece a este reparto.');
        }

        $shipment->update([
            'delivery_id' => null,
            'ubicacion_actual' => 'Dto destino',
        ]);

        $shipment->logActivity("Devuelta a depósito (con problema) desde reparto {$delivery->delivery_number}", 'returned_from_delivery');
    }
}
