@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="space-y-4">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-base font-bold text-gray-800 dark:text-gray-100 tracking-tight flex items-center gap-2">
                    <span class="inline-block w-1 h-4 rounded-full bg-indigo-500"></span>
                    Tablero de Control
                </h1>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 ml-3">
                    Operaciones en tiempo real &middot;
                    <span id="last-refresh" class="text-indigo-400 font-medium"></span>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <div
                    class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5">
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Desde</span>
                    <input type="date" id="filter-from"
                        class="text-[11px] bg-transparent text-gray-700 dark:text-white outline-none w-28">
                    <span class="text-gray-300 dark:text-gray-600 mx-1">|</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Hasta</span>
                    <input type="date" id="filter-to"
                        class="text-[11px] bg-transparent text-gray-700 dark:text-white outline-none w-28">
                </div>
                <button id="btn-apply-filter"
                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold rounded-lg transition">Filtrar</button>
                <button id="btn-clear-filter"
                    class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-[11px] font-medium rounded-lg transition hover:bg-gray-50 dark:hover:bg-gray-600">Limpiar</button>
                <span id="filter-label"
                    class="hidden text-[10px] text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40 px-2 py-1 rounded-full">Filtrando</span>
            </div>
        </div>

        {{-- KPIs: 4 en una fila, sin iconos, estilo uniforme --}}
        <div class="flex items-stretch justify-between gap-2.5 w-full overflow-x-auto pb-1">
            @foreach([
            ['id'=>'k-rutas-viaje', 'label'=>'Rutas en Viaje'],
            ['id'=>'k-desp-viaje', 'label'=>'Despachos Activos'],
            ['id'=>'k-repartos', 'label'=>'Repartos en Curso'],
            ['id'=>'k-problemas', 'label'=>'Con Problemas'],
            ] as $kk)
            <div
                class="flex-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 px-2 py-3 shadow-sm hover:shadow transition text-center min-w-[120px]">
                <span id="{{ $kk['id'] }}"
                    class="text-2xl font-bold tabular-nums text-gray-800 dark:text-gray-100 animate-pulse leading-none block">--</span>
                <p
                    class="text-[9px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mt-1.5 leading-none">
                    {{ $kk['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- TABLAS: arriba de graficos, paginadas a 10 --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            {{-- Guias con Problemas --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse inline-block"></span>
                        <span
                            class="text-[9px] font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">Guias
                            con Problemas</span>
                    </div>
                    <div class="flex items-center gap-1" id="pag-problems"></div>
                </div>
                <table class="min-w-full">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Guia</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Destino</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Problema</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-problems" class="divide-y divide-gray-50 dark:divide-gray-700/40 text-xs">
                        <tr>
                            <td colspan="3"
                                class="px-3 py-4 text-center text-gray-300 dark:text-gray-600 italic text-[10px]">
                                Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Repartos en Curso --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse inline-block"></span>
                        <span
                            class="text-[9px] font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">Repartos
                            en Curso</span>
                    </div>
                    <div class="flex items-center gap-1" id="pag-deliveries"></div>
                </div>
                <table class="min-w-full">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Reparto</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Repartidor</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Zona</th>
                            <th
                                class="px-3 py-2 text-center text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Guias</th>
                            <th
                                class="px-3 py-2 text-center text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Prob.</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-deliveries" class="divide-y divide-gray-50 dark:divide-gray-700/40 text-xs">
                        <tr>
                            <td colspan="5"
                                class="px-3 py-4 text-center text-gray-300 dark:text-gray-600 italic text-[10px]">
                                Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            {{-- Despachos en Curso --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse inline-block"></span>
                        <span
                            class="text-[9px] font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">Despachos
                            en Curso</span>
                    </div>
                    <div class="flex items-center gap-1" id="pag-dispatches"></div>
                </div>
                <table class="min-w-full">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Despacho</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Conductor</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Origen</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Destino</th>
                            <th
                                class="px-3 py-2 text-center text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Rutas</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-dispatches" class="divide-y divide-gray-50 dark:divide-gray-700/40 text-xs">
                        <tr>
                            <td colspan="5"
                                class="px-3 py-4 text-center text-gray-300 dark:text-gray-600 italic text-[10px]">
                                Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Rutas en Viaje --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse inline-block"></span>
                        <span
                            class="text-[9px] font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">Rutas
                            en Viaje</span>
                    </div>
                    <div class="flex items-center gap-1" id="pag-routes"></div>
                </div>
                <table class="min-w-full">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Ruta</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Origen</th>
                            <th
                                class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Destino</th>
                            <th
                                class="px-3 py-2 text-center text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                Guias</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-routes" class="divide-y divide-gray-50 dark:divide-gray-700/40 text-xs">
                        <tr>
                            <td colspan="4"
                                class="px-3 py-4 text-center text-gray-300 dark:text-gray-600 italic text-[10px]">
                                Cargando...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- GRAFICOS: siempre 50/50 --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-3 flex flex-col">
                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Estado
                    de Guias</p>
                <div class="flex items-center gap-3 flex-1">
                    <div id="chart-donut" class="w-36 shrink-0 -ml-1"></div>
                    <div class="flex-1 min-w-0">
                        <table class="w-full text-[11px]">
                            <thead class="bg-gray-100 dark:bg-gray-700">
                                <tr>
                                    <th
                                        class="text-left px-2 py-1 text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300 rounded-tl-md">
                                        Estado</th>
                                    <th
                                        class="text-right px-2 py-1 text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                        N</th>
                                    <th
                                        class="text-right px-2 py-1 text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300 rounded-tr-md">
                                        %</th>
                                </tr>
                            </thead>
                            <tbody id="tbl-donut-body" class="divide-y divide-gray-50 dark:divide-gray-700/40">
                                <tr>
                                    <td colspan="3" class="py-2 text-center text-gray-300 italic text-[10px]">
                                        Cargando...</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 dark:border-gray-600">
                                    <td class="pt-1 text-[10px] font-bold text-gray-600 dark:text-gray-300">Total</td>
                                    <td class="pt-1 text-right text-[10px] font-bold text-gray-700 dark:text-gray-200"
                                        id="tbl-donut-total">--</td>
                                    <td class="pt-1 text-right text-[10px] font-bold text-gray-500">100%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-3 flex flex-col">
                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Volumen
                    Operativo - Ultimos 30 dias</p>
                <div id="chart-line-weekly" class="flex-1 min-h-[190px] -mx-1"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-3 flex flex-col">
                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Guias
                    Entregadas x Dia (ult. 14 dias)</p>
                <div id="chart-bar-day" class="flex-1 min-h-[170px] -mx-1"></div>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-3 flex flex-col">
                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Top 10
                    Destinos por Volumen</p>
                <div id="chart-h-bar-dest" class="flex-1 min-h-[170px] -mx-1"></div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
<script>
var STATS_URL = "{{ route('dashboard.stats') }}";
var PAGE_SIZE = 10;

var STATUS_COLORS = {
    'Dto origen':    '#6366f1',
    'En transito':   '#f97316',
    'Dto destino':   '#3b82f6',
    'En reparto':    '#fb923c',
    'Entregado':     '#22c55e',
    'Con problemas': '#ef4444'
};

var apexDonut = null, apexLine = null, apexBar = null, apexHBar = null;
var tableData = {};

function isDark()     { return document.documentElement.classList.contains('dark'); }
function labelColor() { return isDark() ? '#9ca3af' : '#9ca3af'; }
function gridColor()  { return isDark() ? '#374151' : '#f3f4f6'; }
function fgCard()     { return isDark() ? '#1f2937' : '#ffffff'; }

function stopPulse() {
    document.querySelectorAll('.animate-pulse[id]').forEach(function(el) { el.classList.remove('animate-pulse'); });
}

function getParams() {
    var from = document.getElementById('filter-from').value;
    var to   = document.getElementById('filter-to').value;
    return (from && to) ? '?from=' + from + '&to=' + to : '';
}

// Paginacion generica
function paginate(allRows, page, tbodyId, pagContainerId, renderRowFn, emptyMsg, emptyCols) {
    var totalPages = Math.max(1, Math.ceil(allRows.length / PAGE_SIZE));
    if (page < 1) page = 1;
    if (page > totalPages) page = totalPages;

    var start = (page - 1) * PAGE_SIZE;
    var slice = allRows.slice(start, start + PAGE_SIZE);

    var tbody = document.getElementById(tbodyId);
    if (allRows.length === 0) {
        tbody.innerHTML = '<tr><td colspan="' + emptyCols + '" class="px-3 py-3 text-center text-gray-300 dark:text-gray-600 italic text-[10px]">' + emptyMsg + '</td></tr>';
    } else {
        tbody.innerHTML = slice.map(renderRowFn).join('');
    }

    var pagBox = document.getElementById(pagContainerId);
    if (totalPages <= 1) { pagBox.innerHTML = ''; return; }

    var html = '';
    var btnBase = 'px-1.5 py-0.5 text-[9px] rounded transition font-medium ';
    var btnActive = btnBase + 'bg-indigo-600 text-white';
    var btnInactive = btnBase + 'text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700';

    for (var i = 1; i <= totalPages; i++) {
        html += '<button data-page="' + i + '" class="' + (i === page ? btnActive : btnInactive) + '">' + i + '</button>';
    }
    pagBox.innerHTML = html;
    pagBox.querySelectorAll('button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var p = parseInt(this.getAttribute('data-page'));
            paginate(allRows, p, tbodyId, pagContainerId, renderRowFn, emptyMsg, emptyCols);
        });
    });
}

function loadStats() {
    fetch(STATS_URL + getParams(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var k = data.kpi;
        document.getElementById('k-rutas-viaje').textContent = k.rutas_en_viaje;
        document.getElementById('k-desp-viaje').textContent  = k.despachos_en_viaje;
        document.getElementById('k-repartos').textContent    = k.repartos_en_curso;
        document.getElementById('k-problemas').textContent   = k.guias_con_problemas;
        stopPulse();

        document.getElementById('last-refresh').textContent =
            'actualizado ' + new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });

        renderDonut(data);
        renderLine(data);
        renderBar(data);
        renderHBar(data);
        renderTables(data);
    });
}

