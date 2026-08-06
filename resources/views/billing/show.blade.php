@extends('layouts.app')

@section('content')
<div class="py-12" x-data="{ showPayModal: false }">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Cabecera de la factura --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 mb-4">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                        Factura # {{ $invoice->numero }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $invoice->company?->name }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    @if($invoice->cobrada)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            ✓ Cobrada
                        </span>
                        <form method="POST" action="{{ route('billing.unpay', $invoice) }}"
                              onsubmit="return confirm('¿Confirmar la reversión del cobro de la factura #{{ $invoice->numero }}? Esto limpiará los datos de recibo y marcará las guías como pendientes.')">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 transition">
                                ↺ Revertir Cobro
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            Pendiente
                        </span>
                        <button type="button" @click="showPayModal = true"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                            Marcar como Cobrada
                        </button>
                    @endif
                    @if(!$invoice->cobrada && $invoice->shipments->count() === 0 && auth()->user()?->hasAnyRole(['admin', 'supervisor']))
                        <form method="POST" action="{{ route('billing.destroy', $invoice) }}"
                              onsubmit="return confirm('¿Eliminar esta factura? Esta acción no se puede deshacer.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition">
                                🗑️ Eliminar Factura
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('billing.print', $invoice) }}" onclick="window.open(this.href, '_blank'); return false;"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        🖨️ Imprimir PDF
                    </a>
                    <a href="{{ route('billing.excel', $invoice) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                        📊 Exportar Excel
                    </a>
                    <a href="{{ route('billing.invoices') }}" class="text-sm text-indigo-600 hover:underline">← Ver Facturas</a>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Cliente</p>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $invoice->party?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Fecha Factura</p>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $invoice->fecha_factura?->format('d/m/Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Nº Recibo</p>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $invoice->numero_recibo ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Fecha Cobro</p>
                    <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $invoice->fecha_cobro?->format('d/m/Y') ?? '—' }}</p>
                </div>
                @if($invoice->notas)
                <div class="col-span-2 md:col-span-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase font-medium">Notas</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $invoice->notas }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Guías asociadas --}}
        @php
            $totalFlete = $invoice->shipments->sum('flete');
            $totalSeguro = $invoice->shipments->sum('seguro');
            $totalComision = $invoice->shipments->sum('monto_contra_reembolso');
            $totalRetencion = $invoice->shipments->sum('retencion_mercaderia');
            $totalOtros = $invoice->shipments->sum('otros_cargos');
            $totalTotal = $invoice->shipments->sum('total');
        @endphp
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                Guías incluidas ({{ $invoice->shipments->count() }})
            </h3>
            <div class="overflow-x-auto">
                <table id="invoice-shipments-table" data-invoice-number="{{ $invoice->numero }}" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300">
                        <tr>
                            <th class="p-1 border-b text-center whitespace-nowrap w-16">Fecha</th>
                            <th class="p-1 border-b text-center whitespace-nowrap w-16">F. Entrega</th>
                            <th class="p-2 border-b text-left whitespace-nowrap"># Guía</th>
                            <th class="p-2 border-b text-center whitespace-nowrap">Bultos</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Remito</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Remitente</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Destinatario</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Ubicación</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Flete</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Seguro</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Com. Contr.</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Retiro</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Otros</th>
                            <th class="p-2 border-b text-left font-bold text-indigo-600 whitespace-nowrap">Total</th>
                            @if(!$invoice->cobrada && auth()->user()?->hasAnyRole(['admin', 'supervisor']))
                            <th class="p-2 border-b text-center whitespace-nowrap">Acciones</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($invoice->shipments as $shipment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="p-1 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap text-xs w-16">{{ $shipment->fecha?->format('d/m/y') ?? '-' }}</td>
                            <td class="p-1 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap text-xs w-16">{{ $shipment->fecha_entrega?->format('d/m/y') ?? '—' }}</td>
                            <td class="p-2 font-mono font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $shipment->numero }}</td>
                            <td class="p-2 text-center text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $shipment->items->sum('cantidad') }}</td>
                            <td class="p-2 text-gray-700 dark:text-gray-300 max-w-[160px]">
                                @php
                                    $remitos = $shipment->items->pluck('numero_remito')->filter()->values();
                                @endphp
                                @if($remitos->isNotEmpty())
                                    {!! $remitos->chunk(3)->map(fn($chunk) => $chunk->join(', '))->join('<br>') !!}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-2 text-gray-700 dark:text-gray-300 max-w-[130px] leading-tight" title="{{ $shipment->sender?->name ?? '-' }}">
                                @php
                                    $senderWords = explode(' ', $shipment->sender?->name ?? '-');
                                @endphp
                                @if(count($senderWords) > 2)
                                    {!! implode(' ', array_slice($senderWords, 0, (int) ceil(count($senderWords) / 2))) !!}<br>{!! implode(' ', array_slice($senderWords, (int) ceil(count($senderWords) / 2))) !!}
                                @else
                                    {{ $shipment->sender?->name ?? '-' }}
                                @endif
                            </td>
                            <td class="p-2 text-gray-700 dark:text-gray-300 max-w-[130px] leading-tight" title="{{ $shipment->recipient?->name ?? '-' }}">
                                @php
                                    $recipientWords = explode(' ', $shipment->recipient?->name ?? '-');
                                @endphp
                                @if(count($recipientWords) > 2)
                                    {!! implode(' ', array_slice($recipientWords, 0, (int) ceil(count($recipientWords) / 2))) !!}<br>{!! implode(' ', array_slice($recipientWords, (int) ceil(count($recipientWords) / 2))) !!}
                                @else
                                    {{ $shipment->recipient?->name ?? '-' }}
                                @endif
                            </td>
                            <td class="p-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $shipment->ubicacion_actual ?? '-' }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->flete, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->seguro, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->monto_contra_reembolso, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->retencion_mercaderia, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->otros_cargos, 2, ',', '.') }}</td>
                            <td class="p-2 text-left font-bold text-indigo-700 dark:text-indigo-300 whitespace-nowrap">$ {{ number_format($shipment->total, 2, ',', '.') }}</td>
                            @if(!$invoice->cobrada && auth()->user()?->hasAnyRole(['admin', 'supervisor']))
                            <td class="p-2 text-center whitespace-nowrap">
                                <form method="POST" action="{{ route('billing.detach-shipment', [$invoice, $shipment->id]) }}"
                                      class="inline-block"
                                      onsubmit="return confirm('¿Quitar la guía {{ $shipment->numero }} de esta factura?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center p-1.5 rounded-md bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-colors" title="Quitar de la factura">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ !$invoice->cobrada && auth()->user()?->hasAnyRole(['admin', 'supervisor']) ? 15 : 14 }}" class="p-4 text-center text-gray-500 dark:text-gray-400">Sin guías asociadas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-indigo-50 dark:bg-indigo-900/30 border-t-2 border-indigo-200 dark:border-indigo-700 font-bold">
                        <tr>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300">TOTAL FACTURA:</td>
                            <td class="p-2"></td>
                            <td class="p-2"></td>
                            <td class="p-2"></td>
                            <td class="p-2"></td>
                            <td class="p-2"></td>
                            <td class="p-2"></td>
                            <td class="p-2"></td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalFlete, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalSeguro, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalComision, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalRetencion, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalOtros, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-indigo-700 dark:text-indigo-300 text-lg whitespace-nowrap">
                                $ {{ number_format($totalTotal, 2, ',', '.') }}
                            </td>
                            @if(!$invoice->cobrada && auth()->user()?->hasAnyRole(['admin', 'supervisor']))
                            <td class="p-2"></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    {{-- Modal de Cobro --}}
    <div x-show="showPayModal" style="display: none;" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showPayModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showPayModal = false"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <!-- Modal panel -->
            <div x-show="showPayModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <form method="POST" action="{{ route('billing.pay', $invoice) }}">
                    @csrf
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                    Cobrar Factura #{{ $invoice->numero }}
                                </h3>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    <p>Ingrese los datos del cobro. Todas las guías asociadas se marcarán como cobradas.</p>
                                </div>
                                
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="numero_recibo" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Recibo (Opcional)</label>
                                        <input type="text" name="numero_recibo" id="numero_recibo" 
                                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md" 
                                               placeholder="Ej. 0001-00001234">
                                    </div>
                                    
                                    <div>
                                        <label for="fecha_cobro" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fecha de Cobro</label>
                                        <input type="date" name="fecha_cobro" id="fecha_cobro" required
                                               value="{{ date('Y-m-d') }}"
                                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                            Confirmar Cobro
                        </button>
                        <button type="button" @click="showPayModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/billing/show.js')
@endsection
