@extends('layouts.app')

@section('content')
@php
    $backUrl = auth()->user()->hasAnyRole(['admin', 'supervisor']) 
        ? route('deliveries.index') 
        : route('deliverer.index');
@endphp
<div class="py-6 max-w-lg mx-auto" x-data="{ 
    selectedShipments: {{ json_encode($delivery->shipments->where('ubicacion_actual', 'Entregado')->pluck('id')->map(fn($id) => (string)$id)->values()->toArray()) }},
    originalSelection: {{ json_encode($delivery->shipments->where('ubicacion_actual', 'Entregado')->pluck('id')->map(fn($id) => (string)$id)->values()->toArray()) }},
    hasChanges() {
        return JSON.stringify([...this.selectedShipments].sort()) !== JSON.stringify([...this.originalSelection].sort());
    }
}">
    <div class="space-y-4 pb-20"> {{-- pb-20 prevents sticky footer from overlapping content --}}
        
        {{-- Back Button & Header --}}
        <div class="flex items-center gap-2">
            <a href="{{ $backUrl }}" 
               class="p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>
            </a>
            <div>
                <span class="text-[9px] text-gray-400 dark:text-gray-500 block uppercase font-semibold">Gestionar Planilla</span>
                <h1 class="text-xl font-mono font-bold text-gray-800 dark:text-gray-100">Reparto #{{ $delivery->delivery_number }}</h1>
            </div>
        </div>

        {{-- Delivery Details Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm text-xs grid grid-cols-2 gap-2 text-gray-600 dark:text-gray-400">
            <div>
                <span class="text-gray-400 block text-[9px] uppercase font-semibold">Patente</span>
                <span class="font-mono font-bold text-gray-700 dark:text-gray-300">{{ $delivery->vehicle_plate ?? 'N/A' }}</span>
            </div>
            <div>
                <span class="text-gray-400 block text-[9px] uppercase font-semibold">Sucursal</span>
                <span class="font-bold text-gray-700 dark:text-gray-300">{{ $delivery->location->name ?? 'N/A' }}</span>
            </div>
        </div>

        {{-- Shipments Section --}}
        <div>
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Listado de Guías
                </h2>
                <span class="text-[10px] text-gray-400">
                    {{ $delivery->shipments->where('ubicacion_actual', 'Entregado')->count() }} / {{ $delivery->shipments->count() }} entregadas
                </span>
            </div>

            @if($delivery->shipments->isEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 text-center shadow-sm">
                    <p class="text-xs text-gray-400 italic">No hay guías asignadas a este reparto.</p>
                </div>
            @else
                <form action="{{ route('deliverer.confirm', $delivery) }}" method="POST" id="delivery-form">
                    @csrf
                    <div class="space-y-3">
                        @foreach($delivery->shipments as $shipment)
                            
                            {{-- Shipment Card --}}
                            <label class="block cursor-pointer select-none">
                                <div class="relative bg-white dark:bg-gray-800 rounded-xl border p-4 shadow-sm transition-all duration-200 flex items-start gap-3"
                                     :class="selectedShipments.includes('{{ $shipment->id }}')
                                         ? 'border-green-200 dark:border-green-900 bg-green-50/20 dark:bg-green-950/10'
                                         : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'">
                                    
                                    {{-- Checkbox / Status Indicator --}}
                                    <div class="pt-0.5 shrink-0">
                                        <input type="checkbox" 
                                               name="shipment_ids[]" 
                                               value="{{ $shipment->id }}"
                                               x-model="selectedShipments"
                                               class="w-5 h-5 text-orange-600 border-gray-300 dark:border-gray-600 rounded focus:ring-orange-500 focus:ring-offset-2 dark:bg-gray-700">
                                    </div>

                                    {{-- Shipment Information --}}
                                    <div class="flex-1 min-w-0 text-xs">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-base font-mono font-bold text-gray-800 dark:text-gray-200">#{{ $shipment->numero }}</span>
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded uppercase font-sans"
                                                  :class="selectedShipments.includes('{{ $shipment->id }}')
                                                      ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                                      : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400'"
                                                  x-text="selectedShipments.includes('{{ $shipment->id }}') ? 'Entregado' : 'En reparto'">
                                            </span>
                                        </div>

                                        <div class="mt-2 space-y-1 text-[11px] text-gray-600 dark:text-gray-400">
                                            <div>
                                                <strong class="text-gray-400 dark:text-gray-500 text-[9px] uppercase block tracking-wider leading-none">Destinatario</strong>
                                                <span class="font-medium text-gray-700 dark:text-gray-300 uppercase leading-relaxed">{{ $shipment->recipient->name ?? '-' }}</span>
                                            </div>
                                            
                                            <div class="pt-1">
                                                <strong class="text-gray-400 dark:text-gray-500 text-[9px] uppercase block tracking-wider leading-none">Dirección de Entrega</strong>
                                                <span class="text-gray-700 dark:text-gray-300 leading-relaxed block">{{ $shipment->recipient->address ?? $shipment->recipient->address_line1 ?? '-' }}</span>
                                            </div>
                                            
                                            <div class="pt-1.5 flex justify-between items-center text-[10px] text-gray-400">
                                                <span>Bultos: <strong class="text-gray-700 dark:text-gray-300 font-bold">{{ $shipment->bultos }}</strong></span>
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
@endsection
