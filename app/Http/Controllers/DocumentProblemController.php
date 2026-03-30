<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DocumentProblem;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentProblemController extends Controller
{
    private array $modelMap = [
        'shipment' => \App\Models\Shipment::class,
        'route'    => \App\Models\TransportRoute::class,
        'dispatch' => \App\Models\Dispatch::class,
        'delivery' => \App\Models\Delivery::class,
    ];

    /**
     * Registra un nuevo evento en el historial de problemas del documento.
     * Cuando model_type='shipment' actualiza ubicacion_actual automáticamente.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'model_type' => ['required', 'string', 'in:' . implode(',', array_keys($this->modelMap))],
            'model_id'   => ['required', 'integer', 'min:1'],
            'is_active'  => ['required', 'boolean'],
            'comment'    => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $modelClass = $this->modelMap[$data['model_type']];
        $model      = $modelClass::findOrFail($data['model_id']);

        DB::transaction(function () use ($data, $model, $modelClass) {
            DocumentProblem::create([
                'documentable_type' => $modelClass,
                'documentable_id'   => $model->getKey(),
                'is_active'         => $data['is_active'],
                'comment'           => $data['comment'],
                'user_id'           => Auth::id(),
            ]);

            if ($data['model_type'] === 'shipment') {
                /** @var \App\Models\Shipment $model */
                if ($data['is_active']) {
                    $model->update(['ubicacion_actual' => 'Con problemas']);
                } else {
                    $newStatus = $this->resolveRestoredStatus($model);
                    $model->update(['ubicacion_actual' => $newStatus]);
                }
            }
        });

        return response()->json([
            'success'    => true,
            'message'    => $data['is_active']
                ? 'Problema registrado. La guía fue marcada como "Con problemas".'
                : 'Problema resuelto. La guía fue restaurada.',
            'has_active' => (bool) $data['is_active'],
            'new_status' => $data['model_type'] === 'shipment' ? $model->fresh()->ubicacion_actual : null,
        ]);
    }

    private function resolveRestoredStatus(Shipment $shipment): string
    {
        if ($shipment->delivery_id) {
            return 'En reparto';
        }

        if ($shipment->transport_route_id) {
            $routeStatus = $shipment->transportRoute?->status ?? '';
            return match ($routeStatus) {
                'En viaje'  => 'En transito',
                'Entregada' => 'Dto destino',
                default     => 'Dto destino',
            };
        }

        return 'Dto destino';
    }

    /**
     * Historial de problemas de un documento.
     * GET /documents/problem?model_type=shipment&model_id=5
     */
    public function history(Request $request)
    {
        $data = $request->validate([
            'model_type' => ['required', 'string', 'in:' . implode(',', array_keys($this->modelMap))],
            'model_id'   => ['required', 'integer', 'min:1'],
        ]);

        $modelClass = $this->modelMap[$data['model_type']];
        $model      = $modelClass::findOrFail($data['model_id']);

        $history = $model->problems()->with('user:id,name')->latest()->get();

        return response()->json([
            'history'    => $history,
            'has_active' => $model->hasActiveProblem(),
        ]);
    }

    /**
     * Guías con problemas activos para un modelo padre (route/dispatch/delivery).
     * GET /documents/problem/shipments?model_type=route&model_id=5
     */
    public function shipmentProblems(Request $request)
    {
        $data = $request->validate([
            'model_type' => ['required', 'string', 'in:route,dispatch,delivery'],
            'model_id'   => ['required', 'integer', 'min:1'],
        ]);

        $modelClass = $this->modelMap[$data['model_type']];
        $model      = $modelClass::findOrFail($data['model_id']);

        $shipments = $model->shipments()
            ->where('ubicacion_actual', '=', 'Con problemas')
            ->with(['origin:id,nombre', 'destination:id,nombre', 'currentProblem'])
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'numero'     => $s->numero,
                'origen'     => $s->origin?->nombre ?? '-',
                'destino'    => $s->destination?->nombre ?? '-',
                'problema'   => $s->currentProblem?->comment ?? '-',
                'problem_at' => $s->currentProblem?->created_at?->format('d/m/Y H:i') ?? '-',
            ]);

        return response()->json([
            'shipments' => $shipments->values(),
        ]);
    }
}