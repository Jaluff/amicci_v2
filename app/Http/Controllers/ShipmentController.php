<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\Ubicacion;
use App\Services\ShipmentService;
use App\Services\GuiaImporteService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class ShipmentController extends Controller
{
    public function index()
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        return view('shipments.index', compact('ubicaciones'));
    }

    public function datatable(Request $request)
    {
        $query = Shipment::query()
            ->select([
            'shipments.id',
            'shipments.numero',
            'shipments.fecha',
            'shipments.flete',
            'shipments.total',
            'shipments.ubicacion_actual',
            'origen.nombre as origen_nombre',
            'destino.nombre as destino_nombre',
            'remitente.name as remitente_nombre',
            'destinatario.name as destinatario_nombre',
            DB::raw('(SELECT COALESCE(SUM(si.cantidad), 0) FROM shipment_items si WHERE si.shipment_id = shipments.id) as bultos_total'),
            DB::raw('(SELECT COALESCE(SUM(si.monto_valor_declarado), 0) FROM shipment_items si WHERE si.shipment_id = shipments.id) as valor_declarado_total'),
        ])
            ->leftJoin('ubicaciones as origen', 'shipments.origen_id', '=', 'origen.id')
            ->leftJoin('ubicaciones as destino', 'shipments.destino_id', '=', 'destino.id')
            ->leftJoin('parties as remitente', 'shipments.remitente_id', '=', 'remitente.id')
            ->leftJoin('parties as destinatario', 'shipments.destinatario_id', '=', 'destinatario.id')
            ->whereNull('shipments.deleted_at')
            ->withCount(['problems as has_active_problem' => function ($q) {
            $q->where('is_active', true);
        }]);

        if ($request->filled('origen_id')) {
            $query->where('shipments.origen_id', $request->origen_id);
        }
        if ($request->filled('destino_id')) {
            $query->where('shipments.destino_id', $request->destino_id);
        }

        if ($request->filled('cliente')) {
            $cliente = $request->cliente;
            $query->where(function($q) use ($cliente) {
                $q->where('remitente.name', 'like', "%{$cliente}%")
                  ->orWhere('destinatario.name', 'like', "%{$cliente}%");
            });
        }
        if ($request->filled('fecha_inicio')) {
            $query->whereDate('shipments.fecha', '>=', $request->fecha_inicio);
        }
        if ($request->filled('fecha_fin')) {
            $query->whereDate('shipments.fecha', '<=', $request->fecha_fin);
        }
        if ($request->filled('numero_documento')) {
            $query->where('shipments.numero', 'like', '%' . $request->numero_documento . '%');
        }
        if ($request->filled('ubicacion')) {
            $query->where('shipments.ubicacion_actual', $request->ubicacion);
        }

        return DataTables::of($query)
            ->addColumn('bultos', function ($row) {
            return (int)($row->bultos_total ?? 0);
        })
            ->addColumn('valor_declarado', function ($row) {
            return '$ ' . number_format($row->valor_declarado_total ?? 0, 2);
        })
            ->addColumn('acciones', function ($row) {
            $editUrl = route('shipments.edit', $row->id);
            $printUrl = route('shipments.print', $row->id);
            $deleteUrl = route('shipments.destroy', $row->id);
            $csrf = csrf_token();
            $confirm = 'return confirm(\'¿Eliminar esta guía?\')';
            $deleteForm = "";
            if ($row->ubicacion_actual === 'Dto origen') {
                $deleteForm = "
                    <form action='{$deleteUrl}' method='POST' onsubmit='{$confirm}' class='inline m-0'>
                        <input type='hidden' name='_token' value='{$csrf}'>
                        <input type='hidden' name='_method' value='DELETE'>
                        <button type='submit' title='Eliminar' class='inline-flex items-center justify-center p-2 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800 dark:hover:bg-red-800/60 dark:hover:text-red-300 transition-colors'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='3 6 5 6 21 6'/><path d='M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6'/><path d='M10 11v6'/><path d='M14 11v6'/><path d='M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2'/></svg>
                        </button>
                    </form>";
            }

            return "<div class='flex items-center gap-2'>
                    <a href='{$printUrl}' target='_blank' title='Imprimir' class='inline-flex items-center justify-center p-2 rounded-md bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100 dark:bg-yellow-900/40 dark:text-yellow-400 dark:border-yellow-800 dark:hover:bg-yellow-800/60 dark:hover:text-yellow-300 transition-colors'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 6 2 18 2 18 9'></polyline><path d='M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2'></path><rect x='6' y='14' width='12' height='8'></rect></svg>
                    </a>
                    <a href='{$editUrl}' title='Editar' class='inline-flex items-center justify-center p-2 rounded-md bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 dark:hover:text-blue-300 transition-colors'>
                        <svg xmlns='http://www.w3.org/2000/svg' class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><path d='M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7'/><path d='M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z'/></svg>
                    </a>
                    {$deleteForm}
                </div>";
        })
            ->editColumn('fecha', function ($row) {
            return \Carbon\Carbon::parse($row->fecha)->format('d/m/Y');
        })
            ->editColumn('flete', function ($row) {
            return ucfirst($row->flete ?? '-');
        })
            ->editColumn('total', function ($row) {
            return '$ ' . number_format($row->total ?? 0, 2);
        })
            ->addColumn('ubicacion_actual', function ($row) {
            $colores = [
                'Dto origen' => 'dt-badge-indigo',
                'En transito' => 'dt-badge-yellow',
                'Dto destino' => 'dt-badge-blue',
                'En reparto' => 'dt-badge-orange',
                'Entregado' => 'dt-badge-green',
                'Con problemas' => 'dt-badge-red',
            ];
            $estado = $row->ubicacion_actual ?? '-';
            $color = $colores[$estado] ?? 'dt-badge-gray';

            if ($estado === 'Con problemas') {
                $numero = htmlspecialchars($row->numero ?? '', ENT_QUOTES);
                return "<span class='dt-badge {$color} animate-pulse cursor-pointer btn-open-spm'
                    data-shipment-id='{$row->id}'
                    data-shipment-numero='{$numero}'
                    title='Ver / Resolver problema'>{$estado}</span>";
            }

            return "<span class='dt-badge {$color}'>{$estado}</span>";
        })
            ->addColumn('remitente_destinatario', function ($row) {
                return '<div class="text-xs">' .
                    '<span class="font-bold text-gray-700 dark:text-gray-300">R:</span> ' . ($row->remitente_nombre ?? '-') . '<br>' .
                    '<span class="font-bold text-gray-700 dark:text-gray-300">D:</span> ' . ($row->destinatario_nombre ?? '-') .
                    '</div>';
            })
            ->rawColumns(['acciones', 'ubicacion_actual', 'remitente_destinatario'])
            ->make(true);
    }

    public function create()
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $parties     = Party::withoutGlobalScope('company')->orderBy('name')->get();

        $user = auth()->user();
        $branches = \App\Models\Branch::where('active', true)
            ->permitted()
            ->orderBy('code')
            ->get();

        return view('shipments.create', compact('ubicaciones', 'parties', 'branches'));
    }

    public function store(StoreShipmentRequest $request, ShipmentService $service)
    {
        $validated = $request->validated();

        $validated['cobrada'] = $request->boolean('cobrada');
        $validated['contra_reembolso'] = $request->boolean('contra_reembolso');

        // Limpiar separadores de miles en campos numéricos
        $numericFields = ['flete', 'seguro', 'monto_contra_reembolso', 'retencion_mercaderia', 'otros_cargos', 'subtotal', 'iva_monto', 'total'];
        foreach ($numericFields as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = (float)str_replace(',', '', $validated[$field]);
            }
        }

        $items = $validated['items'];
        $data = collect($validated)->except('items')->toArray();

        $shipmentModel = $service->create($data, $items);

        if ($request->input('action') === 'save_and_print') {
            return redirect()
                ->route('shipments.print', $shipmentModel->id)
                ->with('auto_close_and_reload_opener', true);
        }

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Guía guardada correctamente.');
    }

    public function edit(Shipment $shipment)
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $parties     = Party::withoutGlobalScope('company')->orderBy('name')->get();

        $user = auth()->user();
        $branches = \App\Models\Branch::where('active', true)
            ->permitted()
            ->orderBy('code')
            ->get();

        return view('shipments.edit', compact('shipment', 'ubicaciones', 'parties', 'branches'));
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment, ShipmentService $service)
    {
        $validated = $request->validated();

        $validated['cobrada'] = $request->boolean('cobrada');
        $validated['contra_reembolso'] = $request->boolean('contra_reembolso');

        // Limpiar separadores de miles en campos numéricos
        $numericFields = ['flete', 'seguro', 'monto_contra_reembolso', 'retencion_mercaderia', 'otros_cargos', 'subtotal', 'iva_monto', 'total'];
        foreach ($numericFields as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = (float)str_replace(',', '', $validated[$field]);
            }
        }
        $items = $validated['items'];
        $data = collect($validated)->except('items')->toArray();

        $service->update($shipment, $data, $items);

        if ($request->input('action') === 'save_and_print') {
            return redirect()
                ->route('shipments.print', $shipment->id)
                ->with('auto_close_and_reload_opener', true);
        }

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Guía actualizada correctamente.');
    }

    public function destroy(Shipment $shipment, ShipmentService $service)
    {
        abort_if(!auth()->user()->hasAnyRole(['admin', 'Supervisor']), 403, 'No tienes permisos para anular documentos.');

        if ($shipment->ubicacion_actual !== 'Dto origen') {
            return redirect()
                ->route('shipments.index')
                ->with('error', 'No se puede eliminar una guía que ya tiene movimientos (Ubicación: ' . $shipment->ubicacion_actual . ').');
        }

        $service->delete($shipment);
        return redirect()
            ->route('shipments.index')
            ->with('success', 'Guía eliminada correctamente.');
    }

    public function print(Shipment $shipment)
    {
        $shipment->load([
            'origin',
            'destination',
            'sender',
            'recipient',
            'items',
            'company.addresses'
        ]);

        return view('shipments.print', compact('shipment'));
    }

    /**
     * Calcula el flete para modo 'kg' dado un origen, destino y peso total.
     * Usado por el formulario de guías vía AJAX para actualizar el campo
     * flete en tiempo real cuando el billing_mode del remitente es 'kg'.
     *
     * GET /shipments/calcular-flete?origen_id=&destino_id=&peso_kg=&party_id=
     */
    public function calcularFlete(Request $request, GuiaImporteService $service)
    {
        $origenId    = $request->origen_id;
        $destinoId   = $request->destino_id;
        $pesoKg      = (float) $request->peso_kg;
        $remitenteId = $request->remitente_id;

        if (!$origenId || !$destinoId) {
            return response()->json(['flete' => 0]);
        }

        $origen  = Ubicacion::find($origenId);
        $destino = Ubicacion::find($destinoId);

        if (!$origen || !$destino) {
            return response()->json(['flete' => 0]);
        }

        // Buscar el cuadro tarifario base para esta ruta por nombre (LIKE en ambas direcciones)
        $tariffTable = \App\Models\TariffTable::where(function ($q) use ($origen) {
                $q->whereRaw("LOWER(origin) LIKE CONCAT('%', LOWER(?), '%')", [$origen->nombre])
                  ->orWhereRaw("LOWER(?) LIKE CONCAT('%', LOWER(origin), '%')", [$origen->nombre]);
            })
            ->where(function ($q) use ($destino) {
                $q->whereRaw("LOWER(destination) LIKE CONCAT('%', LOWER(?), '%')", [$destino->nombre])
                  ->orWhereRaw("LOWER(?) LIKE CONCAT('%', LOWER(destination), '%')", [$destino->nombre]);
            })
            ->where('is_active', true)
            ->first();

        if (!$tariffTable) {
            return response()->json(['flete' => 0, 'detalle' => "Sin tarifa cargada para la ruta: {$origen->nombre} → {$destino->nombre}"]);
        }

        // Si tenemos un remitente, intentamos el cálculo profesional vía Service (considera mínimos y acuerdos)
        if ($remitenteId) {
            $shipment = new Shipment([
                'remitente_id' => $remitenteId,
                'origen_id'    => $origenId,
                'destino_id'   => $destinoId,
            ]);
            // Creamos un ítem virtual para que el service sume el peso
            $item = new \App\Models\ShipmentItem(['peso' => $pesoKg, 'tipo_paquete' => 'bultos', 'cantidad' => 1]);
            $shipment->setRelation('items', collect([$item]));

            $res = $service->calcular($shipment, $tariffTable->id);

            if ($res) {
                return response()->json([
                    'flete'   => (float) $res['importe_final'],
                    'detalle' => $res['billing_mode_label'] . ($res['importe_final'] > $res['importe_calculado'] ? " (Mínimo aplicado)" : ""),
                    'table'   => $res['tariff_table_name'],
                ]);
            }
        }

        // Fallback: cálculo genérico de la tabla si no hay acuerdo particular o no se envió remitente
        if ($pesoKg >= 1000) {
            $ton   = $pesoKg / 1000;
            $flete = (float) $tariffTable->rate_per_ton * $ton;
            return response()->json([
                'flete'    => round($flete, 2),
                'detalle'  => "Por tonelada: {$ton} ton × \${$tariffTable->rate_per_ton}",
                'table'    => $tariffTable->name,
            ]);
        }

        $bracket = \App\Models\TariffBracket::where('tariff_table_id', $tariffTable->id)
            ->where('weight_from', '<=', (int) ceil($pesoKg))
            ->where('weight_to',   '>=', (int) ceil($pesoKg))
            ->first();

        if (!$bracket) {
            return response()->json(['flete' => 0, 'detalle' => 'Peso fuera de escala', 'table' => $tariffTable->name]);
        }

        return response()->json([
            'flete'   => (float) $bracket->rate,
            'detalle' => "Tramo {$bracket->weight_from}-{$bracket->weight_to} kg → \${$bracket->rate}",
            'table'   => $tariffTable->name,
        ]);
    }
}