@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Gestión de Despachos</h2>
                <a href="{{ route('dispatches.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Crear Despacho
                </a>
            </div>

            @if(session('success'))
            <div
                class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
            @endif

            <div class="overflow-x-auto">
                <table
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display responsive w-full text-left"
                    id="dispatches-table" data-url="{{ route('dispatches.datatable') }}">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="text-left">
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Número</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Conductor</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Origen - Destino</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Estado</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Detalles Transporte</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Costo</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Total Rutas</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">⚠ Problemas</th>
                            <th class="p-4 font-semibold border-b dark:border-gray-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        {{-- Carga dinámica vía DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/dispatches/dispatches.js')
@endsection