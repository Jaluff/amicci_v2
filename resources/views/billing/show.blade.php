@extends('layouts.app')

@section('content')
<div class="py-12">
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
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                            Pendiente
                        </span>
                        <form method="POST" action="{{ route('billing.pay', $invoice) }}"
                              onsubmit="return confirm('¿Confirmar cobro de la factura #{{ $invoice->numero }}? Todas las guías asociadas se marcarán como cobradas.')">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 transition">
                                Marcar como Cobrada
                            </button>
                        </form>
                        @can('admin')
                        <a href="{{ route('billing.edit', $invoice) }}"
                           class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-600 transition">
                            Editar
                        </a>
                        @endcan
                    @endif
                    <a href="{{ route('billing.print', $invoice) }}" target="_blank"
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
                            <th class="p-2 border-b text-left whitespace-nowrap">Fecha</th>
                            <th class="p-2 border-b text-left whitespace-nowrap"># Guía</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Remitente</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Destinatario</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Ubicación</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Flete</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Seguro</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Com. Contr.</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Ret. Merc.</th>
                            <th class="p-2 border-b text-left whitespace-nowrap">Otros Conc.</th>
                            <th class="p-2 border-b text-left font-bold text-indigo-600 whitespace-nowrap">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($invoice->shipments as $shipment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <td class="p-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $shipment->fecha?->format('d/m/Y') ?? '-' }}</td>
                            <td class="p-2 font-mono font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap">{{ $shipment->numero }}</td>
                            <td class="p-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $shipment->sender?->name ?? '-' }}</td>
                            <td class="p-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $shipment->recipient?->name ?? '-' }}</td>
                            <td class="p-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ $shipment->ubicacion_actual ?? '-' }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->flete, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->seguro, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->monto_contra_reembolso, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->retencion_mercaderia, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-700 dark:text-gray-300 whitespace-nowrap">$ {{ number_format($shipment->otros_cargos, 2, ',', '.') }}</td>
                            <td class="p-2 text-left font-bold text-indigo-700 dark:text-indigo-300 whitespace-nowrap">$ {{ number_format($shipment->total, 2, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="p-4 text-center text-gray-500 dark:text-gray-400">Sin guías asociadas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-indigo-50 dark:bg-indigo-900/30 border-t-2 border-indigo-200 dark:border-indigo-700 font-bold">
                        <tr>
                            <td colspan="5" class="p-2 text-left text-gray-700 dark:text-gray-300">TOTAL FACTURA:</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalFlete, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalSeguro, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalComision, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalRetencion, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-gray-800 dark:text-gray-200 whitespace-nowrap">$ {{ number_format($totalOtros, 2, ',', '.') }}</td>
                            <td class="p-2 text-left text-indigo-700 dark:text-indigo-300 text-lg whitespace-nowrap">
                                $ {{ number_format($totalTotal, 2, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
@vite('resources/js/pages/billing/show.js')
@endsection
