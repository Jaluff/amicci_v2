<div class="mb-4">
    @if(isset($delivery) && $delivery->exists)
    <div class="mb-4">
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

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación del Reparto</label>
        <select name="location_id" id="location_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            required>
            <option value="">Seleccione ubicación</option>
            @foreach($ubicaciones as $ubicacion)
            <option value="{{ $ubicacion->id }}" {{ old('location_id', $delivery->location_id ?? null) == $ubicacion->id
                ? 'selected' : '' }}>
                {{ $ubicacion->nombre }}
            </option>
            @endforeach
        </select>
        @error('location_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Repartidor</label>
        <select name="deliverer_id"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white"
            required>
            <option value="">Seleccione repartidor</option>
            @foreach($deliverers as $deliverer)
            <option value="{{ $deliverer->id }}" {{ old('deliverer_id', $delivery->deliverer_id ?? null) ==
                $deliverer->id ? 'selected' : '' }}>
                {{ $deliverer->name }}
            </option>
            @endforeach
        </select>
        @error('deliverer_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Carga</label>
        <input type="date" name="load_date"
            value="{{ old('load_date', isset($delivery) && $delivery->load_date ? $delivery->load_date->format('Y-m-d') : '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
        @error('load_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
    </div>
</div>

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
                        'Con problemas' => 'dt-badge-red',
                        ];
                        $badge = $badges[$shipment->ubicacion_actual] ?? 'dt-badge-gray';
                        @endphp
                        <span class="dt-badge {{ $badge }}">{{ $shipment->ubicacion_actual }}</span>
                        @if($shipment->hasActiveProblem())
                        <span class="text-red-500 font-bold ml-2 text-xs" title="Tiene un problema reportado activo"
                            data-active-problem="true">(⚠ PROBLEMA)</span>
                        @endif
                    </td>
                    <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ $shipment->bultos ?? 0 }}</td>
                    <td class="p-3 text-center">
                        <button type="button" class="text-red-500 hover:text-red-700 btn-remove-shipment font-bold mr-2"
                            title="Remover">&times;</button>
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