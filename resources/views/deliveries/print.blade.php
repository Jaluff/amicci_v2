<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de Reparto - Reparto {{ $delivery->delivery_number ?? '' }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; margin: 0; }
            @page { margin: 0; size: A4 portrait; }
            .no-print { display: none !important; }
            .manifest-container {
                height: 148.5mm;
                page-break-inside: avoid;
                page-break-after: always;
                box-shadow: none;
                border-radius: 0;
                margin: 0;
                padding: 8mm 10mm;
                box-sizing: border-box;
                border-bottom: 1px dashed #bbb;
            }
        }
        body { background: #f3f4f6; color: #000; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; font-size: 10px; }
        .manifest-container { 
            width: 210mm; 
            height: 148.5mm; 
            margin: 20px auto; 
            background: white; 
            padding: 8mm 10mm; 
            box-sizing: border-box; 
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); 
            border-radius: 8px; 
            position: relative; 
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .border-dark { border: 1px solid #000; }
        table { width: 100%; border-spacing: 0; }
        th, td { border: 0.5px solid #e5e7eb; }
    </style>
</head>
<body class="text-xs">
    <div class="print-actions no-print text-center p-2 bg-gray-800 text-white flex justify-center gap-4 sticky top-0 z-50">
        <span class="font-bold">Vista Previa de Hoja de Reparto</span>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-500 px-4 py-1 rounded text-white font-bold transition">Imprimir Hoja de Reparto</button>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-500 px-4 py-1 rounded text-white font-bold transition">Cerrar</button>
    </div>

    @php
        $allShipments = $delivery->shipments;
        $totalBultosGeneral = 0;
        $totalFleteGeneral = 0;
        
        foreach ($allShipments as $s) {
            $totalBultosGeneral += $s->items->sum('cantidad');
            $totalFleteGeneral += $s->flete;
        }
        
        $chunks = $allShipments->chunk(6);
        $totalChunks = $chunks->count();
    @endphp

    @foreach($chunks as $chunkIndex => $shipmentsChunk)
    <div class="manifest-container bg-white">
        <div>
            <!-- Header Compacto -->
            <div class="flex justify-between items-center mb-3 border-b border-gray-800 pb-1">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/logo_amicci.png') }}" alt="AMICCI" class="h-7 w-auto">
                    <h1 class="text-base font-black uppercase tracking-tighter text-gray-900">Hoja de Reparto</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-lg font-black text-blue-600 tracking-widest">{{ preg_replace('/[^0-9]/', '', $delivery->delivery_number ?? '0000') }}</div>
                        <div class="text-[8px] font-bold text-gray-500 uppercase">Control Interno (Pág. {{ $chunkIndex + 1 }}/{{ $totalChunks }})</div>
                    </div>
                    <div class="text-[9px] text-gray-700 border-l pl-3">Fecha: <strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
                </div>
            </div>

            <!-- Master Data Ultra-Compacto -->
            <div class="mb-3 bg-gray-50 p-2 rounded border border-gray-200 text-[9px] space-y-1">
                <div class="flex justify-between border-b border-gray-200 pb-1">
                    <div>
                        <span class="text-gray-500 font-bold uppercase">Repartidor:</span>
                        <span class="font-black">{{ $delivery->deliverer->name ?? 'NO ASIGNADO' }}</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="text-gray-500 font-bold uppercase">DNI:</span>
                        <span class="font-black">{{ $delivery->deliverer->dni ?? '-' }}</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="text-gray-500 font-bold uppercase">Tel:</span>
                        <span class="font-black">{{ $delivery->deliverer->phone ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-bold uppercase">Ubicación:</span>
                        <span class="font-black">{{ $delivery->location->name ?? '-' }}</span>
                    </div>
                </div>
                <div>
                    <span class="text-gray-500 font-bold uppercase">Vehículo/Patente:</span>
                    <span class="font-black">{{ $delivery->vehicle_plate ?? '-' }}</span>
                </div>
            </div>

            <!-- Table of Shipments Compacta -->
            <div class="mb-3">
                <table class="w-full text-[9px] border-collapse">
                    <thead>
                        <tr class="bg-gray-900 text-white uppercase font-bold">
                            <th class="p-1 text-center w-10">REC</th>
                            <th class="p-1 text-left">N° Guía</th>
                            <th class="p-1 text-left">Remitente</th>
                            <th class="p-1 text-left">Destinatario</th>
                            <th class="p-1 text-center w-12">Bultos</th>
                            <th class="p-1 text-right w-24">Valor Declarado ($)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($shipmentsChunk as $shipment)
                        @php 
                            $bultos = $shipment->items->sum('cantidad');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="p-1 text-center">
                                <div class="w-3.5 h-3.5 border border-gray-400 mx-auto rounded-sm"></div>
                            </td>
                            <td class="p-1 font-bold text-blue-600">{{ $shipment->numero }}</td>
                            <td class="p-1 uppercase text-gray-700 truncate max-w-[120px]">{{ $shipment->sender->name ?? '-' }}</td>
                            <td class="p-1 uppercase text-gray-700 truncate max-w-[120px]">{{ $shipment->recipient->name ?? '-' }}</td>
                            <td class="p-1 text-center font-bold">{{ $bultos }}</td>
                            <td class="p-1 text-right font-mono">{{ number_format($shipment->flete, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($chunkIndex == $totalChunks - 1)
                    <tfoot>
                        <tr class="font-bold bg-gray-100">
                            <td colspan="4" class="p-1 text-right uppercase">Totales Generales</td>
                            <td class="p-1 text-center font-black">{{ $totalBultosGeneral }}</td>
                            <td class="p-1 text-right font-mono font-black">$ {{ number_format($totalFleteGeneral, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div>
            @if($chunkIndex < $totalChunks - 1)
                <div class="text-right text-[9px] text-gray-500 font-bold italic mb-2">
                    Continúa en la siguiente página...
                </div>
            @else
                <!-- Firmas Compactas -->
                <div class="flex justify-end mt-4 mb-2">
                    <div class="w-48 border-t border-gray-400 text-center pt-1">
                        <span class="text-[9px] text-gray-500 uppercase font-bold">Firma y Aclaración Recepción</span>
                    </div>
                </div>
            @endif

            <!-- Footer Legal -->
            <div class="text-[8px] text-gray-400 text-center border-t border-gray-100 pt-1 italic">
                Hoja de reparto interna - TRANSPORTE AMICCI.
            </div>
        </div>
    </div>
    @endforeach

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
