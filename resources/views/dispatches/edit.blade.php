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
'Cargado' => [
'label' => '↩ Revertir a Cargado',
'class' => 'bg-gray-500 hover:bg-gray-600 text-white shadow-gray-200 dark:shadow-gray-900',
'confirm' => '¿Deseas revertir este despacho a estado inicial? Las rutas también se revertirán.',
],
];
@endphp

<div class="py-6">
    <div class="space-y-5">

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

                    {{-- Anular solo si no tiene rutas Y está en estado base "Cargado" --}}
                    @if($currentStatus === \App\StateMachines\DispatchStateMachine::STATUS_CARGADO && $dispatch->routes->count() === 0 &&
                    auth()->user()->hasAnyRole(['admin', 'supervisor', 'Supervisor']))
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

@push('modals')
<!-- Modal de Impresión de Guías por Ruta -->
<div id="print-route-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-80" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Imprimir Guías - Ruta #<span id="modal-route-number"></span>
                </h3>
                <button type="button" class="btn-close-print-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Seleccione las guías que desea imprimir para esta ruta.</p>
                
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <form id="form-print-massive" action="{{ route('shipments.print-massive') }}" method="POST" target="_blank">
                        @csrf
                        <input type="hidden" name="dispatch_id" value="{{ $dispatch->id }}">
                        <input type="hidden" name="route_id" id="modal-route-id-input" value="">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <th class="p-3 text-center w-12">
                                        <input type="checkbox" id="check-all-print" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                                    </th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Número</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remitente</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Destinatario</th>
                                    <th class="p-3 text-left text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Bultos</th>
                                </tr>
                            </thead>
                            <tbody id="print-guides-body" class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                <!-- Se carga vía AJAX -->
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" class="btn-close-print-modal px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    Cancelar
                </button>
                <button type="submit" form="form-print-massive" class="px-5 py-2 text-sm font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md shadow-blue-200 dark:shadow-none transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Imprimir Seleccionadas
                </button>
            </div>
        </div>
    </div>
</div>
@endpush

@section('scripts')
@vite('resources/js/pages/dispatches/form.js')
@endsection