@extends('layouts.app')

@section('content')
<div class="py-12">
    
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Gestión de Repartos</h2>
                <button id="btn-crear-reparto"
                    data-companies="{{ $userCompanies->map->only(['id', 'prefix', 'name', 'color'])->toJson() }}"
                    data-url="{{ route('deliveries.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    + Crear Reparto
                </button>
            </div>

            <div
                class="mb-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 items-end">
                    <div>
                        <label for="filter_company_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                        <select id="filter_company_id"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($userCompanies as $company)
                            <option value="{{ $company->id }}">{{ $company->prefix }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_location_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ubicación</label>
                        <select id="filter_location_id"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_fecha_inicio"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Inicio</label>
                        <input type="date" id="filter_fecha_inicio"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="filter_fecha_fin"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Fin</label>
                        <input type="date" id="filter_fecha_fin"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                    <div>
                        <label for="filter_numero_documento"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número</label>
                        <div class="relative rounded-md shadow-sm flex">
                            <span
                                class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-200 text-gray-600 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 font-medium">
                                REP
                            </span>
                            <input type="text" id="filter_numero_documento"
                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Buscar...">
                        </div>
                    </div>
                    <div>
                        <label for="filter_estado"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                        <select id="filter_estado"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            <option value="Listo">Listo</option>
                            <option value="En reparto">En reparto</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Con problemas">Con problemas</option>
                        </select>
                    </div>
                    <div>
                        <button id="btn-filter"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150 h-[38px]">
                            Buscar
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display responsive w-full text-left"
                    id="deliveries-table" data-url="{{ route('deliveries.datatable') }}">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="text-left">
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Empresa</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Fecha Carga</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Número</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Repartidor</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Ubicación</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Estado</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Total Guías</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Total Bultos</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        <!-- Carga dinámica vía AJAX/Datatables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE AVISO: Guías a Devolver (Usado en index y edit) --}}
<div id="devolution-warning-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-[70]">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-lg shadow-lg rounded-xl bg-white dark:bg-gray-800 dark:border-gray-700">
        <div class="mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="text-amber-500">⚠</span> Guías a Devolver
            </h3>
            <p class="text-sm text-gray-400 mt-1">Este reparto contiene guías con problemas que no han sido entregadas. Al finalizar, cambiarán a estado <b>"Dto destino"</b> para su gestión en sucursal.</p>
        </div>
        
        <div id="devolution-list" class="space-y-2 mb-6 max-h-60 overflow-y-auto">
            {{-- Lista dinámica --}}
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg btn-close-devolution">
                Cerrar
            </button>
            {{-- El botón de confirmación solo se usa en la vista de edición --}}
            <button type="button" id="btn-confirm-finish-anyway" 
                class="hidden px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg">
                Finalizar Reparto y Devolver Guías
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@vite('resources/js/pages/deliveries/deliveries.js')
@endsection