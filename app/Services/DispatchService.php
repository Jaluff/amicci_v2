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
            // Extracción segura del número final (luego del último guión) para evitar problemas con el nuevo formato
            $lastNumberPart = $lastDispatch ? explode('-', $lastDispatch->dispatch_number) : [0];
            $nextNumber = (int) end($lastNumberPart) + 1;

            $branchId = $data['origin_id'] ?? 0;
            
            // Usamos AMI genérico ya que despachos agrupa rutas de múltiples empresas (no tiene company_id)
            $data['dispatch_number'] = sprintf('AMI-%d-D-%08d', $branchId, $nextNumber);

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