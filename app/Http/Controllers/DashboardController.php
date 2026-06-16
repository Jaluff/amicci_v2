<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Deliverer;
use App\Models\Delivery;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Party;
use App\Models\Shipment;
use App\Models\TransportRoute;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $user = auth()->user();
        if ($user->hasRole('repartidor') && !$user->hasAnyRole(['admin', 'supervisor'])) {
            return redirect()->route('deliverer.index');
        }

        $userCompanies = $user->companies;

        return view('dashboard', compact('userCompanies'));
    }

    public function stats(Request $request): JsonResponse
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : null;

        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : null;

        $company_ids = auth()->user()->companies->pluck('id')->toArray();
        $selected_company_id = $request->input('company_id');

        $baseShipQ = Shipment::query();
        $baseRouteQ = TransportRoute::query();
        $baseDispQ = Dispatch::query();
        $baseDelivQ = Delivery::query();

        if ($selected_company_id && in_array($selected_company_id, $company_ids)) {
            $baseShipQ->where('company_id', $selected_company_id);
            $baseRouteQ->where('company_id', $selected_company_id);
            $baseDelivQ->where('company_id', $selected_company_id);
        } else {
            $baseShipQ->whereIn('company_id', $company_ids);
            $baseRouteQ->whereIn('company_id', $company_ids);
            $baseDelivQ->whereIn('company_id', $company_ids);
        }

        $shipQ = clone $baseShipQ;
        $routeQ = clone $baseRouteQ;
        $dispQ = clone $baseDispQ;
        $delivQ = clone $baseDelivQ;

        if ($from && $to) {
            $shipQ->whereBetween('fecha', [$from, $to]);
            $routeQ->whereBetween('created_at', [$from, $to]);
            $dispQ->whereBetween('created_at', [$from, $to]);
            $delivQ->whereBetween('created_at', [$from, $to]);
        }

        // ── SECCIÓN 1: FLUJO DE GUÍAS (estado actual del pipeline) ───
        $guiasTotales = (clone $shipQ)->count();
        $guiasEnOrigen = (clone $shipQ)->where('ubicacion_actual', '=', 'Dto origen')->count();
        $guiasEnTransito = (clone $shipQ)->where('ubicacion_actual', '=', 'En transito')->count();
        $guiasEnDestino = (clone $shipQ)->where('ubicacion_actual', '=', 'Dto destino')->count();
        $guiasEnReparto = (clone $shipQ)->where('ubicacion_actual', '=', 'En reparto')->count();
        $guiasEntregadas = (clone $shipQ)->where('ubicacion_actual', '=', 'Entregado')->count();
        $guiasConProblemas = (clone $shipQ)->whereHas('problems', fn ($q) => $q->where('is_active', true))->count();
        $guiasHoy = (clone $baseShipQ)->whereNotNull('fecha_entrega')->whereDate('fecha_entrega', today())->count();

        // ── SECCIÓN 2: OPERACIONES ACTIVAS ─────────────────────────
        $rutasEnViaje = (clone $routeQ)->where('status', '=', 'En viaje')->count();
        $rutasListasParaSalir = (clone $routeQ)->where('status', '=', 'Cargada')->whereNull('dispatch_id')->count();
        $despachosEnViaje = (clone $dispQ)->where('status', '=', 'En viaje')->count();
        $repartosEnCurso = (clone $delivQ)->where('status', '=', 'En reparto')->count();
        $conductoresActivos = Driver::query()->count();
        $repartidoresActivos = Deliverer::query()->count();

        // ── SECCIÓN 3: TOTALES GENERALES ──────────────────────────
        $totalClientes = Party::query()->count();

        // ── GRÁFICO Dona: guías por estado ─────────────────────────
        $shipmentsByStatus = (clone $shipQ)
            ->select('ubicacion_actual', DB::raw('count(*) as total'))
            ->groupBy('ubicacion_actual')
            ->pluck('total', 'ubicacion_actual');

        // ── GRÁFICO Barras: entregadas por día ─────────────────────
        $barFrom = $from ?? now()->subDays(13)->startOfDay();
        $barTo = $to ?? now()->endOfDay();

        $deliveredPerDay = (clone $baseShipQ)
            ->whereNotNull('fecha_entrega')
            ->whereBetween('fecha_entrega', [$barFrom, $barTo])
            ->select(DB::raw('DATE(fecha_entrega) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        // ── GRÁFICO Línea: volumen en los últimos 30 días ────────
        $lineFrom = $from ?? now()->subDays(30)->startOfDay();
        $lineTo = $to ?? now()->endOfDay();

        $shipmentsPerDay = (clone $baseShipQ)
            ->whereBetween('fecha', [$lineFrom, $lineTo])
            ->select(DB::raw('DATE(fecha) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        $routesPerDay = (clone $baseRouteQ)
            ->whereBetween('created_at', [$lineFrom, $lineTo])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        $dispatchesPerDay = (clone $baseDispQ)
            ->whereBetween('created_at', [$lineFrom, $lineTo])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        $deliveriesPerDay = (clone $baseDelivQ)
            ->whereBetween('created_at', [$lineFrom, $lineTo])
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')->orderBy('day')
            ->pluck('total', 'day');

        $dayKeys = collect($shipmentsPerDay)->keys()
            ->merge(collect($routesPerDay)->keys())
            ->merge(collect($dispatchesPerDay)->keys())
            ->merge(collect($deliveriesPerDay)->keys())
            ->unique()->sort()->values();

        $dayLabels = $dayKeys->map(function ($day) {
            return Carbon::parse($day)->format('d/m');
        });

        // ── Top 10 destinos ─────────────────────────────────────────
        $topDestinations = (clone $shipQ)
            ->select('destino_id', DB::raw('count(*) as total'))
            ->with('destination:id,nombre')
            ->groupBy('destino_id')
            ->orderByDesc('total')
            ->limit(10)->get()
            ->map(fn ($s) => [
                'nombre' => $s->destination?->nombre ?? 'N/A',
                'total' => $s->total,
            ]);

        // ── Guías con problemas (últimas 10) ────────────────────────
        $problemList = (clone $shipQ)
            ->whereHas('problems', fn ($q) => $q->where('is_active', true))
            ->with(['destination:id,nombre', 'currentProblem'])
            ->orderByDesc('updated_at')
            ->limit(10)->get()
            ->map(fn ($s) => [
                'shipment_id' => $s->id,
                'numero' => $s->numero,
                'destino' => $s->destination?->nombre ?? '-',
                'problema' => $s->currentProblem?->comment ?? '-',
            ]);

        // ── Repartos en curso ────────────────────────────────────────
        $activeDeliveriesList = (clone $delivQ)
            ->where('status', '=', 'En reparto')
            ->with(['deliverer:id,name', 'location:id,name'])
            ->withCount(['shipments as guias_con_problema' => fn ($q) => $q->whereHas('problems', fn ($sq) => $sq->where('is_active', true))])
            ->withCount('shipments')
            ->orderByDesc('created_at')
            ->limit(8)->get()
            ->map(fn ($d) => [
                'numero' => $d->delivery_number,
                'repartidor' => $d->deliverer?->name ?? '-',
                'ubicacion' => $d->location?->name ?? '-',
                'guias' => $d->shipments_count,
                'con_problema' => $d->guias_con_problema,
                'edit_url' => route('deliveries.edit', $d->id),
            ]);

        // ── Rutas en viaje activas ────────────────────────────────────
        $activeRoutesList = (clone $routeQ)
            ->where('status', '=', 'En viaje')
            ->with(['origin:id,name', 'destination:id,name', 'dispatch'])
            ->withCount('shipments')
            ->orderByDesc('created_at')
            ->limit(10)->get()
            ->map(fn ($r) => [
                'numero' => $r->route_number,
                'origen' => $r->origin?->name ?? '-',
                'destino' => $r->destination?->name ?? '-',
                'guias' => $r->shipments_count,
            ]);

        // ── Despachos en curso ────────────────────────────────────────
        $activeDispatchesList = (clone $dispQ)
            ->where('status', '=', 'En viaje')
            ->with(['driver:id,name', 'origin:id,name', 'destination:id,name'])
            ->withCount('routes')
            ->orderByDesc('created_at')
            ->limit(10)->get()
            ->map(fn ($dp) => [
                'numero' => $dp->dispatch_number,
                'conductor' => $dp->driver?->name ?? '-',
                'origen' => $dp->origin?->name ?? '-',
                'destino' => $dp->destination?->name ?? '-',
                'rutas' => $dp->routes_count,
                'edit_url' => route('dispatches.edit', $dp->id),
            ]);

        // ── SECCIÓN 4: FACTURACIÓN (solo admin/supervisor) ─────────
        $billingStats = null;
        $billingChart = null;
        $canSeeBilling = auth()->user()?->hasRole(['admin', 'supervisor']);

        if ($canSeeBilling) {
            $baseInvoiceQ = Invoice::query();
            if ($selected_company_id && in_array($selected_company_id, $company_ids)) {
                $baseInvoiceQ->where('company_id', $selected_company_id);
            } else {
                $baseInvoiceQ->whereIn('company_id', $company_ids);
            }
            $invoiceQ = clone $baseInvoiceQ;

            $mesInicio = now()->startOfMonth();
            $mesFin = now()->endOfMonth();

            $totalFacturado = (clone $invoiceQ)->whereBetween('fecha_factura', [$mesInicio, $mesFin])->sum('total');
            $totalCobrado = (clone $invoiceQ)->whereBetween('fecha_factura', [$mesInicio, $mesFin])->where('cobrada', true)->sum('total');
            $totalPendiente = $totalFacturado - $totalCobrado;
            $guiasSinFacturar = (clone $baseShipQ)->whereNull('invoice_id')->whereNull('deleted_at')->count();

            // Gráfico: últimos 6 meses — Facturado vs Cobrado
            $meses = collect();
            for ($i = 5; $i >= 0; $i--) {
                $mes = now()->subMonths($i);
                $meses->push([
                    'label' => $mes->translatedFormat('M Y'),
                    'inicio' => $mes->copy()->startOfMonth(),
                    'fin' => $mes->copy()->endOfMonth(),
                ]);
            }

            $billingChart = [
                'labels' => $meses->pluck('label')->values(),
                'facturado' => $meses->map(fn ($m) => (clone $invoiceQ)->whereBetween('fecha_factura', [$m['inicio'], $m['fin']])->sum('total')
                )->values(),
                'cobrado' => $meses->map(fn ($m) => (clone $invoiceQ)->where('cobrada', true)->whereBetween('fecha_factura', [$m['inicio'], $m['fin']])->sum('total')
                )->values(),
            ];

            $billingStats = [
                'total_facturado' => $totalFacturado,
                'total_cobrado' => $totalCobrado,
                'total_pendiente' => $totalPendiente,
                'guias_sin_facturar' => $guiasSinFacturar,
            ];
        }

        return response()->json([
            'kpi' => [
                // Flujo de guías
                'guias_totales' => $guiasTotales,
                'guias_en_origen' => $guiasEnOrigen,
                'guias_en_transito' => $guiasEnTransito,
                'guias_en_destino' => $guiasEnDestino,
                'guias_en_reparto' => $guiasEnReparto,
                'guias_entregadas' => $guiasEntregadas,
                'guias_con_problemas' => $guiasConProblemas,
                'guias_entregadas_hoy' => $guiasHoy,
                // Operaciones activas
                'rutas_en_viaje' => $rutasEnViaje,
                'rutas_listas_salir' => $rutasListasParaSalir,
                'despachos_en_viaje' => $despachosEnViaje,
                'repartos_en_curso' => $repartosEnCurso,
                'conductores' => $conductoresActivos,
                'repartidores' => $repartidoresActivos,
                // Totales
                'total_clientes' => $totalClientes,
            ],
            'chart_status' => $shipmentsByStatus,
            'chart_bar' => ['labels' => $deliveredPerDay->keys()->values(), 'data' => $deliveredPerDay->values()],
            'chart_line' => [
                'labels' => $dayLabels->values(),
                'shipments' => $dayKeys->map(fn ($d) => $shipmentsPerDay[$d] ?? 0)->values(),
                'routes' => $dayKeys->map(fn ($d) => $routesPerDay[$d] ?? 0)->values(),
                'dispatches' => $dayKeys->map(fn ($d) => $dispatchesPerDay[$d] ?? 0)->values(),
                'deliveries' => $dayKeys->map(fn ($d) => $deliveriesPerDay[$d] ?? 0)->values(),
            ],
            'top_destinations' => $topDestinations->values(),
            'problem_list' => $problemList->values(),
            'active_deliveries_list' => $activeDeliveriesList->values(),
            'active_routes_list' => $activeRoutesList->values(),
            'active_dispatches_list' => $activeDispatchesList->values(),
            'billing' => $billingStats,
            'billing_chart' => $billingChart,
        ]);
    }
}
