@php
    $isEdit = isset($delivery) && $delivery->exists;
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">


    @if($isEdit)
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Creación</label>
        <div class="mt-2 flex items-center gap-2">
            <span class="text-gray-900 dark:text-gray-100 font-medium">
                {{ $delivery->created_at->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>
    @endif
    <input type="hidden" name="status" value="{{ $delivery->status ?? 'Listo' }}">
</div>

<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación del Reparto</label>
        <select name="location_id" id="location_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white select2 {{ $isEdit && $delivery->shipments->count() > 0 ? 'pointer-events-none bg-gray-100 dark:bg-gray-800 opacity-75' : '' }}"
            required {{ $isEdit && $delivery->shipments->count() > 0 ? 'tabindex=-1' : '' }}>
            <option value="">Seleccione ubicación</option>
            @foreach($branches as $branch)
            <option value="{{ $branch->id }}" @selected(old('location_id', $delivery->location_id ?? null) == $branch->id)>
                {{ $branch->name }}
            </option>
            @endforeach
        </select>
        @error('location_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div class="relative">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Repartidor</label>
        <button type="button" onclick="document.getElementById('deliverer-modal').classList.remove('hidden')" class="absolute right-0 top-0 font-bold text-2xl text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 leading-none" title="Nuevo Repartidor" style="margin-top: -2px; z-index: 10;">+</button>
        <select name="deliverer_id" id="deliverer_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white select2"
            required>
            <option value="">Seleccione repartidor</option>
            @foreach($deliverers as $deliverer)
            <option value="{{ $deliverer->id }}" {{ old('deliverer_id', $delivery->deliverer_id ?? null) == $deliverer->id ? 'selected' : '' }}>
                {{ $deliverer->name }} {{ $deliverer->dni ? '(DNI: ' . $deliverer->dni . ')' : '' }}
            </option>
            @endforeach
        </select>
        @error('deliverer_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Patente (Opcional)</label>
        <select name="vehicle_plate" id="vehicle_plate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white select2-tags">
            <option value=""></option>
            @foreach($existingPlates as $plate)
                <option value="{{ $plate }}" @selected(old('vehicle_plate', $delivery->vehicle_plate ?? '') == $plate)>
                    {{ $plate }}
                </option>
            @endforeach
            @if($delivery->vehicle_plate && !$existingPlates->contains($delivery->vehicle_plate))
                <option value="{{ $delivery->vehicle_plate }}" selected>{{ $delivery->vehicle_plate }}</option>
            @endif
        </select>
        @error('vehicle_plate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Carga</label>
        <input type="date" name="load_date"
            value="{{ old('load_date', isset($delivery) && $delivery->load_date ? $delivery->load_date->format('Y-m-d') : date('Y-m-d')) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
        @error('load_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

<div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
            Guías (Shipments) Asignadas
            <span class="assigned-count text-indigo-600 dark:text-indigo-400 bg-indigo-100 dark:bg-indigo-900/40 px-2 py-0.5 rounded-full text-sm">{{ isset($delivery) ? count($delivery->shipments ?? []) : 0 }}</span>
        </h3>
        @if(!isset($delivery) || !$delivery->exists || $delivery->status === 'Listo')
        <button type="button"
            class="btn-open-shipments-modal bg-indigo-100 text-indigo-700 hover:bg-indigo-200 dark:bg-indigo-900/40 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 font-medium py-1.5 px-3 rounded text-sm transition-colors cursor-pointer">
            + Seleccionar Guías
        </button>
        @endif
    </div>

    <div class="overflow-x-auto shadow rounded-lg mb-6 max-w-full">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 w-full" id="selected-shipments-table">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr class="text-left">
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Guía N°</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Remitente</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Destinatario</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Estado</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Bultos</th>
                    <th class="p-3 text-sm font-semibold text-gray-700 dark:text-gray-300 w-16 text-center">Acciones
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                @if(isset($delivery->shipments) && count($delivery->shipments) > 0)
                @foreach($delivery->shipments as $shipment)
                <tr class="shipment-row" data-id="{{ $shipment->id }}">
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                        {{ $shipment->numero }}
                        @if($shipment->hasActiveProblem())
                            <span class="text-amber-500 font-bold ml-1 animate-pulse cursor-pointer btn-open-spm" 
                                data-shipment-id="{{ $shipment->id }}"
                                data-shipment-numero="{{ $shipment->numero }}"
                                style="color: #f59e0b !important;"
                                title="Tiene un problema reporte activo. Click para ver/resolver.">⚠</span>
                        @endif
                        <input type="hidden" name="shipments[]" value="{{ $shipment->id }}">
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $shipment->sender->name ?? '-' }}</td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $shipment->recipient->name ?? '-' }}</td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">
                        @php
                        $badges = [
                        'Dto origen' => 'dt-badge-indigo',
                        'En transito' => 'dt-badge-yellow',
                        'Dto destino' => 'dt-badge-blue',
                        'En reparto' => 'dt-badge-orange',
                        'Entregado' => 'dt-badge-green',
                        'Con problemas' => 'dt-badge-red',
                        ];
                        $badge = $badges[$shipment->ubicacion_actual] ?? 'dt-badge-gray';
                        @endphp
                        <span class="dt-badge {{ $badge }}">{{ $shipment->ubicacion_actual }}</span>
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $shipment->bultos ?? 0 }}</td>
                    <td class="p-3 text-center">
                        @if(!isset($delivery) || !$delivery->exists || $delivery->status === 'Listo')
                        <button type="button" class="text-red-500 hover:text-red-700 btn-remove-shipment font-bold mr-2"
                            title="Remover">&times;</button>
                        @endif
                        <button type="button"
                            class="text-yellow-500 hover:text-yellow-700 btn-problem-shipment font-bold"
                            title="Reportar Problema" data-id="{{ $shipment->id }}">!</button>
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
        <a href="{{ route('deliveries.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            @if(isset($delivery) && $delivery->exists)
            Actualizar
            @else
            Guardar
            @endif
        </button>
    </div>
</div>

<!-- Deliverer Modal -->
<div id="deliverer-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nuevo Repartidor</h3>
                <button type="button" onclick="document.getElementById('deliverer-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4">
                <div id="ajax-deliverer-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre / Razón Social *</label>
                            <input type="text" id="modal_deliverer_name" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
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
                    <div id="deliverer-error-messages" class="hidden text-red-500 text-sm bg-red-50 dark:bg-red-900/30 p-3 rounded"></div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="document.getElementById('deliverer-modal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 text-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancelar</button>
                        <button type="button" id="btn-save-deliverer" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('btn-save-deliverer');
    if(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const container = document.getElementById('ajax-deliverer-form');
            const errorDiv = document.getElementById('deliverer-error-messages');
            
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

            fetch('{{ route("deliverers.store") }}', {
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
                    const select = document.getElementById('deliverer_id');
                    const option = document.createElement('option');
                    option.value = res.body.deliverer.id;
                    option.text = res.body.deliverer.name + (res.body.deliverer.dni ? ` (DNI: ${res.body.deliverer.dni})` : '');
                    option.selected = true;
                    select.appendChild(option);
                    
                    document.getElementById('deliverer-modal').classList.add('hidden');
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
                    errorDiv.innerText = res.body.message || 'Ocurrió un error inesperado al guardar el repartidor.';
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

    // Inicializar Select2 para campos estándar y con tags
    if (typeof $ !== 'undefined') {
        $('.select2').select2({
            width: '100%'
        });

        $('.select2-tags').select2({
            tags: true,
            placeholder: 'Seleccione o escriba una patente',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>