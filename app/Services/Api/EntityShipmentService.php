<?php

namespace App\Services\Api;

use App\Models\Party;
use App\Models\Shipment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EntityShipmentService
{
    /**
     * Obtener guías/envíos filtrados para la entidad dada.
     */
    public function getShipmentsForParty(Party $party, array $filters): LengthAwarePaginator
    {
        $partyId = $party->id;
        $ubicacion = $filters['ubicacion'] ?? null;
        $entidadBusqueda = $filters['entidad'] ?? null;
        $fechaInicio = $filters['fecha_inicio'] ?? date('Y-m-d', strtotime('-1 month'));
        $fechaFin = $filters['fecha_fin'] ?? date('Y-m-d');
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Shipment::with(['remitente', 'destinatario', 'items'])
            ->where(function ($query) use ($partyId) {
                $query->where('remitente_id', $partyId)
                    ->orWhere('destinatario_id', $partyId);
            })
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereBetween('fecha', [$fechaInicio, $fechaFin])
                    ->orWhereDate('updated_at', '>=', $fechaInicio)
                    ->whereDate('updated_at', '<=', $fechaFin);
            })
            ->when($ubicacion, function ($query) use ($ubicacion) {
                return $query->where('ubicacion_actual', $ubicacion);
            })
            ->when($entidadBusqueda, function ($query) use ($entidadBusqueda) {
                return $query->where(function ($q) use ($entidadBusqueda) {
                    $q->whereHas('remitente', function ($r) use ($entidadBusqueda) {
                        $r->where('name', 'like', "%{$entidadBusqueda}%");
                    })->orWhereHas('destinatario', function ($d) use ($entidadBusqueda) {
                        $d->where('name', 'like', "%{$entidadBusqueda}%");
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }
}
