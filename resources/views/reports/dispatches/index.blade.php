@extends('layouts.app')

@section('content')
<style>
    /* ================================================================
       DataTables 2.x scroll layout uses:
         div.dt-scroll-head  → cloned header (visible)
         div.dt-scroll-body  → scrollable body with data rows
       
       The sort icon is a .dt-column-order span (width:12px) inside th.
       To align headers with data, we make .dt-column-order not consume
       inline space by positioning it absolutely within th.
       ================================================================ */

    /* Same base padding for th and td */
    div.dt-scroll-head th,
    div.dt-scroll-body td {
        padding: 4px 6px !important;
        box-sizing: border-box !important;
    }

    /* Header text styles */
    div.dt-scroll-head th {
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 10px !important;
        letter-spacing: -0.025em !important;
        white-space: nowrap !important;
        /* Extra right space so sort icon doesn't overlap text */
        padding-right: 20px !important;
        position: relative !important;
    }

    /* Data cell text styles — same right padding as th so widths match */
    div.dt-scroll-body td {
        font-size: 11px !important;
        line-height: 1.1 !important;
        letter-spacing: -0.02em !important;
        padding-right: 20px !important;
    }

    /* Pull the .dt-column-order icon out of flow so it takes no width */
    div.dt-scroll-head th .dt-column-order {
        position: absolute !important;
        right: 4px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 12px !important;
        height: 20px !important;
    }

    /* Checkbox first column: symmetric, centered */
    div.dt-scroll-head th:first-child,
    div.dt-scroll-body td:first-child {
        padding: 4px 6px !important;
        text-align: center !important;
    }

    /* Print styles */
    @media print {
        header, nav, aside, footer, #page-loader, #filters-container, #local-filters-container, #btn-filter, #btn-toggle-advanced-filters, .dt-controls {
            display: none !important;
        }

        body, .bg-white, .dark\:bg-gray-800 {
            background-color: white !important;
            color: black !important;
        }

        div.dt-scroll-body {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }

        #totals-container {
            display: flex !important;
            flex-direction: row !important;
            justify-content: center !important;
            align-items: flex-start !important;
            gap: 40px !important;
            margin-top: 30px !important;
            width: 100% !important;
        }

        #totals-container > div.hidden {
            display: none !important;
        }

        #totals-container > .totals-card {
            width: 35% !important;
            max-width: 350px !important;
            background-color: #f9fafb !important;
            border: 1px solid #e5e7eb !important;
            padding: 16px !important;
            border-radius: 8px !important;
            display: block !important;
            box-sizing: border-box !important;
        }
    }
</style>

<!-- Full-screen Page Loader Overlay -->
<div id="page-loader" class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-gray-900/60 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-2xl flex flex-col items-center border border-gray-100 dark:border-gray-700 max-w-xs w-full mx-4">
        <svg class="animate-spin h-12 w-12 text-indigo-600 dark:text-indigo-400 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Cargando Reporte...</span>
        <p class="text-xs text-gray-400 mt-1 text-center">Por favor espere mientras procesamos la información.</p>
    </div>
</div>

