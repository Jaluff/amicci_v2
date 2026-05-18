<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    public function create(array $data, array $items): Shipment
    {
        return DB::transaction(function () use ($data, $items) {

            $company = Company::lockForUpdate()->findOrFail($data['company_id']);

            // Numeración por sucursal+empresa via pivot branch_company
            if (! empty($data['branch_id'])) {
                $pivot = DB::table('branch_company')
                    ->where('branch_id', $data['branch_id'])
                    ->where('company_id', $company->id)
                    ->lockForUpdate()
                    ->first();

                if ($pivot) {
                    $nextNumber = $pivot->last_shipment_number + 1;
                    DB::table('branch_company')
                        ->where('branch_id', $data['branch_id'])
                        ->where('company_id', $company->id)
                        ->update(['last_shipment_number' => $nextNumber]);

                    $branch = Branch::findOrFail($data['branch_id']);
                    $number = $branch->generateShipmentNumber($company->prefix, $nextNumber);
                } else {
                    // Crear registro de pivot si no existe
                    DB::table('branch_company')->insert([
                        'branch_id' => $data['branch_id'],
                        'company_id' => $company->id,
                        'last_shipment_number' => 1,
                    ]);

                    $branch = Branch::findOrFail($data['branch_id']);
                    $number = $branch->generateShipmentNumber($company->prefix, 1);
                }
            } else {
                // Fallback: contador legacy de la empresa
                $company->last_shipment_number++;
                $company->save();

                $prefix = substr($company->prefix, 0, 1);
                $number = sprintf('%s0G-%06d', $prefix, $company->last_shipment_number);
            }

            $data['numero'] = $number;
            $data['company_id'] = $company->id;

            $shipment = Shipment::create($data);

            foreach ($items as $item) {
                $shipment->items()->create($item);
            }

            $this->recalculateTotals($shipment);

            $shipment->logActivity('Guía creada', 'created', ['numero' => $number]);

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

            $shipment->logActivity('Guía actualizada', 'updated');

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

        $ivaPercent = (float) ($shipment->iva_percent ?? 21);
        $tax = $subtotal * ($ivaPercent / 100);

        $shipment->update([
            'subtotal' => $subtotal,
            'iva_monto' => $tax,
            'iva_percent' => $ivaPercent,
            'total' => $subtotal + $tax,
        ]);
    }
}
