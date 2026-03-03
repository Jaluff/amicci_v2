@extends('layouts.app')

@section('content')
@php
$canCancel = $shipment->ubicacion_actual === \App\StateMachines\ShipmentStateMachine::STATUS_DTO_ORIGEN;
@endphp

<div class="py-6">
    <div class="w-full sm:px-6 lg:px-8 space-y-5">

        {{-- ══ CABECERA ══════════════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Guía <span class="text-indigo-600 dark:text-indigo-400">{{ $shipment->numero }}</span>
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">
                        Ubicación actual:
                        @php
                        $shipColors = [
                        'Dto origen' => 'dt-badge-indigo',
                        'En transito' => 'dt-badge-yellow',
                        'Dto destino' => 'dt-badge-blue',
                        'En reparto' => 'dt-badge-orange',
                        'Entregado' => 'dt-badge-green',
                        ];
                        @endphp
                        <span class="dt-badge ml-1 {{ $shipColors[$shipment->ubicacion_actual] ?? 'dt-badge-gray' }}">
                            {{ $shipment->ubicacion_actual }}
                        </span>
                        @if($shipment->hasActiveProblem())
                        <span class="dt-badge dt-badge-red ml-2 animate-pulse">
                            ⚠ PROBLEMA
                        </span>
                        @endif
                        <span class="ml-2 text-xs text-gray-400 italic">El estado lo gestiona el sistema (vía
                            Ruta/Despacho)</span>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Anular solo si está en estado base "Dto origen" y tiene permisos --}}
                    @if($canCancel && auth()->user()->hasAnyRole(['admin', 'Supervisor']))
                    <form action="{{ route('shipments.destroy', $shipment) }}" method="POST"
                        onsubmit="return confirm('¿Anular esta guía? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-red-100 hover:bg-red-200 text-red-700 dark:bg-red-900/40 dark:hover:bg-red-900/60 dark:text-red-300 transition">
                            🗑 Anular guía
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('shipments.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-sm font-medium transition">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        @if($errors->any())
        <div
            class="px-6 py-4 bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-lg text-sm border border-red-200 dark:border-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- ══ FORMULARIO DE DATOS ═══════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-6">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Datos de la
                guía</h3>
            @include('shipments._form')
        </div>

        {{-- ══ WIDGET DE PROBLEMAS ════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            @include('partials._problem_widget', ['model' => $shipment, 'modelType' => 'shipment'])
        </div>

    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/shipments/form.js')
@endsection