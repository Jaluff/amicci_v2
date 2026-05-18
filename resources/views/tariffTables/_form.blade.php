@php
    $isEdit   = isset($tariffTable);
    $brackets = $isEdit
        ? $tariffTable->brackets->map(fn($b) => [
            'weight_from' => (int) $b->weight_from,
            'weight_to'   => (int) $b->weight_to,
            'rate'        => (float) $b->rate,
        ])->values()->toArray()
        : [];
@endphp

{{--
    IMPORTANTE: Alpine.data() debe registrarse ANTES de que Alpine.js
    procese los componentes x-data. Por eso el <script> va aquí,
    NO en @push('scripts') que se renderizaría después del DOMContentLoaded.
--}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bracketManager', (initialBrackets, initialTon = 0, initialM3 = 0) => ({
        brackets: initialBrackets,
        rate_per_ton: initialTon,
        rate_per_m3: initialM3,
        globalPercentage: 0,

        addRow() {
            const last = this.brackets[this.brackets.length - 1];
            const nextFrom = last ? (last.weight_to + 1) : 1;
            this.brackets.push({
                weight_from: nextFrom,
                weight_to:   nextFrom + 9,
                rate:        0
            });
        },

        removeRow(index) {
            if (this.brackets.length <= 1) {
                alert('El cuadro debe tener al menos un tramo.');
                return;
            }
            this.brackets.splice(index, 1);
        },

        applyPercentage(percent, index = null) {
            const factor = 1 + (parseFloat(percent) / 100);
            
            if (index !== null) {
                // Aplicar a un tramo específico
                this.brackets[index].rate = Math.round((this.brackets[index].rate * factor) * 100) / 100;
            } else {
                // Aplicar a todos los tramos
                this.brackets.forEach((b, i) => {
                    this.brackets[i].rate = Math.round((b.rate * factor) * 100) / 100;
                });
            }
        },

        applyToGeneral(percent) {
            const factor = 1 + (parseFloat(percent) / 100);
            this.rate_per_ton = Math.round((this.rate_per_ton * factor) * 100) / 100;
            this.rate_per_m3 = Math.round((this.rate_per_m3 * factor) * 100) / 100;
        },

        promptPercentage(index = null) {
            const val = prompt('Ingrese el porcentaje de ajuste (ej: 10 para aumento, -10 para descuento):');
            if (val !== null && !isNaN(val) && val !== '') {
                this.applyPercentage(val, index);
            }
        }
    }));
});
</script>

<form
    method="POST"
    action="{{ $isEdit ? route('tariff-tables.update', $tariffTable) : route('tariff-tables.store') }}"
    class="space-y-6"
    x-data="bracketManager(
        {{ Js::from($brackets) }}, 
        {{ old('rate_per_ton', isset($tariffTable) ? (float) $tariffTable->rate_per_ton : 0) }},
        {{ old('rate_per_m3', isset($tariffTable) ? (float) $tariffTable->rate_per_m3 : 0) }}
    )"
