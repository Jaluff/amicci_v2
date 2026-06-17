<?php

namespace App\Services;

use App\Actions\Invoice\AssignShipmentsToInvoiceAction;
use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function __construct(
        private readonly AssignShipmentsToInvoiceAction $assignShipments,
    ) {}

    /**
     * Crea una nueva factura y asocia las guías seleccionadas.
     *
     * @param  array<int>  $shipmentIds  IDs de las guías a facturar
     * @param  array<string, mixed>  $data  Datos de la factura (numero, fecha_factura, etc.)
     * @param  int  $partyId  ID del cliente facturado
     * @param  bool  $isAdmin  Si el usuario es admin (puede re-facturar)
     */
    public function generateInvoice(
        array $shipmentIds,
        array $data,
        int $partyId,
        int $companyId,
        bool $isAdmin = false,
    ): Invoice {
        return DB::transaction(function () use ($shipmentIds, $data, $partyId, $companyId, $isAdmin): Invoice {
            $invoice = Invoice::where('numero', $data['numero'])
                ->where('company_id', $companyId)
                ->first();

            if ($invoice) {
                if ($invoice->cobrada) {
                    throw new \DomainException("La factura #{$invoice->numero} ya existe y se encuentra cobrada. No se pueden agregar más guías.");
                }

                $invoice->update([
                    'party_id' => $partyId,
                    'fecha_factura' => $data['fecha_factura'],
                    'numero_recibo' => $data['numero_recibo'] ?? $invoice->numero_recibo,
                    'notas' => $data['notas'] ?? $invoice->notas,
                ]);

                // Obtener guías actuales de la factura
                $currentShipmentIds = Shipment::withoutGlobalScopes()
                    ->where('invoice_id', $invoice->id)
                    ->pluck('id')
                    ->toArray();

                $mergedShipmentIds = array_unique(array_merge($currentShipmentIds, $shipmentIds));

                $this->assignShipments->execute($invoice, $mergedShipmentIds, $isAdmin);
            } else {
                $invoice = Invoice::create([
                    'company_id' => $companyId,
                    'party_id' => $partyId,
                    'numero' => $data['numero'],
                    'fecha_factura' => $data['fecha_factura'],
                    'numero_recibo' => $data['numero_recibo'] ?? null,
                    'notas' => $data['notas'] ?? null,
                    'total' => 0, // Se recalcula en el action
                    'cobrada' => false,
                ]);

                $this->assignShipments->execute($invoice, $shipmentIds, $isAdmin);
            }

            return $invoice->fresh();
        });
    }

    /**
     * Marca la factura como cobrada y propaga el estado a todas sus guías.
     */
    public function markAsPaid(Invoice $invoice): void
    {
        if ($invoice->cobrada) {
            return; // Idempotente
        }

        DB::transaction(function () use ($invoice): void {
            $invoice->update([
                'cobrada' => true,
                'fecha_cobro' => now()->toDateString(),
            ]);

            Shipment::withoutGlobalScopes()
                ->where('invoice_id', $invoice->id)
                ->update(['cobrada' => true]);
        });
    }

    /**
     * Recalcula y persiste el total de la factura desde sus guías.
     * Útil tras agregar/quitar guías en modo edición (admin).
     */
    public function recalculateTotal(Invoice $invoice): void
    {
        $total = Shipment::withoutGlobalScopes()
            ->where('invoice_id', $invoice->id)
            ->sum('total');

        $invoice->update(['total' => $total]);
    }
}
