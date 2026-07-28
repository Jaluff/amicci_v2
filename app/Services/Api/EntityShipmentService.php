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
        $ubicacion = !empty($filters['ubicacion']) ? $filters['ubicacion'] : null;
        $entidadBusqueda = !empty($filters['entidad']) ? $filters['entidad'] : null;
        $fechaInicio = !empty($filters['fecha_inicio']) ? $filters['fecha_inicio'] : null;
        $fechaFin = !empty($filters['fecha_fin']) ? $filters['fecha_fin'] : null;
        $perPage = (int) ($filters['per_page'] ?? 15);

        return Shipment::with(['remitente', 'destinatario', 'items'])
            ->where(function ($query) use ($partyId) {
                $query->where('remitente_id', $partyId)
                    ->orWhere('destinatario_id', $partyId);
            })
            ->when($fechaInicio && $fechaFin, function ($query) use ($fechaInicio, $fechaFin) {
                return $query->where(function ($q) use ($fechaInicio, $fechaFin) {
                    $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
                        ->orWhereDate('updated_at', '>=', $fechaInicio)
                        ->whereDate('updated_at', '<=', $fechaFin);
                });
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
