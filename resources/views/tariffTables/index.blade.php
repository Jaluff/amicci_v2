@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Cuadros Tarifarios</h2>
            <a href="{{ route('tariff-tables.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition ease-in-out duration-150">
                + Nuevo Cuadro
            </a>
        </div>

        <div class="overflow-x-auto">
            <table id="tariffTablesTable" data-url="{{ route('tariff-tables.datatable') }}"
                   class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display responsive"
                   style="width:100%">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr class="text-left">
                        <th>Nombre</th>
                        <th>Ruta</th>
                        <th>$/Ton (+1000 kg)</th>
                        <th>$/M3 Aforo</th>
                        <th>Tramos</th>
                        <th>Vigencia</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="module">
$(document).ready(function () {
    $('#tariffTablesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: $('#tariffTablesTable').data('url'),
            type: 'GET'
        },
        columns: [
            { data: 'name',            name: 'name' },
            { data: 'ruta',            name: 'ruta', orderable: false },
            { data: 'rate_per_ton_fmt', name: 'rate_per_ton', className: 'text-right' },
            { data: 'rate_per_m3_fmt', name: 'rate_per_m3', className: 'text-right' },
            { data: 'brackets_count', name: 'brackets_count', className: 'text-center' },
            { data: 'vigencia',        name: 'vigencia', orderable: false },
            { data: 'estado',          name: 'estado', orderable: false, className: 'text-center' },
            { data: 'acciones',        name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
    });
});
</script>
@endsection
