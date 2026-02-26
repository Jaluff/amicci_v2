<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    public function create(array $data, array $items): Shipment
    {
        return DB::transaction(function () use ($data, $items) {

            $company = Company::lockForUpdate()
                ->findOrFail(session('company_id'));

            $company->last_shipment_number++;
            $company->save();

            $number = $company->prefix . '-' . str_pad(
                $company->last_shipment_number,
                8,
                '0',
                STR_PAD_LEFT
            );

            $data['numero'] = $number;
            $data['company_id'] = $company->id;

            $shipment = Shipment::create($data);

            foreach ($items as $item) {
                $shipment->items()->create($item);
            }

            $this->recalculateTotals($shipment);

            return $shipment;
        });
    }

    public function update(Shipment $shipment, array $data, array $items): Shipment
    {
        return DB::transaction(function () use ($shipment, $data, $items) {

            $shipment->update($data);

            $shipment->items()->delete();

            foreach ($items as $item) {
                $shipment->items()->create($item);
            }

            $this->recalculateTotals($shipment);

            return $shipment;
        });
    }

    public function delete(Shipment $shipment)
    {
        $shipment->delete();
    }

    private function recalculateTotals(Shipment $shipment)
    {
        $subtotal =
            (float) ($shipment->flete ?? $shipment->freight_amount ?? 0) +
            (float) ($shipment->seguro ?? $shipment->insurance_amount ?? 0) +
            (float) ($shipment->monto_contra_reembolso ?? $shipment->cod_amount ?? 0) +
            (float) ($shipment->retencion_mercaderia ?? $shipment->retention_mercaderia ?? 0) +
            (float) ($shipment->otros_cargos ?? $shipment->other_charges ?? 0);

        $tax = $subtotal * 0.21;

        $shipment->update([
            'subtotal' => $subtotal,
            'iva_monto' => $tax,
            'total' => $subtotal + $tax
        ]);
    }
}