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
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">CUIT / DNI</label>
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
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Ciudad</label>
                        <input type="text" :name="`addresses[${index}][city]`" x-model="addr.city"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Provincia</label>
                        <select :name="`addresses[${index}][state]`" x-model="addr.state"
                            class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
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