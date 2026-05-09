@php
    $isEdit = isset($load) && $load->exists;
@endphp

{{-- SECCIÓN 1: INFORMACIÓN GENERAL --}}
<div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
    <h3 class="font-bold text-gray-800 dark:text-white mb-2">📋 INFORMACIÓN GENERAL</h3>

    <input type="hidden" name="company_id" value="{{ old('company_id', $load->company_id ?? $company_id ?? null) }}">

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <div>
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Fecha de Carga *</label>
            <input type="date" name="fecha_carga" required
                value="{{ old('fecha_carga', $isEdit ? $load->fecha_carga->format('Y-m-d') : date('Y-m-d')) }}"
                class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            @error('fecha_carga') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        @if($isEdit)
        <div>
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Fecha de Descarga</label>
            <input type="date" name="fecha_descarga"
                value="{{ old('fecha_descarga', $load->fecha_descarga ? $load->fecha_descarga->format('Y-m-d') : '') }}"
                class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <span class="text-[10px] text-gray-500">Se llena auto al llegar a destino</span>
            @error('fecha_descarga') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        @endif

        <div>
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Remito (Opcional)</label>
            <input type="text" name="remito"
                value="{{ old('remito', $load->remito ?? '') }}"
                class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                placeholder="Ej: R-0001">
            @error('remito') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

{{-- SECCIÓN 2: PARTICIPANTES Y RUTA --}}
<div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
    <h3 class="font-bold text-gray-800 dark:text-white mb-2">🚚 PARTICIPANTES Y RUTA</h3>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Origen *</label>
            <select name="origen_id" id="origen_id" required
                class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Seleccione origen</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('origen_id', $load->origen_id ?? null) == $branch->id)>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            @error('origen_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Destino *</label>
            <select name="destino_id" id="destino_id" required
                class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Seleccione destino</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(old('destino_id', $load->destino_id ?? null) == $branch->id)>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            @error('destino_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Remitente *</label>
            <select name="remitente_id" id="remitente_id" required
                class="select2-party w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Seleccione remitente</option>
                @foreach($parties as $party)
                    <option value="{{ $party->id }}" @selected(old('remitente_id', $load->remitente_id ?? null) == $party->id)>
                        {{ $party->name }} ({{ $party->tax_id }})
                    </option>
                @endforeach
            </select>
            @error('remitente_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Destinatario *</label>
            <select name="destinatario_id" id="destinatario_id" required
                class="select2-party w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Seleccione destinatario</option>
                @foreach($parties as $party)
                    <option value="{{ $party->id }}" @selected(old('destinatario_id', $load->destinatario_id ?? null) == $party->id)>
                        {{ $party->name }} ({{ $party->tax_id }})
                    </option>
                @endforeach
            </select>
            @error('destinatario_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

{{-- SECCIÓN 3: TRANSPORTE --}}
<div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
    <h3 class="font-bold text-gray-800 dark:text-white mb-2">🚛 TRANSPORTE</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="relative">
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Chofer</label>
            <button type="button"
                onclick="document.getElementById('driver-modal').classList.remove('hidden')"
                class="absolute right-0 top-0 font-bold text-2xl text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 leading-none"
                title="Nuevo Conductor" style="margin-top: -2px;">+</button>
            <select name="driver_id" id="driver_id"
                class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                <option value="">Sin chofer asignado</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" @selected(old('driver_id', $load->driver_id ?? null) == $driver->id)>
                        {{ $driver->name }} (DNI: {{ $driver->dni }})
                    </option>
                @endforeach
            </select>
            @error('driver_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="font-medium text-gray-700 dark:text-gray-300 block">Observaciones</label>
            <textarea name="observaciones" rows="2"
                class="w-full py-2 px-2 mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('observaciones', $load->observaciones ?? '') }}</textarea>
            @error('observaciones') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
    </div>
</div>

{{-- SECCIÓN 4: IMPORTES & FACTURACIÓN --}}
<div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
    <h3 class="font-bold text-gray-800 dark:text-white mb-2">💰 IMPORTES & FACTURACIÓN</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 border border-indigo-200 dark:border-indigo-700 rounded-lg p-3">
            <label class="font-bold text-indigo-700 dark:text-indigo-300 block">Importe ($) *</label>
            <input type="number" name="importe_factura" id="importe_factura" step="0.01" min="0"
                value="{{ old('importe_factura', $load->importe_factura ?? 0) }}"
                class="w-full py-2 px-2 mt-1 rounded border-indigo-300 dark:border-indigo-600 dark:bg-gray-900 dark:text-white font-bold text-indigo-800 dark:text-indigo-200">
            <p class="text-[10px] text-indigo-500 mt-1">Valor a usar en la factura</p>
            @error('importe_factura') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        @if($isEdit)
        <div class="text-center">
            <p class="text-xs font-semibold text-indigo-500 dark:text-indigo-400 uppercase tracking-wider mb-1">N° Factura</p>
            <p class="text-lg font-bold text-indigo-700 dark:text-indigo-300">
                {{ $load->numero_factura ?? '—' }}
            </p>
            @if($load->fecha_factura)
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $load->fecha_factura->format('d/m/Y') }}</p>
            @endif
        </div>

        <div class="text-center">
            <p class="text-xs font-semibold text-emerald-500 dark:text-emerald-400 uppercase tracking-wider mb-1">N° Recibo</p>
            <p class="text-lg font-bold text-emerald-700 dark:text-emerald-300">
                {{ $load->numero_recibo ?? '—' }}
            </p>
            @if($load->fecha_recibo)
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $load->fecha_recibo->format('d/m/Y') }}</p>
            @endif
        </div>

        <div class="text-center">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Estado</p>
            @php
                $statusColors = [
                    'Preparado' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                    'En viaje'  => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                    'Arribado'  => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                ];
                $sc = $statusColors[$load->estado] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $sc }}">{{ $load->estado }}</span>
            <p class="text-[10px] text-gray-400 mt-1">Gestionado desde la tabla</p>
        </div>
        @endif
    </div>
