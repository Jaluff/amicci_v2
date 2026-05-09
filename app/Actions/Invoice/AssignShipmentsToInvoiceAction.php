<?php

namespace App\Actions\Invoice;

use App\Models\Invoice;
use App\Models\Shipment;

class AssignShipmentsToInvoiceAction
{
    /**
     * Vincula las guías a la factura y recalcula el total.
     * Lanza excepción si alguna guía ya tiene otra factura asignada
     * y el usuario no tiene el rol de admin.
     *
     * @param  array<int>  $shipmentIds
     * @param  bool  $isAdmin  Si es admin puede re-facturar guías ya asignadas
     */
    public function execute(Invoice $invoice, array $shipmentIds, bool $isAdmin = false): void
    {
        $shipments = Shipment::withoutGlobalScopes()
            ->whereIn('id', $shipmentIds)
            ->get();

        if (! $isAdmin) {
            $alreadyInvoiced = $shipments->filter(
                fn (Shipment $s) => $s->invoice_id !== null && $s->invoice_id !== $invoice->id
            );

            if ($alreadyInvoiced->isNotEmpty()) {
                $numbers = $alreadyInvoiced->pluck('numero')->implode(', ');
                throw new \DomainException(
                    "Las siguientes guías ya tienen factura asignada: {$numbers}. Solo un administrador puede re-facturarlas."
                );
            }
        }

        // Desvincula las guías previas de esta factura que no estén en la nueva lista
        Shipment::withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->whereNotIn('id', $shipmentIds)
            ->update(['invoice_id' => null]);

        // Asigna las nuevas guías a esta factura
        Shipment::withoutGlobalScopes()
            ->whereIn('id', $shipmentIds)
            ->update(['invoice_id' => $invoice->id]);

        // Recalcula el total desnormalizado
        $total = Shipment::withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->sum('total');

        $invoice->update(['total' => $total]);
    }
}
