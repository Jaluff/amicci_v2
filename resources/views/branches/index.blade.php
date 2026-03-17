@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

        <div class="flex items-center justify-between mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">🏢 Sucursales</h2>
            <a href="{{ route('branches.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest transition">
                + Nueva Sucursal
            </a>
        </div>

        @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/40 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-md text-sm">
            {{ session('success') }}
        </div>
        @endif

        <table id="branches-table" data-url="{{ route('branches.datatable') }}" class="w-full text-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Ubicación</th>
                    <th>Código</th>
                    <th>Guías emitidas</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@section('scripts')
@vite('resources/js/pages/branches/index.js')
@endsection
@endsection
