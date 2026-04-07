@php
$isEdit = isset($party);
$addr = $isEdit ? $party->primaryAddress : null;
@endphp

<form method="POST" action="{{ $isEdit ? route('parties.update', $party) : route('parties.store') }}" class="space-y-6">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <!-- Datos Fiscales -->
    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
        <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Datos Principales
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre o Razón Social
                    *</label>
                <input type="text" name="name" value="{{ old('name', $party->name ?? '') }}"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Documento</label>
                <select name="document_type"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Seleccionar...</option>
                    @foreach(['CUIT', 'CUIL', 'DNI', 'Pasaporte', 'CDI'] as $dt)
                        <option value="{{ $dt }}" @selected(old('document_type', $party->document_type ?? '') == $dt)>{{ $dt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Documento</label>
                <input type="text" name="document" value="{{ old('document', $party->document ?? '') }}"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Condición Frente al
                    IVA</label>
                <select name="tax_status"
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Seleccionar...</option>
                    @foreach(['Responsable Inscripto', 'Monotributo', 'Exento', 'Consumidor Final'] as $status)
                    <option value="{{ $status }}" @selected(old('tax_status', $party->tax_status ?? '') == $status)>{{
                        $status }}</option>
                    @endforeach
                </select>
            </div>

            <div x-data="{ hasInsurance: {{ old('has_insurance', $party->has_insurance ?? 0) ? 'true' : 'false' }} }" class="md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">IVA (%)</label>
                    <div class="relative mt-1">
                        <input type="number" step="0.01" min="0" max="100" name="iva_percent" value="{{ old('iva_percent', $party->iva_percent ?? '0') }}"
                            class="pr-8 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 text-xs">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">¿Tiene Seguro?</label>
                    <div class="mt-2 flex items-center gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" x-model="hasInsurance" value="true" name="has_insurance" class="text-indigo-600 focus:ring-indigo-500 text-sm">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Sí</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" x-model="hasInsurance" value="false" name="has_insurance" class="text-indigo-600 focus:ring-indigo-500 text-sm">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">No</span>
                        </label>
                    </div>
                </div>

                <div x-show="hasInsurance" x-transition>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Porcentaje de Seguro (%)</label>
                    <div class="relative mt-1">
                        <input type="number" step="0.01" min="0" max="100" name="insurance_percent" value="{{ old('insurance_percent', $party->insurance_percent ?? '') }}"
                            class="pr-8 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 text-xs">%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Direcciones y Contacto (Polimórficas) -->
    @php
    $addressesData = $isEdit && $party->addresses->isNotEmpty()
    ? $party->addresses->toArray()
    : [['id' => '', 'type' => 'Principal', 'address_line1' => '', 'city' => '', 'state' => '', 'zip_code' => '', 'phone'
    => '', 'email' => '', 'is_primary' => true]];
    @endphp
    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700"
        x-data="{ addresses: {{ Js::from($addressesData) }} }">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Direcciones y Contactos
            </h3>
            <button type="button"
                @click="addresses.push({id: '', type: 'Sucursal', address_line1: '', city: '', state: '', zip_code: '', phone: '', email: '', is_primary: false})"
                class="px-3 py-1 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 rounded-md text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700">
                + Añadir Dirección
            </button>
        </div>

        <div class="space-y-4">
            <template x-for="(addr, index) in addresses" :key="index">
                <div
                    class="relative grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded bg-white dark:bg-gray-800">

                    <input type="hidden" :name="`addresses[${index}][id]`" x-model="addr.id">

                    <!-- Tipo y Primaria -->
                    <div
                        class="lg:col-span-4 flex justify-between items-center border-b border-gray-100 dark:border-gray-700 pb-2">
                        <div class="flex items-center gap-4">
                            <select :name="`addresses[${index}][type]`" x-model="addr.type"
                                class="text-sm font-bold bg-transparent border-0 border-b-2 border-indigo-500 focus:ring-0 px-0 py-1 dark:text-gray-200 uppercase"
                                required>
                                <option value="Principal">Principal</option>
                                <option value="Sucursal">Sucursal</option>
                                <option value="Depósito">Depósito</option>
                                <option value="Legal">Legal</option>
                            </select>

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="primary_address_index" :value="index"
                                    :checked="addr.is_primary"
                                    @change="addresses.forEach((a, i) => a.is_primary = (i === index))"
                                    class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Es principal</span>
                            </label>
                            <input type="hidden" :name="`addresses[${index}][is_primary]`"
                                :value="addr.is_primary ? 1 : 0">
                        </div>
                        <template x-if="addresses.length > 1">
                            <button type="button" @click="addresses.splice(index, 1)"
                                class="text-red-500 hover:text-red-700" title="Eliminar dirección">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </template>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Domicilio (Calle,
                            Piso, Nro)</label>
                        <input type="text" :name="`addresses[${index}][address_line1]`" x-model="addr.address_line1"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Ciudad</label>
                        <input type="text" :name="`addresses[${index}][city]`" x-model="addr.city"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Provincia</label>
                        <select :name="`addresses[${index}][state]`" x-model="addr.state"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required>
                            <option value="">Seleccionar...</option>
                            @foreach(['Mendoza', 'San Juan', 'San Luis', 'Buenos Aires', 'CABA', 'Córdoba', 'Santa Fe',
                            'Neuquén', 'Ruta Externa'] as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Teléfono</label>
                        <input type="text" :name="`addresses[${index}][phone]`" x-model="addr.phone"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Correo
                            Electrónico</label>
                        <input type="email" :name="`addresses[${index}][email]`" x-model="addr.email"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Código Postal</label>
                        <input type="text" :name="`addresses[${index}][zip_code]`" x-model="addr.zip_code"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ─── CONFIGURACIÓN TARIFARIA DEL CLIENTE ────────────────────────── --}}
    @php
        $tariffSetting = $isEdit ? ($party->activeTariffSetting ?? null) : null;

        $tsBillingMode   = old('tariff.billing_mode',        $tariffSetting?->billing_mode ?? '');
        $tsMinCharge     = old('tariff.minimum_charge',      $tariffSetting?->minimum_charge ?? '');
        $tsRatePerTon    = old('tariff.rate_per_ton_custom', $tariffSetting?->rate_per_ton_custom ?? '');
        $tsRatePerM3     = old('tariff.rate_per_m3_custom',  $tariffSetting?->rate_per_m3_custom ?? '');
        $tsRatePerBulto  = old('tariff.rate_per_bulto',      $tariffSetting?->rate_per_bulto ?? '');
        $tsMinPerBulto   = old('tariff.minimum_per_bulto',   $tariffSetting?->minimum_per_bulto ?? '');
        $tsRatePerPallet = old('tariff.rate_per_pallet',     $tariffSetting?->rate_per_pallet ?? '');
        $tsMinPerPallet  = old('tariff.minimum_per_pallet',  $tariffSetting?->minimum_per_pallet ?? '');
        $tsDeclaredPct   = old('tariff.declared_value_pct',  $tariffSetting?->declared_value_pct ?? '');
        $tsValidFrom     = old('tariff.valid_from',          $tariffSetting?->valid_from?->format('Y-m-d') ?? now()->format('Y-m-d'));
        $tsValidUntil    = old('tariff.valid_until',         $tariffSetting?->valid_until?->format('Y-m-d') ?? '');
        $tsNotes         = old('tariff.notes',               $tariffSetting?->notes ?? '');

        $isBultosPallets = in_array($tsBillingMode, ['bultos', 'pallets', 'bultos_pallets']);
        $radioValue      = $isBultosPallets ? 'bultos_pallets' : $tsBillingMode;
    @endphp

    <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg border border-amber-200 dark:border-amber-700"
         x-data="{
            enabled: {{ $tsBillingMode ? 'true' : 'false' }},
            mode: '{{ $radioValue }}',
            useBultos:  {{ in_array($tsBillingMode, ['bultos',  'bultos_pallets']) ? 'true' : 'false' }},
            usePallets: {{ in_array($tsBillingMode, ['pallets', 'bultos_pallets']) ? 'true' : 'false' }},
            get billingMode() {
                if (!this.enabled) return '';
                if (this.mode !== 'bultos_pallets') return this.mode;
                if (this.useBultos && this.usePallets) return 'bultos_pallets';
                if (this.useBultos)  return 'bultos';
                if (this.usePallets) return 'pallets';
                return 'bultos_pallets';
            }
         }">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Configuración Tarifaria
                @if($tariffSetting)
                    <span class="text-xs font-normal text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/50 px-2 py-0.5 rounded-full">
                        Activa: {{ $tariffSetting->billing_mode_label }}
                    </span>
                @endif
            </h3>
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="checkbox" x-model="enabled"
                       class="rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                <span class="text-sm text-gray-600 dark:text-gray-400">Configurar tarifa especial</span>
            </label>
        </div>

        <div x-show="enabled" x-collapse class="space-y-5">

            <input type="hidden" name="tariff[billing_mode]" :value="billingMode">

            <div class="space-y-3">
                <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    Modo de Facturación <span class="text-red-500">*</span>
                </p>

                {{-- 1. POR KG --}}
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="mode==='kg' ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300'">
                    <input type="radio" x-model="mode" value="kg" class="mt-0.5 text-amber-500 focus:ring-amber-500" :required="enabled">
                    <div class="flex-1">
                        <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">Por Kg</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Usa la escala de precios del cuadro tarifario determinado por origen/destino de la guía.
                        </p>
                        <div x-show="mode==='kg'" class="mt-2 flex items-center gap-3">
                            <div class="relative w-44">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">Mínimo $</span>
                                <input type="number" name="tariff[minimum_charge]" step="0.01" min="0"
                                       value="{{ $tsMinCharge }}" placeholder="0.00" :disabled="!enabled || mode !== 'kg'"
                                       class="pl-16 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <span class="text-xs text-gray-400">(opcional)</span>
                        </div>
                    </div>
                </label>

                {{-- 2. POR TONELADA --}}
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="mode==='tonelada' ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300'">
                    <input type="radio" x-model="mode" value="tonelada" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">Por Tonelada</span>
                        <div x-show="mode==='tonelada'" class="mt-2 flex flex-wrap items-center gap-3">
                            <div class="relative w-44">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">$/Ton</span>
                                <input type="number" name="tariff[rate_per_ton_custom]" step="0.01" min="0"
                                       value="{{ $tsRatePerTon }}" placeholder="0.00"
                                       :required="mode==='tonelada'" :disabled="!enabled || mode !== 'tonelada'"
                                       class="pl-12 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div class="relative w-44">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">Mínimo $</span>
                                <input type="number" name="tariff[minimum_charge]" step="0.01" min="0"
                                       value="{{ $tsMinCharge }}" placeholder="0.00" :disabled="!enabled || mode !== 'tonelada'"
                                       class="pl-16 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                    </div>
                </label>

                {{-- 3. POR VOLUMEN M3 --}}
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="mode==='volumen' ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300'">
                    <input type="radio" x-model="mode" value="volumen" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">Por Volumen (M3)</span>
                        <div x-show="mode==='volumen'" class="mt-2 flex flex-wrap items-center gap-3">
                            <div class="relative w-44">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">$/M3</span>
                                <input type="number" name="tariff[rate_per_m3_custom]" step="0.01" min="0"
                                       value="{{ $tsRatePerM3 }}" placeholder="0.00"
                                       :required="mode==='volumen'"
                                       class="pl-12 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                            <div class="relative w-44">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">Mínimo $</span>
                                <input type="number" name="tariff[minimum_charge]" step="0.01" min="0"
                                       value="{{ $tsMinCharge }}" placeholder="0.00" :disabled="!enabled || mode !== 'volumen'"
                                       class="pl-16 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                    </div>
                </label>

                {{-- 4. POR BULTOS / PALLETS (combinables) --}}
                <div class="p-3 rounded-lg border transition-colors"
                     :class="mode==='bultos_pallets' ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/30' : 'border-gray-200 dark:border-gray-700'">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" x-model="mode" value="bultos_pallets" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                        <div class="flex-1 min-w-0">
                            <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">
                                Por Bultos / Pallets
                                <span class="text-xs font-normal text-amber-600 dark:text-amber-400 ml-1">(pueden combinarse)</span>
                            </span>
                        </div>
                    </label>
                    <div x-show="mode==='bultos_pallets'" class="mt-3 ml-6 space-y-3">

                        {{-- Bultos: tarifa + mínimo propio --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="useBultos" class="rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Bultos</span>
                            </label>
                            <div x-show="useBultos" class="flex flex-wrap items-center gap-3 ml-6">
                                <div class="relative w-44">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">$/bulto</span>
                                    <input type="number" name="tariff[rate_per_bulto]" step="0.01" min="0"
                                           value="{{ $tsRatePerBulto }}" placeholder="0.00"
                                           :required="mode==='bultos_pallets' && useBultos"
                                           class="pl-14 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                </div>
                                <div class="relative w-44">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">Mín $</span>
                                    <input type="number" name="tariff[minimum_per_bulto]" step="0.01" min="0"
                                           value="{{ $tsMinPerBulto }}" placeholder="0.00"
                                           class="pl-12 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                </div>
                            </div>
                            <p x-show="!useBultos" class="text-xs text-gray-400 italic ml-6">No aplica</p>
                        </div>

                        {{-- Pallets: tarifa + mínimo propio --}}
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" x-model="usePallets" class="rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pallets</span>
                            </label>
                            <div x-show="usePallets" class="flex flex-wrap items-center gap-3 ml-6">
                                <div class="relative w-44">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">$/pallet</span>
                                    <input type="number" name="tariff[rate_per_pallet]" step="0.01" min="0"
                                           value="{{ $tsRatePerPallet }}" placeholder="0.00"
                                           :required="mode==='bultos_pallets' && usePallets"
                                           class="pl-14 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                </div>
                                <div class="relative w-44">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">Mín $</span>
                                    <input type="number" name="tariff[minimum_per_pallet]" step="0.01" min="0"
                                           value="{{ $tsMinPerPallet }}" placeholder="0.00"
                                           class="pl-12 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                </div>
                            </div>
                            <p x-show="!usePallets" class="text-xs text-gray-400 italic ml-6">No aplica</p>
                        </div>

                        <p class="text-xs text-amber-600 dark:text-amber-400">
                            ℹ️ Podés activar Bultos, Pallets o ambos. El importe final suma los dos.
                        </p>
                    </div>
                </div>

                {{-- 5. POR VALOR DECLARADO --}}
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors"
                       :class="mode==='valor_declarado' ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/30' : 'border-gray-200 dark:border-gray-700 hover:border-amber-300'">
                    <input type="radio" x-model="mode" value="valor_declarado" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                    <div class="flex-1 min-w-0">
                        <span class="font-medium text-gray-800 dark:text-gray-200 text-sm">Por Valor Declarado (%)</span>
                        <div x-show="mode==='valor_declarado'" class="mt-2 flex flex-wrap items-start gap-3">
                            <div>
                                <div class="relative w-44">
                                    <input type="number" name="tariff[declared_value_pct]" step="0.0001" min="0" max="100"
                                           value="{{ $tsDeclaredPct }}" placeholder="Ej: 0.5"
                                           :required="mode==='valor_declarado'"
                                           class="pr-8 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                                    <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 text-xs">%</span>
                                </div>
                                <p class="text-xs text-gray-400 mt-1">Ej: 0.5 = 0,5% del valor declarado</p>
                            </div>
                            <div class="relative w-44">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xs">Mínimo $</span>
                                <input type="number" name="tariff[minimum_charge]" step="0.01" min="0"
                                       value="{{ $tsMinCharge }}" placeholder="0.00" :disabled="!enabled || mode !== 'valor_declarado'"
                                       class="pl-16 w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                            </div>
                        </div>
                    </div>
                </label>
            </div>{{-- /modos --}}

            {{-- VIGENCIA Y NOTAS --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-amber-200 dark:border-amber-700">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Válido desde <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="tariff[valid_from]" value="{{ $tsValidFrom }}" :required="enabled"
                           class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Válido hasta
                        <span class="text-xs text-gray-400">(sin fecha = sin vencimiento)</span>
                    </label>
                    <input type="date" name="tariff[valid_until]" value="{{ $tsValidUntil }}"
                           class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                </div>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notas internas</label>
                    <textarea name="tariff[notes]" rows="2"
                              class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm"
                              placeholder="Observaciones internas...">{{ $tsNotes }}</textarea>
                </div>
            </div>

            <p class="text-xs text-amber-700 dark:text-amber-400">
                ⚠️ El cuadro tarifario se determina automáticamente por el <strong>origen y destino de cada guía</strong>.
                El mínimo es propio de cada modo: si el importe calculado es menor, se cobra el mínimo.
            </p>
        </div>
    </div>

    <!-- Botones -->
    <div class="flex items-center gap-4 justify-end">
        <a href="{{ route('parties.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            {{ $isEdit ? 'Actualizar Cliente' : 'Guardar Cliente' }}
        </button>
    </div>
</form>
