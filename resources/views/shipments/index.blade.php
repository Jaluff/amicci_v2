@extends('layouts.app')

@section('content')
<div class="py-12">
    
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Listado de Guías</h2>
                <a href="{{ route('shipments.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Nueva Guía
                </a>
            </div>

            <div
                class="mb-4 bg-gray-50 dark:bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-8 gap-4 items-end">
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
                                GUI
                            </span>
                            <input type="text" id="filter_numero_documento"
                                class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Buscar...">
                        </div>
                    </div>
                    <div>
                        <label for="filter_cliente"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
                        <input type="text" id="filter_cliente"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="Nombre...">
                    </div>
                    <div>
                        <label for="filter_ubicacion"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                        <select id="filter_ubicacion"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todas</option>
                            <option value="Dto origen">Dto origen</option>
                            <option value="En transito">En tránsita</option>
                            <option value="Dto destino">Dto destino</option>
                            <option value="En reparto">En reparto</option>
                            <option value="Entregado">Entregado</option>
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
                <table id="shipmentsTable" data-url="{{ route('shipments.datatable') }}"
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display responsive"
                    style="width:100%">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="text-left">
                            <th>Fecha</th>
                            <th>Número</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Remitente / Destinatario</th>
                            <th>Flete</th>
                            <th>Bultos</th>
                            <th>Valor Decl.</th>
                            <th>Total</th>
                            <th>Ubicación</th>

                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/shipments/index.js')
@endsection