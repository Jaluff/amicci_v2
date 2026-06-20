@extends($layout ?? 'layouts.app')

@section('content')
@php
    $isIframe = request()->has('iframe');
@endphp
<div class="py-2 {{ $isIframe ? 'max-w-full' : 'max-w-lg' }} mx-auto">
    <div class="space-y-4">
        
        @if(!request()->has('iframe'))
        {{-- Welcome Header --}}
        <div class="bg-gradient-to-r from-orange-500 to-amber-600 rounded-2xl p-5 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 opacity-10">
                <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 13H5v-2h14v2z"/>
                </svg>
            </div>
            <h1 class="text-lg font-bold tracking-tight">Hola, {{ auth()->user()->name }}</h1>
        </div>
        @endif

        @if(isset($error))
            {{-- Profile Association Error --}}
            <div class="bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900 rounded-xl p-4 text-center">
                <div class="text-red-500 mb-2">
                    <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Asociación Requerida</h3>
                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $error }}</p>
            </div>
        @else
            {{-- Active Deliveries Section --}}
            <div>
                <div class="flex items-center justify-between mb-4 px-1">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-pulse"></span>
                        Mis Repartos Activos
                    </h2>
                    <span class="text-xs bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300 px-2.5 py-1 rounded-full font-bold">
                        {{ $deliveries->count() }} repartos
                    </span>
                </div>

                @if($deliveries->isEmpty())
                    {{-- Empty State --}}
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center shadow-sm">
                        <div class="text-gray-400 dark:text-gray-600 mb-3">
                            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300">No tienes repartos asignados</h3>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Los nuevos repartos aparecerán aquí cuando sean iniciados.</p>
                    </div>
                @else
                    {{-- Deliveries List --}}
                    <div class="space-y-4">
                        @foreach($deliveries as $delivery)
                            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition duration-200 flex flex-col justify-between gap-3">
                                
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 block uppercase font-semibold">Reparto N°</span>
                                        <h3 class="text-xl font-mono font-bold text-gray-800 dark:text-gray-200">{{ $delivery->delivery_number }}</h3>
                                    </div>
                                    <span class="bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800 text-xs font-bold px-2.5 py-1 rounded uppercase">
                                        En reparto
                                    </span>
                                </div>
 
                                <div class="grid grid-cols-2 gap-4 text-sm border-t border-b border-gray-100 dark:border-gray-700/60 py-3.5 my-1">
                                    <div>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 block">Sucursal:</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $delivery->location->name ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 block">Vehículo/Patente:</span>
                                        <span class="font-mono font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $delivery->vehicle_plate ?? 'N/A' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 block">Guías asignadas:</span>
                                        <span class="font-bold text-gray-900 dark:text-gray-150 text-sm">{{ $delivery->shipments_count ?? 0 }}</span>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 dark:text-gray-500 block">Fecha despacho:</span>
                                        <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $delivery->dispatch_date ? $delivery->dispatch_date->format('d/m/Y') : '-' }}</span>
                                    </div>
                                </div>
 
                                <div class="flex justify-end mt-1">
                                    <a href="{{ route('deliverer.show', $delivery) }}{{ $isIframe ? '?iframe=1' : '' }}" 
                                       class="w-full text-center bg-orange-600 hover:bg-orange-700 text-white font-bold text-base py-3.5 rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                        Gestionar Entregas
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
        
    </div>
</div>
@endsection
