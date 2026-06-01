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
                padding: 3mm 5mm;
                box-sizing: border-box;
            }
        }
        body { background: #f3f4f6; color: #000; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; font-size: 10px; }
        .manifest-container { 
            width: 210mm; 
            height: 148.5mm; 
            margin: 20px auto; 
            background: white; 
            padding: 3mm 5mm; 
            box-sizing: border-box; 
            box-shadow: none; 
            border-radius: 0; 
            position: relative; 
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border: 1px solid #e5e7eb;
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
        
        $chunks = $allShipments->chunk(15);
        $totalChunks = $chunks->count();
    @endphp

    @foreach($chunks as $chunkIndex => $shipmentsChunk)
    <div class="manifest-container bg-white">
        <div>
            <!-- Header Compacto -->
            <div class="flex justify-between items-center mb-1.5 border-b border-gray-800 pb-1">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/logo_amicci.png') }}" alt="AMICCI" class="h-10 w-auto">
                    <h1 class="text-xl font-black uppercase tracking-tighter text-gray-900">Hoja de Reparto</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="text-2xl font-black text-blue-600 tracking-widest">{{ $delivery->delivery_number }}</div>
                        <div class="text-xs font-bold text-gray-500 uppercase">Control Interno (Pág. {{ $chunkIndex + 1 }}/{{ $totalChunks }})</div>
                    </div>
                    <div class="text-sm text-gray-800 border-l pl-3">Fecha: <strong class="text-gray-900">{{ now()->format('d/m/Y H:i') }}</strong></div>
                </div>
            </div>

            <!-- Master Data Ultra-Compacto -->
            <div class="mb-1.5 bg-gray-50 p-1.5 rounded border border-gray-200 text-xs space-y-0.5">
                <div class="flex justify-between border-b border-gray-200 pb-0.5">
                    <div>
                        <span class="text-gray-500 font-bold uppercase text-[10px]">Repartidor:</span>
                        <span class="font-black text-sm text-gray-900">{{ $delivery->deliverer->name ?? 'NO ASIGNADO' }}</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="text-gray-500 font-bold uppercase text-[10px]">DNI:</span>
                        <span class="font-black text-sm text-gray-900">{{ $delivery->deliverer->dni ?? '-' }}</span>
                        <span class="mx-2 text-gray-300">|</span>
                        <span class="text-gray-500 font-bold uppercase text-[10px]">Tel:</span>
                        <span class="font-black text-sm text-gray-900">{{ $delivery->deliverer->phone ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 font-bold uppercase text-[10px]">Ubicación:</span>
                        <span class="font-black text-sm text-gray-900">{{ $delivery->location->name ?? '-' }}</span>
                    </div>
                </div>
                <div>
                    <span class="text-gray-500 font-bold uppercase text-[10px]">Vehículo/Patente:</span>
                    <span class="font-black text-sm text-gray-900">{{ $delivery->vehicle_plate ?? '-' }}</span>
                </div>
            </div>

            <!-- Table of Shipments Compacta -->
            <div>
                <table class="w-full text-[9.5px] border-collapse">
                    <thead>
                        <tr class="bg-gray-900 text-white uppercase font-bold">
                            <th class="py-0.5 px-1 text-center w-8">REC</th>
                            <th class="py-0.5 px-1 text-left">N° Guía</th>
                            <th class="py-0.5 px-1 text-left">Remitente</th>
                            <th class="py-0.5 px-1 text-left">Destinatario</th>
                            <th class="py-0.5 px-1 text-center w-10">Bultos</th>
                            <th class="py-0.5 px-1 text-right w-20">Valor Dec. ($)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($shipmentsChunk as $shipment)
                        @php 
                            $bultos = $shipment->items->sum('cantidad');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="py-0.5 px-1 text-center">
                                <div class="w-3.5 h-3.5 border border-gray-400 mx-auto rounded-sm"></div>
                            </td>
                            <td class="py-0.5 px-1 font-bold text-blue-600">{{ $shipment->numero }}</td>
                            <td class="py-0.5 px-1 uppercase text-gray-700 truncate max-w-[140px]">{{ $shipment->sender->name ?? '-' }}</td>
                            <td class="py-0.5 px-1 uppercase text-gray-700 truncate max-w-[140px]">{{ $shipment->recipient->name ?? '-' }}</td>
                            <td class="py-0.5 px-1 text-center font-bold">{{ $bultos }}</td>
                            <td class="py-0.5 px-1 text-right font-mono">{{ number_format($shipment->flete, 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @if($chunkIndex == $totalChunks - 1)
                    <tfoot>
                        <tr class="font-bold bg-gray-100">
                            <td colspan="4" class="py-0.5 px-1 text-right uppercase">Totales</td>
                            <td class="py-0.5 px-1 text-center font-black">{{ $totalBultosGeneral }}</td>
                            <td class="py-0.5 px-1 text-right font-mono font-black">$ {{ number_format($totalFleteGeneral, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div>
            @if($chunkIndex < $totalChunks - 1)
                <div class="text-right text-[8px] text-gray-500 font-bold italic">
                    Continúa en la siguiente página...
                </div>
            @endif
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
