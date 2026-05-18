@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Generar Factura</h2>
                <a href="{{ route('billing.invoices') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                    Ver Facturas
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="mb-6 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-8 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                        <select id="filter_company_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" required>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                        <select id="filter_party_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value=""></option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"># Guía</label>
                        <input type="text" id="filter_numero" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"># Factura</label>
                        <input type="text" id="filter_invoice_numero" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facturada</label>
                        <select id="filter_facturada" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            <option value="1">Con factura</option>
                            <option value="0">Sin factura</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cobrada</label>
                        <select id="filter_cobrada" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            <option value="0">Pendiente</option>
                            <option value="1">Cobrada</option>
                        </select>
                    </div>
                    <div class="md:col-span-3 lg:col-span-8 flex justify-end">
                        <button id="btn-filter"
                            class="inline-flex items-center justify-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabla de Guías --}}
            <div class="overflow-x-auto shadow rounded-lg mb-4">
                <table id="invoices-table"
                       data-url="{{ route('billing.datatable') }}"
                       class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-2 border-b w-8 text-center">
                                <input type="checkbox" id="select-all" class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer">
                            </th>
                            <th class="p-2 border-b">Fecha</th>
                            <th class="p-2 border-b"># Guía</th>
                            <th class="p-2 border-b">Remitente</th>
                            <th class="p-2 border-b">Destinatario</th>
                            <th class="p-2 border-b">Factura</th>
                            <th class="p-2 border-b">Cobrada</th>
                            <th class="p-2 border-b text-indigo-600 font-bold">Total</th>
                            <th class="p-2 border-b text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- Barra flotante de seleccion --}}
<div id="selection-bar" class="hidden fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-white dark:bg-gray-800 shadow-2xl rounded-full px-6 py-4 border border-gray-200 dark:border-gray-700 z-40 flex items-center gap-6">
    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
        Seleccionados: <span id="selected-count" class="font-bold text-indigo-600 dark:text-indigo-400">0</span>
    </div>
    <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
        Total: <span id="selected-total" class="font-bold text-green-600 dark:text-green-400">$ 0,00</span>
    </div>
    <button id="btn-open-invoice-modal" class="px-4 py-2 bg-indigo-600 text-white rounded-full font-semibold text-xs tracking-widest hover:bg-indigo-700 transition">
        Generar Factura
    </button>
</div>

{{-- Modal Generar Factura --}}
<div id="modal-generate-invoice" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" id="modal-invoice-backdrop"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form action="{{ route('billing.store') }}" method="POST" id="form-generate-invoice">
                @csrf
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                        Generar Factura para <span id="modal-invoice-count" class="text-indigo-600"></span> Guías
                    </h3>
                    <div class="mt-4 space-y-4">
                        <input type="hidden" name="shipment_ids_json" id="hidden-shipment-ids">
                        <input type="hidden" name="company_id" id="modal_invoice_company_id">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente *</label>
                            <select name="party_id" id="invoice_party_id" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm">
                                <option value=""></option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número de Factura *</label>
                                <input type="text" name="numero" required class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm" placeholder="Ej: A-0001-00001234">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Factura *</label>
                                <input type="date" name="fecha_factura" required value="{{ date('Y-m-d') }}" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recibo (Opcional)</label>
                            <input type="text" name="numero_recibo" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm" placeholder="Ej: R-0001-00004321">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas / Observaciones</label>
                            <textarea name="notas" rows="2" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Generar
                    </button>
                    <button type="button" id="btn-close-invoice-modal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@vite('resources/js/pages/billing/index.js')
@endsection