function renderDonut(data) {
    var labels = Object.keys(data.chart_status);
    var vals   = Object.values(data.chart_status);
    var colors = labels.map(function(s) { return STATUS_COLORS[s] || '#9ca3af'; });
    var total  = vals.reduce(function(a,b){ return a+b; }, 0);

    if (apexDonut) apexDonut.destroy();
    apexDonut = new ApexCharts(document.getElementById('chart-donut'), {
        series: vals, labels: labels, colors: colors,
        chart: { type: 'donut', height: 180, background: 'transparent', toolbar: { show: false }, animations: { enabled: true, speed: 500 } },
        dataLabels: { enabled: false }, legend: { show: false },
        stroke: { width: 2, colors: [fgCard()] },
        plotOptions: { pie: { donut: { size: '74%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '10px', color: '#9ca3af', formatter: function() { return total; } }, value: { show: true, fontSize: '20px', fontWeight: 700, color: isDark() ? '#e5e7eb' : '#111827', offsetY: 2 } } } } },
        tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: function(v) { return v + ' guias'; } } }
    });
    apexDonut.render();

    document.getElementById('tbl-donut-total').textContent = total;
    document.getElementById('tbl-donut-body').innerHTML = labels.map(function(label, i) {
        var pct = total > 0 ? Math.round(vals[i] / total * 100) : 0;
        return '<tr><td class="py-1 pr-1"><span class="flex items-center gap-1"><i style="background:' + colors[i] + '" class="inline-block w-2 h-2 rounded-full shrink-0"></i><span class="text-gray-600 dark:text-gray-300 truncate">' + label + '</span></span></td><td class="py-1 text-right font-semibold text-gray-700 dark:text-gray-200">' + vals[i] + '</td><td class="py-1 pl-1 text-right text-gray-400">' + pct + '%</td></tr>';
    }).join('');
}

function renderLine(data) {
    if (apexLine) apexLine.destroy();
    apexLine = new ApexCharts(document.getElementById('chart-line-weekly'), {
        series: [
            { name: 'Guias', data: data.chart_line.shipments },
            { name: 'Rutas', data: data.chart_line.routes },
            { name: 'Despachos', data: data.chart_line.dispatches },
            { name: 'Repartos', data: data.chart_line.deliveries }
        ],
        chart: { type: 'area', height: 200, background: 'transparent', toolbar: { show: false }, zoom: { enabled: false }, animations: { enabled: true, speed: 600 } },
        colors: ['#6366f1', '#f59e0b', '#8b5cf6', '#f97316'],
        dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0.02, stops: [0, 90, 100] } },
        markers: { size: 0, hover: { size: 4 } },
        xaxis: { categories: data.chart_line.labels, labels: { style: { fontSize: '9px', colors: labelColor() }, rotate: -30 }, axisBorder: { show: false }, axisTicks: { show: false }, tickAmount: 10 },
        yaxis: { labels: { style: { fontSize: '9px', colors: labelColor() }, formatter: function(v) { return Math.round(v); } }, min: 0 },
        grid: { borderColor: gridColor(), strokeDashArray: 3, xaxis: { lines: { show: false } }, padding: { left: 2, right: 4 } },
        legend: { position: 'top', horizontalAlign: 'right', fontSize: '10px', labels: { colors: labelColor() }, markers: { width: 16, height: 3, radius: 1 }, itemMargin: { horizontal: 6 } },
        tooltip: { theme: isDark() ? 'dark' : 'light', shared: true, intersect: false }
    });
    apexLine.render();
}

