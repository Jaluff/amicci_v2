<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Delivery;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DeliveryService
{
    public function createDelivery(array $data): Delivery
    {
        return DB::transaction(function () use ($data) {
            $company = \App\Models\Company::lockForUpdate()->findOrFail(session('company_id'));
            $company->last_route_number++;
            $company->save();

            $data['delivery_number'] = $company->prefix . '-REP' . str_pad((string)$company->last_route_number, 8, '0', STR_PAD_LEFT);
            $data['company_id'] = $company->id;

            $shipments = [];
            if (!empty($data['shipments'])) {
                $shipments = Shipment::query()->with('items')->whereIn('id', $data['shipments'])
                    ->where('ubicacion_actual', '=', 'Dto destino')
                    ->whereNull('delivery_id')
                    ->get();
            }

            $data['guide_count'] = count($shipments);
            $data['package_count'] = collect($shipments)->sum(fn($s) => $s->items->sum('cantidad'));
            $data['dispatch_date'] = $data['dispatch_date'] ?? null;

            $delivery = Delivery::create($data);

            if (count($shipments) > 0) {
                Shipment::whereIn('id', $shipments->pluck('id'))
                    ->update([
                    'delivery_id' => $delivery->id,
                    'ubicacion_actual' => 'En reparto'
                ]);
            }

            return $delivery;
        });
    }

    public function updateDelivery(Delivery $delivery, array $data): Delivery
    {
        return DB::transaction(function () use ($delivery, $data) {
            $shipmentIds = $data['shipments'] ?? [];

            // Disconnect old shipments not in new list
            Shipment::where('delivery_id', $delivery->id)
                ->whereNotIn('id', $shipmentIds)
                ->where('ubicacion_actual', '!=', 'Entregado') // don't detach delivered ones
                ->update([
                'delivery_id' => null,
                'ubicacion_actual' => 'Dto destino'
            ]);

            // Connect new ones
            if (!empty($shipmentIds)) {
                $shipments = Shipment::query()->with('items')->whereIn('id', $shipmentIds)
                    ->where(function ($q) use ($delivery) {
                    $q->whereNull('delivery_id')
                        ->orWhere('delivery_id', $delivery->id);
                }
                )
                    ->get();

                Shipment::whereIn('id', $shipmentIds)->update([
                    'delivery_id' => $delivery->id,
                    'ubicacion_actual' => 'En reparto'
                ]);

                $data['guide_count'] = count($shipments);
                $data['package_count'] = collect($shipments)->sum(fn($s) => $s->items->sum('cantidad'));
            }
            else {
                $data['guide_count'] = 0;
                $data['package_count'] = 0;
            }

            $delivery->update($data);

            return $delivery;
        });
    }
}