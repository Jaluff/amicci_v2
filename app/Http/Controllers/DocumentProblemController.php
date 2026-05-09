<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Dispatch;
use App\Models\DocumentProblem;
use App\Models\Shipment;
use App\Models\TransportRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentProblemController extends Controller
{
    private array $modelMap = [
        'shipment' => Shipment::class,
        'route' => TransportRoute::class,
        'dispatch' => Dispatch::class,
        'delivery' => Delivery::class,
    ];

    /**
     * Registra un nuevo evento en el historial de problemas del documento.
     * Cuando model_type='shipment' actualiza ubicacion_actual automáticamente.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'model_type' => ['required', 'string', 'in:'.implode(',', array_keys($this->modelMap))],
            'model_id' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $modelClass = $this->modelMap[$data['model_type']];
        $model = $modelClass::findOrFail($data['model_id']);

        DB::transaction(function () use ($data, $model, $modelClass) {
            // Cancelar todos los problemas anteriores que quedaron abiertos
            // para este documento. Así el query where('is_active', true)
            // funcionará siempre mostrando SOLO si hay un problema realmente actual.
            $model->problems()->where('is_active', true)->update(['is_active' => false]);

            DocumentProblem::create([
                'documentable_type' => $modelClass,
                'documentable_id' => $model->getKey(),
                'is_active' => $data['is_active'],
                'comment' => $data['comment'],
                'user_id' => Auth::id(),
            ]);

            if ($data['model_type'] === 'shipment') {
                /** @var Shipment $model */
                if ($data['is_active']) {
                    // El usuario prefiere NO desvincular del reparto automáticamente.
                    // La guía se mantiene en "En reparto" pero con el tag de problema.
                    $model->logActivity("Problema reportado: {$data['comment']}", 'problem_reported');
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => $data['is_active']
                ? 'Problema registrado.'
                : 'Problema resuelto.',
            'has_active' => (bool) $data['is_active'],
        ]);
    }

    /**
     * Historial de problemas de un documento.
     * GET /documents/problem?model_type=shipment&model_id=5
     */
    public function history(Request $request)
    {
        $data = $request->validate([
            'model_type' => ['required', 'string', 'in:'.implode(',', array_keys($this->modelMap))],
            'model_id' => ['required', 'integer', 'min:1'],
        ]);

        $modelClass = $this->modelMap[$data['model_type']];
        $model = $modelClass::findOrFail($data['model_id']);

        $history = $model->problems()->with('user:id,name')->latest()->get();

        return response()->json([
            'history' => $history,
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
            'model_id' => ['required', 'integer', 'min:1'],
        ]);

        $modelClass = $this->modelMap[$data['model_type']];
        $model = $modelClass::findOrFail($data['model_id']);

        $shipments = $model->shipments()
            ->whereHas('problems', fn ($q) => $q->where('is_active', true))
            ->with(['origin:id,nombre', 'destination:id,nombre', 'currentProblem'])
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'numero' => $s->numero,
                'origen' => $s->origin?->nombre ?? '-',
                'destino' => $s->destination?->nombre ?? '-',
                'problema' => $s->currentProblem?->comment ?? '-',
                'problem_at' => $s->currentProblem?->created_at?->format('d/m/Y H:i') ?? '-',
            ]);

        return response()->json([
            'shipments' => $shipments->values(),
        ]);
    }
}
