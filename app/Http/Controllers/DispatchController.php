<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispatchRequest;
use App\Http\Requests\UpdateDispatchRequest;
use App\Http\Resources\DispatchResource;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\TransportRoute;
use App\Services\DispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DispatchController extends Controller
{
    public function __construct(
        private DispatchService $dispatchService
        )
    {
    }

    public function index(): View
    {
        return view('dispatches.index');
    }

    public function datatable(): JsonResponse
    {
        $query = Dispatch::query()
            ->with(['driver', 'origin', 'destination'])
            ->withCount('routes')
            ->withCount(['shipments as problem_count' => function ($q) {
            $q->where('ubicacion_actual', '=', 'Con problemas');
        }]);

        return DataTables::of($query)
            ->addColumn('acciones', function ($row) {
            $editUrl = route('dispatches.edit', $row->id);
            $deleteUrl = route('dispatches.destroy', $row->id);
            $csrf = csrf_token();
            $confirm = "return confirm('¿Eliminar este despacho?')";

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
                $label = $row->dispatch_number ?? "Despacho #{$row->id}";
                return "<span class='dt-badge dt-badge-red animate-pulse cursor-pointer problem-badge' data-model-type='dispatch' data-model-id='{$row->id}' data-label='{$label}'>⚠ {$row->problem_count} problema" . ($row->problem_count > 1 ? 's' : '') . "</span>";
            }
            return "<span class='text-gray-400 dark:text-gray-600 text-xs'>—</span>";
        })
            ->rawColumns(['acciones', 'problemas'])
            ->make(true);
    }

    public function availableRoutes(Request $request): JsonResponse
    {
        $query = TransportRoute::with(['origin', 'destination'])
            ->withCount('shipments')
            ->where('status', 'Cargada')
            ->whereNull('dispatch_id');

        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->origin_id);
        }
        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        return DataTables::of($query)
            ->addColumn('check', function ($row) {
            return '<input type="checkbox" class="route-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                    value="' . $row->id . '"
                    data-numero="' . $row->route_number . '"
                    data-origen="' . ($row->origin->nombre ?? '-') . '"
                    data-destino="' . ($row->destination->nombre ?? '-') . '"
                    data-rutas="' . $row->shipments_count . '"
                    data-estado="' . $row->status . '">';
        })
            ->addColumn('origen_nombre', fn($row) => $row->origin->nombre ?? '-')
            ->addColumn('destino_nombre', fn($row) => $row->destination->nombre ?? '-')
            ->rawColumns(['check'])
            ->make(true);
    }

    public function create(): View
    {
        $drivers = Driver::all(['id', 'name', 'dni']);
        $ubicaciones = \App\Models\Ubicacion::orderBy('nombre')->get();
        return view('dispatches.create', compact('drivers', 'ubicaciones'));
    }

    public function store(StoreDispatchRequest $request)
    {
        try {
            $this->dispatchService->createDispatch($request->validated());
            return redirect()->route('dispatches.index')->with('success', 'Despacho creado exitosamente.');
        }
        catch (\InvalidArgumentException $e) {
            return back()->withErrors(['routes' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Dispatch $dispatch): View
    {
        $drivers = Driver::all(['id', 'name', 'dni']);
        $ubicaciones = \App\Models\Ubicacion::orderBy('nombre')->get();

        $dispatch->load(['routes' => function ($q) {
            $q->select(['transport_routes.id', 'route_number', 'origin_id', 'destination_id', 'dispatch_id', 'status'])
                ->with(['origin:id,nombre', 'destination:id,nombre'])
                ->withCount('shipments');
        }]);

        return view('dispatches.edit', compact('dispatch', 'drivers', 'ubicaciones'));
    }

    public function update(UpdateDispatchRequest $request, Dispatch $dispatch)
    {
        try {
            $this->dispatchService->updateDispatch($dispatch, $request->validated());
            return redirect()->route('dispatches.index')->with('success', 'Despacho actualizado exitosamente.');
        }
        catch (\InvalidArgumentException $e) {
            return back()->withErrors(['routes' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Dispatch $dispatch)
    {
        abort_if(!auth()->user()->hasAnyRole(['admin', 'Supervisor']), 403, 'No tienes permisos para anular documentos.');

        // Desasignar rutas antes de eliminar
        $dispatch->routes()->update(['dispatch_id' => null]);
        $dispatch->delete();
        return redirect()->route('dispatches.index')->with('success', 'Despacho eliminado.');
    }

    public function show(Dispatch $dispatch): JsonResponse
    {
        return response()->json([
            'dispatch' => new DispatchResource($dispatch->load(['driver', 'origin', 'destination'])->loadCount('routes'))
        ]);
    }
}