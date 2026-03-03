<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    @if(!isset($dispatch) || !$dispatch->exists)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
        {{-- El estado NO se edita desde el form, solo se muestra. Los cambios van por botones de acción --}}
        @php
        $statusColors = [
        'Cargado' => 'dt-badge-blue',
        'En viaje' => 'dt-badge-yellow',
        'Arribado' => 'dt-badge-green',
        ];
        $statusColor = $statusColors[$dispatch->status ?? 'Cargado'] ?? 'dt-badge-gray';
        @endphp
        <div class="mt-2 flex items-center gap-2">
            <span class="dt-badge {{ $statusColor }}">
                {{ $dispatch->status ?? 'Cargado' }}
            </span>
            <span class="text-xs text-gray-400 dark:text-gray-500">El estado se gestiona con los botones de
                acción</span>
        </div>
    </div>
    @endif
    {{-- Hidden: mantiene el status actual para el submit del form (no lo cambia) --}}
    <input type="hidden" name="status" value="{{ $dispatch->status ?? 'Cargado' }}">

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Conductor</label>
        <select name="driver_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            required>
            <option value="">Seleccione un conductor</option>
            @foreach($drivers as $driver)
            <option value="{{ $driver->id }}" {{ old('driver_id', $dispatch->driver_id) == $driver->id ? 'selected' : ''
                }}>
                {{ $driver->name }} (DNI: {{ $driver->dni }})
            </option>
            @endforeach
        </select>
        @error('driver_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° de Precinto</label>
        <input type="text" name="seal_number" value="{{ old('seal_number', $dispatch->seal_number ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            placeholder="Opcional">
        @error('seal_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° de Semi</label>
        <input type="text" name="semi_number" value="{{ old('semi_number', $dispatch->semi_number ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            placeholder="Opcional">
        @error('semi_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">N° de Chasis</label>
        <input type="text" name="chassis_number" value="{{ old('chassis_number', $dispatch->chassis_number ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            placeholder="Opcional">
        @error('chassis_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Origen</label>
        <select name="origin_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            required>
            <option value="">Seleccione origen</option>
            @foreach($ubicaciones as $ubicacion)
            <option value="{{ $ubicacion->id }}" {{ old('origin_id', $dispatch->origin_id) == $ubicacion->id ?
                'selected' : '' }}>
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
            <option value="{{ $ubicacion->id }}" {{ old('destination_id', $dispatch->destination_id) == $ubicacion->id ?
                'selected' : '' }}>
                {{ $ubicacion->nombre }}
            </option>
            @endforeach
        </select>
        @error('destination_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Costo</label>
        <input type="number" name="cost" value="{{ old('cost', $dispatch->cost ?? 0) }}" min="0" step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            placeholder="0.00">
        @error('cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

<div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Rutas Asignadas</h3>
        <button type="button"
            class="btn-open-routes-modal bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 font-medium py-1.5 px-3 rounded text-sm transition-colors cursor-pointer">
            + Seleccionar Rutas
        </button>
    </div>

    @error('routes') <div class="text-red-500 text-sm mt-1 mb-4">{{ $message }}</div> @enderror

    <div class="overflow-x-auto shadow rounded-lg mb-6 max-w-full">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 w-full" id="selected-routes-table">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr class="text-left">
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Ruta N°</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Origen</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Destino</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Estado</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Guías</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300 w-16 text-center">Quitar</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @if(isset($dispatch->routes) && count($dispatch->routes) > 0)
                @foreach($dispatch->routes as $route)
                <tr class="route-row" data-id="{{ $route->id }}">
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                        {{ $route->route_number }}
                        <input type="hidden" name="routes[]" value="{{ $route->id }}">
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $route->origin->nombre ?? '-' }}</td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $route->destination->nombre ?? '-' }}
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                        @php
                        $badges = [
                        'Cargada' => 'dt-badge-blue',
                        'En viaje' => 'dt-badge-yellow',
                        'Entregada' => 'dt-badge-green',
                        'Con problemas' => 'dt-badge-red'
                        ];
                        $badge = $badges[$route->status] ?? 'dt-badge-gray';
                        @endphp
                        <span class="dt-badge {{ $badge }}">{{ $route->status }}</span>
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $route->shipments_count ?? 0 }}</td>
                    <td class="p-3 text-center">
                        <button type="button" class="text-red-500 hover:text-red-700 btn-remove-route font-bold"
                            title="Remover">&times;</button>
                    </td>
                </tr>
                @endforeach
                @else
                <tr class="empty-row">
                    <td colspan="6" class="p-4 text-center text-gray-500 text-sm">Aún no se han asignado rutas</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="flex justify-end gap-3 pt-4">
        <a href="{{ route('dispatches.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            @if($dispatch->exists)
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