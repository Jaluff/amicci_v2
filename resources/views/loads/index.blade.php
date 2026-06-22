@extends('layouts.app')

@section('content')
<div class="py-12">
    
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Gestión de Cargas</h2>
                <button id="btn-nueva-carga"
                    data-companies="{{ $userCompanies->map->only(['id', 'prefix', 'name', 'color'])->toJson() }}"
                    data-url="{{ route('loads.create') }}"
                    data-title="Nueva Carga Completa"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                    + Nueva Carga
                </button>
            </div>

            @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Filtros --}}
            <div class="mb-6 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-7 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Empresa</label>
                        <select id="filter_company" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nº Carga</label>
                        <input type="text" id="filter_numero" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm" placeholder="Opcional">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rango de Fechas</label>
                        <input type="text" id="filter_fecha_range" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm bg-white dark:bg-gray-800" placeholder="Seleccionar..." readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                        <select id="filter_estado" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="Todos">Todos</option>
                            <option value="Preparado">Preparado</option>
                            <option value="En viaje">En viaje</option>
                            <option value="Arribado">Arribado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Facturada</label>
                        <select id="filter_facturada" class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm text-sm">
                            <option value="">Todas</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
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
                        <button id="btn-filter"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 transition">
                            Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto shadow rounded-lg">
                <table id="loads-table" data-url="{{ route('loads.datatable') }}"
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center w-12" title="Empresa">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-auto text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            </th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Nº Carga</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Fecha</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Remitente</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Destinatario</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Origen / Destino</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-right">Importe</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Estado</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Fact.</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Cobr.</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"></tbody>
                </table>
            </div>
        </div>
</div>

{{-- Modales se inyectan aquí a través de JS --}}
@include('loads._modals')

@endsection

@section('scripts')
@vite('resources/js/pages/loads/index.js')
@endsection
