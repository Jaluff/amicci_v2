<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Deliverer;
use App\Models\Delivery;
use App\Models\Shipment;
use App\Models\Ubicacion;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryService $deliveryService)
    {
    }

    public function index(): View|JsonResponse
    {
        return view('deliveries.index');
    }

    public function datatable()
    {
        $query = Delivery::query()
            ->with(['deliverer', 'location'])
            ->withCount(['shipments as problem_count' => function ($q) {
            $q->where('ubicacion_actual', '=', 'Con problemas');
        }]);

        return DataTables::of($query)
            ->addColumn('acciones', function ($row) {
            $editUrl = route('deliveries.edit', $row->id);
            $deleteUrl = route('deliveries.destroy', $row->id);
            $csrf = csrf_token();
            $confirm = 'return confirm(\'¿Eliminar este reparto?\')';
            return "<div class='flex items-center gap-2'>
                        <a href='{$editUrl}' title='Editar' class='inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 dark:hover:text-blue-300 transition-colors'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                    </a>
                    <form action='{$deleteUrl}' method='POST' onsubmit='{$confirm}' class='inline m-0'>
                        <input type='hidden' name='_token' value='{$csrf}'>
                        <input type='hidden' name='_method' value='DELETE'>
                        <button type='submit' title='Eliminar' class='inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 dark:hover:text-red-300 transition-colors'>
                                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'/><path d='M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6'/><path d='M10 11v6'/><path d='M14 11v6'/><path d='M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2'/></svg>
                            </button>
                        </form>
                    </div>";
        })
            ->addColumn('problemas', function ($row) {
            if ($row->problem_count > 0) {
                $label = "Reparto #{$row->delivery_number}";
                return "<span class='dt-badge dt-badge-red animate-pulse cursor-pointer problem-badge' data-model-type='delivery' data-model-id='{$row->id}' data-label='{$label}'>⚠ {$row->problem_count} problema" . ($row->problem_count > 1 ? 's' : '') . "</span>";
            }
            return "<span class='text-gray-400 dark:text-gray-600 text-xs'>—</span>";
        })
            ->rawColumns(['acciones', 'problemas'])
            ->make(true);
    }

    public function create(): View
    {
        $deliverers = Deliverer::orderBy('name')->get();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        return view('deliveries.create', compact('deliverers', 'ubicaciones'));
    }

    public function store(StoreDeliveryRequest $request)
    {
        $this->deliveryService->createDelivery($request->validated());
        return redirect()->route('deliveries.index')->with('success', 'Reparto creado exitosamente.');
    }

    public function edit(Delivery $delivery): View
    {
        $deliverers = Deliverer::orderBy('name')->get();
        $ubicaciones = Ubicacion::orderBy('nombre')->get();

        $delivery->load(['shipments' => function ($q) {
            $q->select(['shipments.id', 'shipments.numero', 'shipments.origen_id', 'shipments.destino_id', 'shipments.delivery_id', 'shipments.ubicacion_actual'])
                ->with(['origin:id,nombre', 'destination:id,nombre'])
                ->withCount(['items as bultos' => function ($query) {
                $query->select(DB::raw('COALESCE(SUM(cantidad), 0)'));
            }
                ]);
        }]);

        return view('deliveries.edit', compact('delivery', 'deliverers', 'ubicaciones'));
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        $this->deliveryService->updateDelivery($delivery, $request->validated());
        return redirect()->route('deliveries.index')->with('success', 'Reparto actualizado exitosamente.');
    }

    public function destroy(Delivery $delivery)
    {
        abort_if(!auth()->user()->hasAnyRole(['admin', 'Supervisor']), 403, 'No tienes permisos para anular documentos.');

        $delivery->shipments()->update([
            'delivery_id' => null,
            'ubicacion_actual' => 'Dto destino', // return to Dto destino if deleted
        ]);
        $delivery->delete();
        return redirect()->route('deliveries.index')->with('success', 'Reparto eliminado exitosamente.');
    }

    public function availableShipments(Request $request)
    {
        $query = Shipment::query()
            ->select([
            'shipments.id',
            'shipments.numero',
            'shipments.fecha',
            'shipments.total',
            'shipments.ubicacion_actual',
            'origen.nombre as origen_nombre',
            'destino.nombre as destino_nombre',
            DB::raw('(SELECT COALESCE(SUM(si.cantidad), 0) FROM shipment_items si WHERE si.shipment_id = shipments.id) as bultos_total')
        ])
            ->leftJoin('ubicaciones as origen', 'shipments.origen_id', '=', 'origen.id')
            ->leftJoin('ubicaciones as destino', 'shipments.destino_id', '=', 'destino.id')
            ->whereNull('shipments.deleted_at')
            ->where('shipments.ubicacion_actual', '=', 'Dto destino');

        // We only allow those that do not have a delivery, or belong to the current one being edited
        if ($request->filled('delivery_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('shipments.delivery_id')
                    ->orWhere('shipments.delivery_id', $request->delivery_id);
            });
        }
        else {
            $query->whereNull('shipments.delivery_id');
        }

        if ($request->filled('location_id')) {
            $query->where('shipments.destino_id', $request->location_id);
        }

        return DataTables::of($query)
            ->addColumn('bultos', function ($row) {
            return (int)($row->bultos_total ?? 0);
        })
            ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="shipment-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500" value="' . $row->id . '" data-numero="' . $row->numero . '" data-origen="' . $row->origen_nombre . '" data-destino="' . $row->destino_nombre . '" data-bultos="' . (int)($row->bultos_total ?? 0) . '" data-estado="' . $row->ubicacion_actual . '">';
        })
            ->editColumn('fecha', function ($row) {
            return Carbon::parse($row->fecha)->format('d/m/Y');
        })
            ->rawColumns(['check'])
            ->make(true);
    }
}