@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6">Reporte de Guías</h2>

            {{-- Filtros --}}
            <div class="mb-6 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4 gap-4 items-end">
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cobrada</label>
                        <select id="filter_cobrada" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Inicio</label>
                        <input type="date" id="filter_start_date" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Fin</label>
                        <input type="date" id="filter_end_date" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <button id="btn-filter" class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition h-[38px] text-center">
                            Filtrar Reporte
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto shadow rounded-lg mb-6 max-w-full">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display w-full text-left text-sm whitespace-nowrap" id="reports-table" data-url="{{ route('reports.dispatches.datatable') }}">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-2 border-b w-10 text-center"><input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-indigo-500 cursor-pointer w-4 h-4"></th>
                            <th class="p-2 border-b">Fecha</th>
                            <th class="p-2 border-b"># Guía</th>
                            <th class="p-2 border-b">Remitente</th>
                            <th class="p-2 border-b">Destinatario</th>
                            <th class="p-2 border-b">Origen</th>
                            <th class="p-2 border-b">Destino</th>
                            <th class="p-2 border-b">Ruta</th>
                            <th class="p-2 border-b">Despacho</th>
                            <th class="p-2 border-b">Reparto</th>
                            <th class="p-2 border-b">Pagar En</th>
                            <th class="p-2 border-b">Cobrada</th>
                            <th class="p-2 border-b">Remitos</th>
                            <th class="p-2 border-b">Ubicación</th>
                            <th class="p-2 border-b">Bultos</th>
                            <th class="p-2 border-b">Peso</th>
                            <th class="p-2 border-b">Volumen</th>
                            <th class="p-2 border-b">Flete</th>
                            <th class="p-2 border-b">Seguro</th>
                            <th class="p-2 border-b">ContraReem.</th>
                            <th class="p-2 border-b">Reten. Mer.</th>
                            <th class="p-2 border-b">V. Declarado</th>
                            <th class="p-2 border-b text-indigo-600 font-bold">Total</th>
                        </tr>
                    </thead>
                    <tfoot class="bg-indigo-50 dark:bg-indigo-900 border-t-2 border-indigo-200 dark:border-indigo-700 font-bold text-gray-800 dark:text-gray-200">
                        <tr>
                            <th colspan="14" class="p-2 text-right">TOTALES:</th>
                            <th class="p-2"></th>
                            <th class="p-2"></th>
                            <th class="p-2"></th>
                            <th class="p-2"></th>
                            <th class="p-2"></th>
                            <th class="p-2"></th>
                            <th class="p-2"></th>
                            <th class="p-2"></th>
                            <th class="p-2 text-indigo-700 dark:text-indigo-300"></th>
                        </tr>
                    </tfoot>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    </tbody>
                </table>
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
