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
                        <select id="filter_company_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                        <select id="filter_party_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value=""></option>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}">{{ $party->name }}</option>
                            @endforeach
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
                            <th class="p-2 border-b">Fecha Emisión</th>
                            <th class="p-2 border-b"># Factura</th>
                            <th class="p-2 border-b">Cliente</th>
                            <th class="p-2 border-b">Recibo</th>
                            <th class="p-2 border-b">Cant. Guías</th>
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
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@vite('resources/js/pages/billing/invoices.js')
@endsection
