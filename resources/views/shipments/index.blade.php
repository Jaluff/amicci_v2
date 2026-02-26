@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-full mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Listado de Guías</h2>
                <a href="{{ route('shipments.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Nueva Guía
                </a>
            </div>

            <div class="overflow-x-auto">
                <table id="shipmentsTable" data-url="{{ route('shipments.datatable') }}"
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display responsive"
                    style="width:100%">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="text-left">
                            <th>Número</th>
                            <th>Fecha</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Remitente</th>
                            <th>Destinatario</th>
                            <th>Flete</th>
                            <th>Bultos</th>
                            <th>Valor Decl.</th>
                            <th>Total</th>
                            <th>Ubicación</th>
                            <th>Facturación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/shipments/index.js')
@endsection