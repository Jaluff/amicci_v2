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
    Alpine.data('bracketManager', (initialBrackets) => ({
        brackets: initialBrackets,

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
        }
    }));
});
</script>

<form
    method="POST"
    action="{{ $isEdit ? route('tariff-tables.update', $tariffTable) : route('tariff-tables.store') }}"
    class="space-y-6"
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
                <input type="text" name="origin"
                    value="{{ old('origin', $tariffTable->origin ?? '') }}"
                    placeholder="Ej: Buenos Aires"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
            </div>

            {{-- Destino --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Destino <span class="text-red-500">*</span>
                </label>
                <input type="text" name="destination"
                    value="{{ old('destination', $tariffTable->destination ?? '') }}"
                    placeholder="Ej: Mendoza Este"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
            </div>

            {{-- Tarifa por Tonelada --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tarifa por Tonelada (+1000 kg) <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">$</span>
                    <input type="number" name="rate_per_ton" step="0.01" min="0"
                        value="{{ old('rate_per_ton', isset($tariffTable) ? (float) $tariffTable->rate_per_ton : '') }}"
                        class="pl-7 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>
            </div>

            {{-- Tarifa por M3 --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Aforo por M3 <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">$</span>
                    <input type="number" name="rate_per_m3" step="0.01" min="0"
                        value="{{ old('rate_per_m3', isset($tariffTable) ? (float) $tariffTable->rate_per_m3 : '') }}"
                        class="pl-7 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required>
                </div>
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

            {{-- Porcentaje Contra-Reembolso --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Contra-Reembolso (%)
                </label>
                <div class="mt-1 relative">
                    <input type="number" name="contra_reembolso_percent" step="0.01" min="0" max="100"
                        value="{{ old('contra_reembolso_percent', isset($tariffTable) ? (float) $tariffTable->contra_reembolso_percent : '0.00') }}"
                        class="pr-7 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 text-sm">%</span>
                </div>
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
    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700"
         x-data="bracketManager({{ Js::from($brackets) }})">

        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M3 14h18M10 3v18M14 3v18"/>
                </svg>
                Escala de Tarifas por Peso (Kg)
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400 ml-1">
                    (<span x-text="brackets.length"></span> tramos)
                </span>
            </h3>
            <button type="button" @click="addRow()"
                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition">
                + Agregar tramo
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2 text-center w-10">#</th>
                        <th class="px-4 py-2 text-right">Desde (Kg)</th>
                        <th class="px-4 py-2 text-right">Hasta (Kg)</th>
                        <th class="px-4 py-2 text-right">Tarifa ($)</th>
                        <th class="px-4 py-2 text-center w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(bracket, index) in brackets" :key="index">
                        <tr class="border-t border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-4 py-2 text-center text-gray-400 text-xs" x-text="index + 1"></td>

                            <td class="px-2 py-1">
                                <input type="number" :name="`brackets[${index}][weight_from]`"
                                    x-model.number="bracket.weight_from"
                                    min="1" step="1"
                                    class="w-24 text-right rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                            </td>

                            <td class="px-2 py-1">
                                <input type="number" :name="`brackets[${index}][weight_to]`"
                                    x-model.number="bracket.weight_to"
                                    min="1" step="1"
                                    class="w-24 text-right rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required>
                            </td>

                            <td class="px-2 py-1">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-2 flex items-center text-gray-400 text-xs">$</span>
                                    <input type="number" :name="`brackets[${index}][rate]`"
                                        x-model.number="bracket.rate"
                                        min="0" step="0.01"
                                        class="pl-5 w-36 text-right rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                </div>
                            </td>

                            <td class="px-2 py-1 text-center">
                                <button type="button" @click="removeRow(index)"
                                    class="text-red-400 hover:text-red-600 transition"
                                    title="Eliminar tramo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </template>

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
