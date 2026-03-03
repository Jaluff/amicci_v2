@extends('layouts.app')

@section('content')
@php
$sm = $dispatch->stateMachine();
$currentStatus = $sm->currentStatus();
$availableTransitions = $sm->transitions()[$currentStatus] ?? [];
$btnConfig = [
'En viaje' => [
'label' => '🚛 Marcar En viaje',
'class' => 'bg-yellow-500 hover:bg-yellow-600 text-white shadow-yellow-200 dark:shadow-yellow-900',
'confirm' => '¿Confirmar que el despacho salió? Esto actualizará todas las rutas y guías asociadas.',
],
'Arribado' => [
'label' => '✅ Marcar Arribado',
'class' => 'bg-green-600 hover:bg-green-700 text-white shadow-green-200 dark:shadow-green-900',
'confirm' => '¿Confirmar que el despacho llegó a destino? Esto marcará todas las rutas como Entregadas.',
],
];
@endphp

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

        {{-- ══ CABECERA ══════════════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                        Despacho <span class="text-indigo-600 dark:text-indigo-400">{{ $dispatch->dispatch_number
                            }}</span>
                    </h2>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">
                        Estado actual:
                        @php
                        $statusColors = [
                        'Cargado' => 'dt-badge-blue',
                        'En viaje' => 'dt-badge-yellow',
                        'Arribado' => 'dt-badge-green',
                        ];
                        @endphp
                        <span class="dt-badge ml-1 {{ $statusColors[$currentStatus] ?? 'dt-badge-gray' }}">
                            {{ $currentStatus }}
                        </span>
                        @if($dispatch->hasActiveProblem())
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
                        data-model-type="dispatch" data-model-id="{{ $dispatch->id }}"
                        data-transition="{{ $transition }}" @if($cfg['confirm']) data-confirm="{{ $cfg['confirm'] }}"
                        @endif>
                        {{ $cfg['label'] }}
                    </button>
                    @endforeach

                    @if(count($availableTransitions) === 0)
                    <span class="text-sm text-gray-400 italic">Estado final — sin transiciones</span>
                    @endif

                    {{-- Anular solo si está en estado base "Cargado" y tiene permisos --}}
                    @if($currentStatus === \App\StateMachines\DispatchStateMachine::STATUS_CARGADO &&
                    auth()->user()->hasAnyRole(['admin', 'Supervisor']))
                    <form action="{{ route('dispatches.destroy', $dispatch) }}" method="POST"
                        onsubmit="return confirm('¿Anular este despacho? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm bg-red-100 hover:bg-red-200 text-red-700 dark:bg-red-900/40 dark:hover:bg-red-900/60 dark:text-red-300 transition">
                            🗑 Anular despacho
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('dispatches.index') }}"
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
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        {{-- ══ FORMULARIO DE DATOS ═══════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-6">
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Datos del
                despacho</h3>
            <form action="{{ route('dispatches.update', $dispatch->id) }}" method="POST" id="dispatch-form">
                @csrf
                @method('PUT')
                @include('dispatches._form', ['dispatch' => $dispatch])
            </form>
        </div>

        {{-- ══ WIDGET DE PROBLEMAS ════════════════════════════════════════════ --}}
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
            @include('partials._problem_widget', ['model' => $dispatch, 'modelType' => 'dispatch'])
        </div>

    </div>
</div>

@include('dispatches._modal_routes')
@endsection

@section('scripts')
@vite('resources/js/pages/dispatches/form.js')
@endsection