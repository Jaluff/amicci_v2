<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deliverer;
use App\Models\Delivery;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\TransportRoute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard');
    }

    public function stats(Request $request): JsonResponse
    {
        $from = $request->input('from')
            ? \Carbon\Carbon::parse($request->input('from'))->startOfDay()
            : null;

        $to = $request->input('to')
            ? \Carbon\Carbon::parse($request->input('to'))->endOfDay()
            : null;

        $shipQ   = Shipment::query();
        $routeQ  = TransportRoute::query();
        $dispQ   = Dispatch::query();
        $delivQ  = Delivery::query();

        if ($from && $to) {
            $shipQ->whereBetween('fecha', [$from, $to]);
            $routeQ->whereBetween('created_at', [$from, $to]);
            $dispQ->whereBetween('created_at', [$from, $to]);
            $delivQ->whereBetween('created_at', [$from, $to]);
        }

        // ── SECCIÓN 1: FLUJO DE GUÍAS (estado actual del pipeline) ───
        $guiasTotales       = (clone $shipQ)->count();
        $guiasEnOrigen      = (clone $shipQ)->where('ubicacion_actual', '=', 'Dto origen')->count();
        $guiasEnTransito    = (clone $shipQ)->where('ubicacion_actual', '=', 'En transito')->count();
        $guiasEnDestino     = (clone $shipQ)->where('ubicacion_actual', '=', 'Dto destino')->count();
        $guiasEnReparto     = (clone $shipQ)->where('ubicacion_actual', '=', 'En reparto')->count();
        $guiasEntregadas    = (clone $shipQ)->where('ubicacion_actual', '=', 'Entregado')->count();
        $guiasConProblemas  = (clone $shipQ)->where('ubicacion_actual', '=', 'Con problemas')->count();
        $guiasHoy           = Shipment::query()->whereNotNull('fecha_entrega')->whereDate('fecha_entrega', today())->count();

        // ── SECCIÓN 2: OPERACIONES ACTIVAS ─────────────────────────
        $rutasEnViaje       = (clone $routeQ)->where('status', '=', 'En viaje')->count();
        $rutasListasParaSalir = (clone $routeQ)->where('status', '=', 'Cargada')->whereNull('dispatch_id')->count();
        $despachosEnViaje   = (clone $dispQ)->where('status', '=', 'En viaje')->count();
        $repartosEnCurso    = (clone $delivQ)->where('status', '=', 'En reparto')->count();
        $conductoresActivos = Driver::query()->count();
        $repartidoresActivos = Deliverer::query()->count();

        // ── SECCIÓN 3: TOTALES GENERALES ──────────────────────────
        $totalClientes      = Party::query()->count();

        // ── GRÁFICO Dona: guías por estado ─────────────────────────
        $shipmentsByStatus = (clone $shipQ)
            ->select('ubicacion_actual', DB::raw('count(*) as total'))
            ->groupBy('ubicacion_actual')
            ->pluck('total', 'ubicacion_actual');

        // ── GRÁFICO Barras: entregadas por día ─────────────────────
        $barFrom = $from ?? now()->subDays(13)->startOfDay();
        $barTo   = $to   ?? now()->endOfDay();

        $deliveredPerDay = Shipment::query()
            ->whereNotNull('fecha_entrega')
            ->whereBetween('fecha_entrega', [$barFrom, $barTo])
            ->select(DB::raw('DATE(fecha_entrega) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        // ── GRÁFICO Línea: creadas vs entregadas por semana ────────
        $lineFrom = $from ?? now()->subWeeks(7)->startOfWeek();
        $lineTo   = $to   ?? now()->endOfWeek();

        $createdPerWeek = Shipment::query()
            ->whereBetween('fecha', [$lineFrom, $lineTo])
            ->select(DB::raw('YEARWEEK(fecha, 1) as week'), DB::raw('count(*) as total'))
            ->groupBy('week')->orderBy('week')
            ->pluck('total', 'week');

        $deliveredPerWeek = Shipment::query()
            ->whereNotNull('fecha_entrega')
            ->whereBetween('fecha_entrega', [$lineFrom, $lineTo])
            ->select(DB::raw('YEARWEEK(fecha_entrega, 1) as week'), DB::raw('count(*) as total'))
            ->groupBy('week')->orderBy('week')
            ->pluck('total', 'week');

        $weekKeys = collect($createdPerWeek)->keys()
            ->merge(collect($deliveredPerWeek)->keys())
            ->unique()->sort()->values();

        $weekLabels = $weekKeys->map(function ($yw) {
            $ywStr = (string) $yw;
            $year  = (int) substr($ywStr, 0, 4);
            $week  = (int) substr($ywStr, 4);
            return "Sem {$week}/" . substr($ywStr, 2, 2);
        });

        // ── Top 10 destinos ─────────────────────────────────────────
        $topDestinations = (clone $shipQ)
            ->select('destino_id', DB::raw('count(*) as total'))
            ->with('destination:id,nombre')
            ->groupBy('destino_id')
            ->orderByDesc('total')
            ->limit(10)->get()
            ->map(fn($s) => [
                'nombre' => $s->destination?->nombre ?? 'N/A',
                'total'  => $s->total,
            ]);

        // ── Guías con problemas (últimas 10) ────────────────────────
        $problemList = Shipment::query()
            ->where('ubicacion_actual', '=', 'Con problemas')
            ->with(['destination:id,nombre', 'currentProblem'])
            ->orderByDesc('updated_at')
            ->limit(10)->get()
            ->map(fn($s) => [
                'shipment_id' => $s->id,
                'numero'      => $s->numero,
                'destino'     => $s->destination?->nombre ?? '-',
                'problema'    => $s->currentProblem?->comment ?? '-',
            ]);

        // ── Repartos en curso ────────────────────────────────────────
        $activeDeliveriesList = Delivery::query()
            ->where('status', '=', 'En reparto')
            ->with(['deliverer:id,name', 'location:id,nombre'])
            ->withCount(['shipments as guias_con_problema' => fn($q) => $q->where('ubicacion_actual', '=', 'Con problemas')])
            ->withCount('shipments')
            ->orderByDesc('created_at')
            ->limit(8)->get()
            ->map(fn($d) => [
                'numero'        => $d->delivery_number,
                'repartidor'    => $d->deliverer?->name ?? '-',
                'ubicacion'     => $d->location?->nombre ?? '-',
                'guias'         => $d->shipments_count,
                'con_problema'  => $d->guias_con_problema,
                'edit_url'      => route('deliveries.edit', $d->id),
            ]);

        // ── Rutas en viaje activas ────────────────────────────────────
        $activeRoutesList = TransportRoute::query()
            ->where('status', '=', 'En viaje')
            ->with(['origin:id,nombre', 'destination:id,nombre', 'dispatch'])
            ->withCount('shipments')
            ->orderByDesc('created_at')
            ->limit(6)->get()
            ->map(fn($r) => [
                'numero'  => $r->route_number,
                'origen'  => $r->origin?->nombre ?? '-',
                'destino' => $r->destination?->nombre ?? '-',
                'guias'   => $r->shipments_count,
            ]);

        return response()->json([
            'kpi' => [
                // Flujo de guías
                'guias_totales'          => $guiasTotales,
                'guias_en_origen'        => $guiasEnOrigen,
                'guias_en_transito'      => $guiasEnTransito,
                'guias_en_destino'       => $guiasEnDestino,
                'guias_en_reparto'       => $guiasEnReparto,
                'guias_entregadas'       => $guiasEntregadas,
                'guias_con_problemas'    => $guiasConProblemas,
                'guias_entregadas_hoy'   => $guiasHoy,
                // Operaciones activas
                'rutas_en_viaje'         => $rutasEnViaje,
                'rutas_listas_salir'     => $rutasListasParaSalir,
                'despachos_en_viaje'     => $despachosEnViaje,
                'repartos_en_curso'      => $repartosEnCurso,
                'conductores'            => $conductoresActivos,
                'repartidores'           => $repartidoresActivos,
                // Totales
                'total_clientes'         => $totalClientes,
            ],
            'chart_status'          => $shipmentsByStatus,
            'chart_bar'             => ['labels' => $deliveredPerDay->keys()->values(), 'data' => $deliveredPerDay->values()],
            'chart_line'            => [
                'labels'    => $weekLabels->values(),
                'created'   => $weekKeys->map(fn($w) => $createdPerWeek[$w] ?? 0)->values(),
                'delivered' => $weekKeys->map(fn($w) => $deliveredPerWeek[$w] ?? 0)->values(),
            ],
            'top_destinations'       => $topDestinations->values(),
            'problem_list'           => $problemList->values(),
            'active_deliveries_list' => $activeDeliveriesList->values(),
            'active_routes_list'     => $activeRoutesList->values(),
        ]);
    }
}