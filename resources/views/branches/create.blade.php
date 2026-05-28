@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-6 border-b border-gray-200 dark:border-gray-700 pb-2">
            🏢 Nueva Sucursal
        </h2>

        @if ($errors->any())
        <div class="mb-4 bg-red-50 dark:bg-red-900/40 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 p-4 rounded-md">
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('branches.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Izquierda: Datos Básicos y Dirección (2/3 de ancho) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Datos Operativos -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            </svg>
                            Datos Operativos
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Nombre *</label>
                                <x-text-input name="name" type="text" value="{{ old('name') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" placeholder="Sucursal Mendoza" required />
                            </div>

                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Código numérico *</label>
                                <x-text-input name="code" type="number" value="{{ old('code') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700"
                                    min="1" max="99" required />
                            </div>

                            <div class="md:col-span-3">
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Ubicación Física *</label>
                                <select name="ubicacion_id" required
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Seleccionar Ubicación...</option>
                                    @foreach($ubicaciones as $ub)
                                    <option value="{{ $ub->id }}" @selected(old('ubicacion_id') == $ub->id)>{{ $ub->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Datos de Dirección y Contacto -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Ubicación y Contacto
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Domicilio (Calle, Nro, Piso)</label>
                                <x-text-input name="address_line1" type="text" value="{{ old('address_line1') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Domicilio Línea 2 (Piso, Dpto, etc.)</label>
                                <x-text-input name="address_line2" type="text" value="{{ old('address_line2') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" />
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Ciudad</label>
                                <x-text-input name="city" type="text" value="{{ old('city') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" />
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Provincia</label>
                                <x-text-input name="state" type="text" value="{{ old('state') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" />
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Código Postal</label>
                                <x-text-input name="zip_code" type="text" value="{{ old('zip_code') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" />
                            </div>
                            <div>
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Teléfono</label>
                                <x-text-input name="phone" type="text" value="{{ old('phone') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="font-medium text-gray-700 dark:text-gray-300 block mb-1">Correo Electrónico</label>
                                <x-text-input name="email" type="email" value="{{ old('email') }}"
                                    class="w-full py-2 px-3 rounded border-gray-300 dark:border-gray-700" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Empresas Asociadas y Estado (1/3 de ancho) -->
                <div class="space-y-6">
                    <!-- Empresas -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Empresas Vinculadas
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Esta sucursal estará operativa y disponible para las siguientes empresas:</p>

                        <div class="space-y-3">
                            @foreach($companies as $company)
                            <div class="flex items-start gap-3 p-3 bg-white dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700 shadow-sm">
                                <input type="checkbox" name="companies[]" id="company_{{ $company->id }}" value="{{ $company->id }}"
                                    {{ in_array($company->id, old('companies', [])) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5" />
                                <div>
                                    <label for="company_{{ $company->id }}" class="text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer block leading-tight">
                                        {{ $company->name }}
                                    </label>
                                    <span class="text-[10px] text-gray-400 block mt-0.5">{{ $company->legal_name }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Configuración y Estado -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700 space-y-4">
                        <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                            </svg>
                            Estado
                        </h3>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_primary" id="is_primary" value="1"
                                {{ old('is_primary') ? 'checked' : '' }} class="rounded" />
                            <label for="is_primary" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Es sucursal principal (guía)</label>
                        </div>

                        <div class="flex items-center gap-2 border-t border-gray-100 dark:border-gray-700 pt-3">
                            <input type="checkbox" name="active" id="active" value="1"
                                {{ old('active', '1') ? 'checked' : '' }} class="rounded" />
                            <label for="active" class="text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">Activa</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700 justify-end">
                <a href="{{ route('branches.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 transition">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-semibold transition">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
