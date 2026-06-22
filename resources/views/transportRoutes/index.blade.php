@extends('layouts.app')

@section('content')
<div class="py-12">
    
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Gestión de Rutas</h2>
                <button id="btn-crear-ruta"
                    data-companies="{{ $userCompanies->map->only(['id', 'prefix', 'name', 'color'])->toJson() }}"
                    data-url="{{ route('routes.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    + Crear Ruta
                </button>
            </div>

            <div
                class="mb-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4 items-end">
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
                        <label for="filter_origen_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Origen</label>
                        <select id="filter_origen_id"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_destino_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destino</label>
                        <select id="filter_destino_id"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_fecha_range"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rango de Fechas</label>
                        <input type="text" id="filter_fecha_range"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm bg-white dark:bg-gray-800"
                            placeholder="Seleccionar..." readonly>
                    </div>
                    <div>
                        <label for="filter_numero_documento"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Número</label>
                        <div class="relative rounded-md shadow-sm flex">
                            <span
                                class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-200 text-gray-600 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400 font-medium">
                                RUT
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
                            <option value="Cargada">Cargada</option>
                            <option value="En viaje">En viaje</option>
                            <option value="Entregada">Entregada</option>
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
                    id="routes-table" data-url="{{ route('routes.datatable') }}">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="text-left">
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center w-12" title="Empresa">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-auto text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            </th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Fecha</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Número</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Origen / Destino</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Transporte</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Estado</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Total Guías</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Acciones</th>
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

@endsection

@section('scripts')
@vite('resources/js/pages/transportRoutes/transportRoutes.js')
@endsection