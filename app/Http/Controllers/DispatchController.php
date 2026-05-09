<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDispatchRequest;
use App\Http\Requests\UpdateDispatchRequest;
use App\Http\Resources\DispatchResource;
use App\Models\Branch;
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
    ) {
    }

    public function index(): View
    {
        $branches = Branch::where('active', true)->orderBy('code')->get();
        return view('dispatches.index', compact('branches'));
    }

    public function datatable(Request $request): JsonResponse
    {
        $query = Dispatch::query()
            ->with(['driver', 'origin', 'destination'])
            ->withCount(['routes', 'shipments'])
            ->withCount(['shipments as problem_count' => function ($q) {
                $q->whereHas('problems', fn($query) => $query->where('is_active', true));
            }]);

        if ($request->filled('company_id')) {
            // Ignorar company_id si viene en el request de filtros antiguos, 
            // o eliminar si ya no se usa.
        }

        if ($request->filled('origen_id')) {
            $query->where('origin_id', $request->origen_id);
        }
        if ($request->filled('destino_id')) {
            $query->where('destination_id', $request->destino_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }
        if ($request->filled('numero_documento')) {
            $query->where('dispatch_number', 'like', '%' . $request->numero_documento . '%');
        }
        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'Con problemas') {
                $query->whereHas('shipments', function ($sq) {
                    $sq->whereHas('problems', fn($pq) => $pq->where('is_active', true));
                });
            } else {
                $query->where('status', $estado);
            }
        }

        $isAdminOrSupervisor = auth()->user()->hasAnyRole(['admin', 'supervisor', 'Supervisor']);

        return DataTables::of($query->orderByDesc('dispatches.created_at'))

            ->editColumn('dispatch_number', function ($row) {
                $numberHtml = "<span class='font-mono font-bold text-gray-800 dark:text-gray-200'>{$row->dispatch_number}</span>";
                if ($row->problem_count > 0) {
                    $numberHtml .= " <span class='text-red-500 animate-pulse font-bold ml-1' style='color: #dc2626 !important;' title='Contiene guías con problemas abiertos'>⚠</span>";
                }
                return $numberHtml;
            })
            ->addColumn('fecha', function ($row) {
                return $row->created_at ? $row->created_at->format('d/m/Y') : '-';
            })
            ->addColumn('ruta_corta', function ($row) {
                $or = mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->origin?->name ?? '-'));
                $ds = mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->destination?->name ?? '-'));
                return "<div class='flex flex-col gap-0.5 items-center'>
                            <span class='px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 text-[9px] font-bold w-fit'>$or</span>
                            <span class='px-1.5 py-0.5 rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 text-[9px] font-bold w-fit'>$ds</span>
                        </div>";
            })
            ->addColumn('acciones', function ($row) use ($isAdminOrSupervisor) {
                $editUrl = route('dispatches.edit', $row->id);
                $deleteUrl = route('dispatches.destroy', $row->id);
                $csrf = csrf_token();
                $confirm = 'return confirm(\'¿Eliminar este despacho?\')';

                $sm = $row->stateMachine();
                $currentStatus = $sm->currentStatus();
                $availableTransitions = $sm->transitions()[$currentStatus] ?? [];

                $btnConfig = [
                    'En viaje' => [
                        'label'   => '🚛 En Viaje',
                        'class'   => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                        'confirm' => '¿Confirmar inicio de viaje?',
                    ],
                    'Arribado' => [
                        'label'   => '✅ Arribado',
                        'class'   => 'bg-green-600 hover:bg-green-700 text-white',
                        'confirm' => '¿Confirmar llegada a destino?',
                    ],
                    'Cargado' => [
                        'label'   => '↩ Revertir',
                        'class'   => 'bg-gray-500 hover:bg-gray-600 text-white',
                        'confirm' => '¿Deseas revertir este despacho a estado inicial? Las rutas también se revertirán.',
                    ],
                ];

                $statusButtons = "";
                foreach ($availableTransitions as $transition) {
                    $cfg = $btnConfig[$transition] ?? ['label' => $transition, 'class' => 'bg-gray-600 text-white', 'confirm' => null];
                    $confAttr = $cfg['confirm'] ? "data-confirm='{$cfg['confirm']}'" : "";
                    $statusButtons .= "<button type='button' class='inline-flex items-center gap-1 px-2.5 py-1.5 mb-1 sm:mb-0 rounded-md font-bold text-xs shadow-sm transition-all {$cfg['class']}' data-model-type='dispatch' data-model-id='{$row->id}' data-transition='{$transition}' {$confAttr} title='{$cfg['label']}'>{$cfg['label']}</button>";
                }

                $deleteForm = "";
                // El usuario aclara: si tiene 0 RUTAS O está en estado Cargado, permitir eliminar (si es admin/supervisor)
                $canDelete = $isAdminOrSupervisor && (
                    $row->routes_count == 0 && 
                    $currentStatus === \App\StateMachines\DispatchStateMachine::STATUS_CARGADO
                );

                if ($canDelete) {
                    $deleteForm = "
                    <form action='{$deleteUrl}' method='POST' onsubmit='{$confirm}' class='inline m-0'>
                        <input type='hidden' name='_token' value='{$csrf}'>
                        <input type='hidden' name='_method' value='DELETE'>
                        <button type='submit' title='Eliminar' class='inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 dark:hover:text-red-300 transition-colors'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'/><path d='M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6'/><path d='M10 11v6'/><path d='M14 11v6'/><path d='M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2'/></svg>
                        </button>
                    </form>";
                }

                return "<div class='flex items-center gap-2 flex-wrap'>
                    {$statusButtons}
                    <div class='flex items-center gap-1'>
                        <a href='{$editUrl}' title='Editar' class='inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 dark:hover:text-blue-300 transition-colors'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                        </a>
                        {$deleteForm}
                    </div>
                </div>";
            })
            ->rawColumns(['acciones', 'dispatch_number', 'ruta_corta'])
            ->make(true);
    }

    public function availableRoutes(Request $request): JsonResponse
    {
        $query = TransportRoute::query()
            ->select('transport_routes.*', 'companies.prefix as empresa_prefix', 'companies.color as empresa_color')
            ->join('companies', 'transport_routes.company_id', '=', 'companies.id')
            ->with(['origin', 'destination'])
            ->withCount('shipments')
            ->withCount(['shipments as problem_count' => function ($q) {
                $q->whereHas('problems', fn($query) => $query->where('is_active', true));
            }])
            ->where('status', 'Cargada')
            ->whereNull('dispatch_id');

        // Filtrar por sucursal de origen
        if ($request->filled('origin_id')) {
            $query->where('transport_routes.origin_id', $request->origin_id);
        }
        // Filtrar por sucursal de destino
        if ($request->filled('destination_id')) {
            $query->where('transport_routes.destination_id', $request->destination_id);
        }

        return DataTables::of($query)
            ->addColumn('empresa', function ($row) {
                $color = $row->empresa_color ?? '#6366f1';
                return "<span class='px-2 py-1 rounded-full text-[10px] font-bold text-white shadow-sm' style='background-color: {$color}'>{$row->empresa_prefix}</span>";
            })
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="route-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                    value="' . $row->id . '"
                    data-numero="' . $row->route_number . '"
                    data-origen="' . ($row->origin->name ?? '-') . '"
                    data-destino="' . ($row->destination->name ?? '-') . '"
                    data-rutas="' . $row->shipments_count . '"
                    data-estado="' . $row->status . '"
                    data-has-problem="' . ($row->problem_count > 0 ? 'true' : 'false') . '">';
            })
            ->addColumn('origen_nombre', fn($row) => mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->origin?->name ?? $row->origin?->nombre ?? '-')))
            ->addColumn('destino_nombre', fn($row) => mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->destination?->name ?? $row->destination?->nombre ?? '-')))
            ->rawColumns(['check', 'empresa'])
            ->make(true);
    }

    public function create(Request $request): View
    {
        $drivers  = Driver::all(['id', 'name', 'dni']);
        $branches = Branch::where('active', true)
            ->permitted()
            ->orderBy('code')
            ->get();

        return view('dispatches.create', compact('drivers', 'branches'));
    }

    public function store(StoreDispatchRequest $request)
    {
        $data = $request->validated();

        try {
            $this->dispatchService->createDispatch($data);
            return redirect()->route('dispatches.index')->with('success', 'Despacho creado exitosamente.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['routes' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Dispatch $dispatch): View
    {
        $drivers  = Driver::all(['id', 'name', 'dni']);
        $branches = Branch::where('active', true)
            ->permitted()
            ->orderBy('code')
            ->get();

        $dispatch->load(['routes' => function ($q) {
            $q->select(['transport_routes.id', 'route_number', 'origin_id', 'destination_id', 'dispatch_id', 'status'])
                ->with(['origin:id,name', 'destination:id,name'])
                ->withCount('shipments');
        }])->loadCount('shipments');

        return view('dispatches.edit', compact('dispatch', 'drivers', 'branches'));
    }

    public function update(UpdateDispatchRequest $request, Dispatch $dispatch)
    {
        try {
            $this->dispatchService->updateDispatch($dispatch, $request->validated());
            return redirect()->route('dispatches.index')->with('success', 'Despacho actualizado exitosamente.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['routes' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Dispatch $dispatch)
    {
        abort_if(!auth()->user()->hasAnyRole(['admin', 'supervisor', 'Supervisor']), 403, 'No tienes permisos para anular documentos.');

        // Permitir anular SOLO si está en estado base "Cargado" Y no tiene rutas (está vacío)
        if ($dispatch->status !== \App\StateMachines\DispatchStateMachine::STATUS_CARGADO || $dispatch->routes()->count() > 0) {
            return redirect()->route('dispatches.index')->with('error', 'Solo se pueden anular despachos que no tengan rutas asignadas y estén en estado inicial.');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($dispatch) {
            // Desasignar rutas
            $dispatch->routes()->update(['dispatch_id' => null]);
            $dispatch->delete();
        });

        return redirect()->route('dispatches.index')->with('success', 'Despacho anulado y rutas liberadas.');
    }

    public function show(Dispatch $dispatch): JsonResponse
    {
        return response()->json([
            'dispatch' => new DispatchResource($dispatch->load(['driver', 'origin', 'destination'])->loadCount('routes'))
        ]);
    }
}