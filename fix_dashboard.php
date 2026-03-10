<?php
$file = '/Users/EmiJaluff/Www/amicci2-sys/resources/views/dashboard.blade.php';
$lines = file($file);

$output = [];
foreach ($lines as $line) {
    if (trim($line) === "@section('scripts')") {
        break;
    }
    $output[] = $line;
}

$script = <<<HTML
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const STATS_URL = "{{ route('dashboard.stats') }}";

    const STATUS_COLORS = {
        'Dto origen': '#6366f1',
        'En transito': '#f59e0b',
        'Dto destino': '#3b82f6',
        'En reparto': '#f97316',
        'Entregado': '#22c55e',
        'Con problemas': '#ef4444',
    };

    let chartDonut, chartBarDay, chartLineWeekly, chartHBarDest;

    const isDark = () => document.documentElement.classList.contains('dark');
    const gridColor = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const labelColor = () => isDark() ? '#9ca3af' : '#6b7280';

    function stopPulse() {
        document.querySelectorAll('.animate-pulse[id]').forEach(el => el.classList.remove('animate-pulse'));
    }

    function getParams() {
        const from = document.getElementById('filter-from').value;
        const to = document.getElementById('filter-to').value;
        return (from && to) ? `?from=\${from}&to=\${to}` : '';
    }

    async function loadStats() {
        const res = await fetch(STATS_URL + getParams(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();
        const k = data.kpi;

        // ── KPI Operaciones Activas ─────────────────────────────
        document.getElementById('k-transito').textContent = k.guias_en_transito;
        document.getElementById('k-reparto').textContent = k.guias_en_reparto;
        document.getElementById('k-problemas').textContent = k.guias_con_problemas;
        document.getElementById('k-rutas-viaje').textContent = k.rutas_en_viaje;
        document.getElementById('k-desp-viaje').textContent = k.despachos_en_viaje;
        document.getElementById('k-repartos').textContent = k.repartos_en_curso;

        stopPulse();

        // ── Timestamp ──────────────────────────────────────────
        document.getElementById('last-refresh').textContent =
            'Actualizado: ' + new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });

        // ── Dona ───────────────────────────────────────────────
        const statusLabels = Object.keys(data.chart_status);
        const statusData = Object.values(data.chart_status);
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
            options: {
                plugins: { legend: { display: false } }, scales: {
                    x: { grid: { color: gridColor() }, ticks: { color: labelColor() } },
                    y: { grid: { color: gridColor() }, ticks: { color: labelColor(), stepSize: 1 }, beginAtZero: true }
                }
            }
        });

        // ── Línea creadas vs entregadas ─────────────────────────
        if (chartLineWeekly) chartLineWeekly.destroy();
        chartLineWeekly = new Chart(document.getElementById('chart-line-weekly'), {
            type: 'line',
            data: {
                labels: data.chart_line.labels,
                datasets: [
                    { label: 'Guías', data: data.chart_line.shipments, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', tension: 0.3, fill: true, pointRadius: 2 },
                    { label: 'Rutas', data: data.chart_line.routes, borderColor: '#eab308', backgroundColor: 'rgba(234,179,8,0.1)', tension: 0.3, fill: true, pointRadius: 2 },
                    { label: 'Despachos', data: data.chart_line.dispatches, borderColor: '#a855f7', backgroundColor: 'rgba(168,85,247,0.1)', tension: 0.3, fill: true, pointRadius: 2 },
                    { label: 'Repartos', data: data.chart_line.deliveries, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', tension: 0.3, fill: true, pointRadius: 2 },
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
            options: {
                indexAxis: 'y', plugins: { legend: { display: false } }, scales: {
                    x: { grid: { color: gridColor() }, ticks: { color: labelColor(), stepSize: 1 }, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { color: labelColor() } }
                }
            }
        });

        // ── Tabla: guías con problemas ─────────────────────────
        const tblP = document.getElementById('tbl-problems');
        tblP.innerHTML = data.problem_list.length === 0
            ? '<tr><td colspan="3" class="px-4 py-6 text-center text-green-500 font-medium">🎉 Sin guías con problemas activos</td></tr>'
            : data.problem_list.map(p => `
            <tr class="hover:bg-red-50 dark:hover:bg-red-900/10 transition">
                <td class="px-4 py-2.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">\${p.numero}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">\${p.destino}</td>
                <td class="px-4 py-2.5">
                    <button type="button" class="btn-open-spm text-left text-red-600 dark:text-red-400 hover:underline text-sm line-clamp-1"
                        data-shipment-id="\${p.shipment_id}" data-shipment-numero="\${p.numero}">
                        \${p.problema}
                    </button>
                </td>
            </tr>`).join('');

        // ── Tabla: repartos en curso ────────────────────────────
        const tblD = document.getElementById('tbl-deliveries');
        tblD.innerHTML = data.active_deliveries_list.length === 0
            ? '<tr><td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-sm">No hay repartos en curso</td></tr>'
            : data.active_deliveries_list.map(d => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition cursor-pointer" onclick="window.location='\${d.edit_url}'">
                <td class="px-4 py-2.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-sm">\${d.numero}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">\${d.repartidor}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">\${d.ubicacion}</td>
                <td class="px-4 py-2.5 text-center font-semibold text-gray-800 dark:text-gray-100 text-sm">\${d.guias}</td>
                <td class="px-4 py-2.5 text-center text-sm">
                    \${d.con_problema > 0
                    ? \`<span class="text-red-500 font-bold">⚠ \${d.con_problema}</span>\`
                    : '<span class="text-green-500">✓</span>'}
                </td>
            </tr>`).join('');

        // ── Tabla: rutas en viaje ───────────────────────────────
        const tblR = document.getElementById('tbl-routes');
        tblR.innerHTML = data.active_routes_list.length === 0
            ? '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 italic text-sm">No hay rutas en viaje</td></tr>'
            : data.active_routes_list.map(r => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition">
                <td class="px-4 py-2.5 font-mono font-bold t600 dark:text-indigo-400 text-sm">\${r.numero}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">\${r.origen}</td>
                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 text-sm">\${r.destino}</td>
                <td class="px-4 py-2.5 text-center font-semibold text-gray-800 dark:text-gray-100 text-sm">\${r.guias}</td>
            </tr>`).join('');
    }

    document.getElementById('btn-apply-filter').addEventListener('click', () => {
        const from = document.getElementById('filter-from').value;
        const to = document.getElementById('filter-to').value;
        if (!from || !to) { alert('Seleccioná ambas fechas para filtrar.'); return; }
        document.getElementById('filter-label').classList.remove('hidden');
        loadStats();
    });

    document.getElementById('btn-clear-filter').addEventListener('click', () => {
        document.getElementById('filter-from').value = '';
        document.getElementById('filter-to').value = '';
        document.getElementById('filter-label').classList.add('hidden');
        loadStats();
    });

    document.addEventListener('DOMContentLoaded', loadStats);
    window.addEventListener('themeChanged', loadStats);
</script>
@endsection

HTML;

file_put_contents($file, implode("", $output) . $script);
echo "File updated.";
