<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Guía {{ $shipment->numero }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            body {
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page {
                margin: 0;
                size: A4 portrait;
            }

            .no-print {
                display: none !important;
            }

            .page {
                margin: 0 !important;
                padding: 0.3cm !important;
                width: 100%;
                box-sizing: border-box;
                height: 14.85cm !important;
                page-break-after: always;
            }
        }

        body {
            background: #f3f4f6;
            color: #000;
            font-family: ui-sans-serif, system-ui, sans-serif;
            font-size: 11px;
        }

        .page {
            max-width: 21cm;
            margin: 1cm auto;
            background: white;
            padding: 0.5cm;
            box-sizing: border-box;
        }

        .border-layout {
            border: 1.5px solid #000;
            border-radius: 8px;
        }

        .inner-border {
            border: 1px solid #000;
        }

        .border-b-dark {
            border-bottom: 1.5px solid #000;
        }

        .border-r-dark {
            border-right: 1.5px solid #000;
        }

        .border-l-dark {
            border-left: 1.5px solid #000;
        }

        .border-t-dark {
            border-top: 1.5px solid #000;
        }

        .bg-header {
            background-color: #fceea7;
        }

        .txt-xs {
            font-size: 9px;
            line-height: 1.1;
        }

        .txt-2xs {
            font-size: 8px;
            line-height: 1;
        }
    </style>
</head>

<body class="text-sm">
    <div class="print-actions no-print text-center p-4 bg-gray-800 text-white flex justify-center gap-4">
        <span>Vista Previa de Impresión</span>
        <button onclick="window.print()"
            class="bg-blue-600 hover:bg-blue-500 px-4 py-1 rounded text-white font-bold">Imprimir Ahora</button>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-500 px-4 py-1 rounded text-white font-bold">
            Cerrar
        </button>
    </div>

    <!-- Contenedor mitad hoja A4 -->
    <div class="page">
        <div class="overflow-hidden" style="border: 1.5px solid #000; border-radius: 8px;">
            <!-- Header Section -->
            <div class="flex relative" style="border-bottom: 1.5px solid #000;">
                <!-- X sobre la línea central -->
                <div class="absolute top-0 left-[55%] -translate-x-1/2 bg-[#dc8a18] text-white font-bold flex items-center justify-center text-[42px] leading-none z-10"
                    style="border-bottom: 1.5px solid #000; border-left: 1.5px solid #000; border-right: 1.5px solid #000; width: 46px; height: 46px; padding-bottom: 3px;">
                    X
                </div>

                <!-- Col 1: Logo & Info (65%) -->
                <div class="w-[55%] flex flex-col pt-2 pb-1 pr-6" style="border-right: 1.5px solid #000;">
                    <div class="flex items-start">
                        <!-- Logo -->
                        <div class="w-[60%] pl-4 flex flex-col items-center">

                            <img src="{{ asset('images/logo_amicci.png') }}" alt="Transporte AMICCI"
                                class="w-[95%] h-auto">
                            <div class="w-full text-right text-[8px] font-bold leading-none pr-2 mt-0.5">
                                {{ $shipment->company->legal_name ?: $shipment->company->name }}
                            </div>

                        </div>
                        <!-- Bullets -->
                        <div class="w-[40%] pl-0 pt-0">
                            <ul
                                class="text-[#dc8a18] text-[10.5px] font-medium leading-[1.3] m-0 pl-0 tracking-tighter list-none">
                                <li class="flex items-center"><span
                                        class="text-[#dc8a18] mr-1.5 text-[15px] leading-none">&bull;</span><span
                                        class="text-black">Transportes de carga</span></li>
                                <li class="flex items-center"><span
                                        class="text-[#dc8a18] mr-1.5 text-[15px] leading-none">&bull;</span><span
                                        class="text-black">Logística</span></li>
                                <li class="flex items-center"><span
                                        class="text-[#dc8a18] mr-1.5 text-[15px] leading-none">&bull;</span><span
                                        class="text-black leading-tight">Servicio contrareembolso</span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="text-[10px] leading-tight text-black mt-3 flex gap-2 w-full" style="padding-left: 4%;">
                        @php
                            $branches = \App\Models\Branch::where('active', true)->get();
                            $primary = $branches->first(fn($b) => $b->is_primary) ?? $branches->first();
                            $secondaries = $branches->filter(fn($b) => !$b->is_primary);
                        @endphp

                        <!-- Primera columna de dirección (Sucursal Principal) -->
                        <div class="w-1/2">
                            @if($primary)
                                <div class="mb-1.5">
                                    <p class="font-bold mb-0">{{ $primary->name }}
                                        {{-- - {{ $primary->state ?? ($primary->city ?? '') }} --}}
                                    </p>
                                    <p class="mb-0">
                                        {{ $primary->address_line1 }}
                                        @if($primary->address_line2)
                                            <br>{{ $primary->address_line2 }}
                                        @endif
                                        <br>{{ $primary->city ? $primary->city : '' }}{{ $primary->zip_code ? ' - CP.' . $primary->zip_code : '' }}
                                    </p>
                                    <p class="mb-0">Tel. {{ $primary->phone ?? '' }} </br> {{ $primary->email ?? '' }}</p>
                                </div>
                            @else
                                <div class="mb-1.5 text-white">
                                    <p class="font-bold mb-0">&nbsp;</p>
                                    <p class="mb-0">&nbsp;</p>
                                    <p class="mb-0">&nbsp;</p>
                                </div>
                            @endif
                        </div>

                        <!-- Segunda columna de dirección (Otras Sucursales) -->
                        <div class="w-1/2 text-right">
                            @foreach($secondaries as $sec)
                                <div class="mb-1.5">
                                    <p class="font-bold mb-0">{{ $sec->name }}
                                        {{-- - {{ $sec->state ?? ($sec->city ?? ' ') }} --}}
                                    </p>
                                    <p class="mb-0">
                                        {{ $sec->address_line1 }}
                                        @if($sec->address_line2)
                                            <br>{{ $sec->address_line2 }}
                                        @endif
                                        <br>{{ $sec->city ? $sec->city : '' }}{{ $sec->zip_code ? ' - CP.' . $sec->zip_code : '' }}
                                    </p>
                                    <p class="mb-0">Tel. {{ $sec->phone ?? '' }} </br> {{ $sec->email ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Col 2: Right Side (35%) -->
                <div class="w-[45%] flex flex-col pt-2 pb-1.5 pl-8 pr-1 bg-white">
                    <div class="flex items-center justify-between w-full mb-1">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[13px] font-bold">FECHA:</span>
                            <span class="text-[14px] font-bold">{{ $shipment->fecha->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-1 justify-end pl-3">
                            <span class="font-bold text-[13px] tracking-tight whitespace-nowrap">Guia</span>
                            <div
                                class="border-[1.5px] border-black rounded-[1rem] w-28 h-6.5 font-bold text-[13px] flex items-center justify-center tracking-normal bg-white shadow-sm p-1 overflow-hidden text-ellipsis whitespace-nowrap">
                                {{ $shipment->numero }}
                            </div>
                        </div>
                    </div>
                    <div class="text-gray-900 uppercase tracking-tighter w-full text-right font-bold pr-2 mb-1"
                        style="font-size: 8px; line-height: 1;">
                        DOCUMENTO NO VALIDO COMO FACTURA
                    </div>

                    <div class="mt-4 pr-0 text-left flex flex-col justify-end items-end">
                        <div class="w-[85%] text-[10.5px] leading-[1.2] text-gray-900 font-medium">
                            <div><span class="font-bold">CUIT:</span> {{ $shipment->company?->cuit ?? '' }}</div>
                            <div><span class="font-bold">Ing. Brutos:</span> {{ $shipment->company?->gross_income ?? '' }}</div>
                            <div><span class="font-bold">Establecimiento:</span> {{ $shipment->company?->establishment ?? '' }}</div>
                            <div class="whitespace-nowrap"><span class="font-bold">Sede de Timbrado:</span> {{ $shipment->company?->stamping_headquarters ?? '' }}</div>
                            <div class="whitespace-nowrap"><span class="font-bold">Fecha Inicio Activ:</span> {{ $shipment->company?->start_of_activities ? \Carbon\Carbon::parse($shipment->company->start_of_activities)->format('d/m/Y') : '' }}</div>
                            <div class="font-bold pt-0.5" style="font-size: 9.5px;">
                                IVA RESPONSABLE INSCRIPTO
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remitente & Destinatario -->
            <div class="flex border-b-dark text-[9.5px] leading-tight">
                <!-- Remitente -->
                <div class="w-1/2 border-r-dark p-1">
                    <table class="w-full" style="line-height: 1.1;">
                        <tr>
                            <td class="w-16 align-top">REMITENTE:</td>
                            <td class="font-bold uppercase">{{ $shipment->sender->name ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">DOMICILIO:</td>
                            <td class="uppercase">
                                @php
                                    $senderAddr = $shipment->sender?->primaryAddress;
                                @endphp
                                @if($senderAddr)
                                    {{ trim($senderAddr->address_line1 . ' ' . $senderAddr->address_line2) }}
                                    @php
                                        $locParts = array_filter([
                                            $senderAddr->city,
                                            $senderAddr->state,
                                            $senderAddr->zip_code,
                                        ]);
                                    @endphp
                                    @if(!empty($locParts))
                                        <br>{{ implode(', ', $locParts) }}
                                    @endif
                                @else
                                    {{ $shipment->sender->address ?? '' }}
                                    @if($shipment->origin)<br>{{ $shipment->origin->nombre }}@endif
                                @endif
                            </td>
                        </tr>
                        @php
                            $senderPhone = $shipment->sender?->phone ?? $senderAddr?->phone;
                        @endphp
                        @if($senderPhone)
                            <tr>
                                <td class="align-top">TELÉFONO:</td>
                                <td class="uppercase font-bold">{{ $senderPhone }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="align-top" colspan="2">FLETE A PAGAR EN: <span class="font-bold ml-2">{{
    ucfirst($shipment->flete_a_pagar_en ?? '-') }}</span></td>
                        </tr>
                    </table>
                </div>
                <!-- Destinatario -->
                <div class="w-1/2 p-1 relative" style="padding-bottom: 14px;">
                    <table class="w-full" style="line-height: 1.1;">
                        <tr>
                            <td class="w-20 align-top">DESTINATARIO:</td>
                            <td class="font-bold uppercase">{{ $shipment->recipient->name ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="align-top">DOMICILIO:</td>
                            <td class="uppercase">
                                @php
                                    $recipientAddr = $shipment->recipient?->primaryAddress;
                                @endphp
                                @if($recipientAddr)
                                    {{ trim($recipientAddr->address_line1 . ' ' . $recipientAddr->address_line2) }}
                                    @php
                                        $locPartsDest = array_filter([
                                            $recipientAddr->city,
                                            $recipientAddr->state,
                                            $recipientAddr->zip_code,
                                        ]);
                                    @endphp
                                    @if(!empty($locPartsDest))
                                        <br>{{ implode(', ', $locPartsDest) }}
                                    @endif
                                @else
                                    {{ $shipment->recipient->address ?? '' }}
                                    @if($shipment->destination)<br>{{ $shipment->destination->nombre }}@endif
                                @endif
                            </td>
                        </tr>
                        @php
                            $recipientPhone = $shipment->recipient?->phone ?? $recipientAddr?->phone;
                        @endphp
                        @if($recipientPhone)
                            <tr>
                                <td class="align-top">TELÉFONO:</td>
                                <td class="uppercase font-bold">{{ $recipientPhone }}</td>
                            </tr>
                        @endif
                    </table>
                    <div class="absolute bottom-0.5 left-1 w-full pr-2 flex justify-between text-[8px] font-bold">
                        <div>I.V.A.: <span>{{ $shipment->recipient?->tax_status ?? '0' }}</span></div>
                        <div>CUIT: <span>{{ $shipment->recipient?->document ?? '' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Items Table & Importes -->
            <div class="flex border-b-dark"
                style="height: 14rem; max-height: 14rem; min-height: 14rem; overflow: hidden;">
                <!-- Bultos (Left 70%) -->
                <div class="w-[70%] flex flex-col h-full">
                    <div class="flex bg-header border-b-dark font-bold text-left text-[11px] py-0.5">
                        <div class="w-[10%] pl-1">CANT</div>
                        <div class="w-[15%] pl-1">TIPO</div>
                        <div class="w-[25%] pl-1">REMITO</div>
                        <div class="w-[25%] pl-1">PART. REC.</div>
                        <div class="w-[12%] pl-1">PESO</div>
                        <div class="w-[13%] pl-1">VOLUMEN</div>
                    </div>
                    <div class="flex-1 p-0.5 overflow-hidden">
                        @foreach($shipment->items as $item)
                            <div
                                class="flex text-[10.5px] font-normal items-center text-left leading-none py-0.5 border-b border-gray-200">
                                <div class="w-[10%] text-black pl-1">{{ $item->cantidad }}</div>
                                <div class="w-[15%] text-black pl-1">{{ ucfirst($item->tipo_paquete) }}</div>
                                <div
                                    class="w-[25%] text-black uppercase overflow-hidden text-ellipsis whitespace-nowrap pl-1">
                                    {{ $item->numero_remito ?? '-' }}
                                </div>
                                <div
                                    class="w-[25%] text-black uppercase overflow-hidden text-ellipsis whitespace-nowrap pl-1">
                                    {{ $item->referencia_recepcion ?? '-' }}
                                </div>
                                <div class="w-[12%] text-black pl-1">{{ (int) $item->peso }} kg</div>
                                <div class="w-[13%] text-black pl-1">
                                    {{ $item->volumen ? $item->volumen . ' m³' : '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Inner Grid Row -->
                    <div
                        class="border-t-dark flex bg-gray-50 text-[8.5px] font-bold p-1 h-6 items-center border-t border-gray-700">
                        <span class="mr-3">Nº hoja de ruta</span>
                        <span class="mr-3">Vº Bº Dep.</span>
                        <span class="mr-3">Repart.</span>
                        <span class="mr-3">Peso hoja de Ruta</span>
                        <span class="mr-3">Paso Cobro</span>
                        <span class="mr-3">C.C.</span>
                        <span>Fac Nº {{ $shipment->numero_factura ?? '' }}</span>
                    </div>
                </div>

                <!-- Importe (Right 30%) -->
                <div class="w-[30%] border-l-dark flex flex-col h-full">
                    <div class="bg-header border-b-dark font-bold text-center text-[10px] py-0.5">
                        IMPORTE
                    </div>
                    <div class="flex-1">
                        <table class="w-full h-full text-[11px] font-normal text-black"
                            style="table-layout: fixed; line-height: 1.1;">
                            <tr class="border-b border-gray-400">
                                <td
                                    class="pl-1 border-r border-gray-400 w-3/5 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Flete</td>
                                <td class="text-right pr-1 w-2/5">{{ number_format($shipment->flete, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="border-b border-gray-400">
                                <td
                                    class="pl-1 border-r border-gray-400 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Seguro</td>
                                <td class="text-right pr-1">{{ number_format($shipment->seguro, 2, ',', '.') }}</td>
                            </tr>
                            <tr class="border-b border-gray-400">
                                <td
                                    class="pl-1 border-r border-gray-400 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Comisión Contr.</td>
                                <td class="text-right pr-1">{{ number_format(
    $shipment->monto_contra_reembolso,
    2,
    ',',
    '.'
) }}</td>
                            </tr>
                            <tr class="border-b border-gray-400">
                                <td
                                    class="pl-1 border-r border-gray-400 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Otros conceptos</td>
                                <td class="text-right pr-1">{{ number_format($shipment->otros_cargos, 2, ',', '.')
                                    }}
                                </td>
                            </tr>
                            <tr class="border-b border-gray-400">
                                <td
                                    class="pl-1 border-r border-gray-400 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Retiro Merc.</td>
                                <td class="text-right pr-1">{{ number_format(
    $shipment->retencion_mercaderia,
    2,
    ',',
    '.'
) }}</td>
                            </tr>
                            <tr class="border-b border-gray-400 font-bold text-black">
                                <td
                                    class="pl-1 border-r border-gray-400 uppercase overflow-hidden text-ellipsis whitespace-nowrap">
                                    Subtotal $:</td>
                                <td class="text-right pr-1">{{ number_format($shipment->subtotal, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="border-b border-gray-400">
                                <td
                                    class="pl-1 border-r border-gray-400 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Iva Resp. Insc.</td>
                                <td class="text-right pr-1">{{ number_format($shipment->iva_monto, 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr class="font-bold text-black text-[11.5px]">
                                <td
                                    class="pl-1 border-r border-gray-400 uppercase overflow-hidden text-ellipsis whitespace-nowrap">
                                    Total $:</td>
                                <td class="text-right pr-1">{{ number_format($shipment->total, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer Details -->
            <div class="flex h-16">
                <!-- Valor Declarado -->
                <div class="w-1/4 border-r-dark p-2 txt-xs relative font-normal">
                    VALOR DECLARADO:<br><span
                        class="font-bold">${{ number_format($shipment->items->sum('monto_valor_declarado'), 2, ',', '.') }}</span>
                    <div
                        class="mt-1 flex flex-col justify-end absolute bottom-1 h-full w-full left-0 pl-2 pb-1 font-normal">
                        <div class="w-full border-t border-transparent pt-3">
                            ASEGURADO POR:<br><span class="font-bold">&nbsp;</span>
                        </div>
                    </div>
                </div>
                <!-- Observaciones -->
                <div class="w-1/2 p-2 border-r-dark">
                    <div class="txt-xs font-bold mb-1">OBSERVACIONES:</div>
                    <div class="txt-xs">Obs: {{ $shipment->notas }}</div>
                </div>
                <!-- Recibí -->
                <div class="w-1/4 relative">
                    <div class="absolute bottom-1 w-full text-center txt-xs">
                        Recibí conforme (firma)
                    </div>
                </div>
            </div>

        </div>


        <!-- Texto legal en pie de página -->
        <div class="px-1 text-justify tracking-tighter text-black w-full"
            style="font-size: 6px; line-height: 1.1; margin-top: 4px;">
            NOTA IMPORTANTE: TRANSPORTE AMICCI Requerirá a los cargadores la DECLARACION DE VALORES DE SUS CARGAS, a
            fin
            de
            que la empresa consitituya el seguro sobre las mismas, caso contrario no se hará responsable de averías,
            daños y
            las pérdidas que se produzcan como consecuencia de cualquier riesgo del transporte. El seguro cubre
            averías
            y/o
            pérdidas por consecuencia directa del choque, vuelco o incendio a porrata. Como así también ampara las
            mercaderías hasta el destino que determina esta CARTA DE PORTE. TRANSPORTE AMICCI no se responsabiliza
            por
            mercadería sin embalaje, o con embalaje deficiente. Después de los 7 días se cobrará el almacenaje
            correspondiente y pasado el año no tendrá derecho a reclamo alguno. La empresa no se hará responsable
            por
            atrasos en el transporte y entregas siempre que las causas provengan del cargador o consignatario como
            también
            de paros, huelgas o causas de fuerza mayor. El Remitente bajo su responsabilidad exclusiva, declara no
            cargar
            mercadería prohibida por la Ley a transportar (Pólvora, inflamables, etc.). De suceder el hecho amparado
            en
            el
            seguro y de abonarles la Cía. De Seguros la indemnización que corresponda, declaran cederle y
            transferirle
            todos
            los Derechos para que accione por su cuenta y cargo contra los que resulten responsables, obligándose a
            prestar
            colaboración si así se lo solicitan, autorizando para que en caso de no hacerlo se accione en su nombre.
            La empresa no se responsabiliza de los valores declarados de esta carga porque de ellos toma únicamente
            nota
            a
            lo manifestado en la etiqueta sin verificar el contenido por lo tanto esta declaración no responsabiliza
            a
            la
            compañía de los referidos valores. DOCUMENTO DE CARTA DE PORTE NO VÁLIDO COMO FACTURA
        </div>
    </div>

    <script>
        @if(session('auto_close_and_reload_opener'))
            // Intentamos redirigir a la pestaña origen (solo por si las dudas)
            if (window.opener && !window.opener.closed) {
                window.opener.location.href = "{{ route('shipments.index') }}";
            }

            // Nos suscribimos al evento "afterprint" 
            // Si no hay opener, entonces no podemos dejar una pestaña muerta abierta
            // Así que si no hay opener en vez de cerrarse, regresamos ahí mismo al índice.
            window.addEventListener('afterprint', function () {
                if (window.opener && !window.opener.closed) {
                    window.close();
                } else {
                    window.location.href = "{{ route('shipments.index') }}";
                }
            });
        @endif

        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>