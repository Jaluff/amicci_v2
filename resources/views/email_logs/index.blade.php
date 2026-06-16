@extends('layouts.app')

@section('content')
<div class="py-6" x-data="{ 
    selectedLog: null, 
    modalOpen: false, 
    emailsEnabled: {{ $emailsEnabled ? 'true' : 'false' }},
    toggleEmails(val) {
        fetch('{{ route('email-logs.toggle') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ enabled: val })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.toastr.success(data.message);
                loadStats();
            } else {
                window.toastr.error('Error al actualizar la configuración.');
                this.emailsEnabled = !val;
            }
        })
        .catch(err => {
            console.error(err);
            window.toastr.error('Error de red al actualizar la configuración.');
            this.emailsEnabled = !val;
        });
    }
}">
    <div class="space-y-4">

        {{-- HEADER --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-base font-bold text-gray-800 dark:text-gray-100 tracking-tight flex items-center gap-2">
                    <span class="inline-block w-1 h-4 rounded-full bg-indigo-500"></span>
                    Gestión de Correos
                </h1>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 ml-3">
                    Historial y estado de notificaciones a clientes &middot;
                    <span id="last-refresh" class="text-indigo-400 font-medium"></span>
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 shadow-sm">
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Empresa</span>
                    <select id="filter-company" class="text-[11px] bg-transparent text-gray-700 dark:text-gray-100 dark:bg-gray-800 outline-none">
                        <option value="" class="dark:bg-gray-800">Todas</option>
                        @foreach($userCompanies as $company)
                        <option value="{{ $company->id }}" class="dark:bg-gray-800">{{ $company->prefix }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 shadow-sm">
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Estado</span>
                    <select id="filter-status" class="text-[11px] bg-transparent text-gray-700 dark:text-gray-100 dark:bg-gray-800 outline-none">
                        <option value="" class="dark:bg-gray-800">Todos</option>
                        <option value="sent" class="dark:bg-gray-800">Enviado</option>
                        <option value="failed" class="dark:bg-gray-800">Fallido</option>
                        <option value="sending" class="dark:bg-gray-800">Enviando</option>
                        <option value="pending" class="dark:bg-gray-800">Pendiente</option>
                    </select>
                </div>
                <div class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 shadow-sm">
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Desde</span>
                    <input type="date" id="filter-from"
                        class="text-[11px] bg-transparent text-gray-700 dark:text-white outline-none w-28">
                    <span class="text-gray-300 dark:text-gray-600 mx-1">|</span>
                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Hasta</span>
                    <input type="date" id="filter-to"
                        class="text-[11px] bg-transparent text-gray-700 dark:text-white outline-none w-28">
                </div>
                <button id="btn-apply-filter"
                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[11px] font-semibold rounded-lg transition shadow-sm">Filtrar</button>
                <button id="btn-clear-filter"
                    class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-[11px] font-medium rounded-lg transition hover:bg-gray-50 dark:hover:bg-gray-600 shadow-sm">Limpiar</button>
                <span id="filter-label"
                    class="hidden text-[10px] text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40 px-2 py-1 rounded-full">Filtrando</span>
            </div>
        </div>

        {{-- Configuración Global de Envíos --}}
        <div class="flex items-center">
            <div class="flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 shadow-sm">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" x-model="emailsEnabled" @change="toggleEmails(emailsEnabled)"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300"
                          x-text="emailsEnabled ? 'Envío global de correos: Habilitado' : 'Envío global de correos: Deshabilitado'">
                    </span>
                </label>
            </div>
        </div>

        {{-- KPIs: 4 en una fila, sin iconos, estilo uniforme --}}
        <div class="flex items-stretch justify-between gap-2.5 w-full overflow-x-auto pb-1">
            @foreach([
                ['id'=>'k-total', 'label'=>'Total Notificaciones'],
                ['id'=>'k-sent', 'label'=>'Enviados Correctamente'],
                ['id'=>'k-pending', 'label'=>'En Cola / Enviando'],
                ['id'=>'k-failed', 'label'=>'Intentos Fallidos'],
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

        {{-- GRAFICOS SUPERIORES: 50/50 --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            {{-- Dona: Estado de Notificaciones --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-3 flex flex-col">
                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Estado de Notificaciones</p>
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

            {{-- Linea/Area: Volumen Diario --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-3 flex flex-col">
                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Volumen Diario de Notificaciones</p>
                <div id="chart-area-daily" class="flex-1 min-h-[190px] -mx-1"></div>
            </div>
        </div>

        {{-- INFERIOR: 50/50 GRÁFICO ETAPAS Y TABLA DE LOGS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
            {{-- Horizontal Bar: Etapas --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-3 flex flex-col">
                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Notificaciones por Etapa del Proceso</p>
                <div id="chart-stages" class="flex-1 min-h-[220px] -mx-1"></div>
            </div>

            {{-- Tabla de logs --}}
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col">
                <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse inline-block"></span>
                        <span
                            class="text-[9px] font-bold uppercase tracking-widest text-gray-600 dark:text-gray-300">Historial Reciente</span>
                    </div>
                    <div class="flex items-center gap-1" id="pag-logs"></div>
                </div>
                <div class="flex-1 overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Fecha</th>
                                <th
                                    class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Guía</th>
                                <th
                                    class="px-3 py-2 text-left text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Destinatario</th>
                                <th
                                    class="px-3 py-2 text-center text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Estado</th>
                                <th
                                    class="px-3 py-2 text-center text-[9px] font-semibold uppercase text-gray-500 dark:text-gray-300">
                                    Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbl-logs" class="divide-y divide-gray-50 dark:divide-gray-700/40 text-xs">
                            <tr>
                                <td colspan="5"
                                    class="px-3 py-4 text-center text-gray-300 dark:text-gray-600 italic text-[10px]">
                                    Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- DETALLE MODAL (Alpine.js) --}}
    <div x-show="modalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
             @click.away="modalOpen = false">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-750">
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider flex items-center gap-2">
                    <span class="inline-block w-1.5 h-3 rounded-full bg-indigo-500"></span>
                    Detalle de Notificación
                </h3>
                <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <div class="p-4 space-y-3.5 text-[11px]" x-if="selectedLog">
                <div class="grid grid-cols-2 gap-2.5">
                    <div>
                        <span class="text-gray-400 dark:text-gray-500 uppercase tracking-wider block text-[9px] font-semibold">Fecha y Hora</span>
                        <span class="text-gray-700 dark:text-gray-200 font-medium" x-text="formatDate(selectedLog.created_at)"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 dark:text-gray-500 uppercase tracking-wider block text-[9px] font-semibold">Guía</span>
                        <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400" x-text="selectedLog.shipments_count && selectedLog.shipments_count > 1 ? selectedLog.shipments_count + ' guías' : (selectedLog.shipment ? selectedLog.shipment.numero : '-')"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 dark:text-gray-500 uppercase tracking-wider block text-[9px] font-semibold">Empresa</span>
                        <span class="text-gray-700 dark:text-gray-200 font-medium" x-text="selectedLog.company ? selectedLog.company.prefix : '-'"></span>
                    </div>
                    <div>
                        <span class="text-gray-400 dark:text-gray-500 uppercase tracking-wider block text-[9px] font-semibold">Estado de Envío</span>
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-semibold border uppercase mt-0.5" 
                              :class="getStatusBadgeClass(selectedLog.status)" 
                              x-text="getStatusLabel(selectedLog.status)"></span>
                    </div>
                </div>

                <div>
                    <span class="text-gray-400 dark:text-gray-500 uppercase tracking-wider block text-[9px] font-semibold">Cliente</span>
                    <span class="text-gray-700 dark:text-gray-200 font-medium" x-text="selectedLog.party ? selectedLog.party.name : '-'"></span>
                </div>

                <div>
                    <span class="text-gray-400 dark:text-gray-500 uppercase tracking-wider block text-[9px] font-semibold">Correo de Destino</span>
                    <span class="text-gray-700 dark:text-gray-200 font-mono" x-text="selectedLog.recipient"></span>
                </div>

                <div>
                    <span class="text-gray-400 dark:text-gray-500 uppercase tracking-wider block text-[9px] font-semibold">Etapa de la Guía</span>
                    <span class="text-gray-700 dark:text-gray-200 font-medium" x-text="getStageLabel(selectedLog.stage)"></span>
                </div>

                <div x-show="selectedLog.status === 'failed'" class="p-2.5 bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 rounded-lg">
                    <span class="text-red-500 dark:text-red-400 uppercase tracking-wider block text-[9px] font-bold mb-1">Detalle del Error</span>
                    <p class="text-red-700 dark:text-red-300 font-mono break-words leading-relaxed text-[10px]" x-text="selectedLog.error_message || 'Error desconocido'"></p>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2 bg-gray-50 dark:bg-gray-750">
                <button @click="modalOpen = false" class="px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 text-[10px] font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition">Cerrar</button>
                <button x-show="selectedLog && selectedLog.status === 'failed'" 
                        @click="resendMail(selectedLog.id); modalOpen = false" 
                        class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-semibold rounded-lg transition flex items-center gap-1 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5L21 12L3 4.5V10.5L14.25 12L3 13.5V19.5Z"/></svg>
                    Reenviar Correo
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
<script>
var STATS_URL = "{{ route('email-logs.stats') }}";
var DATATABLE_URL = "{{ route('email-logs.datatable') }}";
var currentPage = 1;

var STATUS_COLORS = {
    'sent':    '#22c55e',
    'failed':   '#ef4444',
    'sending':   '#3b82f6',
    'pending':    '#f59e0b'
};

var STAGE_LABELS = {
    'created': 'Al crear guía',
    'en_transito': 'Al salir a destino',
    'dto_destino': 'Al arribar a destino',
    'en_reparto': 'En reparto',
    'entregado': 'Entregado'
};

var STATUS_LABELS = {
    'sent': 'Enviado',
    'failed': 'Fallido',
    'sending': 'Enviando',
    'pending': 'Pendiente'
};

var apexDonut = null, apexArea = null, apexBar = null;

function isDark()     { return document.documentElement.classList.contains('dark'); }
function labelColor() { return '#9ca3af'; }
function gridColor()  { return isDark() ? '#374151' : '#f3f4f6'; }
function fgCard()     { return isDark() ? '#1f2937' : '#ffffff'; }

function stopPulse() {
    document.querySelectorAll('.animate-pulse[id]').forEach(function(el) { el.classList.remove('animate-pulse'); });
}

function getParams() {
    var from = document.getElementById('filter-from').value;
    var to   = document.getElementById('filter-to').value;
    var company = document.getElementById('filter-company').value;
    var status = document.getElementById('filter-status').value;
    var params = [];
    if (from && to) { params.push('from=' + from); params.push('to=' + to); }
    if (company) { params.push('company_id=' + company); }
    if (status) { params.push('status=' + status); }
    return params.length ? '?' + params.join('&') : '';
}

function loadStats() {
    fetch(STATS_URL + getParams(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(res) { 
        if (!res.ok) throw new Error('Error en el servidor: ' + res.status);
        return res.json(); 
    })
    .then(function(data) {
        var k = data.kpi;
        document.getElementById('k-total').textContent = k.total || 0;
        document.getElementById('k-sent').textContent  = k.sent || 0;
        document.getElementById('k-pending').textContent = k.pending || 0;
        document.getElementById('k-failed').textContent   = k.failed || 0;
        stopPulse();

        document.getElementById('last-refresh').textContent =
            'actualizado ' + new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });

        try {
            renderDonut(data);
            renderArea(data);
            renderStagesBar(data);
        } catch (e) {
            console.error('Error al renderizar gráficos:', e);
        }
    })
    .catch(function(err) {
        console.error('Error al cargar estadísticas:', err);
        stopPulse();
    });
}

function loadLogsTable(page = 1) {
    currentPage = page;
    var params = getParams();
    var conn = params ? '&' : '?';
    
    fetch(DATATABLE_URL + params + conn + 'page=' + page, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(res => res.json())
    .then(data => {
        var tbody = document.getElementById('tbl-logs');
        if (!data.data || data.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-3 py-4 text-center text-gray-300 dark:text-gray-600 italic text-[10px]">Sin notificaciones registradas</td></tr>';
            document.getElementById('pag-logs').innerHTML = '';
            return;
        }

        var html = data.data.map(function(log) {
            var badgeClass = getStatusBadgeClass(log.status);
            var statusLabel = getStatusLabel(log.status);
            
            var viewButton = '<a href="/admin/email-logs/' + log.id + '/preview" target="_blank" onclick="event.stopPropagation();" class="p-1 rounded bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-850 transition-colors inline-flex items-center justify-center shrink-0" title="Ver Correo">' +
                '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644M21.964 12.002a1.012 1.012 0 010-.644M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>' +
                '</a>';

            var resendButton = '';
            if (log.status === 'failed') {
                resendButton = '<button type="button" onclick="event.stopPropagation(); resendMail(' + log.id + ')" class="p-1 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-400 dark:border-indigo-800 dark:hover:bg-indigo-850 transition-colors inline-flex items-center justify-center shrink-0" title="Reenviar">' +
                    '<svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5L21 12L3 4.5V10.5L14.25 12L3 13.5V19.5Z"/></svg>' +
                    '</button>';
            }

            var trClick = "var scope = Alpine.find(document.querySelector('[x-data]')); scope.selectedLog = " + JSON.stringify(log).replace(/"/g, '&quot;') + "; scope.modalOpen = true;";

            var guideCell = (log.shipment ? log.shipment.numero : '-');
            if (log.shipments_count && log.shipments_count > 1) {
                guideCell = log.shipments_count + ' guías';
            }

            return '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors cursor-pointer" onclick="' + trClick + '">' +
                '<td class="px-3 py-1.5 text-gray-500 dark:text-gray-400 text-[10px] whitespace-nowrap">' + formatDate(log.created_at) + '</td>' +
                '<td class="px-3 py-1.5 font-mono font-bold text-indigo-600 dark:text-indigo-400 text-[10px]">' + guideCell + '</td>' +
                '<td class="px-3 py-1.5 text-gray-600 dark:text-gray-300 text-[10px] max-w-[120px] truncate" title="' + log.recipient + '">' + log.recipient + '</td>' +
                '<td class="px-3 py-1.5 text-center">' +
                    '<span class="inline-block px-1 rounded text-[8px] font-bold border uppercase ' + badgeClass + '">' + statusLabel + '</span>' +
                '</td>' +
                '<td class="px-3 py-1.5 text-center flex items-center justify-center gap-1">' +
                    viewButton +
                    resendButton +
                '</td>' +
                '</tr>';
        }).join('');
        tbody.innerHTML = html;

        renderPagination(data, 'pag-logs', loadLogsTable);
    });
}

function resendMail(id) {
    fetch('/admin/email-logs/' + id + '/resend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.toastr.success(data.message);
            loadStats();
            loadLogsTable(currentPage);
        } else {
            window.toastr.error(data.message || 'Error al reenviar.');
        }
    })
    .catch(err => {
        console.error(err);
        window.toastr.error('Error de red al reenviar el correo.');
    });
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    return d.toLocaleDateString('es-AR', {day: '2-digit', month: '2-digit'}) + ' ' + 
           d.toLocaleTimeString('es-AR', {hour: '2-digit', minute: '2-digit'});
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'sent': return 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/40 dark:text-green-400 dark:border-green-800';
        case 'failed': return 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/40 dark:text-red-400 dark:border-red-800';
        case 'sending': return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800';
        case 'pending': return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-400 dark:border-amber-800';
        default: return 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
    }
}

function getStatusLabel(status) {
    return STATUS_LABELS[status] || status;
}

function getStageLabel(stage) {
    return STAGE_LABELS[stage] || stage;
}

function renderPagination(paginator, containerId, clickFn) {
    var container = document.getElementById(containerId);
    if (!paginator || paginator.last_page <= 1) {
        container.innerHTML = '';
        return;
    }

    var html = '';
    var btnBase = 'px-1.5 py-0.5 text-[9px] rounded transition font-medium ';
    var btnActive = btnBase + 'bg-indigo-600 text-white shadow-sm';
    var btnInactive = btnBase + 'text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700';

    for (var i = 1; i <= paginator.last_page; i++) {
        if (i === paginator.current_page) {
            html += '<button type="button" class="' + btnActive + '">' + i + '</button>';
        } else {
            html += '<button type="button" data-page="' + i + '" class="' + btnInactive + '">' + i + '</button>';
        }
    }
    container.innerHTML = html;
    container.querySelectorAll('button[data-page]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var p = parseInt(this.getAttribute('data-page'));
            clickFn(p);
        });
    });
}

function renderDonut(data) {
    var labels = Object.keys(data.chart_status).map(getStatusLabel);
    var vals   = Object.values(data.chart_status);
    var colors = Object.keys(data.chart_status).map(function(s) { return STATUS_COLORS[s] || '#9ca3af'; });
    var total  = vals.reduce(function(a,b){ return a+b; }, 0);

    if (apexDonut) apexDonut.destroy();
    apexDonut = new ApexCharts(document.getElementById('chart-donut'), {
        series: vals, labels: labels, colors: colors,
        chart: { type: 'donut', height: 180, background: 'transparent', toolbar: { show: false }, animations: { enabled: true, speed: 500 } },
        dataLabels: { enabled: false }, legend: { show: false },
        stroke: { width: 2, colors: [fgCard()] },
        plotOptions: { pie: { donut: { size: '74%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '10px', color: '#9ca3af', formatter: function() { return total; } }, value: { show: true, fontSize: '20px', fontWeight: 700, color: isDark() ? '#e5e7eb' : '#111827', offsetY: 2 } } } } },
        tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: function(v) { return v + ' correos'; } } }
    });
    apexDonut.render();

    document.getElementById('tbl-donut-total').textContent = total;
    document.getElementById('tbl-donut-body').innerHTML = Object.keys(data.chart_status).map(function(key, i) {
        var pct = total > 0 ? Math.round(vals[i] / total * 100) : 0;
        return '<tr><td class="py-1 pr-1"><span class="flex items-center gap-1"><i style="background:' + colors[i] + '" class="inline-block w-2 h-2 rounded-full shrink-0"></i><span class="text-gray-600 dark:text-gray-300 truncate">' + labels[i] + '</span></span></td><td class="py-1 text-right font-semibold text-gray-700 dark:text-gray-200">' + vals[i] + '</td><td class="py-1 pl-1 text-right text-gray-400">' + pct + '%</td></tr>';
    }).join('');
}

function renderArea(data) {
    if (apexArea) apexArea.destroy();
    apexArea = new ApexCharts(document.getElementById('chart-area-daily'), {
        series: [{ name: 'Correos', data: data.chart_daily.data }],
        chart: { type: 'area', height: 190, background: 'transparent', toolbar: { show: false }, zoom: { enabled: false } },
        colors: ['#6366f1'],
        dataLabels: { enabled: false }, stroke: { curve: 'smooth', width: 2 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0.02 } },
        xaxis: { categories: data.chart_daily.labels, labels: { style: { fontSize: '9px', colors: labelColor() }, rotate: -30 }, axisBorder: { show: false }, axisTicks: { show: false }, tickAmount: 10 },
        yaxis: { labels: { style: { fontSize: '9px', colors: labelColor() }, formatter: function(v) { return Math.round(v); } }, min: 0 },
        grid: { borderColor: gridColor(), strokeDashArray: 3, padding: { left: 2, right: 4 } },
        tooltip: { theme: isDark() ? 'dark' : 'light' }
    });
    apexArea.render();
}

function renderStagesBar(data) {
    var categories = data.chart_stages.map(function(d) { return getStageLabel(d.stage); });
    var values = data.chart_stages.map(function(d) { return d.total; });

    if (apexBar) apexBar.destroy();
    apexBar = new ApexCharts(document.getElementById('chart-stages'), {
        series: [{ name: 'Correos', data: values }],
        chart: { type: 'bar', height: 210, background: 'transparent', toolbar: { show: false } },
        colors: ['#f97316'], dataLabels: { enabled: false },
        plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '50%' } },
        xaxis: { categories: categories, labels: { style: { fontSize: '9px', colors: labelColor() }, formatter: function(v) { return Math.round(v); } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { fontSize: '9px', colors: labelColor() }, maxWidth: 90 } },
        grid: { borderColor: gridColor(), strokeDashArray: 3, yaxis: { lines: { show: false } }, padding: { left: 2, right: 4 } },
        tooltip: { theme: isDark() ? 'dark' : 'light', y: { formatter: function(v) { return v + ' correos'; } } }
    });
    apexBar.render();
}

document.getElementById('btn-apply-filter').addEventListener('click', function() {
    var from = document.getElementById('filter-from').value;
    var to   = document.getElementById('filter-to').value;
    if ((from && !to) || (!from && to)) { alert('Selecciona ambas fechas.'); return; }
    document.getElementById('filter-label').classList.remove('hidden');
    loadStats();
    loadLogsTable(1);
});

document.getElementById('btn-clear-filter').addEventListener('click', function() {
    document.getElementById('filter-from').value = '';
    document.getElementById('filter-to').value   = '';
    document.getElementById('filter-company').value = '';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-label').classList.add('hidden');
    loadStats();
    loadLogsTable(1);
});

document.getElementById('filter-company').addEventListener('change', function() {
    document.getElementById('filter-label').classList.remove('hidden');
    loadStats();
    loadLogsTable(1);
});

document.getElementById('filter-status').addEventListener('change', function() {
    document.getElementById('filter-label').classList.remove('hidden');
    loadStats();
    loadLogsTable(1);
});

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadLogsTable(1);
});
window.addEventListener('themeChanged', function() { 
    loadStats(); 
    loadLogsTable(currentPage);
});
</script>
@endsection