</div>

{{-- BOTONES --}}
<div class="flex justify-end gap-3 pt-4 mt-2 border-t border-gray-200 dark:border-gray-700">
    <a href="{{ route('loads.index') }}"
        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        Cancelar
    </a>
    <button type="submit"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition ease-in-out duration-150">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
        </svg>
        {{ $isEdit ? 'Actualizar Carga' : 'Guardar Carga' }}
    </button>
</div>

<!-- Driver Modal -->
<div id="driver-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Nuevo Conductor</h3>
                <button type="button" onclick="document.getElementById('driver-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4">
                <div id="ajax-driver-form" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre / Razón Social *</label>
                            <input type="text" name="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">DNI / Documento</label>
                            <input type="text" name="dni" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Licencia</label>
                            <input type="text" name="license_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                            <input type="text" name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-white">
                        </div>
                    </div>
                    <div id="driver-error-messages" class="hidden text-red-500 text-sm bg-red-50 dark:bg-red-900/30 p-3 rounded"></div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <button type="button" onclick="document.getElementById('driver-modal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 dark:text-gray-300 text-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition">Cancelar</button>
                        <button type="button" id="btn-save-driver"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">Guardar</button>
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
            inputs.forEach(input => { if(input.name) data[input.name] = input.value; });
            fetch('{{ route("drivers.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
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
                        messages.forEach(msg => { errorsHtml += `<li>${msg}</li>`; });
                    }
                    errorsHtml += '</ul>';
                    errorDiv.innerHTML = errorsHtml;
                    errorDiv.classList.remove('hidden');
                } else {
                    errorDiv.innerText = res.body.message || 'Error inesperado.';
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerText = 'Guardar';
                errorDiv.innerText = 'Error de conexión.';
                errorDiv.classList.remove('hidden');
            });
        });
    }
});
</script>
