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
'Listo' => [
'label' => '🔙 Revertir a Listo',
'class' => 'bg-gray-600 hover:bg-gray-700 text-white shadow-gray-200 dark:shadow-gray-900',
'confirm' => '¿Deshacer el inicio de reparto? Esto devolverá las guías a Dto Destino.',
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
                        data-transition="{{ $transition }}" 
                        @if($transition === 'Finalizado') id="btn-delivery-finish" @endif
                        @if($cfg['confirm']) data-confirm="{{ $cfg['confirm'] }}" @endif>
                        {{ $cfg['label'] }}
                    </button>
                    @endforeach

                    @if($delivery->hasActiveProblem())
                    <button type="button"
                        class="btn-show-devolutions inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-amber-100 hover:bg-amber-200 text-amber-700 dark:bg-amber-900/40 dark:hover:bg-amber-900/60 dark:text-amber-300 transition shadow-lg"
                        data-model-id="{{ $delivery->id }}" data-numero="{{ $delivery->delivery_number }}">
                        <span class="text-amber-500">⚠</span> Ver Devoluciones
                    </button>
                    @endif

                    <a href="{{ route('deliveries.print', $delivery) }}" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 dark:bg-blue-900/40 dark:hover:bg-blue-900/60 dark:text-blue-300 transition shadow-lg">
                        🖨️ Imprimir Reparto
                    </a>

                    @if(count($availableTransitions) === 0)
                    <span class="text-sm text-gray-400 italic">Estado final — sin transiciones</span>
                    @endif

                    {{-- Anular solo si está en estado base "Listo" y tiene permisos --}}
                    @if($canCancel && auth()->user()->hasAnyRole(['admin', 'supervisor', 'Supervisor']))
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
                <input type="hidden" name="company_id" value="{{ $delivery->company_id }}">
                @include('deliveries._form', ['delivery' => $delivery])
            </form>
        </div>

        {{-- ══ WIDGET DE PROBLEMAS ════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            @include('partials._problem_widget', ['model' => $delivery, 'modelType' => 'delivery'])
        </div>

    </div>
</div>

{{-- MODAL DE AVISO: Guías a Devolver --}}
<div id="devolution-warning-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-[70]">
    <div class="relative top-20 mx-auto p-6 border w-full max-w-lg shadow-lg rounded-xl bg-white dark:bg-gray-800 dark:border-gray-700">
        <div class="mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span class="text-amber-500">⚠</span> Guías a Devolver
            </h3>
            <p class="text-sm text-gray-400 mt-1">Este reparto contiene guías con problemas que no han sido entregadas. Al finalizar, cambiarán a estado <b>"Dto destino"</b> para su gestión en sucursal.</p>
        </div>
        
        <div id="devolution-list" class="space-y-2 mb-6 max-h-60 overflow-y-auto">
            {{-- Lista dinámica --}}
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg btn-close-devolution">
                Revisar Guías
            </button>
            <button type="button" id="btn-confirm-finish-anyway" 
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-lg">
                Finalizar Reparto y Devolver Guías
            </button>
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