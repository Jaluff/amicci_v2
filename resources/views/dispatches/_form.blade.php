@php
    $isEdit = isset($dispatch) && $dispatch->exists;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">


    <div class="relative">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Conductor</label>
        <button type="button" onclick="document.getElementById('driver-modal').classList.remove('hidden')" class="absolute right-0 top-0 font-bold text-2xl text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 leading-none" title="Nuevo Conductor" style="margin-top: -2px;">+</button>
        <select name="driver_id" id="driver_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            required>
            <option value="">Seleccione un conductor</option>
            @foreach($drivers as $driver)
            <option value="{{ $driver->id }}" {{ old('driver_id', $dispatch->driver_id) == $driver->id ? 'selected' : '' }}>
                {{ $driver->name }} (DNI: {{ $driver->dni }})
            </option>
            @endforeach
        </select>
        @error('driver_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    @if($isEdit)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Estado</label>
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
    <input type="hidden" name="status" value="{{ $dispatch->status ?? 'Cargado' }}">
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
        <select name="origin_id" id="origin_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white {{ $isEdit && $dispatch->routes->count() > 0 ? 'pointer-events-none bg-gray-100 dark:bg-gray-800 opacity-75' : '' }}"
            required {{ $isEdit && $dispatch->routes->count() > 0 ? 'tabindex=-1' : '' }}>
            <option value="">Seleccione origen</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected(old('origin_id', $dispatch->origin_id ?? ($defaultOriginId ?? null)) == $branch->id)>
                {{ $branch->name }}
            </option>
            @endforeach
        </select>
        @error('origin_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Destino</label>
        <select name="destination_id" id="destination_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white {{ $isEdit && $dispatch->routes->count() > 0 ? 'pointer-events-none bg-gray-100 dark:bg-gray-800 opacity-75' : '' }}"
            required {{ $isEdit && $dispatch->routes->count() > 0 ? 'tabindex=-1' : '' }}>
            <option value="">Seleccione destino</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected(old('destination_id', $dispatch->destination_id) == $branch->id)>
                {{ $branch->name }}
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
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
            Rutas Asignadas
            <span class="assigned-count text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/40 px-2 py-0.5 rounded-full text-sm">{{ isset($dispatch) ? count($dispatch->routes ?? []) : 0 }}</span>
        </h3>
        @if(!isset($dispatch) || !$dispatch->exists || $dispatch->status === 'Cargado')
        <button type="button"
            class="btn-open-routes-modal bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 font-medium py-1.5 px-3 rounded text-sm transition-colors cursor-pointer">
            + Seleccionar Rutas
        </button>
        @endif
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
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300 w-32 text-center">Acciones</th>
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
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $route->origin->name ?? $route->origin->nombre ?? '-' }}</td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $route->destination->name ?? $route->destination->nombre ?? '-' }}
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
                        <div class="flex items-center justify-center gap-2">
                            @if($route->shipments_count > 0)
                            <button type="button" class="btn-print-route-guides text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                data-route-id="{{ $route->id }}" data-route-number="{{ $route->route_number }}" title="Imprimir Guías">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            </button>
                            @endif

                            @if(!isset($dispatch) || !$dispatch->exists || $dispatch->status === 'Cargado')
                            <button type="button" class="text-red-500 hover:text-red-700 btn-remove-route font-bold text-xl leading-none"
                                title="Remover">&times;</button>
                            @endif
                        </div>
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
                    d="M8 7H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                </path>
            </svg>
            Guardar
            @endif
        </button>
    </div>
</div>

<!-- Driver Modal -->
<div id="driver-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nuevo Conductor</h3>
                <button type="button" onclick="document.getElementById('driver-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4">
                <div id="ajax-driver-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre / Razón Social *</label>
                            <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">DNI / Documento</label>
                            <input type="text" name="dni" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Licencia</label>
                            <input type="text" name="license_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                            <input type="text" name="phone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Correo Electrónico</label>
                            <input type="email" name="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                            <input type="text" name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                    <div id="driver-error-messages" class="hidden text-red-500 text-sm bg-red-50 dark:bg-red-900/30 p-3 rounded"></div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="document.getElementById('driver-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 text-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancelar</button>
                        <button type="button" id="btn-save-driver" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btn-save-driver');
    if(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const container = document.getElementById('ajax-driver-form');
            const errorDiv = document.getElementById('driver-error-messages');
            
            btn.disabled = true;
            btn.innerText = 'Guardando...';
            errorDiv.classList.add('hidden');
            errorDiv.innerHTML = '';

            const inputs = container.querySelectorAll('input, select, textarea');
            const data = {};
            inputs.forEach(input => {
                if(input.name) {
                    data[input.name] = input.value;
                }
            });

            fetch('{{ route("drivers.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json().then(data => ({status: response.status, body: data})))
            .then(res => {
                btn.disabled = false;
                btn.innerText = 'Guardar';
                
                if (res.status === 200 || res.status === 201) {
                    const select = document.getElementById('driver_id');
                    const option = document.createElement('option');
                    option.value = res.body.driver.id;
                    option.text = `${res.body.driver.name} (DNI: ${res.body.driver.dni})`;
                    option.selected = true;
                    select.appendChild(option);
                    
                    document.getElementById('driver-modal').classList.add('hidden');
                    inputs.forEach(input => input.value = '');
                } else if (res.status === 422) {
                    let errorsHtml = '<ul class="list-disc pl-5">';
                    for (const [key, messages] of Object.entries(res.body.errors)) {
                        messages.forEach(msg => {
                            errorsHtml += `<li>${msg}</li>`;
                        });
                    }
                    errorsHtml += '</ul>';
                    errorDiv.innerHTML = errorsHtml;
                    errorDiv.classList.remove('hidden');
                } else {
                    errorDiv.innerText = res.body.message || 'Ocurrió un error inesperado al guardar el conductor.';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(error => {
                btn.disabled = false;
                btn.innerText = 'Guardar';
                errorDiv.innerText = 'Error de conexión. Intente nuevamente.';
                errorDiv.classList.remove('hidden');
            });
        });
    }
});
</script>