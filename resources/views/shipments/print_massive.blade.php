<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hoja de Ruta - Despacho {{ $dispatch->dispatch_number ?? '' }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 0.5cm; size: A4 landscape; }
            .no-print { display: none !important; }
        }
        body { background: #f3f4f6; color: #000; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; font-size: 10px; }
        .manifest-container { max-width: 28.7cm; margin: 0.5cm auto; background: white; padding: 0.5cm 1cm; box-sizing: border-box; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border-radius: 8px; }
        .border-dark { border: 1px solid #000; }
        table { width: 100%; border-spacing: 0; }
        th, td { border: 0.5px solid #e5e7eb; }
    </style>
</head>
<body class="text-xs">
    <div class="print-actions no-print text-center p-2 bg-gray-800 text-white flex justify-center gap-4 sticky top-0 z-50">
        <span class="font-bold">Vista Previa de Hoja de Ruta</span>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-500 px-4 py-1 rounded text-white font-bold transition">Imprimir Hoja de Ruta</button>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-500 px-4 py-1 rounded text-white font-bold transition">Cerrar</button>
    </div>

    <div class="manifest-container bg-white">
        <!-- Header Compacto -->
        <div class="flex justify-between items-center mb-4 border-b border-gray-800 pb-2">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/logo_amicci.png') }}" alt="AMICCI" class="h-8 w-auto">
                <h1 class="text-lg font-black uppercase tracking-tighter text-gray-900">Hoja de Ruta</h1>
            </div>
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <div class="text-xl font-black text-blue-600 tracking-widest">{{ preg_replace('/[^0-9]/', '', $dispatch->dispatch_number ?? '0000') }}</div>
                    <div class="text-[9px] font-bold text-gray-500 uppercase">Control Interno</div>
                </div>
                <div class="text-[10px] text-gray-700 border-l pl-4">Fecha: <strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
            </div>
        </div>

        <!-- Master Data Ultra-Compacto -->
        <div class="mb-4 bg-gray-50 p-2 rounded border border-gray-200 text-[10px] space-y-1">
            <div class="flex justify-between border-b border-gray-200 pb-1">
                <div>
                    <span class="text-gray-500 font-bold uppercase">Conductor:</span>
                    <span class="font-black">{{ $dispatch->driver->name ?? 'NO ASIGNADO' }}</span>
                    <span class="mx-2 text-gray-300">|</span>
                    <span class="text-gray-500 font-bold uppercase">DNI:</span>
                    <span class="font-black">{{ $dispatch->driver->dni ?? '-' }}</span>
                    <span class="mx-2 text-gray-300">|</span>
                    <span class="text-gray-500 font-bold uppercase">Tel:</span>
                    <span class="font-black">{{ $dispatch->driver->phone ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-bold uppercase">Ruta:</span>
                    <span class="font-black">{{ $dispatch->origin->name ?? '-' }} → {{ $dispatch->destination->name ?? '-' }}</span>
                </div>
            </div>
            <div class="flex gap-6">
                <div>
                    <span class="text-gray-500 font-bold uppercase">Vehículo/Patente:</span>
                    <span class="font-black">{{ $dispatch->chassis_number ?? '-' }} / {{ $dispatch->semi_number ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500 font-bold uppercase">Precinto:</span>
                    <span class="font-black text-blue-600">{{ $dispatch->seal_number ?? '-' }}</span>
                </div>
            </div>
        </div>

        <!-- Table of Shipments Compacta -->
        <div class="mb-4">
            <table class="w-full text-[10px] border-collapse">
                <thead>
                    <tr class="bg-gray-900 text-white uppercase font-bold">
                        <th class="p-1 text-center w-10">REC</th>
                        <th class="p-1 text-left">N° Guía</th>
                        <th class="p-1 text-left">Remitente</th>
                        <th class="p-1 text-left">Destinatario</th>
                        <th class="p-1 text-center">Bultos</th>
                        <th class="p-1 text-right">Valor Declarado ($)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php 
                        $totalBultos = 0; 
                        $totalFlete = 0;
                    @endphp
                    @foreach($shipments as $shipment)
                    @php 
                        $bultos = $shipment->items->sum('cantidad');
                        $totalBultos += $bultos;
                        $totalFlete += $shipment->flete;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="p-1 text-center">
                            <div class="w-4 h-4 border border-gray-400 mx-auto rounded-sm"></div>
                        </td>
                        <td class="p-1 font-bold text-blue-600">{{ $shipment->numero }}</td>
                        <td class="p-1 uppercase text-gray-700 truncate max-w-[200px]">{{ $shipment->sender->name ?? '-' }}</td>
                        <td class="p-1 uppercase text-gray-700 truncate max-w-[200px]">{{ $shipment->recipient->name ?? '-' }}</td>
                        <td class="p-1 text-center font-bold">{{ $bultos }}</td>
                        <td class="p-1 text-right font-mono">{{ number_format($shipment->flete, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="font-bold bg-gray-100">
                        <td colspan="4" class="p-1 text-right uppercase">Totales</td>
                        <td class="p-1 text-center">{{ $totalBultos }}</td>
                        <td class="p-1 text-right font-mono">$ {{ number_format($totalFlete, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Firmas Compactas -->
        <div class="flex justify-end mt-12">
            <div class="w-64 border-t border-gray-400 text-center pt-1">
                <span class="text-[10px] text-gray-500 uppercase font-bold">Firma y Aclaración Recepción Destino</span>
            </div>
        </div>

        <!-- Footer Legal -->
        <div class="mt-4 text-[8px] text-gray-400 text-center border-t border-gray-100 pt-2 italic">
            Hoja de ruta interna - TRANSPORTE AMICCI.
        </div>
    </div>

    <script>
        window.onload = function() {
            // window.print();
        }
    </script>
</body>
</html>
