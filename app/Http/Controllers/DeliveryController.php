<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\Branch;
use App\Models\Deliverer;
use App\Models\Delivery;
use App\Models\Shipment;
use App\Services\DeliveryService;
use App\StateMachines\DeliveryStateMachine;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryService $deliveryService) {}

    public function index(): View|JsonResponse
    {
        $branches = Branch::where('active', true)->orderBy('code')->get();
        $userCompanies = auth()->user()->companies;

        return view('deliveries.index', compact('branches', 'userCompanies'));
    }

    public function datatable(Request $request)
    {
        $query = Delivery::query()
            ->select('deliveries.*', 'companies.prefix as empresa_prefix', 'companies.color as empresa_color')
            ->leftJoin('companies', 'deliveries.company_id', '=', 'companies.id')
            ->with(['deliverer', 'location'])
            ->withCount('shipments')
            ->withCount(['shipments as total_bultos' => function ($q) {
                $q->select(DB::raw('COALESCE(SUM(shipment_items.cantidad), 0)'))
                    ->join('shipment_items', 'shipments.id', '=', 'shipment_items.shipment_id');
            }])
            ->withCount(['shipments as problem_count' => function ($q) {
                $q->whereHas('problems', fn ($query) => $query->where('is_active', true));
            }])
            ->whereIn('deliveries.company_id', auth()->user()->companies->pluck('id'));

        if ($request->filled('company_id')) {
            $query->where('deliveries.company_id', $request->company_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('load_date', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('load_date', '<=', $request->fecha_fin);
        }
        if ($request->filled('numero_documento')) {
            $query->where('delivery_number', 'like', '%'.$request->numero_documento.'%');
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

        $isAdminOrSupervisor = auth()->user()->hasAnyRole(['admin', 'supervisor', 'Supervisor']);

        return DataTables::of($query->orderByDesc('deliveries.created_at'))
            ->addColumn('acciones', function ($row) use ($isAdminOrSupervisor) {
                $editUrl = route('deliveries.edit', $row->id);
                $deleteUrl = route('deliveries.destroy', $row->id);
                $csrf = csrf_token();
                $confirm = 'return confirm(\'¿Eliminar este reparto?\')';

                $sm = $row->stateMachine();
                $currentStatus = $sm->currentStatus();
                $availableTransitions = $sm->transitions()[$currentStatus] ?? [];

                $btnConfig = [
                    'En reparto' => [
                        'label' => '🚛 En Reparto',
                        'class' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                        'confirm' => '¿Confirmar que el reparto inicia?',
                    ],
                    'Finalizado' => [
                        'label' => '✅ Finalizado',
                        'class' => 'bg-green-600 hover:bg-green-700 text-white',
                        'confirm' => '¿Confirmar finalizar?',
                    ],
                    'Listo' => [
                        'label' => '🔙 Revertir a Listo',
                        'class' => 'bg-gray-600 hover:bg-gray-700 text-white',
                        'confirm' => '¿Deshacer el inicio de reparto? Esto devolverá las guías a destino.',
                    ],
                ];

                $statusButtons = '';
                foreach ($availableTransitions as $transition) {
                    $cfg = $btnConfig[$transition] ?? ['label' => $transition, 'class' => 'bg-gray-600 text-white', 'confirm' => null];
                    $confAttr = $cfg['confirm'] ? "data-confirm='{$cfg['confirm']}'" : '';
                    $statusButtons .= "<button type='button' class='inline-flex items-center gap-1 px-2.5 py-1.5 mb-1 sm:mb-0 rounded-md font-bold text-xs shadow-sm transition-all {$cfg['class']}' data-model-type='delivery' data-model-id='{$row->id}' data-transition='{$transition}' {$confAttr} title='{$cfg['label']}'>{$cfg['label']}</button>";
                }

                $printUrl = route('deliveries.print', $row->id);
                $delivererShowUrl = route('deliverer.show', $row->id);

                $delivererShowBtn = "
                    <a href='{$delivererShowUrl}' data-delivery-number='{$row->delivery_number}' class='btn-deliverer-modal-show inline-flex items-center justify-center p-2 rounded-md bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 dark:bg-purple-900/40 dark:text-purple-400 dark:border-purple-800 dark:hover:bg-purple-800/60 transition-colors' title='Ver Vista Móvil (Repartidor)'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                            <rect x='5' y='2' width='14' height='20' rx='2' ry='2'></rect>
                            <line x1='12' y1='18' x2='12.01' y2='18'></line>
                        </svg>
                    </a>
                ";

                $deleteForm = '';
                if ($row->shipments_count == 0 && $currentStatus === DeliveryStateMachine::READY && $isAdminOrSupervisor) {
                    $deleteForm = "
                    <form action='{$deleteUrl}' method='POST' onsubmit=\"{$confirm}\" class='inline m-0'>
                        <input type='hidden' name='_token' value='{$csrf}'>
                        <input type='hidden' name='_method' value='DELETE'>
                        <button type='submit' title='Eliminar' class='inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 dark:hover:text-red-300 transition-colors'>
                                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'/><path d='M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6'/><path d='M10 11v6'/><path d='M14 11v6'/><path d='M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2'/></svg>
                            </button>
                        </form>";
                }

                $devolutionBtn = '';
                if ($row->problem_count > 0) {
                    $devolutionBtn = "
                        <button type='button' title='Guías a devolver' 
                            class='btn-show-devolutions inline-flex items-center justify-center p-2 rounded-md bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 dark:bg-amber-900/40 dark:text-amber-400 dark:border-amber-800 dark:hover:bg-amber-800/60 transition-colors'
                            data-model-id='{$row->id}' data-numero='{$row->delivery_number}'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M3 6h18'/><path d='M3 10h18'/><path d='M3 14h18'/><path d='M3 18h18'/></svg>
                        </button>
                    ";
                }

                return "<div class='flex items-center gap-2 flex-wrap'>
                        {$statusButtons}
                        <div class='flex items-center gap-1'>
                            {$delivererShowBtn}
                            <a href='{$editUrl}' title='Editar' class='inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 dark:hover:text-blue-300 transition-colors'>
                                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                            </a>
                            <a href='{$printUrl}' target='_blank' title='Imprimir Reparto' class='inline-flex items-center justify-center p-2 rounded-md bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-400 dark:border-yellow-800 dark:hover:bg-yellow-800/60 transition-colors'>
                                <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 6 2 18 2 18 9'/><path d='M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'/><rect x='6' y='14' width='12' height='8'/></svg>
                            </a>
                            {$devolutionBtn}
                            {$deleteForm}
                        </div>
                    </div>";
            })
            ->editColumn('delivery_number', function ($row) {
                $numberHtml = "<span class='font-mono font-bold text-gray-800 dark:text-gray-200'>{$row->delivery_number}</span>";
                if ($row->problem_count > 0) {
                    $numberHtml .= " <span class='text-amber-500 animate-pulse font-bold ml-1' style='color: #f59e0b !important;' title='Contiene guías con problemas abiertos'>⚠</span>";
                }

                return $numberHtml;
            })
            ->addColumn('guide_count', function ($row) {
                return $row->shipments_count;
            })
            ->addColumn('package_count', function ($row) {
                return (int) $row->total_bultos;
            })
            ->addColumn('empresa', function ($row) {
                return "<span class='font-bold text-gray-700 dark:text-gray-300'>{$row->empresa_prefix}</span>";
            })
            ->rawColumns(['acciones', 'delivery_number', 'empresa'])
            ->make(true);
    }

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
                return redirect()->route('deliveries.index')->with('error', 'Debes seleccionar una empresa primero.');
            }
        } elseif (! $companies->contains('id', $company_id)) {
            abort(403, 'No tienes permiso para operar en esta empresa.');
        }

        $deliverers = Deliverer::orderBy('name')->get();
        $branches = Branch::where('active', true)
            ->whereHas('companies', function ($q) use ($company_id) {
                $q->where('companies.id', $company_id);
            })
            ->permitted()
            ->orderBy('code')
            ->get();

        $existingPlates = Delivery::whereNotNull('vehicle_plate')
            ->where('vehicle_plate', '!=', '')
            ->distinct()
            ->pluck('vehicle_plate');

        $selected_company = $companies->firstWhere('id', $company_id);

        return view('deliveries.create', compact('deliverers', 'branches', 'existingPlates', 'selected_company'));
    }

    public function store(StoreDeliveryRequest $request)
    {
        $data = $request->validated();
        if (! auth()->user()->companies->contains('id', $data['company_id'])) {
            abort(403, 'No tienes permiso para operar en esta empresa.');
        }

        $this->deliveryService->createDelivery($data);

        return redirect()->route('deliveries.index')->with('success', 'Reparto creado exitosamente.');
    }

    public function edit(Delivery $delivery): View
    {
        $deliverers = Deliverer::orderBy('name')->get();
        $branches = Branch::where('active', true)
            ->permitted()
            ->orderBy('code')
            ->get();

        $delivery->load(['shipments' => function ($q) {
            $q->select(['shipments.id', 'shipments.numero', 'shipments.remitente_id', 'shipments.destinatario_id', 'shipments.delivery_id', 'shipments.ubicacion_actual'])
                ->with(['sender:id,name', 'recipient:id,name'])
                ->withCount(['items as bultos' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(cantidad), 0)'));
                }]);
        }]);

        $existingPlates = Delivery::whereNotNull('vehicle_plate')
            ->where('vehicle_plate', '!=', '')
            ->distinct()
            ->pluck('vehicle_plate');

        return view('deliveries.edit', compact('delivery', 'deliverers', 'branches', 'existingPlates'));
    }

    public function update(UpdateDeliveryRequest $request, Delivery $delivery)
    {
        $this->deliveryService->updateDelivery($delivery, $request->validated());

        return redirect()->route('deliveries.index')->with('success', 'Reparto actualizado exitosamente.');
    }

    public function destroy(Delivery $delivery)
    {
        abort_if(! auth()->user()->hasAnyRole(['admin', 'supervisor', 'Supervisor']), 403, 'No tienes permisos para anular documentos.');

        if ($delivery->status !== DeliveryStateMachine::READY) {
            return redirect()->route('deliveries.index')->with('error', 'No se puede anular un reparto cuyo estado no es "Listo".');
        }

        DB::transaction(function () use ($delivery) {
            // Desasignar guías
            $shipments = $delivery->shipments;
            foreach ($shipments as $s) {
                $s->update(['delivery_id' => null]);
                $s->logActivity("Desvinculada por anulación del reparto {$delivery->delivery_number}", 'unassigned_delivery');
            }
            $delivery->delete();
        });

        return redirect()->route('deliveries.index')->with('success', 'Reparto anulado y guías liberadas exitosamente.');
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
                'remitente.name as remitente_nombre',
                'destinatario.name as destinatario_nombre',
                'companies.prefix as empresa_prefix',
                'companies.color as empresa_color',
                DB::raw('(SELECT COALESCE(SUM(si.cantidad), 0) FROM shipment_items si WHERE si.shipment_id = shipments.id) as bultos_total'),
            ])
            ->join('companies', 'shipments.company_id', '=', 'companies.id')
            ->leftJoin('parties as remitente', 'shipments.remitente_id', '=', 'remitente.id')
            ->leftJoin('parties as destinatario', 'shipments.destinatario_id', '=', 'destinatario.id')
            ->whereNull('shipments.deleted_at')
            ->where('shipments.ubicacion_actual', '=', 'Dto destino')
            ->withCount([
                'problems as has_active_problem' => fn ($q) => $q->where('is_active', true),
                'problems as has_resolved_problem' => fn ($q) => $q->where('is_active', false),
            ]);

        // We only allow those that do not have a delivery, or belong to the current one being edited
        if ($request->filled('delivery_id')) {
            $query->where(function ($q) use ($request) {
                $q->whereNull('shipments.delivery_id')
                    ->orWhere('shipments.delivery_id', $request->delivery_id);
            });
        } else {
            $query->whereNull('shipments.delivery_id');
        }

        // Filtrar por sucursal: guías cuya ubicación de destino pertenezca a la sucursal
        if ($request->filled('location_id')) {
            $query->whereIn('shipments.destino_id', function ($sub) use ($request) {
                $sub->select('id')
                    ->from('ubicaciones')
                    ->where('branch_id', $request->location_id);
            });
        }

        // Filtrar por empresa (Obligatorio en arquitectura stateless para evitar mezclar datos)
        if ($request->filled('company_id')) {
            $query->where('shipments.company_id', $request->company_id);
        }

        return DataTables::of($query)
            ->addColumn('empresa', function ($row) {
                $color = $row->empresa_color ?? '#6366f1';

                return "<span class='px-2 py-1 rounded-full text-[10px] font-bold text-white shadow-sm' style='background-color: {$color}'>{$row->empresa_prefix}</span>";
            })
            ->addColumn('bultos', function ($row) {
                return (int) ($row->bultos_total ?? 0);
            })
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="shipment-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500" 
                    value="'.$row->id.'" 
                    data-numero="'.$row->numero.'" 
                    data-remitente="'.$row->remitente_nombre.'" 
                    data-destinatario="'.$row->destinatario_nombre.'" 
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

    public function returnShipment(Delivery $delivery, Shipment $shipment): JsonResponse
    {
        try {
            $this->deliveryService->returnShipmentFromDelivery($delivery, $shipment);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function print(Delivery $delivery): \Illuminate\View\View
    {
        $delivery->load(['deliverer', 'location', 'shipments' => function ($q) {
            $q->with(['sender', 'recipient', 'items']);
        }]);

        return view('deliveries.print', compact('delivery'));
    }
}
