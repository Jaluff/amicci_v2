<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRouteRequest;
use App\Http\Resources\TransportRouteResource;
use App\Models\Branch;
use App\Models\Shipment;
use App\Models\TransportRoute;
use App\Services\TransportRouteService;
use App\StateMachines\RouteStateMachine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class TransportRouteController extends Controller
{
    public function __construct(
        private TransportRouteService $routeService
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $companies = $user->companies;

        if ($companies->isEmpty()) {
            abort(403, 'No tienes ninguna empresa asignada.');
        }

        $company_id = $request->input('company_id');

        if (! $company_id) {
            if ($companies->count() === 1) {
                $company_id = $companies->first()->id;
            } else {
                return redirect()->route('routes.index')->with('error', 'Debes seleccionar una empresa primero.');
            }
        } elseif (! $companies->contains('id', $company_id)) {
            abort(403, 'No tienes permiso para operar en esta empresa.');
        }

        $branches = Branch::where('active', true)
            ->whereHas('companies', function ($q) use ($company_id) {
                $q->where('companies.id', $company_id);
            })
            ->orderBy('code')
            ->get();

        $userBranch = $user->branches->first();
        $defaultOriginId = $userBranch ? $userBranch->id : null;

        $selected_company = $companies->firstWhere('id', $company_id);

        return view('transportRoutes.create', compact('branches', 'selected_company', 'defaultOriginId'));
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
                'remitente.name as remitente_nombre',
                'destinatario.name as destinatario_nombre',
                'companies.prefix as empresa_prefix',
                'companies.color as empresa_color',
                DB::raw('(SELECT COALESCE(SUM(si.cantidad), 0) FROM shipment_items si WHERE si.shipment_id = shipments.id) as bultos_total'),
            ])
            ->join('companies', 'shipments.company_id', '=', 'companies.id')
            ->leftJoin('ubicaciones as origen', 'shipments.origen_id', '=', 'origen.id')
            ->leftJoin('ubicaciones as destino', 'shipments.destino_id', '=', 'destino.id')
            ->leftJoin('parties as remitente', 'shipments.remitente_id', '=', 'remitente.id')
            ->leftJoin('parties as destinatario', 'shipments.destinatario_id', '=', 'destinatario.id')
            ->whereNull('shipments.deleted_at')
            ->where('shipments.ubicacion_actual', '=', 'Dto origen')
            ->whereNull('shipments.transport_route_id');

        // Filtrar por sucursal de origen: guías cuya ubicación de origen pertenezca a la sucursal
        if ($request->filled('origin_id')) {
            $query->whereIn('shipments.origen_id', function ($sub) use ($request) {
                $sub->select('id')
                    ->from('ubicaciones')
                    ->where('branch_id', $request->origin_id);
            });
        }

        // Filtrar por sucursal de destino: guías cuya ubicación de destino pertenezca a la sucursal
        if ($request->filled('destination_id')) {
            $query->whereIn('shipments.destino_id', function ($sub) use ($request) {
                $sub->select('id')
                    ->from('ubicaciones')
                    ->where('branch_id', $request->destination_id);
            });
        }

        // Filtrar por empresa (Obligatorio en arquitectura stateless para evitar mezclar datos)
        if ($request->filled('company_id')) {
            $query->where('shipments.company_id', $request->company_id);
        }

        $query->withCount(['problems as has_active_problem' => fn ($q) => $q->where('is_active', true)])
            ->withCount(['problems as has_resolved_problem' => fn ($q) => $q->where('is_active', false)]);

        return DataTables::of($query)
            ->addColumn('empresa', function ($row) {
                $color = $row->empresa_color ?? '#6366f1';

                return "<span class='px-2 py-1 rounded-full text-[10px] font-bold text-white shadow-sm' style='background-color: {$color}'>{$row->empresa_prefix}</span>";
            })
            ->addColumn('bultos', function ($row) {
                return (int) ($row->bultos_total ?? 0);
            })
            ->addColumn('origen_nombre', fn ($row) => mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->origen_nombre ?? '-')))
            ->addColumn('destino_nombre', fn ($row) => mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->destino_nombre ?? '-')))
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="shipment-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500" 
                    value="'.$row->id.'" 
                    data-numero="'.$row->numero.'" 
                    data-remitente="'.($row->remitente_nombre ?? '-').'" 
                    data-destinatario="'.($row->destinatario_nombre ?? '-').'" 
                    data-bultos="'.(int) ($row->bultos_total ?? 0).'" 
                    data-estado="'.$row->ubicacion_actual.'" 
                    data-has-problem="'.($row->has_active_problem > 0 ? 'true' : 'false').'"
                    data-has-resolved-problem="'.($row->has_resolved_problem > 0 ? 'true' : 'false').'">';
            })
            ->editColumn('fecha', function ($row) {
                return Carbon::parse($row->fecha)->format('d/m/Y');
            })
            ->rawColumns(['check', 'empresa'])
            ->make(true);
    }

    public function index(): View|JsonResponse
    {
        $branches = Branch::where('active', true)->orderBy('code')->get();
        $userCompanies = auth()->user()->companies;

        return view('transportRoutes.index', compact('branches', 'userCompanies'));
    }

    public function datatable(Request $request)
    {
        $query = TransportRoute::query()
            ->select('transport_routes.*', 'companies.prefix as empresa_prefix', 'companies.color as empresa_color')
            ->leftJoin('companies', 'transport_routes.company_id', '=', 'companies.id')
            ->with(['origin', 'destination', 'dispatch'])
            ->withCount('shipments')
            ->withCount(['shipments as problem_count' => function ($q) {
                $q->whereHas('problems', fn ($query) => $query->where('is_active', true));
            }])
            ->whereIn('transport_routes.company_id', auth()->user()->companies->pluck('id'));

        if ($request->filled('company_id')) {
            $query->where('transport_routes.company_id', $request->company_id);
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
            $query->where('route_number', 'like', '%'.$request->numero_documento.'%');
        }
        if ($request->filled('estado')) {
            $estado = $request->estado;
            if ($estado === 'Con problemas') {
                $query->whereHas('shipments', function ($sq) {
                    $sq->whereHas('problems', fn ($pq) => $pq->where('is_active', true));
                });
            } else {
                $query->where('status', $estado);
            }
        }

        return DataTables::of($query->orderByDesc('transport_routes.created_at'))
            ->addColumn('empresa', function ($row) {
                $color = $row->empresa_color ?? '#6366f1';

                return "<span class='px-2 py-1 rounded-full text-[10px] font-bold text-white shadow-sm' style='background-color: {$color}'>{$row->empresa_prefix}</span>";
            })
            ->editColumn('route_number', function ($row) {
                $numberHtml = "<span class='font-mono font-bold text-gray-800 dark:text-gray-200'>{$row->route_number}</span>";
                if ($row->problem_count > 0) {
                    $numberHtml .= " <span class='text-amber-500 animate-pulse font-bold ml-1' style='color: #f59e0b !important;' title='Contiene guías con problemas abiertos'>⚠</span>";
                }

                return $numberHtml;
            })
            ->addColumn('ruta_corta', function ($row) {
                $or = mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->origin?->name ?? '-'));
                $ds = mb_strtoupper(str_ireplace('SUCURSAL ', '', $row->destination?->name ?? '-'));

                return "<div class='flex flex-col gap-0.5 items-center'>
                            <span class='px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 text-[9px] font-bold w-fit'>$or</span>
                            <span class='px-1.5 py-0.5 rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 text-[9px] font-bold w-fit'>$ds</span>
                        </div>";
            })
            ->addColumn('acciones', function ($row) {
                $editUrl = route('routes.edit', $row->id);
                $deleteUrl = route('routes.destroy', $row->id);
                $csrf = csrf_token();
                $confirm = 'return confirm(\'¿Eliminar esta ruta?\')';
                $deleteForm = '';
                if ($row->shipments_count == 0 && $row->status === 'Cargada') {
                    $deleteForm = "
                    <form action='{$deleteUrl}' method='POST' onsubmit='{$confirm}' class='inline m-0'>
                        <input type='hidden' name='_token' value='{$csrf}'>
                        <input type='hidden' name='_method' value='DELETE'>
                        <button type='submit' title='Eliminar' class='inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 dark:hover:text-red-300 transition-colors'>
                                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'/><path d='M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6'/><path d='M10 11v6'/><path d='M14 11v6'/><path d='M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2'/></svg>
                            </button>
                        </form>";
                }

                return "<div class='flex items-center gap-2 justify-center'>
                        <a href='{$editUrl}' title='Editar' class='inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 dark:hover:text-blue-300 transition-colors'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                    </a>
                    {$deleteForm}
                    </div>";
            })
            ->rawColumns(['acciones', 'route_number', 'empresa', 'ruta_corta'])
            ->make(true);
    }

    public function store(StoreRouteRequest $request)
    {
        $data = $request->validated();
        if (! auth()->user()->companies->contains('id', $data['company_id'])) {
            abort(403, 'No tienes permiso para operar en esta empresa.');
        }

        $this->routeService->createRoute($data);

        return redirect()->route('routes.index')->with('success', 'Ruta creada exitosamente.');
    }

    public function edit(TransportRoute $route): View
    {
        $branches = Branch::where('active', true)
            ->orderBy('code')
            ->get();

        $defaultOriginId = $route->origin_id;

        $route->load(['shipments' => function ($q) {
            $q->select([
                'shipments.id', 'shipments.numero', 'shipments.origen_id', 'shipments.destino_id',
                'shipments.remitente_id', 'shipments.destinatario_id',
                'shipments.transport_route_id', 'shipments.ubicacion_actual',
            ])
                ->with([
                    'origin:id,nombre',
                    'destination:id,nombre',
                    'sender:id,name',
                    'recipient:id,name',
                ])
                ->withCount(['items as bultos' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(cantidad), 0)'));
                }]);
        }]);

        $route->load('dispatch');

        return view('transportRoutes.edit', compact('route', 'branches', 'defaultOriginId'));
    }

    public function update(StoreRouteRequest $request, TransportRoute $route)
    {
        $this->routeService->updateRoute($route, $request->validated());

        return redirect()->route('routes.index')->with('success', 'Ruta actualizada exitosamente.');
    }

    public function destroy(TransportRoute $route)
    {
        abort_if(! auth()->user()->hasAnyRole(['admin', 'Supervisor']), 403, 'No tienes permisos para anular documentos.');

        if ($route->status !== RouteStateMachine::STATUS_CARGADA) {
            return redirect()->route('routes.index')->with('error', 'No se puede anular una ruta cuyo estado no es "Cargada".');
        }

        DB::transaction(function () use ($route) {
            // Desasignar guías vinculadas
            $shipments = $route->shipments;
            foreach ($shipments as $s) {
                $s->update(['transport_route_id' => null]);
                $s->logActivity("Desvinculada por anulación de la ruta {$route->route_number}", 'unassigned_route');
            }
            $route->delete();
        });

        return redirect()->route('routes.index')->with('success', 'Ruta anulada y guías liberadas exitosamente.');
    }

    public function show(TransportRoute $ruta): JsonResponse
    {
        return response()->json([
            'route' => new TransportRouteResource($ruta->loadCount('shipments')),
        ]);
    }

    public function getShipments(TransportRoute $route): JsonResponse
    {
        $shipments = $route->shipments()
            ->with(['origin:id,nombre', 'destination:id,nombre', 'sender:id,name', 'recipient:id,name'])
            ->withCount(['items as bultos' => function ($q) {
                $q->select(DB::raw('COALESCE(SUM(cantidad), 0)'));
            }])
            ->get();

        return response()->json($shipments);
    }
}
