<?php

namespace App\Actions\Invoice;

use App\Models\Invoice;
use App\Models\Shipment;

class DetachShipmentsFromInvoiceAction
{
    /**
     * Desvincula guías específicas de la factura (solo admin).
     * Recalcula el total de la factura tras la operación.
     *
     * @param  array<int>  $shipmentIds
     */
    public function execute(Invoice $invoice, array $shipmentIds): void
    {
        if ($invoice->cobrada) {
            throw new \DomainException(
                'No se pueden quitar guías de una factura ya cobrada.'
            );
        }

        Shipment::withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->whereIn('id', $shipmentIds)
            ->update(['invoice_id' => null]);

        $total = Shipment::withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->sum('total');

        $invoice->update(['total' => $total]);
    }
}
