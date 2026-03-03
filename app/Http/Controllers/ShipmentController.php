<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\Ubicacion;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;

class ShipmentController extends Controller
{
    public function index()
    {
        return view('shipments.index');
    }

    public function datatable()
    {
        $query = Shipment::query()
            ->select([
            'shipments.id',
            'shipments.numero',
            'shipments.fecha',
            'shipments.flete',
            'shipments.total',
            'shipments.ubicacion_actual',
            'shipments.estado_facturacion',
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
            ->whereNull('shipments.deleted_at');

        return DataTables::of($query)
            ->addColumn('bultos', function ($row) {
            return (int)($row->bultos_total ?? 0);
        })
            ->addColumn('valor_declarado', function ($row) {
            return '$ ' . number_format($row->valor_declarado_total ?? 0, 2);
        })
            ->addColumn('acciones', function ($row) {
            $editUrl = route('shipments.edit', $row->id);
            $deleteUrl = route('shipments.destroy', $row->id);
            $csrf = csrf_token();
            $confirm = 'return confirm(\'¿Eliminar esta guía?\')';
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
            ->editColumn('estado_facturacion', function ($row) {
            return $row->estado_facturacion ?? '-';
        })
            ->rawColumns(['acciones', 'ubicacion_actual'])
            ->make(true);
    }

    public function create()
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $parties = Party::withoutGlobalScope('company')->orderBy('name')->get();
        return view('shipments.create', compact('ubicaciones', 'parties'));
    }

    public function store(StoreShipmentRequest $request, ShipmentService $service)
    {
        $validated = $request->validated();

        // Convertir valores booleanos
        $validated['cobrada'] = $request->boolean('cobrada');
        $validated['contra_reembolso'] = $request->boolean('contra_reembolso');
        $validated['rendida'] = $request->boolean('rendida');

        // Limpiar separadores de miles en campos numéricos
        $numericFields = ['flete', 'seguro', 'monto_contra_reembolso', 'retencion_mercaderia', 'otros_cargos', 'subtotal', 'iva_monto', 'total'];
        foreach ($numericFields as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = (float)str_replace(',', '', $validated[$field]);
            }
        }

        $items = $validated['items'];
        $data = collect($validated)->except('items')->toArray();

        $service->create($data, $items);

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Guía guardada correctamente.');
    }

    public function edit(Shipment $shipment)
    {
        $ubicaciones = Ubicacion::orderBy('nombre')->get();
        $parties = Party::withoutGlobalScope('company')->orderBy('name')->get();
        return view('shipments.edit', compact('shipment', 'ubicaciones', 'parties'));
    }

    public function update(UpdateShipmentRequest $request, Shipment $shipment, ShipmentService $service)
    {
        $validated = $request->validated();

        // Convertir valores booleanos
        $validated['cobrada'] = $request->boolean('cobrada');
        $validated['contra_reembolso'] = $request->boolean('contra_reembolso');
        $validated['rendida'] = $request->boolean('rendida');

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

        return redirect()
            ->route('shipments.index')
            ->with('success', 'Guía actualizada correctamente.');
    }

    public function destroy(Shipment $shipment, ShipmentService $service)
    {
        abort_if(!auth()->user()->hasAnyRole(['admin', 'Supervisor']), 403, 'No tienes permisos para anular documentos.');

        $service->delete($shipment);
        return redirect()
            ->route('shipments.index')
            ->with('success', 'Guía eliminada correctamente.');
    }
}