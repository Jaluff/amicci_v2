@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="w-full sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Listado de Usuarios</h2>
                <a href="{{ route('users.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Nuevo Usuario
                </a>
            </div>

            <div class="overflow-x-auto">
                <table id="usersTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Empresas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($users as $user)
                        <tr>
                            <td
                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->hasRole('admin') ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $user->roles->pluck('name')->implode(', ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->companies->pluck('name')->implode(', ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex gap-2">
                                <a href="{{ route('users.edit', $user) }}"
                                    class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de eliminar este usuario?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#usersTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
            }
        });
    });
</script>

<style>
    /* Estilos base para DataTables */
    .dataTables_wrapper {
        color: #374151;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_processing,
    .dataTables_wrapper .dataTables_paginate {
        color: #374151;
        margin-bottom: 1rem;
    }

    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.25rem 0.5rem;
        background-color: transparent;
        color: #374151;
    }

    /* ===== DARK MODE STYLES ===== */
    .dark .dataTables_wrapper {
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_length {
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_filter {
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_filter input {
        border-color: #4b5563 !important;
        background-color: #1f2937 !important;
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_info {
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_processing {
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate {
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_length label {
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_filter label {
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_length select {
        border-color: #4b5563 !important;
        background-color: #1f2937 !important;
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_length select option {
        background-color: #1f2937 !important;
        color: #e5e7eb !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #e5e7eb !important;
        border-color: #4b5563 !important;
        background: transparent !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: #f3f4f6 !important;
        border-color: #6b7280 !important;
        background: #374151 !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #6b7280 !important;
        background: transparent !important;
    }

    .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #374151 !important;
        color: #f3f4f6 !important;
        border-color: #6b7280 !important;
    }

    .dark table.dataTable thead th,
    .dark table.dataTable thead td {
        border-bottom: 1px solid #4b5563 !important;
        color: #e5e7eb !important;
    }

    .dark table.dataTable tbody td {
        color: #e5e7eb !important;
    }
</style>
@endsection