>
    @csrf
    @if($isEdit) @method('PUT') @endif

    @if($errors->any())
        <div class="p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ─── DATOS GENERALES DEL CUADRO ─────────────────────────────────── --}}
    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4">Datos del Cuadro Tarifario</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Nombre descriptivo --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name"
                    value="{{ old('name', $tariffTable->name ?? '') }}"
                    placeholder="Ej: Buenos Aires → Mendoza Este"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
            </div>

            {{-- Origen --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Origen <span class="text-red-500">*</span>
                </label>
                <select name="origin_id" 
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
                    <option value="">Seleccione origen...</option>
                    @foreach($ubicaciones as $u)
                        <option value="{{ $u->id }}" {{ (old('origin_id', $tariffTable->origin_id ?? '') == $u->id) ? 'selected' : '' }}>
                            {{ $u->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Destino --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Destino <span class="text-red-500">*</span>
                </label>
                <select name="destination_id" 
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
                    <option value="">Seleccione destino...</option>
                    @foreach($ubicaciones as $u)
                        <option value="{{ $u->id }}" {{ (old('destination_id', $tariffTable->destination_id ?? '') == $u->id) ? 'selected' : '' }}>
                            {{ $u->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tarifa por Tonelada --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tarifa por Tonelada (+1000 kg) <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">$</span>
                        <input type="number" name="rate_per_ton" step="0.01" min="0"
                            x-model.number="rate_per_ton"
                            class="pl-7 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                    </div>
                </div>
            </div>

            {{-- Tarifa por M3 --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Aforo por M3 <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">$</span>
                        <input type="number" name="rate_per_m3" step="0.01" min="0"
                            x-model.number="rate_per_m3"
                            class="pl-7 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <button type="button" @click="const p = prompt('Porcentaje de ajuste general (ej: 10, -5):'); if(p) applyToGeneral(p)"
                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    Ajustar tarifas Ton/M3 rápidamente
                </button>
            </div>

            {{-- Válido desde --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Válido desde <span class="text-red-500">*</span>
                </label>
                <input type="date" name="valid_from"
                    value="{{ old('valid_from', isset($tariffTable) ? $tariffTable->valid_from->format('Y-m-d') : '') }}"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
            </div>



            {{-- Válido hasta --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Válido hasta <span class="text-xs text-gray-400">(vacío = sin vencimiento)</span>
                </label>
                <input type="date" name="valid_until"
                    value="{{ old('valid_until', isset($tariffTable) && $tariffTable->valid_until ? $tariffTable->valid_until->format('Y-m-d') : '') }}"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            {{-- Activo --}}
            <div class="flex items-center gap-3 md:col-span-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                    {{ old('is_active', $tariffTable->is_active ?? true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Cuadro activo (disponible para cálculo de guías)
                </label>
            </div>
        </div>
    </div>

    {{-- ─── TRAMOS DE PESO (ESCALA TARIFARIA) ──────────────────────────── --}}
    <div class="mt-8 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">

        <div class="flex flex-col items-center mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 bg-indigo-600 rounded-lg shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M14 3v18"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white uppercase tracking-tight">Escala de Tarifas</h3>
            </div>

            {{-- Herramienta de Ajuste Global --}}
            <div class="bg-gray-100 dark:bg-gray-700/50 p-4 rounded-xl border border-gray-200 dark:border-gray-600 flex flex-col sm:flex-row items-center gap-6 shadow-inner">
                <div class="flex flex-col items-center sm:items-start">
                    <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Ajuste Porcentual</span>
                    <div class="flex items-center gap-2">
                        <input type="number" x-model="globalPercentage" placeholder="0" 
                            class="w-24 text-xl font-bold rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white text-center focus:ring-indigo-500">
                        <span class="text-xl font-bold text-gray-400">%</span>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button type="button" @click="applyPercentage(globalPercentage)" 
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase rounded-lg transition-colors shadow-sm">
                        Aplicar a Tramos
                    </button>
                    <button type="button" @click="applyToGeneral(globalPercentage)" 
                        class="px-5 py-2 bg-slate-600 hover:bg-slate-700 text-white text-xs font-bold uppercase rounded-lg transition-colors shadow-sm">
                        Aplicar a Ton/M3
                    </button>
                </div>
            </div>
        </div>

        <div class="max-w-5xl mx-auto">
            <div class="flex justify-end mb-4">
                <button type="button" @click="addRow()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-bold uppercase rounded-lg transition-colors shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round"/></svg>
                    Nuevo Tramo
                </button>
            </div>

            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                <table class="w-full text-sm border-collapse">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-center w-16 text-[11px] font-bold uppercase">Item</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase">Desde (Kg)</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase">Hasta (Kg)</th>
                            <th class="px-4 py-3 text-right text-[11px] font-bold uppercase">Tarifa ($)</th>
                            <th class="px-4 py-3 text-center w-32 text-[11px] font-bold uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        <template x-for="(bracket, index) in brackets" :key="index">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-4 py-3 text-center text-gray-400 font-bold" x-text="index + 1"></td>

                                <td class="px-4 py-3">
                                    <input type="number" :name="`brackets[${index}][weight_from]`"
                                        x-model.number="bracket.weight_from"
                                        class="w-full text-right rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 text-sm focus:ring-indigo-500">
                                </td>

                                <td class="px-4 py-3">
                                    <input type="number" :name="`brackets[${index}][weight_to]`"
                                        x-model.number="bracket.weight_to"
                                        class="w-full text-right rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 text-sm focus:ring-indigo-500">
                                </td>

                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <div class="relative flex-1 max-w-[150px]">
                                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 font-bold">$</span>
                                            <input type="number" :name="`brackets[${index}][rate]`"
                                                x-model.number="bracket.rate"
                                                class="pl-7 w-full text-right font-bold text-indigo-600 dark:text-indigo-400 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:ring-indigo-500">
                                        </div>
                                        <button type="button" @click="promptPercentage(index)" 
                                            class="p-2 text-gray-400 hover:text-indigo-600 transition-colors" title="Ajustar %">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </button>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeRow(index)"
                                        class="text-red-500 hover:text-red-700 transition-colors" title="Eliminar">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <p class="mt-4 text-center text-[11px] text-gray-500 uppercase tracking-widest font-bold">
                ⚠️ Para 1000 Kg o más se utiliza la tarifa por tonelada de los datos generales.
            </p>
        </div>
    </div>

                    <tr x-show="brackets.length === 0">
                        <td colspan="5" class="text-center py-6 text-gray-400 text-sm">
                            No hay tramos. Hacé clic en "+ Agregar tramo" para comenzar.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            ⚠️ Para envíos de <strong>1000 kg o más</strong> se utiliza la <strong>Tarifa por Tonelada</strong> indicada arriba, no los tramos de esta tabla.
        </p>
    </div>

    {{-- ─── BOTONES ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-4 justify-end">
        <a href="{{ route('tariff-tables.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ $isEdit ? 'Actualizar Cuadro' : 'Guardar Cuadro' }}
        </button>
    </div>
</form>
