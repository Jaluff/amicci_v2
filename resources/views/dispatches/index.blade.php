@extends('layouts.app')

@section('content')
<div class="py-12">
    
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Gestión de Despachos</h2>
                <a href="{{ route('dispatches.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Crear Despacho
                </a>
            </div>

            @if(session('success'))
            <div
                class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
            @endif

            <div
                class="mb-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4 items-end">
                    <div>
                        <label for="filter_origen_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Origen</label>
                        <select id="filter_origen_id"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            @foreach($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_destino_id"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Destino</label>
                        <select id="filter_destino_id"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            @foreach($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}">{{ $ubicacion->nombre }}</option>
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
                                DES
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
                            <option value="Cargado">Cargado</option>
                            <option value="En viaje">En viaje</option>
                            <option value="Arribado">Arribado</option>
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
                    id="dispatches-table" data-url="{{ route('dispatches.datatable') }}">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="text-left">
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Número</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Conductor</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Origen - Destino</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Estado</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Detalles Transporte</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Costo</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Total Rutas</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">⚠ Problemas</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        {{-- Carga dinámica vía DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/dispatches/dispatches.js')
@endsection