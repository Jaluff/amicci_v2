@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Nueva Factura</h2>
                <a href="{{ route('billing.index') }}" class="text-sm text-indigo-600 hover:underline">← Volver al listado</a>
            </div>

            @if($errors->any())
                <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Panel superior: Filtros de búsqueda de guías --}}
            <div class="mb-5 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">1. Buscar Guías</p>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                        <select id="filter_company_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" required>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"># Guía</label>
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
                    <div class="lg:col-span-5 flex justify-end">
                        <button id="btn-filter"
                            class="inline-flex items-center justify-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                            Buscar Guías
                        </button>
                    </div>
                </div>
            </div>

            {{-- Tabla de Guías disponibles --}}
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">2. Seleccionar Guías</p>
            <div class="overflow-x-auto shadow rounded-lg mb-5">
                <table id="available-shipments-table"
                       data-url="{{ route('billing.available-shipments') }}"
                       class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-2 border-b w-10 text-center">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 focus:ring-indigo-500 cursor-pointer w-4 h-4">
                            </th>
                            <th class="p-2 border-b">Fecha</th>
                            <th class="p-2 border-b"># Guía</th>
                            <th class="p-2 border-b">Remitente</th>
                            <th class="p-2 border-b">Destinatario</th>
                            <th class="p-2 border-b">Ubicación</th>
                            <th class="p-2 border-b">Factura Actual</th>
                            <th class="p-2 border-b">Cobrada</th>
                            <th class="p-2 border-b">Flete</th>
                            <th class="p-2 border-b">Seguro</th>
                            <th class="p-2 border-b">Com. Contr.</th>
                            <th class="p-2 border-b">Ret. Merc.</th>
                            <th class="p-2 border-b text-indigo-600 font-bold">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"></tbody>
                </table>
            </div>

            {{-- Panel de resumen + formulario de factura --}}
            <div class="bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg p-5 mb-5">
                <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-3">3. Datos de la Factura</p>
                <div class="flex flex-col lg:flex-row gap-6 items-start">

                    {{-- Resumen de selección --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-indigo-200 dark:border-indigo-600 p-4 min-w-[220px]">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Guías seleccionadas</p>
                        <p id="selected-count" class="text-3xl font-bold text-indigo-700 dark:text-indigo-300">0</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 mb-1">Total a facturar</p>
                        <p id="selected-total" class="text-2xl font-bold text-green-700 dark:text-green-300">$ 0,00</p>
                    </div>

                    {{-- Formulario --}}
                    <form id="invoice-form" method="POST" action="{{ route('billing.store') }}" class="flex-1">
                        @csrf
                        {{-- Hidden inputs para los IDs seleccionados y empresa --}}
                        <div id="shipment-ids-container"></div>
                        <input type="hidden" name="company_id" id="invoice_company_id">

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="lg:col-span-2">
                                <label for="party_select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Cliente <span class="text-red-500">*</span>
                                </label>
                                <select id="party_select" name="party_id" required
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm @error('party_id') border-red-500 @enderror">
                                    <option value=""></option>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->id }}" {{ old('party_id') == $party->id ? 'selected' : '' }}>
                                            {{ $party->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="numero" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    # Factura <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="numero" name="numero" value="{{ old('numero') }}" required
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm @error('numero') border-red-500 @enderror"
                                    placeholder="Ej: A-00123">
                            </div>
                            <div>
                                <label for="fecha_factura" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Fecha Factura <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="fecha_factura" name="fecha_factura" value="{{ old('fecha_factura', date('Y-m-d')) }}" required
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm @error('fecha_factura') border-red-500 @enderror">
                            </div>
                            <div>
                                <label for="numero_recibo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"># Recibo</label>
                                <input type="text" id="numero_recibo" name="numero_recibo" value="{{ old('numero_recibo') }}"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm"
                                    placeholder="Opcional">
                            </div>
                            <div class="lg:col-span-3">
                                <label for="notas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                                <input type="text" id="notas" name="notas" value="{{ old('notas') }}"
                                    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm"
                                    placeholder="Opcional">
                            </div>
                        </div>

                        <div class="flex justify-end mt-5">
                            <button type="submit" id="btn-generate"
                                disabled
                                class="inline-flex items-center justify-center px-8 py-2.5 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-green-700 active:bg-green-900 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                ✓ Generar Factura
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@vite('resources/js/pages/billing/create.js')
@endsection
