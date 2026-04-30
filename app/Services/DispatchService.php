<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Dispatch;
use App\Models\TransportRoute;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DispatchService
{
    public function createDispatch(array $data): Dispatch
    {
        return DB::transaction(function () use ($data) {
            // Numeración global para despachos (independiente de empresa)
            $lastDispatch = Dispatch::orderByDesc('id')->first();
            $nextNumber = ($lastDispatch ? (int) filter_var($lastDispatch->dispatch_number, FILTER_SANITIZE_NUMBER_INT) : 0) + 1;

            $data['dispatch_number'] = 'D' . str_pad((string) $nextNumber, 8, '0', STR_PAD_LEFT);

            $dispatch = Dispatch::create($data);

            // Registrar estado inicial en el historial (creación no pasa por transitionTo)
            \App\Models\StatusHistory::create([
                'model_type'      => Dispatch::class,
                'model_id'        => $dispatch->id,
                'from_status'     => null,
                'to_status'       => $dispatch->status,
                'comment'         => 'Estado inicial al crear el despacho',
                'user_id'         => \Illuminate\Support\Facades\Auth::id(),
                'transitioned_at' => now(),
            ]);

            if (!empty($data['routes'])) {
                $this->assignRoutes($dispatch, $data['routes']);
            }

            return $dispatch->load(['driver', 'origin', 'destination'])->loadCount('routes');
        });
    }

    public function updateDispatch(Dispatch $dispatch, array $data): Dispatch
    {
        return DB::transaction(function () use ($dispatch, $data) {
            $newStatus     = $data['status'] ?? null;
            $currentStatus = $dispatch->status; // Capturar ANTES de cualquier cambio

            // Separar campos de metadata
            $fieldsToUpdate = collect($data)->except('status', 'routes')->toArray();

            // 1. Actualizar campos de metadata directamente
            if (!empty($fieldsToUpdate)) {
                $dispatch->update($fieldsToUpdate);
            }

            // 2. Reasignar rutas SÓLO si el despacho está en "Cargado" (editable)
            if ($currentStatus === \App\StateMachines\DispatchStateMachine::STATUS_CARGADO) {
                // Si 'routes' no viene en el request (ej. se quitaron todas), pasamos array vacío
                $this->assignRoutes($dispatch, $data['routes'] ?? []);
            }

            // 3. Cambio de estado vía StateMachine (dispara cascada DESPUÉS de fijar rutas)
            if ($newStatus && $newStatus !== $currentStatus) {
                $dispatch->stateMachine()->transitionTo(
                    $newStatus,
                    'Actualizado desde formulario'
                );
                $dispatch->refresh();
            }

            return $dispatch->load(['driver', 'origin', 'destination'])->loadCount('routes');
        });
    }

    private function assignRoutes(Dispatch $dispatch, array $routeIds): void
    {
        // Validar que las rutas nuevas a asignar estén en estado "Cargada".
        // Las que YA pertenecen a este despacho se ignoran en esta validación para permitir actualizaciones de otros campos.
        $invalid = TransportRoute::whereIn('id', $routeIds)
            ->where('dispatch_id', '!=', $dispatch->id)
            ->where('status', '!=', 'Cargada')
            ->count();

        if ($invalid > 0) {
            throw new InvalidArgumentException('Solo se pueden asignar rutas con estado "Cargada".');
        }

        // Desasignar rutas previas que ya no están en la lista
        TransportRoute::where('dispatch_id', $dispatch->id)
            ->whereNotIn('id', $routeIds)
            ->update(['dispatch_id' => null]);

        // Asignar las nuevas rutas
        if (!empty($routeIds)) {
            TransportRoute::whereIn('id', $routeIds)
                ->update(['dispatch_id' => $dispatch->id]);
        }
    }
}