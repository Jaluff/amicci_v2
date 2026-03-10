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

        {{-- ══ FORMULARIO DE DATOS Y TIMELINE ═══════════════════════════════════════════ --}}
        <div x-data="{ showTimeline: false }" class="flex flex-col lg:flex-row gap-6 items-start">
            
            <!-- Columna Principal (Formularios y Problemas) -->
            <div class="flex-1 w-full space-y-5 transition-all duration-300">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Datos de la guía</h3>
                        
                        <button type="button" @click="showTimeline = !showTimeline"
                            class="text-xs font-semibold px-3 py-1.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition inline-flex items-center gap-2">
                            <svg x-show="!showTimeline" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <svg x-show="showTimeline" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                            <span x-show="!showTimeline">Ver Historial</span>
                            <span x-show="showTimeline" style="display: none;">Ocultar Historial</span>
                        </button>
                    </div>
                    @include('shipments._form')
                </div>

                <!-- Widget de Problemas -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-4">
                    @include('partials._problem_widget', ['model' => $shipment, 'modelType' => 'shipment'])
                </div>
            </div>

            <!-- Columna Timeline Lateral Ocultable -->
            <div x-show="showTimeline" x-transition.opacity style="display: none;" 
                class="w-full lg:w-80 shrink-0 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg px-6 py-6 sticky top-6">
                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-6">Historial de Eventos</h3>
                
                <div class="relative space-y-6">
                    <!-- Línea vertical central punteada -->
                    <div class="absolute left-[11px] top-2 bottom-2 w-px border-l-2 border-dashed border-gray-200 dark:border-gray-700"></div>

                    @forelse($shipment->activityLogs as $log)
                    <div class="relative flex items-start gap-4">
                        <div class="w-6 h-6 rounded-full bg-indigo-50 dark:bg-indigo-900/50 border-2 border-indigo-400 dark:border-indigo-500 flex items-center justify-center shrink-0 z-10 mt-0.5">
                            <div class="w-2 h-2 rounded-full bg-indigo-500 dark:bg-indigo-400"></div>
                        </div>
                        <div class="flex-1 pb-1">
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 leading-tight">{{ $log->description }}</p>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 flex flex-col xl:flex-row xl:justify-between items-start xl:items-center gap-1">
                                <span class="whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                @if($log->causer)
                                <span class="bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-gray-600 dark:text-gray-300 truncate max-w-[120px]" title="{{ $log->causer->name }}">
                                    👤 {{ explode(' ', $log->causer->name)[0] }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="relative flex items-start gap-4">
                        <div class="w-6 h-6 rounded-full bg-gray-50 dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-600 shrink-0 z-10 mt-0.5"></div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 pt-0.5">Sin eventos.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
        </div>

    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/shipments/form.js')
@endsection