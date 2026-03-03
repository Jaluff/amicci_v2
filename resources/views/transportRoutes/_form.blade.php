<div class="mb-4">
    @if(isset($route) && $route->exists)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Creación</label>
        <div class="mt-2 flex items-center gap-2">
            <span class="text-gray-900 dark:text-gray-100 font-medium">
                {{ $route->created_at->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
    @endif
    <input type="hidden" name="status" value="{{ $route->status ?? 'Cargada' }}">
</div>


<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Origen</label>
        <select name="origin_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            required>
            <option value="">Seleccione origen</option>
            @foreach($ubicaciones as $ubicacion)
            <option value="{{ $ubicacion->id }}" {{ old('origin_id', $route->origin_id) == $ubicacion->id ? 'selected' :
                '' }}>
                {{ $ubicacion->nombre }}
            </option>
            @endforeach
        </select>
        @error('origin_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Destino</label>
        <select name="destination_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            required>
            <option value="">Seleccione destino</option>
            @foreach($ubicaciones as $ubicacion)
            <option value="{{ $ubicacion->id }}" {{ old('destination_id', $route->destination_id) == $ubicacion->id ?
                'selected' : '' }}>
                {{ $ubicacion->nombre }}
            </option>
            @endforeach
        </select>
        @error('destination_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

@if(isset($route) && $route->dispatch && ($route->dispatch->semi_number || $route->dispatch->chassis_number ||
$route->dispatch->seal_number))
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    @if($route->dispatch->semi_number)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Semi (del Despacho)</label>
        <input type="text" value="{{ $route->dispatch->semi_number }}" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
    </div>
    @endif
    @if($route->dispatch->chassis_number)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Chasis (del Despacho)</label>
        <input type="text" value="{{ $route->dispatch->chassis_number }}" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
    </div>
    @endif
    @if($route->dispatch->seal_number)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° Precinto (del Despacho)</label>
        <input type="text" value="{{ $route->dispatch->seal_number }}" readonly
            class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 text-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400">
    </div>
    @endif
</div>
@endif

<div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Guías (Shipments) Asignadas</h3>
        <button type="button"
            class="btn-open-shipments-modal bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 font-medium py-1.5 px-3 rounded text-sm transition-colors cursor-pointer">
            + Seleccionar Guías
        </button>
    </div>

    <div class="overflow-x-auto shadow rounded-lg mb-6 max-w-full">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 w-full" id="selected-shipments-table">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr class="text-left">
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Guía N°</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Origen</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Destino</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Estado</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Bultos</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300 w-16 text-center">Quitar</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @if(isset($route->shipments) && count($route->shipments) > 0)
                @foreach($route->shipments as $shipment)
                <tr class="shipment-row" data-id="{{ $shipment->id }}">
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                        {{ $shipment->numero }}
                        <input type="hidden" name="shipments[]" value="{{ $shipment->id }}">
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $shipment->origin->nombre ?? '-' }}</td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $shipment->destination->nombre ?? '-' }}
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                        @php
                        $badges = [
                        'Dto origen' => 'dt-badge-indigo',
                        'En transito' => 'dt-badge-yellow',
                        'Dto destino' => 'dt-badge-blue',
                        'En reparto' => 'dt-badge-orange',
                        'Entregado' => 'dt-badge-green',
                        ];
                        $badge = $badges[$shipment->ubicacion_actual] ?? 'dt-badge-gray';
                        @endphp
                        <span class="dt-badge {{ $badge }}">{{ $shipment->ubicacion_actual }}</span>
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $shipment->bultos ?? 0 }}</td>
                    <td class="p-3 text-center">
                        <button type="button" class="text-red-500 hover:text-red-700 btn-remove-shipment font-bold"
                            title="Remover">&times;</button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr class="empty-row">
                    <td colspan="6" class="p-4 text-center text-gray-500 text-sm">Aún no se han asignado guías</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    @error('shipments') <div class="text-red-500 text-sm mt-1 mb-4">{{ $message }}</div> @enderror

    <div class="flex justify-end gap-3 pt-4">
        <a href="{{ route('routes.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            @if($route->exists)
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
            </svg>
            Actualizar
            @else
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                </path>
            </svg>
            Guardar
            @endif
        </button>
    </div>
</div>