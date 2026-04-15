<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Dispatch;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DispatchReportController extends Controller
{
    public function index()
    {
        // Datos para los filtros
        $companies = Company::all();
        $parties = Party::withoutGlobalScopes()->get();
        $ubicaciones = Ubicacion::all();

        return view('reports.dispatches.index', compact('companies', 'parties', 'ubicaciones'));
    }

    public function datatable(Request $request)
    {
        $query = Shipment::withoutGlobalScopes()
            ->with([
                'sender', 'recipient', 'origin', 'destination', 
                'delivery', 'transportRoute.dispatch', 'items'
            ])
            ->withSum('items', 'cantidad')
            ->withSum('items', 'peso')
            ->withSum('items', 'volumen')
            ->withSum('items', 'monto_valor_declarado');

        // Aplicar filtros
        if ($request->filled('start_date')) {
            $query->whereDate('fecha', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('fecha', '<=', $request->end_date);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('party_id')) {
            $partyIds = is_array($request->party_id) ? $request->party_id : [$request->party_id];
            $query->where(function ($q) use ($partyIds) {
                $q->whereIn('remitente_id', $partyIds)
                  ->orWhereIn('destinatario_id', $partyIds);
            });
        }
        if ($request->filled('dispatch_number')) {
            $query->whereHas('transportRoute.dispatch', function ($q) use ($request) {
                $q->where('dispatch_number', 'like', '%' . $request->dispatch_number . '%');
            });
        }
        if ($request->filled('route_number')) {
            $query->whereHas('transportRoute', function ($q) use ($request) {
                $q->where('route_number', 'like', '%' . $request->route_number . '%');
            });
        }
        if ($request->filled('delivery_number')) {
            $query->whereHas('delivery', function ($q) use ($request) {
                $q->where('delivery_number', 'like', '%' . $request->delivery_number . '%');
            });
        }
        if ($request->filled('origin_id')) {
            $originIds = is_array($request->origin_id) ? $request->origin_id : [$request->origin_id];
            $query->whereIn('origen_id', $originIds);
        }
        if ($request->filled('destination_id')) {
            $destinationIds = is_array($request->destination_id) ? $request->destination_id : [$request->destination_id];
            $query->whereIn('destino_id', $destinationIds);
        }
        if ($request->filled('ubicacion_actual')) {
            $ua = is_array($request->ubicacion_actual) ? $request->ubicacion_actual : [$request->ubicacion_actual];
            $query->whereIn('ubicacion_actual', $ua);
        }
        if ($request->filled('cobrada') && in_array($request->cobrada, ['1', '0'])) {
            $query->where('cobrada', $request->cobrada === '1');
        }

        return DataTables::of($query->orderByDesc('shipments.fecha'))
            ->addColumn('selection', function($row) {
                return '<input type="checkbox" class="row-select w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer" value="'.$row->id.'">';
            })
            ->addColumn('despacho_numero', function ($row) {
                return $row->transportRoute?->dispatch?->dispatch_number ?? '-';
            })
            ->addColumn('ruta_numero', function ($row) {
                return $row->transportRoute?->route_number ?? '-';
            })
            ->addColumn('reparto_numero', function ($row) {
                return $row->delivery?->delivery_number ?? '-';
            })
            ->addColumn('remitos', function ($row) {
                return $row->items->pluck('numero_remito')->filter()->implode(', ');
            })
            ->addColumn('sender_name', fn($row) => $row->sender?->name ?? '-')
            ->addColumn('recipient_name', fn($row) => $row->recipient?->name ?? '-')
            ->addColumn('origin_name', fn($row) => $row->origin?->nombre ?? '-')
            ->addColumn('destination_name', fn($row) => $row->destination?->nombre ?? '-')
            ->editColumn('fecha', function ($row) {
                return $row->fecha ? $row->fecha->format('d/m/Y') : '-';
            })
            ->editColumn('flete_a_pagar_en', function ($row) {
                return $row->flete_a_pagar_en ?? '-';
            })
            ->editColumn('cobrada', function ($row) {
                return $row->cobrada ? 'Sí' : 'No';
            })
            ->editColumn('flete', fn($row) => '$ ' . number_format($row->flete, 2, ',', '.'))
            ->editColumn('seguro', fn($row) => '$ ' . number_format($row->seguro, 2, ',', '.'))
            ->editColumn('monto_contra_reembolso', fn($row) => '$ ' . number_format($row->monto_contra_reembolso, 2, ',', '.'))
            ->editColumn('retencion_mercaderia', fn($row) => '$ ' . number_format($row->retencion_mercaderia, 2, ',', '.'))
            ->editColumn('total', fn($row) => '$ ' . number_format($row->total, 2, ',', '.'))
            ->editColumn('items_sum_peso', fn($row) => number_format($row->items_sum_peso, 2, ',', '.'))
            ->editColumn('items_sum_volumen', fn($row) => number_format($row->items_sum_volumen, 2, ',', '.'))
            ->editColumn('items_sum_monto_valor_declarado', fn($row) => '$ ' . number_format($row->items_sum_monto_valor_declarado, 2, ',', '.'))
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

                if ($estado === 'En reparto' && $row->delivery_id) {
                    $url = route('deliveries.edit', $row->delivery_id);
                    $num = htmlspecialchars($row->delivery?->delivery_number ?? 'S/N');
                    return "<div class='dt-status-stacked'>
                                <a href='{$url}' class='dt-badge {$color} dt-badge-link'>{$estado}</a>
                                <a href='{$url}' class='text-[9px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline'>#{$num}</a>
                            </div>";
                } elseif ($estado === 'En transito' && $row->transport_route_id) {
                    $url = route('routes.edit', $row->transport_route_id);
                    $num = htmlspecialchars($row->transportRoute?->route_number ?? 'S/N');
                    return "<div class='dt-status-stacked'>
                                <a href='{$url}' class='dt-badge {$color} dt-badge-link'>{$estado}</a>
                                <a href='{$url}' class='text-[9px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline'>#{$num}</a>
                            </div>";
                }

                return "<span class='dt-badge {$color}'>{$estado}</span>";
            })
            ->rawColumns(['selection', 'ubicacion_actual'])
            ->make(true);
    }
}
