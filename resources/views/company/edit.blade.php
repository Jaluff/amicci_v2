@extends('layouts.app')

@section('content')
<div class="py-6">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h2
                class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6 border-b border-gray-200 dark:border-gray-700 pb-2">
                Ajustes de la Empresa: {{ $company->name }}
            </h2>

            <!-- Errors -->
            @if ($errors->any())
            <div
                class="mb-4 bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 p-4 rounded-md">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(session('success'))
            <div
                class="mb-4 bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 p-4 rounded-md">
                {{ session('success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('company.update') }}" class="space-y-6">
                @csrf
                @method('PUT')

                @php $addr = $company->primaryAddress; @endphp

                <!-- 1. Configuración del Sistema (Relacionado a la operatoria) -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Parámetros del Sistema
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Comercial
                                Corto *</label>
                            <input type="text" name="name" value="{{ old('name', $company->name) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            <span class="text-xs text-gray-500">Este nombre identifica la interfaz de usuario.</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prefijo
                                (Guías)</label>
                            <input type="text" name="prefix" value="{{ old('prefix', $company->prefix) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Última
                                Guía</label>
                            <input type="number" name="last_shipment_number"
                                value="{{ old('last_shipment_number', $company->last_shipment_number) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Última
                                Ruta</label>
                            <input type="number" name="last_route_number"
                                value="{{ old('last_route_number', $company->last_route_number) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Último
                                Despacho</label>
                            <input type="number" name="last_dispatch_number"
                                value="{{ old('last_dispatch_number', $company->last_dispatch_number) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                    </div>
                </div>

                <!-- 2. Información Legal y Facturación -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                            </path>
                        </svg>
                        Datos Legales y de Facturación
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Razón Social
                                Legal</label>
                            <input type="text" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">CUIT</label>
                            <input type="text" name="cuit" value="{{ old('cuit', $company->cuit) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ingresos
                                Brutos</label>
                            <input type="text" name="gross_income"
                                value="{{ old('gross_income', $company->gross_income) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Inicio
                                Actividades</label>
                            <input type="date" name="start_of_activities"
                                value="{{ old('start_of_activities', $company->start_of_activities) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Establecimiento
                                (Facturación)</label>
                            <input type="text" name="establishment"
                                value="{{ old('establishment', $company->establishment) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sede o Casa
                                Central</label>
                            <input type="text" name="stamping_headquarters"
                                value="{{ old('stamping_headquarters', $company->stamping_headquarters) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- 3. Direcciones y Contacto (Polimórficas) -->
                @php
                $addressesData = $company->addresses->isEmpty()
                ? [['id' => '', 'type' => 'Principal', 'address_line1' => '', 'city' => '', 'state' => '', 'zip_code' =>
                '', 'phone' => '', 'email' => '', 'is_primary' => true]]
                : $company->addresses->toArray();
                @endphp
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700"
                    x-data="{ addresses: {{ Js::from($addressesData) }} }">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Direcciones y Contactos
                        </h3>
                        <button type="button"
                            @click="addresses.push({id: '', type: 'Sucursal', address_line1: '', city: '', state: '', zip_code: '', phone: '', email: '', is_primary: false})"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-sm font-medium hover:bg-blue-100 dark:bg-blue-900/40 dark:text-blue-400 dark:border-blue-800 dark:hover:bg-blue-800/60 dark:hover:text-blue-300 transition-colors">
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
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Es
                                                principal</span>
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
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Domicilio
                                        (Calle, Piso, Nro)</label>
                                    <input type="text" :name="`addresses[${index}][address_line1]`"
                                        x-model="addr.address_line1"
                                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                        required>
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-500 dark:text-gray-400">Ciudad</label>
                                    <input type="text" :name="`addresses[${index}][city]`" x-model="addr.city"
                                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-500 dark:text-gray-400">Provincia</label>
                                    <input type="text" :name="`addresses[${index}][state]`" x-model="addr.state"
                                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-500 dark:text-gray-400">Teléfono</label>
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
                                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Código
                                        Postal</label>
                                    <input type="text" :name="`addresses[${index}][zip_code]`" x-model="addr.zip_code"
                                        class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex items-center gap-4 justify-end">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        Guardar Ajustes
                    </button>
                </div>
            </form>

        </div>
</div>
@endsection