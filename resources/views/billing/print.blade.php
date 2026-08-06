<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Factura {{ $invoice->numero }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0; }
            @page { margin: 0.3cm; size: A4 landscape; }
            .no-print { display: none !important; }
            .invoice-container {
                width: 100%;
                margin: 0;
                padding: 0;
                border: none;
            }
        }
        body { background: #f3f4f6; color: #000; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; font-size: 11px; }
        .invoice-container { 
            width: 277mm; 
            margin: 20px auto; 
            background: white; 
            padding: 10mm 12mm; 
            box-sizing: border-box; 
            box-shadow: none; 
            border-radius: 0; 
            border: 1px solid #e5e7eb;
        }
        table { width: 100%; border-spacing: 0; }
        th, td { border: 0.5px solid #e5e7eb; padding: 4px 6px !important; }
        table.dataTable { margin: 0 !important; width: 100% !important; border-collapse: collapse !important; }
        .dt-buttons { display: inline-flex !important; gap: 8px; }
        .buttons-colvis { background: #4b5563 !important; color: white !important; font-weight: bold; border-radius: 4px; padding: 4px 12px !important; border: none !important; font-size: 12px !important; }
        @media print {
            .dt-buttons { display: none !important; }
        }
    </style>
    <!-- DataTables CDN dependencies for exports -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
</head>
<body class="text-xs">
    <div class="print-actions no-print text-center p-2 bg-gray-800 text-white flex justify-center items-center gap-4 sticky top-0 z-50">
        <span class="font-bold text-sm mr-4">Factura #{{ $invoice->numero }}</span>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-500 px-4 py-1.5 rounded text-white font-bold transition flex items-center gap-1.5">🖨️ Imprimir / Guardar PDF</button>
        <button id="btn-export-excel" class="bg-green-600 hover:bg-green-500 px-4 py-1.5 rounded text-white font-bold transition flex items-center gap-1.5">📊 Exportar a Excel</button>
        <button id="btn-export-pdf" class="bg-red-600 hover:bg-red-500 px-4 py-1.5 rounded text-white font-bold transition flex items-center gap-1.5">📄 Exportar a PDF (Horizontal)</button>
        <div id="colvis-container" class="inline-block"></div>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-500 px-4 py-1.5 rounded text-white font-bold transition ml-4">Cerrar</button>
    </div>

    @php
        $totalFlete = $invoice->shipments->sum('flete');
        $totalSeguro = $invoice->shipments->sum('seguro');
        $totalComision = $invoice->shipments->sum('monto_contra_reembolso');
        $totalRetencion = $invoice->shipments->sum('retencion_mercaderia');
        $totalOtros = $invoice->shipments->sum('otros_cargos');
        $totalTotal = $invoice->shipments->sum('total');
    @endphp

    <div class="invoice-container bg-white">
        <!-- Header -->
        <div class="flex justify-between items-start mb-6 border-b border-gray-800 pb-4">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo_amicci.png') }}" alt="AMICCI" class="h-12 w-auto">
                <div>
                    <h1 class="text-xl font-black uppercase tracking-tighter text-gray-900">Factura / Liquidación</h1>
                    <p class="text-xs text-gray-500 font-bold mt-0.5">{{ $invoice->company?->name }}</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-2xl font-black text-blue-600 tracking-widest"># {{ $invoice->numero }}</div>
                <div class="text-[10px] text-gray-700 mt-1">Fecha Emisión: <strong>{{ $invoice->fecha_factura?->format('d/m/Y') ?? '-' }}</strong></div>
            </div>
        </div>

        <!-- Master Data -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 bg-gray-50 p-3 rounded border border-gray-200 text-xs">
            <div>
                <span class="text-gray-500 font-bold uppercase block text-[10px]">Cliente</span>
                <span class="font-black text-sm text-gray-900">{{ $invoice->party?->name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500 font-bold uppercase block text-[10px]">Nº Recibo</span>
                <span class="font-black text-sm text-gray-900">{{ $invoice->numero_recibo ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500 font-bold uppercase block text-[10px]">Fecha Cobro</span>
                <span class="font-black text-sm text-gray-900">{{ $invoice->fecha_cobro?->format('d/m/Y') ?? '—' }}</span>
            </div>
            <div>
                <span class="text-gray-500 font-bold uppercase block text-[10px]">Estado</span>
                <span class="font-black text-sm text-gray-900 uppercase">
                    {{ $invoice->cobrada ? '✓ Cobrada' : 'Pendiente' }}
                </span>
            </div>
            @if($invoice->notes ?? $invoice->notas)
            <div class="col-span-2 md:col-span-4 mt-2 border-t pt-2">
                <span class="text-gray-500 font-bold uppercase block text-[10px]">Notas</span>
                <span class="text-gray-800">{{ $invoice->notes ?? $invoice->notas }}</span>
            </div>
            @endif
        </div>

        <!-- Table of Shipments -->
        <div class="mb-6">
            <h3 class="text-sm font-bold text-gray-900 mb-3 uppercase tracking-wider border-b pb-1">Guías Incluidas</h3>
            <table id="invoice-print-table" class="w-full text-[8.5px] border-collapse">
                <thead>
                    <tr class="bg-gray-950 text-white uppercase font-bold text-left">
                        <th class="p-1 text-center whitespace-nowrap w-14">Fecha</th>
                        <th class="p-1 text-center whitespace-nowrap w-14">F.Entrega</th>
                        <th class="p-1.5 whitespace-nowrap"># Guía</th>
                        <th class="p-1.5 text-center whitespace-nowrap">Bultos</th>
                        <th class="p-1.5 whitespace-nowrap">Remito</th>
                        <th class="p-1.5 whitespace-nowrap">Remitente</th>
                        <th class="p-1.5 whitespace-nowrap">Destinatario</th>
                        <th class="p-1.5 whitespace-nowrap">Flete</th>
                        <th class="p-1.5 whitespace-nowrap">Seguro</th>
                        <th class="p-1.5 whitespace-nowrap">Com. Contr.</th>
                        <th class="p-1.5 whitespace-nowrap">Retiro</th>
                        <th class="p-1.5 whitespace-nowrap">Otros</th>
                        <th class="p-1.5 font-bold text-right whitespace-nowrap">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($invoice->shipments as $shipment)
                    <tr class="hover:bg-gray-50">
                        <td class="p-1 text-center whitespace-nowrap text-gray-700 w-14">{{ $shipment->fecha?->format('d/m/y') ?? '-' }}</td>
                        <td class="p-1 text-center whitespace-nowrap text-gray-700 w-14">{{ $shipment->fecha_entrega?->format('d/m/y') ?? '—' }}</td>
                        <td class="p-1.5 font-bold text-blue-600 whitespace-nowrap">{{ $shipment->numero }}</td>
                        <td class="p-1.5 text-center text-gray-700 whitespace-nowrap">{{ $shipment->items->sum('cantidad') }}</td>
                        <td class="p-1.5 text-gray-700 max-w-[150px]">
                            @php
                                $remitos = $shipment->items->pluck('numero_remito')->filter()->values();
                            @endphp
                            @if($remitos->isNotEmpty())
                                {!! $remitos->chunk(3)->map(fn($chunk) => $chunk->join(', '))->join('<br>') !!}
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-1.5 uppercase text-gray-700 max-w-[120px] leading-tight">
                            @php
                                $senderWords = explode(' ', $shipment->sender?->name ?? '-');
                            @endphp
                            @if(count($senderWords) > 2)
                                {!! implode(' ', array_slice($senderWords, 0, (int) ceil(count($senderWords) / 2))) !!}<br>{!! implode(' ', array_slice($senderWords, (int) ceil(count($senderWords) / 2))) !!}
                            @else
                                {{ $shipment->sender?->name ?? '-' }}
                            @endif
                        </td>
                        <td class="p-1.5 uppercase text-gray-700 max-w-[120px] leading-tight">
                            @php
                                $recipientWords = explode(' ', $shipment->recipient?->name ?? '-');
                            @endphp
                            @if(count($recipientWords) > 2)
                                {!! implode(' ', array_slice($recipientWords, 0, (int) ceil(count($recipientWords) / 2))) !!}<br>{!! implode(' ', array_slice($recipientWords, (int) ceil(count($recipientWords) / 2))) !!}
                            @else
                                {{ $shipment->recipient?->name ?? '-' }}
                            @endif
                        </td>
                        <td class="p-1.5 text-left text-gray-700 whitespace-nowrap">$ {{ number_format($shipment->flete, 2, ',', '.') }}</td>
                        <td class="p-1.5 text-left text-gray-700 whitespace-nowrap">$ {{ number_format($shipment->seguro, 2, ',', '.') }}</td>
                        <td class="p-1.5 text-left text-gray-700 whitespace-nowrap">$ {{ number_format($shipment->monto_contra_reembolso, 2, ',', '.') }}</td>
                        <td class="p-1.5 text-left text-gray-700 whitespace-nowrap">$ {{ number_format($shipment->retencion_mercaderia, 2, ',', '.') }}</td>
                        <td class="p-1.5 text-left text-gray-700 whitespace-nowrap">$ {{ number_format($shipment->otros_cargos, 2, ',', '.') }}</td>
                        <td class="p-1.5 text-right font-mono font-bold text-gray-900 whitespace-nowrap">$ {{ number_format($shipment->total, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-100 text-gray-900">
                        <td colspan="7" class="p-2 text-right uppercase">Totales:</td>
                        <td class="p-2 text-left whitespace-nowrap">$ {{ number_format($totalFlete, 2, ',', '.') }}</td>
                        <td class="p-2 text-left whitespace-nowrap">$ {{ number_format($totalSeguro, 2, ',', '.') }}</td>
                        <td class="p-2 text-left whitespace-nowrap">$ {{ number_format($totalComision, 2, ',', '.') }}</td>
                        <td class="p-2 text-left whitespace-nowrap">$ {{ number_format($totalRetencion, 2, ',', '.') }}</td>
                        <td class="p-2 text-left whitespace-nowrap">$ {{ number_format($totalOtros, 2, ',', '.') }}</td>
                        <td class="p-2 text-right font-mono font-black text-indigo-700 text-sm whitespace-nowrap">
                            $ {{ number_format($totalTotal, 2, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer Info -->
        <div class="text-[9px] text-gray-400 text-center border-t border-gray-100 pt-3 italic">
            Comprobante de factura / liquidación interna - TRANSPORTE AMICCI.
        </div>
    </div>

    <!-- DataTables script resources -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#invoice-print-table').DataTable({
                paging: false,
                searching: false,
                info: false,
                ordering: false,
                dom: 'Brt',
                buttons: [
                    {
                        extend: 'colvis',
                        text: '👁️ Columnas',
                        className: 'btn-colvis-custom'
                    },
                    {
                        extend: 'excelHtml5',
                        title: 'Factura_{{ $invoice->numero }}',
                        footer: true,
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'pdfHtml5',
                        title: 'Factura_{{ $invoice->numero }}',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        footer: true,
                        exportOptions: { columns: ':visible' }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                }
            });

            // Mover el botón colvis a la barra superior y ocultar los demás botones nativos
            $('.buttons-excel, .buttons-pdf').hide();
            $('.buttons-colvis').appendTo('#colvis-container');

            // Enlazar botones personalizados
            $('#btn-export-excel').on('click', function() {
                table.button('.buttons-excel').trigger();
            });
            $('#btn-export-pdf').on('click', function() {
                table.button('.buttons-pdf').trigger();
            });

            // Imprimir automáticamente al cargar
            window.print();

            // Cerrar automáticamente después de imprimir o cancelar
            window.addEventListener('afterprint', function () {
                window.close();
            });
        });
    </script>
</body>
</html>
