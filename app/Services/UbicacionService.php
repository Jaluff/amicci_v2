<?php

namespace App\Services;

use App\Models\Ubicacion;
use Illuminate\Database\Eloquent\Collection;

class UbicacionService
{
    /**
     * Get all locations.
     *
     * @return Collection<int, Ubicacion>
     */
    public function getAll(): Collection
    {
        return Ubicacion::orderBy('nombre')->get();
    }

    /**
     * Store a new location.
     *
     * @param array{nombre: string} $data
     * @return Ubicacion
     */
    public function store(array $data): Ubicacion
    {
        return Ubicacion::create($data);
    }

    /**
     * Update an existing location.
     *
     * @param Ubicacion $ubicacion
     * @param array{nombre: string} $data
     * @return bool
     */
    public function update(Ubicacion $ubicacion, array $data): bool
    {
        return $ubicacion->update($data);
    }

    /**
     * Delete a location.
     *
     * @param Ubicacion $ubicacion
     * @return bool
     * @throws \Exception
     */
    public function delete(Ubicacion $ubicacion): bool
    {
        // Check if used in shipments (guías)
        $usedInShipments = \App\Models\Shipment::where('origen_id', $ubicacion->id)
            ->orWhere('destino_id', $ubicacion->id)
            ->exists();

        if ($usedInShipments) {
            throw new \Exception('No se puede eliminar la ubicación porque está vinculada a una o más guías.');
        }

        // Check if used in transport routes
        $usedInRoutes = \App\Models\TransportRoute::where('origin_id', $ubicacion->id)
            ->orWhere('destination_id', $ubicacion->id)
            ->exists();

        if ($usedInRoutes) {
            throw new \Exception('No se puede eliminar la ubicación porque está vinculada a una o más rutas de transporte.');
        }

        return (bool) $ubicacion->delete();
    }
}
