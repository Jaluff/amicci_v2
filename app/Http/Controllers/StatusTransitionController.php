<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dispatch;
use App\Models\Shipment;
use App\Models\TransportRoute;
use App\StateMachines\Exceptions\InvalidTransitionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StatusTransitionController extends Controller
{
    /**
     * Mapa de tipos permitidos → modelo correspondiente.
     * Agregar aquí los nuevos documentos (Reparto, etc.) sin tocar la lógica.
     */
    private array $modelMap = [
        'shipment' => Shipment::class ,
        'route' => TransportRoute::class ,
        'dispatch' => Dispatch::class ,
    ];

    public function transition(Request $request): JsonResponse
    {
        $request->validate([
            'model_type' => ['required', 'string', 'in:' . implode(',', array_keys($this->modelMap))],
            'model_id' => ['required', 'integer'],
            'status' => ['required', 'string'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $modelClass = $this->modelMap[$request->model_type];
        $model = $modelClass::findOrFail($request->model_id);

        try {
            $updated = $model->stateMachine()->transitionTo(
                $request->status,
                $request->comment
            );

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado exitosamente.',
                'new_status' => $updated->{ $model->stateMachine()->currentStatus() === $request->status
                ? 'status'
                : 'ubicacion_actual'} ?? $request->status,
            ]);

        }
        catch (InvalidTransitionException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Retorna las transiciones disponibles para un modelo dado.
     * Úsalo para renderizar dinámicamente los botones de estado en el frontend.
     */
    public function available(Request $request): JsonResponse
    {
        $request->validate([
            'model_type' => ['required', 'string', 'in:' . implode(',', array_keys($this->modelMap))],
            'model_id' => ['required', 'integer'],
        ]);

        $modelClass = $this->modelMap[$request->model_type];
        $model = $modelClass::findOrFail($request->model_id);
        $sm = $model->stateMachine();
        $current = $sm->currentStatus();
        $transitions = $sm->transitions();

        return response()->json([
            'current' => $current,
            'available' => $transitions[$current] ?? [],
        ]);
    }
}