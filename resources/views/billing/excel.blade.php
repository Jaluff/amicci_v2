<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura {{ $invoice->numero }}</title>
    <style>
        .title { font-size: 16px; font-weight: bold; text-align: center; }
        .header-table, .items-table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        .header-table td { padding: 5px; border: 1px solid #ccc; }
        .items-table th, .items-table td { padding: 6px; border: 1px solid #000; text-align: left; }
        .items-table th { background-color: #f3f4f6; font-weight: bold; }
        .font-bold { font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="9" class="title" style="font-size: 16px; font-weight: bold; text-align: center;">FACTURA / LIQUIDACIÓN</td>
        </tr>
        <tr>
            <td colspan="4"><b>Número:</b> {{ $invoice->numero }}</td>
            <td colspan="5"><b>Fecha Emisión:</b> {{ $invoice->fecha_factura?->format('d/m/Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Cliente:</b> {{ $invoice->party?->name ?? '-' }}</td>
            <td colspan="5"><b>Empresa:</b> {{ $invoice->company?->name }}</td>
        </tr>
        <tr>
            <td colspan="4"><b>Nº Recibo:</b> {{ $invoice->numero_recibo ?? '—' }}</td>
            <td colspan="5"><b>Fecha Cobro:</b> {{ $invoice->fecha_cobro?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @if($invoice->notas)
        <tr>
            <td colspan="9"><b>Notas:</b> {{ $invoice->notas }}</td>
        </tr>
        @endif
        <tr>
            <td colspan="9">&nbsp;</td>
        </tr>
        <tr style="background-color: #000000; color: #ffffff; font-weight: bold;">
            <th style="border: 1px solid #000;">Fecha</th>
            <th style="border: 1px solid #000;">F. Entrega</th>
            <th style="border: 1px solid #000;"># Guía</th>
            <th style="border: 1px solid #000;">Remitente</th>
            <th style="border: 1px solid #000;">Destinatario</th>
            <th style="border: 1px solid #000;">Flete</th>
            <th style="border: 1px solid #000;">Seguro</th>
            <th style="border: 1px solid #000;">Com. Contr.</th>
            <th style="border: 1px solid #000;">Ret. Merc.</th>
            <th style="border: 1px solid #000;">Otros Conc.</th>
            <th style="border: 1px solid #000; text-align: right;">Total</th>
        </tr>
        @php
            $totalFlete = $invoice->shipments->sum('flete');
            $totalSeguro = $invoice->shipments->sum('seguro');
            $totalComision = $invoice->shipments->sum('monto_contra_reembolso');
            $totalRetencion = $invoice->shipments->sum('retencion_mercaderia');
            $totalOtros = $invoice->shipments->sum('otros_cargos');
            $totalTotal = $invoice->shipments->sum('total');
        @endphp
        @foreach($invoice->shipments as $shipment)
        <tr>
            <td style="border: 1px solid #000;">{{ $shipment->fecha?->format('d/m/Y') ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $shipment->fecha_entrega?->format('d/m/Y') ?? '—' }}</td>
            <td style="border: 1px solid #000; font-weight: bold;">{{ $shipment->numero }}</td>
            <td style="border: 1px solid #000;">{{ $shipment->sender?->name ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $shipment->recipient?->name ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($shipment->flete, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($shipment->seguro, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($shipment->monto_contra_reembolso, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($shipment->retencion_mercaderia, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($shipment->otros_cargos, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">$ {{ number_format($shipment->total, 2, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr style="background-color: #f3f4f6; font-weight: bold;">
            <td colspan="5" style="border: 1px solid #000; text-align: right;">TOTALES:</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($totalFlete, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($totalSeguro, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($totalComision, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($totalRetencion, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: left;">$ {{ number_format($totalOtros, 2, ',', '.') }}</td>
            <td style="border: 1px solid #000; text-align: right; font-weight: bold;">$ {{ number_format($totalTotal, 2, ',', '.') }}</td>
        </tr>
    </table>
</body>
</html>
