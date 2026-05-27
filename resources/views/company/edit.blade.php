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

            <form method="POST" action="{{ route('company.update', $company) }}" class="space-y-6">
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
                    </h3>                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre Comercial Corto *</label>
                            <input type="text" name="name" value="{{ old('name', $company->name) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                            <span class="text-xs text-gray-500">Este nombre identifica la interfaz de usuario.</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prefijo (Guías)</label>
                            <input type="text" name="prefix" value="{{ old('prefix', $company->prefix) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color Distintivo</label>
                            <div class="flex items-center gap-3 mt-1">
                                <input type="color" name="color" value="{{ old('color', $company->color) }}"
                                    class="h-10 w-full rounded border border-gray-300 dark:border-gray-700 p-1 bg-white dark:bg-gray-800 cursor-pointer">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Última Guía</label>
                            <input type="number" name="last_shipment_number"
                                value="{{ old('last_shipment_number', $company->last_shipment_number) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Última Ruta</label>
                            <input type="number" name="last_route_number"
                                value="{{ old('last_route_number', $company->last_route_number) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Último Despacho</label>
                            <input type="number" name="last_dispatch_number"
                                value="{{ old('last_dispatch_number', $company->last_dispatch_number) }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>
                        </div>
                    </div> </div>
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cargo por Contra-reembolso (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="contra_reembolso_percent"
                                value="{{ old('contra_reembolso_percent', $company->contra_reembolso_percent ?? '0') }}"
                                class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                 <!-- 3. Sucursales de Operación -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700 mt-6">
                    <h3 class="font-bold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                        Sucursales Vinculadas
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Selecciona las sucursales en las que opera esta empresa.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($branches as $branch)
                        <div class="flex items-start gap-3 p-3 bg-white dark:bg-gray-800 rounded border border-gray-100 dark:border-gray-700 shadow-sm">
                            <input type="checkbox" name="branches[]" id="branch_{{ $branch->id }}" value="{{ $branch->id }}"
                                {{ in_array($branch->id, old('branches', $company->branches->pluck('id')->toArray())) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5" />
                            <div>
                                <label for="branch_{{ $branch->id }}" class="text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer block leading-tight">
                                    {{ $branch->name }}
                                </label>
                                @if($branch->address_line1)
                                <span class="text-xs text-gray-400 block mt-0.5">{{ $branch->address_line1 }}, {{ $branch->city }}</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4"></div></div>

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