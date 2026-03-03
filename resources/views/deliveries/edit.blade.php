@extends('layouts.app')

@section('content')
@php
$canCancel = $delivery->status === \App\StateMachines\DeliveryStateMachine::READY;
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

        {{-- ══ CABECERA ══════════════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Reparto <span class="text-indigo-600 dark:text-indigo-400">{{ $delivery->delivery_number
                            }}</span>
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">
                        Estado:
                        @php
                        $deliveryColors = [
                        'Listo' => 'dt-badge-blue',
                        'En reparto' => 'dt-badge-yellow',
                        'Finalizado' => 'dt-badge-green',
                        ];
                        @endphp
                        <span class="dt-badge ml-1 {{ $deliveryColors[$delivery->status] ?? 'dt-badge-gray' }}">
                            {{ $delivery->status }}
                        </span>
                        @if($delivery->hasActiveProblem())
                        <span class="dt-badge dt-badge-red ml-2 animate-pulse">
                            ⚠ PROBLEMA
                        </span>
                        @endif
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    {{-- Anular solo si está en estado base "Listo" y tiene permisos --}}
                    @if($canCancel && auth()->user()->hasAnyRole(['admin', 'Supervisor']))
                    <form action="{{ route('deliveries.destroy', $delivery) }}" method="POST"
                        onsubmit="return confirm('¿Anular este reparto? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-red-100 hover:bg-red-200 text-red-700 dark:bg-red-900/40 dark:hover:bg-red-900/60 dark:text-red-300 transition">
                            🗑 Anular reparto
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('deliveries.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg text-sm font-medium transition">
                        ← Volver
                    </a>
                </div>
            </div>
        </div>

        {{-- ══ FORMULARIO DE DATOS ═══════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-6">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Datos del
                reparto</h3>
            <form action="{{ route('deliveries.update', $delivery->id) }}" method="POST" id="delivery-form">
                @csrf
                @method('PUT')
                @include('deliveries._form', ['delivery' => $delivery])
            </form>
        </div>

        {{-- ══ WIDGET DE PROBLEMAS ════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            @include('partials._problem_widget', ['model' => $delivery, 'modelType' => 'delivery'])
        </div>

    </div>
</div>

@include('deliveries._modal_shipments')
@include('partials._modal_shipment_problems')
@endsection

@section('scripts')
<script>
    window.deliveryId = "{{ $delivery->id }}";
</script>
@vite('resources/js/pages/deliveries/deliveries.js')
@endsection