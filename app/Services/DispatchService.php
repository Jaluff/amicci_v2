<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\Dispatch;
use App\Models\TransportRoute;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DispatchService
{
    public function createDispatch(array $data): Dispatch
    {
        return DB::transaction(function () use ($data) {
            /** @var Company $company */
            $company = Company::lockForUpdate()->findOrFail(session('company_id'));

            $company->last_dispatch_number++;
            $company->save();

            $data['dispatch_number'] = $company->prefix . '-D' . str_pad(
                (string)$company->last_dispatch_number,
                8,
                '0',
                STR_PAD_LEFT
            );
            $data['company_id'] = $company->id;

            $dispatch = Dispatch::create($data);

            // Registrar estado inicial en el historial (creación no pasa por transitionTo)
            \App\Models\StatusHistory::create([
                'model_type' => Dispatch::class ,
                'model_id' => $dispatch->id,
                'from_status' => null,
                'to_status' => $dispatch->status,
                'comment' => 'Estado inicial al crear el despacho',
                'user_id' => \Illuminate\Support\Facades\Auth::id(),
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
            $newStatus = $data['status'] ?? null;
            $currentStatus = $dispatch->status; // Capturar ANTES de cualquier cambio

            // Separar campos de metadata
            $fieldsToUpdate = collect($data)->except('status', 'routes')->toArray();

            // 1. Actualizar campos de metadata directamente
            if (!empty($fieldsToUpdate)) {
                $dispatch->update($fieldsToUpdate);
            }

            // 2. Reasignar rutas SÓLO si el despacho está en "Cargado" (editable)
            //    Una vez que sale a "En viaje" las rutas ya no se puede cambiar
            if (isset($data['routes']) && $currentStatus === \App\StateMachines\DispatchStateMachine::STATUS_CARGADO) {
                $this->assignRoutes($dispatch, $data['routes']);
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
        // Validar que las rutas estén en estado "Cargada"
        $invalid = TransportRoute::whereIn('id', $routeIds)
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