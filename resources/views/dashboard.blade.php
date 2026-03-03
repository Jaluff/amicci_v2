@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8 space-y-6">

        {{-- ══ HEADER ══════════════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        🖥 Tablero de Control
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Estado operacional en tiempo real · <span id="last-refresh" class="italic"></span>
                    </p>
                </div>
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Desde</label>
                        <input type="date" id="filter-from"
                            class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white px-3 py-1.5">
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Hasta</label>
                        <input type="date" id="filter-to"
                            class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white px-3 py-1.5">
                    </div>
                    <button id="btn-apply-filter"
                        class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                        🔍 Filtrar
                    </button>
                    <button id="btn-clear-filter"
                        class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition">
                        ✕ Todo
                    </button>
                    <span id="filter-label"
                        class="hidden text-xs text-indigo-500 italic bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded-full">
                        Filtrando por rango
                    </span>
                </div>
            </div>
        </div>

        {{-- ══ SECCIÓN 1: FLUJO DE GUÍAS ════════════════════════════════════ --}}
        <div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3 px-1">
                📦 Flujo de Guías en el Sistema
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-3">
                @foreach([
                ['id' => 'k-total', 'label' => 'Guías en Sistema', 'icon' => '📦', 'ring' => 'ring-gray-300
                dark:ring-gray-600', 'text' => 'text-gray-700 dark:text-gray-200'],
                ['id' => 'k-origen', 'label' => 'Guías en Depósito Origen', 'icon' => '🏭', 'ring' => 'ring-indigo-300
                dark:ring-indigo-700', 'text' => 'text-indigo-700 dark:text-indigo-300'],
                ['id' => 'k-transito', 'label' => 'Guías en Tránsito', 'icon' => '🚛', 'ring' => 'ring-yellow-300
                dark:ring-yellow-700', 'text' => 'text-yellow-700 dark:text-yellow-300'],
                ['id' => 'k-destino', 'label' => 'Guías en Depósito Destino', 'icon' => '🏬', 'ring' => 'ring-blue-300
                dark:ring-blue-700', 'text' => 'text-blue-700 dark:text-blue-300'],
                ['id' => 'k-reparto', 'label' => 'Guías en Reparto', 'icon' => '🛵', 'ring' => 'ring-orange-300
                dark:ring-orange-700', 'text' => 'text-orange-600 dark:text-orange-300'],
                ['id' => 'k-entregadas','label' => 'Guías Entregadas', 'icon' => '✅', 'ring' => 'ring-green-300
                dark:ring-green-700', 'text' => 'text-green-700 dark:text-green-300'],
                ['id' => 'k-hoy', 'label' => 'Entregas de Hoy', 'icon' => '📅', 'ring' => 'ring-teal-300
                dark:ring-teal-700', 'text' => 'text-teal-700 dark:text-teal-300'],
                ['id' => 'k-problemas', 'label' => 'Guías con Problemas Activos', 'icon' => '⚠️', 'ring' =>
                'ring-red-300 dark:ring-red-700', 'text' => 'text-red-600 dark:text-red-400'],
                ] as $c)
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 {{ $c['ring'] }} p-4 flex flex-col gap-2 hover:shadow-md transition group">
                    <div class="flex justify-between items-start">
                        <span class="text-xl">{{ $c['icon'] }}</span>
                        <span id="{{ $c['id'] }}"
                            class="text-2xl font-extrabold {{ $c['text'] }} tabular-nums animate-pulse">—</span>
                    </div>
                    <p
                        class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 leading-tight mt-auto">
                        {{ $c['label'] }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══ SECCIÓN 2: OPERACIONES ACTIVAS ══════════════════════════════ --}}
        <div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3 px-1">
                ⚡ Operaciones Activas Ahora
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
                @foreach([
                ['id' => 'k-rutas-viaje', 'label' => 'Rutas en Viaje', 'icon' => '🚛', 'bg' => 'from-yellow-500
                to-amber-400'],
                ['id' => 'k-rutas-listas', 'label' => 'Rutas Listas para Despachar', 'icon' => '📋', 'bg' =>
                'from-blue-500 to-cyan-400'],
                ['id' => 'k-desp-viaje', 'label' => 'Despachos en Tránsito', 'icon' => '📦', 'bg' => 'from-purple-500
                to-violet-400'],
                ['id' => 'k-repartos', 'label' => 'Repartos en Curso', 'icon' => '🛵', 'bg' => 'from-orange-500
                to-red-400'],
                ['id' => 'k-conductores', 'label' => 'Conductores Registrados', 'icon' => '🧑‍✈️', 'bg' =>
                'from-gray-500 to-slate-400'],
                ['id' => 'k-repartidores', 'label' => 'Repartidores Registrados', 'icon' => '🧑‍🦯', 'bg' =>
                'from-teal-500 to-emerald-400'],
                ] as $c)
                <div class="rounded-xl shadow-sm p-4 flex flex-col gap-2 bg-gradient-to-br {{ $c['bg'] }} text-white">
                    <div class="flex justify-between items-start">
                        <span class="text-2xl">{{ $c['icon'] }}</span>
                        <span id="{{ $c['id'] }}" class="text-3xl font-extrabold tabular-nums animate-pulse">—</span>
                    </div>
                    <p class="text-[10px] font-bold uppercase tracking-wider opacity-90 leading-tight mt-auto">{{
                        $c['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- ══ SECCIÓN 3: TOTALES ════════════════════════════════════════════ --}}
        <div>
            <h2 class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-3 px-1">
                📊 Totales Generales del Período
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-1 gap-3">
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 p-4 flex flex-col gap-1 items-center">
                    <span class="text-2xl">👥</span>
                    <span id="k-clientes"
                        class="text-3xl font-extrabold text-gray-800 dark:text-gray-100 tabular-nums animate-pulse">—</span>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Clientes
                        Registrados</p>
                </div>
            </div>
        </div>

        {{-- ══ GRÁFICOS FILA 1 ═══════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl px-6 py-5 flex flex-col">
                <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">
                    Distribución por Estado</h3>
                <div class="flex-1 flex items-center justify-center min-h-[220px]">
                    <canvas id="chart-donut"></canvas>
                </div>
            </div>
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl px-6 py-5 flex flex-col">
                <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Guías
                    Entregadas por Día (últimos 14 días)</h3>
                <div class="flex-1 min-h-[220px]">
                    <canvas id="chart-bar-day"></canvas>
                </div>
            </div>
        </div>

        {{-- ══ GRÁFICOS FILA 2 ═══════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl px-6 py-5 flex flex-col">
                <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Guías
                    Creadas vs Entregadas — Por Semana</h3>
                <div class="flex-1 min-h-[230px]"><canvas id="chart-line-weekly"></canvas></div>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl px-6 py-5 flex flex-col">
                <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Top 10
                    Destinos con Mayor Volumen</h3>
                <div class="flex-1 min-h-[230px]"><canvas id="chart-h-bar-dest"></canvas></div>
            </div>
        </div>

        {{-- ══ TABLAS OPERATIVAS ════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Guías con problemas activos --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-red-50 dark:bg-red-900/10 flex items-center gap-2">
                    <span class="inline-block w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                    <h3 class="text-sm font-bold text-red-700 dark:text-red-400 uppercase tracking-wider">Guías con
                        Problemas Activos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    Guía N°</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    Destino</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    Detalle del Problema</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-problems" class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-400 italic text-sm">Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Repartos en curso --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-orange-50 dark:bg-orange-900/10 flex items-center gap-2">
                    <span class="inline-block w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                    <h3 class="text-sm font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wider">Repartos
                        en Curso</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    N° Reparto</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    Repartidor</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    Zona</th>
                                <th
                                    class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    Guías</th>
                                <th
                                    class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                    ⚠</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-deliveries" class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-sm">Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Rutas en viaje --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-xl overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-yellow-50 dark:bg-yellow-900/10 flex items-center gap-2">
                <span class="inline-block w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                <h3 class="text-sm font-bold text-yellow-700 dark:text-yellow-400 uppercase tracking-wider">Rutas
                    Actualmente en Viaje</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40">
                        <tr>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                N° Ruta</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Origen</th>
                            <th
                                class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Destino</th>
                            <th
                                class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                Guías en Ruta</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-routes" class="divide-y divide-gray-100 dark:divide-gray-700">
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-400 italic text-sm">Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
const STATS_URL = "{{ route('dashboard.stats') }}";

const STATUS_COLORS = {
    'Dto origen':    '#6366f1',
    'En transito':   '#f59e0b',
    'Dto destino':   '#3b82f6',
    'En reparto':    '#f97316',
    'Entregado':     '#22c55e',
    'Con problemas': '#ef4444',
};

let chartDonut, chartBarDay, chartLineWeekly, chartHBarDest;

const isDark      = () => document.documentElement.classList.contains('dark');
const gridColor   = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
const labelColor  = () => isDark() ? '#9ca3af' : '#6b7280';

function stopPulse() {
    document.querySelectorAll('.animate-pulse[id]').forEach(el => el.classList.remove('animate-pulse'));
}

function getParams() {
    const from = document.getElementById('filter-from').value;
    const to   = document.getElementById('filter-to').value;
    return (from && to) ? `?from=${from}&to=${to}` : '';
}

async function loadStats() {
    const res  = await fetch(STATS_URL + getParams(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    const data = await res.json();
    const k    = data.kpi;

    // ── KPI Flujo de Guías ─────────────────────────────────
    document.getElementById('k-total').textContent      = k.guias_totales;
    document.getElementById('k-origen').textContent     = k.guias_en_origen;
    document.getElementById('k-transito').textContent   = k.guias_en_transito;
    document.getElementById('k-destino').textContent    = k.guias_en_destino;
    document.getElementById('k-reparto').textContent    = k.guias_en_reparto;
    document.getElementById('k-entregadas').textContent = k.guias_entregadas;
    document.getElementById('k-hoy').textContent        = k.guias_entregadas_hoy;
    document.getElementById('k-problemas').textContent  = k.guias_con_problemas;

    // ── KPI Operaciones Activas ─────────────────────────────
    document.getElementById('k-rutas-viaje').textContent  = k.rutas_en_viaje;
    document.getElementById('k-rutas-listas').textContent = k.rutas_listas_salir;
    document.getElementById('k-desp-viaje').textContent   = k.despachos_en_viaje;
    document.getElementById('k-repartos').textContent     = k.repartos_en_curso;
    document.getElementById('k-conductores').textContent  = k.conductores;
    document.getElementById('k-repartidores').textContent = k.repartidores;

    // ── KPI Totales ─────────────────────────────────────────
    document.getElementById('k-clientes').textContent = k.total_clientes;

    stopPulse();

    // ── Timestamp ──────────────────────────────────────────
    document.getElementById('last-refresh').textContent =
        'Actualizado: ' + new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });

    // ── Dona ───────────────────────────────────────────────
    const statusLabels = Object.keys(data.chart_status);
    const statusData   = Object.values(data.chart_status);
    const statusColors = statusLabels.map(s => STATUS_COLORS[s] || '#9ca3af');
    if (chartDonut) chartDonut.destroy();
    chartDonut = new Chart(document.getElementById('chart-donut'), {
        type: 'doughnut',
        data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: statusColors, borderWidth: 2, borderColor: isDark() ? '#1f2937' : '#fff' }] },
        options: { cutout: '65%', plugins: { legend: { position: 'bottom', labels: { color: labelColor(), font: { size: 11 }, padding: 10 } } } }
    });

    // ── Barras entregadas x día ─────────────────────────────
    if (chartBarDay) chartBarDay.destroy();
    chartBarDay = new Chart(document.getElementById('chart-bar-day'), {
        type: 'bar',
        data: {
            labels: data.chart_bar.labels,
            datasets: [{ label: 'Guías Entregadas', data: data.chart_bar.data, backgroundColor: 'rgba(99,102,241,0.75)', borderRadius: 5 }]
        },
        options: { plugins: { legend: { display: false } }, scales: {
            x: { grid: { color: gridColor() }, ticks: { color: labelColor() } },
            y: { grid: { color: gridColor() }, ticks: { color: labelColor(), stepSize: 1 }, beginAtZero: true }
        }}
    });

    // ── Línea creadas vs entregadas ─────────────────────────
    if (chartLineWeekly) chartLineWeekly.destroy();
    chartLineWeekly = new Chart(document.getElementById('chart-line-weekly'), {
        type: 'line',
        data: {
            labels: data.chart_line.labels,
            datasets: [
                { label: 'Guías Creadas',    data: data.chart_line.created,   borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)',  tension: 0.3, fill: true, pointRadius: 4 },
                { label: 'Guías Entregadas', data: data.chart_line.delivered, borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.08)', tension: 0.3, fill: true, pointRadius: 4 },
            ]
        },
        options: {
            plugins: { legend: { labels: { color: labelColor() } } },
            scales: {
                x: { grid: { color: gridColor() }, ticks: { color: labelColor() } },
                y: { grid: { color: gridColor() }, ticks: { color: labelColor(), stepSize: 1 }, beginAtZero: true }
            }
        }
    });

    // ── Barras horizontales top destinos ───────────────────
    if (chartHBarDest) chartHBarDest.destroy();
    chartHBarDest = new Chart(document.getElementById('chart-h-bar-dest'), {
        type: 'bar',
        data: {
            labels: data.top_destinations.map(d => d.nombre),
            datasets: [{ label: 'Guías', data: data.top_destinations.map(d => d.total), backgroundColor: 'rgba(249,115,22,0.75)', borderRadius: 4 }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: {
            x: { grid: { color: gridColor() }, ticks: { color: labelColor(), stepSize: 1 }, beginAtZero: true },
            y: { grid: { display: false }, ticks: { color: labelColor() } }
        }}
    });

    // ── Tabla: guías con problemas ─────────────────────────
    const tblP = document.getElementById('tbl-problems');
    tblP.innerHTML = data.problem_list.length === 0
        ? '<tr><td colspan="3" class="px-4 py-6 text-center text-green-500 font-medium">🎉 Sin guías con problemas activos</td></tr>'
        : data.problem_list.map(p => `
            <tr class="hover:bg-red-50 dark:hover:bg-red-900/10 transition">
                <td class="px-4 py-2.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">${p.numero}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">${p.destino}</td>
                <td class="px-4 py-2.5">
                    <button type="button" class="btn-open-spm text-left text-red-600 dark:text-red-400 hover:underline text-sm line-clamp-1"
                        data-shipment-id="${p.shipment_id}" data-shipment-numero="${p.numero}">
                        ${p.problema}
                    </button>
                </td>
            </tr>`).join('');

    // ── Tabla: repartos en curso ────────────────────────────
    const tblD = document.getElementById('tbl-deliveries');
    tblD.innerHTML = data.active_deliveries_list.length === 0
        ? '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-sm">No hay repartos en curso</td></tr>'
        : data.active_deliveries_list.map(d => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition cursor-pointer" onclick="window.location='${d.edit_url}'">
                <td class="px-4 py-2.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">${d.numero}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">${d.repartidor}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">${d.ubicacion}</td>
                <td class="px-4 py-2.5 text-center font-semibold text-gray-800 dark:text-gray-100 text-sm">${d.guias}</td>
                <td class="px-4 py-2.5 text-center text-sm">
                    ${d.con_problema > 0
                        ? `<span class="text-red-500 font-bold">⚠ ${d.con_problema}</span>`
                        : '<span class="text-green-500">✓</span>'}
                </td>
            </tr>`).join('');

    // ── Tabla: rutas en viaje ───────────────────────────────
    const tblR = document.getElementById('tbl-routes');
    tblR.innerHTML = data.active_routes_list.length === 0
        ? '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 italic text-sm">No hay rutas en viaje</td></tr>'
        : data.active_routes_list.map(r => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                <td class="px-4 py-2.5 font-mono font-bold t600 dark:text-indigo-400 text-sm">${r.numero}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">${r.origen}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">${r.destino}</td>
                <td class="px-4 py-2.5 text-center font-semibold text-gray-800 dark:text-gray-100 text-sm">${r.guias}</td>
            </tr>`).join('');
}

document.getElementById('btn-apply-filter').addEventListener('click', () => {
    const from = document.getElementById('filter-from').value;
    const to   = document.getElementById('filter-to').value;
    if (!from || !to) { alert('Seleccioná ambas fechas para filtrar.'); return; }
    document.getElementById('filter-label').classList.remove('hidden');
    loadStats();
});

document.getElementById('btn-clear-filter').addEventListener('click', () => {
    document.getElementById('filter-from').value = '';
    document.getElementById('filter-to').value   = '';
    document.getElementById('filter-label').classList.add('hidden');
    loadStats();
});

document.addEventListener('DOMContentLoaded', loadStats);
window.addEventListener('themeChanged', loadStats);
</script>
@endsection