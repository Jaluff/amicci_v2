@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Listado de Facturas</h2>
                <a href="{{ route('billing.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                    Generar Factura
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="mb-6 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4 items-end">
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"># Factura</label>
                        <input type="text" id="filter_numero" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cobrada</label>
                        <select id="filter_cobrada" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            <option value="0">Pendiente</option>
                            <option value="1">Cobrada</option>
                        </select>
                    </div>
                    <div class="md:col-span-3 lg:col-span-7 flex justify-end">
                        <button id="btn-filter"
                            class="inline-flex items-center justify-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabla de Facturas --}}
            <div class="overflow-x-auto shadow rounded-lg">
                <table id="invoices-list-table"
                       data-url="{{ route('billing.invoices-datatable') }}"
                       class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-2 border-b !text-left whitespace-nowrap">Fecha Emisión</th>
                            <th class="p-2 border-b !text-left whitespace-nowrap">Fecha Cobro</th>
                            <th class="p-2 border-b !text-left whitespace-nowrap"># Factura</th>
                            <th class="p-2 border-b !text-left whitespace-nowrap">Cliente</th>
                            <th class="p-2 border-b !text-left whitespace-nowrap">Recibo</th>
                            <th class="p-2 border-b !text-left whitespace-nowrap">Cant. Guías</th>
                            <th class="p-2 border-b !text-left text-indigo-600 font-bold whitespace-nowrap">Total</th>
                            <th class="p-2 border-b !text-center whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- Modal para cobrar factura desde la tabla --}}
<div id="modal-pay-invoice" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-pay-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" id="modal-pay-overlay"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
            <form id="form-pay-invoice" method="POST" action="">
                @csrf
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-pay-title">
                                Cobrar Factura #<span id="modal-invoice-numero"></span>
                            </h3>
                            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                <p>Ingrese los datos del cobro. Todas las guías asociadas se marcarán como cobradas.</p>
                            </div>
                            
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label for="modal_numero_recibo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Recibo (Opcional)</label>
                                    <input type="text" name="numero_recibo" id="modal_numero_recibo" 
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" 
                                           placeholder="Ej. 0001-00001234">
                                </div>
                                
                                <div>
                                    <label for="modal_fecha_cobro" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Cobro</label>
                                    <input type="date" name="fecha_cobro" id="modal_fecha_cobro" required
                                           value="{{ date('Y-m-d') }}"
                                           class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                        Confirmar Cobro
                    </button>
                    <button type="button" id="btn-cancel-pay-modal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition">
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
@vite('resources/js/pages/billing/invoices.js')
@endsection