<div class="py-6">
    <div class="max-w-full mx-auto px-2">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-3">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">Reporte de Guías</h2>

            {{-- Filtros --}}
            <div id="filters-container" class="mb-6 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <!-- Filtros Principales (Constantes) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                        <select id="filter_company_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente(s)</label>
                        <select id="filter_party_id" multiple class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Inicio</label>
                        <input type="date" id="filter_start_date" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Fin</label>
                        <input type="date" id="filter_end_date" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                </div>

                <!-- Botones de Acción principales -->
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <button id="btn-filter" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition h-[38px] text-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                        </svg>
                        Filtrar Reporte
                    </button>
                    <button id="btn-toggle-advanced-filters" type="button" class="inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-gray-700 active:bg-gray-200 dark:active:bg-gray-900 transition h-[38px] text-center shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" id="advanced-filters-icon" class="h-4 w-4 mr-2 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span id="advanced-filters-text">Más Filtros</span>
                    </button>
                </div>

                <!-- Filtros Ocultables -->
                <div id="advanced-filters-container" class="hidden border-t border-gray-200 dark:border-gray-700 pt-4 mt-4 transition-all duration-300 ease-in-out">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Origen(es)</label>
                            <select id="filter_origin_id" multiple class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                @foreach($ubicaciones as $ubi)
                                <option value="{{ $ubi->id }}">{{ $ubi->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destino(s)</label>
                            <select id="filter_destination_id" multiple class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                @foreach($ubicaciones as $ubi)
                                <option value="{{ $ubi->id }}">{{ $ubi->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado / Ub. Actual</label>
                            <select id="filter_ubicacion_actual" multiple class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                <option value="Dto origen">Dto origen</option>
                                <option value="En transito">En tránsito</option>
                                <option value="Dto destino">Dto destino</option>
                                <option value="En reparto">En reparto</option>
                                <option value="Entregado">Entregado</option>
                                <option value="Con problemas">Con problemas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Despacho N°</label>
                            <input type="text" id="filter_dispatch_number" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hoja de Ruta N°</label>
                            <input type="text" id="filter_route_number" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reparto N°</label>
                            <input type="text" id="filter_delivery_number" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Factura N°</label>
                            <input type="text" id="filter_invoice_number" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filtros locales sobre registros mostrados --}}
            <div id="local-filters-container" class="mb-4 bg-indigo-50/50 dark:bg-indigo-950/20 p-4 rounded-lg border border-indigo-100 dark:border-indigo-900/50">
                <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-300 mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z" />
                    </svg>
                    Filtrar sobre los registros ya mostrados en la tabla
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="local_filter_remitente" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Remitente</label>
                        <select id="local_filter_remitente" multiple class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                        </select>
                    </div>
                    <div>
                        <label for="local_filter_destinatario" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Destinatario</label>
                        <select id="local_filter_destinatario" multiple class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                        </select>
                    </div>
                    <div>
                        <label for="local_filter_cobrada" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Cobrada</label>
                        <select id="local_filter_cobrada" multiple class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto shadow rounded-lg mb-6 max-w-full">
                <table class="divide-y divide-gray-200 dark:divide-gray-700 display text-left text-sm whitespace-nowrap" id="reports-table" data-url="{{ route('reports.dispatches.datatable') }}">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-2 border-b text-center"><input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-indigo-500 cursor-pointer w-4 h-4"></th>
                            <th class="p-2 border-b">Fecha</th>
                            <th class="p-2 border-b">F. Entrega</th>
                            <th class="p-2 border-b"># Guía</th>
                            <th class="p-2 border-b">Remitente</th>
                            <th class="p-2 border-b">Destinatario</th>
                            <th class="p-2 border-b">Origen</th>
                            <th class="p-2 border-b">Destino</th>
                            <th class="p-2 border-b">Ruta</th>
                            <th class="p-2 border-b">Despacho</th>
                            <th class="p-2 border-b">Reparto</th>
                            <th class="p-2 border-b text-center">Pagar En</th>
                            <th class="p-2 border-b text-center">Cobrada</th>
                            <th class="p-2 border-b">Remitos</th>
                            <th class="p-2 border-b text-center">Ubicación</th>
                            <th class="p-2 border-b text-center">Bultos</th>
                            <th class="p-2 border-b text-right">Peso</th>
                            <th class="p-2 border-b text-right">Volumen</th>
                            <th class="p-2 border-b text-right">Flete</th>
                            <th class="p-2 border-b text-right">Seguro</th>
                            <th class="p-2 border-b text-right">ContraReem.</th>
                            <th class="p-2 border-b text-right">Retiro Mer.</th>
                            <th class="p-2 border-b text-right">V. Declarado</th>
                            <th class="p-2 border-b text-indigo-600 font-bold text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    </tbody>
                </table>
            </div>

            <!-- Sección de Totales de Importes Centrada (Columnas 2 y 3 en grid de 4) -->
            <div id="totals-container" class="grid grid-cols-1 md:grid-cols-4 gap-16 mt-6">
                <!-- Columna 1 vacía para centrar -->
                <div class="hidden md:block"></div>

                <!-- Tabla Totales de la Página (Seleccionados) -->
                <div class="totals-card md:col-span-1 bg-gray-50 dark:bg-gray-900/40 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></span>
                        Totales de la Página Actual
                    </h3>
                    <table class="w-full text-xs text-gray-700 dark:text-gray-300 border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
                                <th class="py-2 font-semibold text-gray-500 dark:text-gray-400">Concepto</th>
                                <th class="py-2 text-right font-semibold text-gray-500 dark:text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr>
                                <td class="py-2 font-medium">Flete</td>
                                <td id="page-total-flete" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium">Seguro</td>
                                <td id="page-total-seguro" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium">Contra Reembolso</td>
                                <td id="page-total-contra-reembolso" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium">Retiro Mercadería</td>
                                <td id="page-total-retencion-mercaderia" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium">Valor Declarado</td>
                                <td id="page-total-valor-declarado" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr class="border-t border-gray-200 dark:border-gray-700 font-bold bg-indigo-50/30 dark:bg-indigo-950/10">
                                <td class="py-2.5 text-indigo-700 dark:text-indigo-400">Total</td>
                                <td id="page-total-sum" class="py-2.5 text-right text-indigo-700 dark:text-indigo-400">$ 0,00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tabla Totales del Filtro General -->
                <div class="totals-card md:col-span-1 bg-gray-50 dark:bg-gray-900/40 p-4 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                        Totales del Filtro General
                    </h3>
                    <table class="w-full text-xs text-gray-700 dark:text-gray-300 border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-left">
                                <th class="py-2 font-semibold text-gray-500 dark:text-gray-400">Concepto</th>
                                <th class="py-2 text-right font-semibold text-gray-500 dark:text-gray-400">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr>
                                <td class="py-2 font-medium">Flete</td>
                                <td id="general-total-flete" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium">Seguro</td>
                                <td id="general-total-seguro" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium">Contra Reembolso</td>
                                <td id="general-total-contra-reembolso" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium">Retiro Mercadería</td>
                                <td id="general-total-retencion-mercaderia" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr>
                                <td class="py-2 font-medium">Valor Declarado</td>
                                <td id="general-total-valor-declarado" class="py-2 text-right font-bold text-gray-900 dark:text-white">$ 0,00</td>
                            </tr>
                            <tr class="border-t border-gray-200 dark:border-gray-700 font-bold bg-green-50/30 dark:bg-green-950/10">
                                <td class="py-2.5 text-green-700 dark:text-green-400">Total</td>
                                <td id="general-total-sum" class="py-2.5 text-right text-green-700 dark:text-green-400">$ 0,00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Columna 4 vacía para centrar -->
                <div class="hidden md:block"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- CDNs para exportación (PDFMake/JSZip no requieren jQuery) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- CSS de DataTables Buttons y Select2 -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />



@vite('resources/js/pages/reports/dispatches.js')
@endsection
