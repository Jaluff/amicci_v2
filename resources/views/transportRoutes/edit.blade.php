@extends('layouts.app')

@section('content')
@php
$canCancel = $route->status === \App\StateMachines\RouteStateMachine::STATUS_CARGADA;
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

        {{-- ══ CABECERA ══════════════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Ruta <span class="text-indigo-600 dark:text-indigo-400">{{ $route->route_number }}</span>
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">
                        Estado:
                        @php
                        $routeColors = [
                        'Cargada' => 'dt-badge-blue',
                        'En viaje' => 'dt-badge-yellow',
                        'Entregada' => 'dt-badge-green',
                        ];
                        @endphp
                        <span class="dt-badge ml-1 {{ $routeColors[$route->status] ?? 'dt-badge-gray' }}">
                            {{ $route->status }}
                        </span>
                        @if($route->hasActiveProblem())
                        <span class="dt-badge dt-badge-red ml-2 animate-pulse">
                            ⚠ PROBLEMA
                        </span>
                        @endif
                        <span class="ml-2 text-xs text-gray-400 italic">El estado lo gestiona el sistema (vía
                            Despacho)</span>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Anular solo si está en estado base "Cargada" y tiene permisos --}}
                    @if($canCancel && auth()->user()->hasAnyRole(['admin', 'Supervisor']))
                    <form action="{{ route('routes.destroy', $route) }}" method="POST"
                        onsubmit="return confirm('¿Anular esta ruta? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-red-100 hover:bg-red-200 text-red-700 dark:bg-red-900/40 dark:hover:bg-red-900/60 dark:text-red-300 transition">
                            🗑 Anular ruta
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('routes.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-sm font-medium transition">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        {{-- ══ FORMULARIO DE DATOS ═══════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-6">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Datos de la
                ruta</h3>
            <form action="{{ route('routes.update', $route) }}" method="POST" id="route-form">
                @csrf
                @method('PUT')
                @include('transportRoutes._form')
            </form>
        </div>

        {{-- ══ WIDGET DE PROBLEMAS ════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            @include('partials._problem_widget', ['model' => $route, 'modelType' => 'route'])
        </div>

    </div>
</div>

@include('transportRoutes._modal_shipments')
@endsection

@section('scripts')
@vite('resources/js/pages/transportRoutes/form.js')
@endsection