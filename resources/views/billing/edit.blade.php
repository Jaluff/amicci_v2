@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                    Editar Factura # {{ $invoice->numero }}
                </h2>
                <a href="{{ route('billing.show', $invoice) }}" class="text-sm text-indigo-600 hover:underline">← Ver factura</a>
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

            <form method="POST" action="{{ route('billing.update', $invoice) }}" id="edit-invoice-form">
                @csrf
                @method('PUT')
                <div id="shipment-ids-container">
                    @foreach($invoice->shipments as $s)
                        <input type="hidden" name="shipment_ids[]" value="{{ $s->id }}" class="pre-selected-shipment">
                    @endforeach
                </div>

                {{-- Datos básicos --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="lg:col-span-2">
                        <label for="party_display" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                        <input type="text" id="party_display" value="{{ $invoice->party?->name }}" readonly
                            class="w-full border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm text-sm bg-gray-50 cursor-not-allowed">
                    </div>
                    <div>
                        <label for="numero" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            # Factura <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="numero" name="numero" value="{{ old('numero', $invoice->numero) }}" required
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="fecha_factura" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Fecha Factura <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="fecha_factura" name="fecha_factura"
                            value="{{ old('fecha_factura', $invoice->fecha_factura?->format('Y-m-d')) }}" required
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label for="numero_recibo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"># Recibo</label>
                        <input type="text" id="numero_recibo" name="numero_recibo"
                            value="{{ old('numero_recibo', $invoice->numero_recibo) }}"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="notas" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                        <input type="text" id="notas" name="notas" value="{{ old('notas', $invoice->notas) }}"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                    </div>
                </div>

                {{-- Filtros para agregar guías --}}
                <div class="mb-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg border border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Agregar / Quitar Guías</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                            <select id="filter_company_id" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                                <option value="">Todas</option>
                                {{-- Las empresas se pasan desde el controller en edit view si se necesita; simplificamos aquí --}}
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
                        <div>
                            <button type="button" id="btn-filter"
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition h-[38px]">
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabla de guías disponibles --}}
                <div class="overflow-x-auto shadow rounded-lg mb-5">
                    <table id="available-shipments-table"
                           data-url="{{ route('billing.available-shipments') }}"
                           data-preselected="{{ $invoice->shipments->pluck('id')->toJson() }}"
                           class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                            <tr>
                                <th class="p-2 border-b w-10 text-center">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600 w-4 h-4">
                                </th>
                                <th class="p-2 border-b">Fecha</th>
                                <th class="p-2 border-b"># Guía</th>
                                <th class="p-2 border-b">Remitente</th>
                                <th class="p-2 border-b">Destinatario</th>
                                <th class="p-2 border-b">Ubicación</th>
                                <th class="p-2 border-b">Factura Actual</th>
                                <th class="p-2 border-b">Cobrada</th>
                                <th class="p-2 border-b text-right">Flete</th>
                                <th class="p-2 border-b text-right">Seguro</th>
                                <th class="p-2 border-b text-right">Com. Contr.</th>
                                <th class="p-2 border-b text-right">Ret. Merc.</th>
                                <th class="p-2 border-b text-right font-bold text-indigo-600">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"></tbody>
                    </table>
                </div>

                {{-- Resumen y guardar --}}
                <div class="flex items-center justify-between bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-200 dark:border-indigo-700 rounded-lg p-4">
                    <div class="flex gap-8">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Guías seleccionadas</p>
                            <p id="selected-count" class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">0</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                            <p id="selected-total" class="text-2xl font-bold text-green-700 dark:text-green-300">$ 0,00</p>
                        </div>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center px-8 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-indigo-700 transition">
                        Guardar Cambios
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@vite('resources/js/pages/billing/create.js')
@endsection