function renderBar(data) {
    if (apexBar) apexBar.destroy();
    apexBar = new ApexCharts(document.getElementById('chart-bar-day'), {
        series: [{ name: 'Entregadas', data: data.chart_bar.data }],
        chart: { type: 'bar', height: 180, background: 'transparent', toolbar: { show: false } },
        colors: ['#6366f1'], dataLabels: { enabled: false },
        plotOptions: { bar: { borderRadius: 3, columnWidth: '50%' } },
        xaxis: { categories: data.chart_bar.labels, labels: { style: { fontSize: '9px', colors: labelColor() } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { fontSize: '9px', colors: labelColor() }, formatter: function(v) { return Math.round(v); } } },
        grid: { borderColor: gridColor(), strokeDashArray: 3, xaxis: { lines: { show: false } }, padding: { left: 2, right: 4 } },
        tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: function(v) { return v + ' guias'; } } }
    });
    apexBar.render();
}

function renderHBar(data) {
    if (apexHBar) apexHBar.destroy();
    apexHBar = new ApexCharts(document.getElementById('chart-h-bar-dest'), {
        series: [{ name: 'Guias', data: data.top_destinations.map(function(d) { return d.total; }) }],
        chart: { type: 'bar', height: 180, background: 'transparent', toolbar: { show: false } },
        colors: ['#f97316'], dataLabels: { enabled: false },
        plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '50%' } },
        xaxis: { categories: data.top_destinations.map(function(d) { return d.nombre; }), labels: { style: { fontSize: '9px', colors: labelColor() }, formatter: function(v) { return Math.round(v); } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { fontSize: '9px', colors: labelColor() }, maxWidth: 90 } },
        grid: { borderColor: gridColor(), strokeDashArray: 3, yaxis: { lines: { show: false } }, padding: { left: 2, right: 4 } },
        tooltip: { theme: isDark() ? 'dark' : 'light', x: { show: true }, y: { formatter: function(v) { return v + ' guias'; } } }
    });
    apexHBar.render();
}

