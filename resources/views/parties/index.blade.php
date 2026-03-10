@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Listado de Clientes</h2>
                <a href="{{ route('parties.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Nuevo Cliente
                </a>
            </div>

            <div class="overflow-x-auto">
                <table id="partiesTable" data-url="{{ route('parties.datatable') }}"
                    class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 display responsive"
                    style="width:100%">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr class="text-left">
                            <th>Nombre / Razón Social</th>
                            <th>CUIT / DNI</th>
                            <th>Dirección Principal</th>
                            <th>Contacto</th>
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
<script type="module">
    $(document).ready(function () {
        const table = $('#partiesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: $('#partiesTable').data('url'),
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'document', name: 'document', render: function (data) { return data || '-'; } },
                { data: 'direcciones', name: 'direcciones', orderable: false, searchable: false },
                { data: 'contacto', name: 'contacto', orderable: false, searchable: false },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ]
        });
    });
</script>
@endsection