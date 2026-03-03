@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Crear Nueva Ruta</h2>
                <a href="{{ route('routes.index') }}"
                    class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    &larr; Volver
                </a>
            </div>

            <form action="{{ route('routes.store') }}" method="POST" id="route-form">
                @csrf
                @include('transportRoutes._form', ['route' => new \App\Models\TransportRoute()])
            </form>
        </div>
    </div>
</div>

{{-- Modal para seleccionar Guías --}}
@include('transportRoutes._modal_shipments')
@endsection

@section('scripts')
@vite('resources/js/pages/transportRoutes/form.js')
@endsection