function renderTables(data) {
    // Problemas
    paginate(data.problem_list, 1, 'tbl-problems', 'pag-problems', function(p) {
        return '<tr class="hover:bg-red-50/60 dark:hover:bg-red-900/10 transition-colors"><td class="px-3 py-1.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-[11px]">' + p.numero + '</td><td class="px-3 py-1.5 text-gray-600 dark:text-gray-300 text-[11px]">' + p.destino + '</td><td class="px-3 py-1.5"><button type="button" class="btn-open-spm text-left text-red-500 dark:text-red-400 hover:underline text-[11px] line-clamp-1" data-shipment-id="' + p.shipment_id + '" data-shipment-numero="' + p.numero + '">' + p.problema + '</button></td></tr>';
    }, 'Sin problemas activos', 3);

    // Repartos
    paginate(data.active_deliveries_list, 1, 'tbl-deliveries', 'pag-deliveries', function(d) {
        var prob = d.con_problema > 0 ? '<span class="text-red-500 font-semibold">&#9888; ' + d.con_problema + '</span>' : '<span class="text-green-500">&#10003;</span>';
        return '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer" onclick="window.location=\'' + d.edit_url + '\'"><td class="px-3 py-1.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-[11px]">' + d.numero + '</td><td class="px-3 py-1.5 text-gray-600 dark:text-gray-300 text-[11px]">' + d.repartidor + '</td><td class="px-3 py-1.5 text-gray-500 dark:text-gray-400 text-[11px]">' + d.ubicacion + '</td><td class="px-3 py-1.5 text-center font-semibold text-gray-700 dark:text-gray-200 text-[11px]">' + d.guias + '</td><td class="px-3 py-1.5 text-center text-[11px]">' + prob + '</td></tr>';
    }, 'Sin repartos en curso', 5);

    // Despachos
    paginate(data.active_dispatches_list || [], 1, 'tbl-dispatches', 'pag-dispatches', function(dp) {
        return '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer" onclick="window.location=\'' + dp.edit_url + '\'"><td class="px-3 py-1.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-[11px]">' + dp.numero + '</td><td class="px-3 py-1.5 text-gray-600 dark:text-gray-300 text-[11px]">' + dp.conductor + '</td><td class="px-3 py-1.5 text-gray-500 dark:text-gray-400 text-[11px]">' + dp.origen + '</td><td class="px-3 py-1.5 text-gray-500 dark:text-gray-400 text-[11px]">' + dp.destino + '</td><td class="px-3 py-1.5 text-center font-semibold text-gray-700 dark:text-gray-200 text-[11px]">' + dp.rutas + '</td></tr>';
    }, 'Sin despachos en curso', 5);

    // Rutas
    paginate(data.active_routes_list, 1, 'tbl-routes', 'pag-routes', function(r) {
        return '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"><td class="px-3 py-1.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-[11px]">' + r.numero + '</td><td class="px-3 py-1.5 text-gray-600 dark:text-gray-300 text-[11px]">' + r.origen + '</td><td class="px-3 py-1.5 text-gray-500 dark:text-gray-400 text-[11px]">' + r.destino + '</td><td class="px-3 py-1.5 text-center font-semibold text-gray-700 dark:text-gray-200 text-[11px]">' + r.guias + '</td></tr>';
    }, 'Sin rutas en viaje', 4);
}

document.getElementById('btn-apply-filter').addEventListener('click', function() {
    var from = document.getElementById('filter-from').value;
    var to   = document.getElementById('filter-to').value;
    if (!from || !to) { alert('Selecciona ambas fechas.'); return; }
    document.getElementById('filter-label').classList.remove('hidden');
    loadStats();
});

document.getElementById('btn-clear-filter').addEventListener('click', function() {
    document.getElementById('filter-from').value = '';
    document.getElementById('filter-to').value   = '';
    document.getElementById('filter-label').classList.add('hidden');
    loadStats();
});

document.addEventListener('DOMContentLoaded', loadStats);
window.addEventListener('themeChanged', function() { loadStats(); });
</script>
@endsection
