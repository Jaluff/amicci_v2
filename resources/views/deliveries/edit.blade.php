@extends('layouts.app')

@section('content')
@php
$sm = $delivery->stateMachine();
$currentStatus = $sm->currentStatus();
$availableTransitions = $sm->transitions()[$currentStatus] ?? [];
$btnConfig = [
'En reparto' => [
'label' => '🚛 Marcar En Reparto',
'class' => 'bg-yellow-500 hover:bg-yellow-600 text-white shadow-yellow-200 dark:shadow-yellow-900',
'confirm' => '¿Confirmar que el reparto inicia? Esto pasará todas las guías a En Reparto.',
],
'Finalizado' => [
'label' => '✅ Marcar Finalizado',
'class' => 'bg-green-600 hover:bg-green-700 text-white shadow-green-200 dark:shadow-green-900',
'confirm' => '¿Confirmar que el reparto ha finalizado?',
],
];
$canCancel = $currentStatus === \App\StateMachines\DeliveryStateMachine::READY;
@endphp

<div class="py-6">
    <div class="space-y-5">

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
                        <span class="dt-badge ml-1 {{ $deliveryColors[$currentStatus] ?? 'dt-badge-gray' }}">
                            {{ $currentStatus }}
                        </span>
                        @if($delivery->hasActiveProblem())
                        <span class="dt-badge dt-badge-red ml-2 animate-pulse">
                            ⚠ PROBLEMA
                        </span>
                        @endif
                    </p>
                </div>

                {{-- ── Botones de transición de estado (arriba y prominentes) ── --}}
                <div class="flex flex-wrap items-center gap-3">
                    @foreach($availableTransitions as $transition)
                    @php $cfg = $btnConfig[$transition] ?? ['label' => $transition, 'class' => 'bg-gray-600
                    hover:bg-gray-700 text-white', 'confirm' => null]; @endphp
                    <button type="button"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl font-bold text-sm shadow-lg transition-all duration-150 {{ $cfg['class'] }}"
                        data-model-type="delivery" data-model-id="{{ $delivery->id }}"
                        data-transition="{{ $transition }}" @if($cfg['confirm']) data-confirm="{{ $cfg['confirm'] }}"
                        @endif>
                        {{ $cfg['label'] }}
                    </button>
                    @endforeach

                    @if(count($availableTransitions) === 0)
                    <span class="text-sm text-gray-400 italic">Estado final — sin transiciones</span>
                    @endif

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