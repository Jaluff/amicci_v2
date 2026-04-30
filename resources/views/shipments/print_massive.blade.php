<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manifiesto de Carga - Despacho {{ $dispatch->dispatch_number ?? '' }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            body { background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 1cm; size: A4 portrait; }
            .no-print { display: none !important; }
        }
        body { background: #f3f4f6; color: #000; font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; font-size: 12px; }
        .manifest-container { max-width: 21cm; margin: 1cm auto; background: white; padding: 1.5cm; box-sizing: border-box; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border-radius: 8px; }
        .border-dark { border: 1.5px solid #000; }
        .bg-header { background-color: #f8fafc; }
    </style>
</head>
<body class="text-sm">
    <div class="print-actions no-print text-center p-4 bg-gray-800 text-white flex justify-center gap-4 sticky top-0 z-50">
        <span class="font-bold">Vista Previa de Manifiesto de Carga</span>
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-500 px-4 py-1 rounded text-white font-bold transition">Imprimir Manifiesto</button>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-500 px-4 py-1 rounded text-white font-bold transition">Cerrar</button>
    </div>

    <div class="manifest-container bg-white">
        <!-- Header -->
        <div class="flex justify-between items-start mb-8 border-b-2 border-gray-800 pb-6">
            <div class="flex flex-col">
                <img src="{{ asset('images/logo_amicci.png') }}" alt="AMICCI" class="h-16 w-auto mb-2">
                <h1 class="text-xl font-black uppercase tracking-tighter text-gray-900">Manifiesto de Carga</h1>
            </div>
            <div class="text-right">
                <div class="text-3xl font-black text-blue-600 tracking-widest mb-1">{{ preg_replace('/[^0-9]/', '', $dispatch->dispatch_number ?? '0000') }}</div>
                <div class="text-sm font-bold text-gray-500 uppercase">Documento de Control Interno</div>
                <div class="text-sm text-gray-700 mt-2">Fecha: <strong>{{ now()->format('d/m/Y H:i') }}</strong></div>
            </div>
        </div>

        <!-- Master Data -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h3 class="text-xs font-black text-gray-400 uppercase mb-3 tracking-widest">Información del Transporte</h3>
                    <div class="grid grid-cols-2 gap-y-2">
                        <span class="text-gray-500">Conductor:</span>
                        <span class="font-bold">{{ $dispatch->driver->name ?? 'NO ASIGNADO' }}</span>
                        
                        <span class="text-gray-500">DNI:</span>
                        <span class="font-bold">{{ $dispatch->driver->dni ?? '-' }}</span>

                        <span class="text-gray-500">Origen Despacho:</span>
                        <span class="font-bold uppercase">{{ $dispatch->origin->name ?? '-' }}</span>

                        <span class="text-gray-500">Destino Despacho:</span>
                        <span class="font-bold uppercase">{{ $dispatch->destination->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <h3 class="text-xs font-black text-gray-400 uppercase mb-3 tracking-widest">Detalles del Vehículo</h3>
                    <div class="grid grid-cols-2 gap-y-2">
                        <span class="text-gray-500">Patente/Semi:</span>
                        <span class="font-bold uppercase">{{ $dispatch->semi_number ?? '-' }}</span>
                        
                        <span class="text-gray-500">Chasis/Camión:</span>
                        <span class="font-bold uppercase">{{ $dispatch->chassis_number ?? '-' }}</span>

                        <span class="text-gray-500">N° Precinto:</span>
                        <span class="font-bold text-blue-600">{{ $dispatch->seal_number ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table of Shipments -->
        <div class="mb-8">
            <h3 class="text-xs font-black text-gray-400 uppercase mb-3 tracking-widest">Detalle de Guías Incluidas</h3>
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-gray-900 text-white uppercase tracking-tighter">
                        <th class="p-3 text-left rounded-l-lg">N° Guía</th>
                        <th class="p-3 text-left">Remitente</th>
                        <th class="p-3 text-left">Destinatario</th>
                        <th class="p-3 text-center">Bultos</th>
                        <th class="p-3 text-right">Flete ($)</th>
                        <th class="p-3 text-right rounded-r-lg">Seguro ($)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php 
                        $totalBultos = 0; 
                        $totalFlete = 0;
                        $totalSeguro = 0;
                    @endphp
                    @foreach($shipments as $shipment)
                    @php 
                        $bultos = $shipment->items->sum('cantidad');
                        $totalBultos += $bultos;
                        $totalFlete += $shipment->flete;
                        $totalSeguro += $shipment->seguro;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-3 font-black text-blue-600">{{ $shipment->numero }}</td>
                        <td class="p-3 uppercase text-gray-700 font-medium">{{ $shipment->sender->name ?? '-' }}</td>
                        <td class="p-3 uppercase text-gray-700 font-medium">{{ $shipment->recipient->name ?? '-' }}</td>
                        <td class="p-3 text-center font-bold">{{ $bultos }}</td>
                        <td class="p-3 text-right font-mono">{{ number_format($shipment->flete, 2, ',', '.') }}</td>
                        <td class="p-3 text-right font-mono">{{ number_format($shipment->seguro, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-900 bg-gray-50">
                    <tr class="font-black text-gray-900">
                        <td colspan="3" class="p-3 text-right uppercase tracking-widest">Totales Generales</td>
                        <td class="p-3 text-center">{{ $totalBultos }}</td>
                        <td class="p-3 text-right font-mono">$ {{ number_format($totalFlete, 2, ',', '.') }}</td>
                        <td class="p-3 text-right font-mono">$ {{ number_format($totalSeguro, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-3 gap-12 mt-20 pt-10">
            <div class="border-t border-gray-400 text-center pt-2">
                <span class="text-[10px] text-gray-500 uppercase font-bold">Firma Conductor</span>
            </div>
            <div class="border-t border-gray-400 text-center pt-2">
                <span class="text-[10px] text-gray-500 uppercase font-bold">Firma Despacho Origen</span>
            </div>
            <div class="border-t border-gray-400 text-center pt-2">
                <span class="text-[10px] text-gray-500 uppercase font-bold">Firma Recepción Destino</span>
            </div>
        </div>

        <!-- Footer Legal -->
        <div class="mt-12 text-[9px] text-gray-400 text-center border-t border-gray-100 pt-4 italic">
            Este documento constituye un manifiesto de carga interno de TRANSPORTE AMICCI. 
            La veracidad de los datos aquí consignados es responsabilidad del personal operativo.
        </div>
    </div>

    <script>
        window.onload = function() {
            // window.print();
        }
    </script>
</body>
</html>
