@extends($layout ?? 'layouts.app')

@section('content')
@php
    $backUrl = auth()->user()->hasAnyRole(['admin', 'supervisor']) 
        ? route('deliveries.index') 
        : route('deliverer.index');
    $isIframe = request()->has('iframe');
@endphp
<div class="{{ $isIframe ? 'max-w-full' : 'max-w-lg' }} mx-auto" x-data="{ 
    selectedShipments: {{ json_encode($delivery->shipments->where('ubicacion_actual', 'Entregado')->pluck('id')->map(fn($id) => (string)$id)->values()->toArray()) }},
    originalSelection: {{ json_encode($delivery->shipments->where('ubicacion_actual', 'Entregado')->pluck('id')->map(fn($id) => (string)$id)->values()->toArray()) }},
    hasChanges() {
        return JSON.stringify([...this.selectedShipments].sort()) !== JSON.stringify([...this.originalSelection].sort());
    }
}">
    <div class="space-y-4 pb-20"> {{-- pb-20 prevents sticky footer from overlapping content --}}
        
        {{-- Back Button & Header --}}
        @if(!$isIframe)
        <div class="mb-4">
            <a href="{{ $backUrl }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm shadow-md transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
                <span>Volver al Listado</span>
            </a>
        </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-mono font-bold text-gray-800 dark:text-gray-100">Reparto #{{ $delivery->delivery_number }}</h1>
            </div>
        </div>

        {{-- Delivery Details Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm text-sm grid grid-cols-2 gap-2 text-gray-600 dark:text-gray-400">
            <div>
                <span class="text-gray-400 block text-xs uppercase font-semibold">Patente</span>
                <span class="font-mono font-bold text-gray-800 dark:text-gray-200 text-base">{{ $delivery->vehicle_plate ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-400 block text-xs uppercase font-semibold">Sucursal</span>
                <span class="font-bold text-gray-800 dark:text-gray-200 text-base">{{ $delivery->location->name ?? 'N/A' }}</span>
            </div>
        </div>

        {{-- Shipments Section --}}
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Listado de Guías
                </h2>
                <span class="text-sm text-gray-400">
                    {{ $delivery->shipments->where('ubicacion_actual', 'Entregado')->count() }} / {{ $delivery->shipments->count() }} entregadas
                </span>
            </div>

            @if($delivery->shipments->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 text-center shadow-sm">
                    <p class="text-base text-gray-400 italic">No hay guías asignadas a este reparto.</p>
                </div>
            @else
                <form action="{{ route('deliverer.confirm', $delivery) }}" method="POST" id="delivery-form">
                    @csrf
                    <div class="{{ $isIframe ? 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2' : 'space-y-3' }}">
                        @foreach($delivery->shipments as $shipment)
                            
                            {{-- Shipment Card --}}
                            <label class="block cursor-pointer select-none">
                                <div class="relative bg-white dark:bg-gray-800 rounded-xl border p-4 shadow-sm transition-all duration-200 flex items-start gap-4"
                                     :class="selectedShipments.includes('{{ $shipment->id }}')
                                         ? 'border-green-200 dark:border-green-900 bg-green-50/10 dark:bg-green-950/5'
                                         : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
                                    
                                    {{-- Checkbox / Status Indicator --}}
                                    <div class="pt-1 shrink-0">
                                        <input type="checkbox" 
                                               name="shipment_ids[]" 
                                               value="{{ $shipment->id }}"
                                               x-model="selectedShipments"
                                               class="w-6 h-6 text-orange-600 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-orange-500 dark:bg-gray-700">
                                    </div>
 
                                    {{-- Shipment Information --}}
                                    <div class="flex-1 min-w-0 text-base" x-data="{ showProblemForm: false }">
                                        <div class="flex items-center justify-between gap-1">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="text-lg font-mono font-bold text-gray-900 dark:text-gray-100 truncate">#{{ $shipment->numero }}</span>
                                                @if($shipment->hasActiveProblem())
                                                    <span class="text-red-500 font-bold animate-pulse cursor-pointer btn-open-spm text-lg" 
                                                        data-shipment-id="{{ $shipment->id }}"
                                                        data-shipment-numero="{{ $shipment->numero }}"
                                                        style="color: #ef4444 !important;"
                                                        title="Tiene un problema activo. Click para ver/resolver.">⚠</span>
                                                @endif
                                            </div>
                                            <div class="flex flex-col items-end gap-1 shrink-0">
                                                <div class="flex items-center gap-1.5">
                                                    <button type="button"
                                                        @click.prevent="showProblemForm = !showProblemForm"
                                                        class="w-7 h-7 flex items-center justify-center text-red-600 hover:text-red-700 bg-red-100 dark:bg-red-950/20 border border-red-200 dark:border-red-900 rounded-lg font-bold text-base transition"
                                                        title="Reportar Problema" data-id="{{ $shipment->id }}">
                                                        !
                                                    </button>
                                                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg uppercase font-sans text-center min-w-[85px]"
                                                          :class="selectedShipments.includes('{{ $shipment->id }}')
                                                              ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                              : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                                          x-text="selectedShipments.includes('{{ $shipment->id }}') ? 'Entregado' : 'En reparto'">
                                                    </span>
                                                </div>
                                                <span class="text-sm text-gray-500">Bultos: <strong class="text-gray-850 dark:text-gray-200 font-bold">{{ $shipment->bultos }}</strong></span>
                                            </div>
                                        </div>

                                        <div class="mt-2 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                                            <div>
                                                <span class="font-semibold text-gray-800 dark:text-gray-200 uppercase block truncate"><span class="text-gray-400 font-normal">Dest:</span> {{ $shipment->recipient->name ?? '-' }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-855 dark:text-gray-250 block truncate" title="{{ $shipment->recipient->address ?? $shipment->recipient->address_line1 ?? '-' }}"><span class="text-gray-400">Dir:</span> {{ $shipment->recipient->address ?? $shipment->recipient->address_line1 ?? '-' }}</span>
                                            </div>
                                        </div>

                                        {{-- TEXTAREA INLINE PARA REPORTE DE PROBLEMAS --}}
                                        <div x-show="showProblemForm" x-transition class="mt-2 p-2 bg-red-50/50 dark:bg-red-950/10 border border-red-100 dark:border-red-900/50 rounded space-y-1">
                                            <span class="text-[9px] font-bold text-red-700 dark:text-red-400 block uppercase">Reportar Problema</span>
                                            <textarea placeholder="Detalle el problema con la guía..." rows="2" class="inline-problem-comment w-full px-2 py-1 text-[10px] rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-red-500 focus:ring-red-500 resize-none"></textarea>
                                            <div class="flex justify-end gap-1.5">
                                                <button type="button" @click.prevent="showProblemForm = false" class="px-2 py-0.5 text-[9px] bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">Cancelar</button>
                                                <button type="button" class="btn-save-inline-problem px-2 py-0.5 text-[9px] bg-red-600 hover:bg-red-700 text-white font-bold rounded shadow transition" data-shipment-id="{{ $shipment->id }}">Guardar</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </label>
                        @endforeach
                    </div>
                    
                    {{-- Sticky Action Footer --}}
                    <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/90 dark:bg-gray-900/90 backdrop-blur border-t border-gray-200 dark:border-gray-800 shadow-lg z-40 transition-all duration-300"
                         x-show="hasChanges()"
                         x-transition:enter="transition ease-out duration-300 transform translate-y-full"
                         x-transition:enter-end="translate-y-0"
                         x-transition:leave="transition ease-in duration-200 transform translate-y-0"
                         x-transition:leave-end="translate-y-full">
                        <div class="max-w-lg mx-auto">
                            <button type="submit" 
                                    class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold text-sm py-3 rounded-xl transition shadow-md flex items-center justify-center gap-1.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Guardar Cambios
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
        
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Al reportar un problema, recargamos la página para actualizar el estado visual de la guía en el panel del repartidor
        $(document).on('documentProblemStored', function () {
            window.location.reload();
        });

        // Handler para guardar problema desde textarea inline
        $(document).on('click', '.btn-save-inline-problem', function (e) {
            e.preventDefault();
            const $btn = $(this);
            const $container = $btn.closest('[x-data]');
            const shipmentId = $btn.data('shipment-id');
            const comment = $container.find('.inline-problem-comment').val().trim();

            if (!comment) {
                alert('Debe escribir un comentario para reportar el problema.');
                return;
            }

            $btn.prop('disabled', true).text('Guardando...');

            $.ajax({
                url: '/documents/problem',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    model_type: 'shipment',
                    model_id: shipmentId,
                    is_active: '1',
                    comment: comment
                },
                success: function () {
                    // Emitir evento para recargar la página
                    $(document).trigger('documentProblemStored');
                },
                error: function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'No se pudo guardar el problema.'));
                    $btn.prop('disabled', false).text('Guardar');
                }
            });
        });
    });
</script>

@include('partials._modal_shipment_problems')

@endsection
