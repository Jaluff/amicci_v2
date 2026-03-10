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
                padding: 1cm !important;
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
        <button onclick="window.close()"
            class="bg-gray-600 hover:bg-gray-500 px-4 py-1 rounded text-white font-bold">
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
                                {{ $shipment->company->legal_name }}
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

                    <div class="text-[9px] leading-tight text-black mt-3 flex gap-2 w-full" style="padding-left: 4%;">
                        @php
                        $addresses = collect();
                        if ($shipment->company) {
                        $addresses = $shipment->company->addresses;
                        }
                        $primary = $addresses->first(fn($a) => $a->is_primary == true || $a->is_primary == 1) ??
                        $addresses->first();
                        $secondaries = $addresses->filter(fn($a) => $a->is_primary == false && $a->is_primary != 1);
                        @endphp

                        <!-- Primera columna de dirección -->
                        <div class="w-1/2">
                            @if($primary)
                            <div class="mb-1.5">
                                <p class="font-bold mb-0">{{ $primary->state ?? ($primary->city ?? '') }}</p>
                                <p class="mb-0">{{ trim($primary->address_line1 . ($primary->address_line2 ? ' ' .
                                    $primary->address_line2 : '')) }}</br>{{ $primary->city ? $primary->city :
                                    ''
                                    }}{{
                                    $primary->zip_code ? ' - CP.' . $primary->zip_code : '' }}</p>
                                <p class="mb-0">Tel. {{ $primary->phone ?? ($shipment->company->phone ?? '') }} </br> {{
                                    $primary->email ? $primary->email : '' }}</p>
                            </div>
                            @else
                            <div class="mb-1.5 text-white">
                                <p class="font-bold mb-0">&nbsp;</p>
                                <p class="mb-0">&nbsp;</p>
                                <p class="mb-0">&nbsp;</p>
                            </div>
                            @endif
                        </div>

                        <!-- Segunda columna de dirección -->
                        <div class="w-1/2 text-right">
                            @foreach($secondaries as $sec)
                            <div class="mb-0">
                                <p class="font-bold mb-0">{{ $sec->state ?? ($sec->city ?? ' ') }}</p>
                                <p class="mb-0">{{ trim($sec->address_line1 . ($sec->address_line2 ? ' ' .
                                    $sec->address_line2 : '')) }}</br>{{ $sec->city ? $sec->city : '' }}{{
                                    $sec->zip_code
                                    ? ' - CP.' . $sec->zip_code : '' }}</p>
                                <p class="mb-0">Tel. {{ $sec->phone ?? '' }} </br> {{ $sec->email ? $sec->email : '' }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Col 2: Right Side (35%) -->
                <div class="w-[45%] flex flex-col pt-2 pb-1.5 pl-8 pr-3 bg-white">
                    <div class="text-right flex justify-end items-end mb-1 pr-0">
                        <span class="text-[13px] mr-1">FECHA:</span>
                        <span class="text-[14px] font-bold">{{ $shipment->fecha->format('d/m/Y') }}</span>
                    </div>

                    <div class="flex flex-col items-end mb-1 w-full pl-6">
                        <div class="flex items-center w-[85%] gap-2 mb-0.5">
                            <span class="font-bold text-[13px] tracking-tight whitespace-nowrap">Guia de carga
                                N&deg;</span>
                            <div
                                class="border-[1.5px] border-black rounded-[1rem] flex-1 h-7 font-bold text-[19px] flex items-center justify-center tracking-widest bg-white shadow-sm mt-0.5 p-1">
                                {{ preg_replace('/[^0-9]/', '', $shipment->numero) }}
                            </div>
                        </div>
                        <div class="text-gray-900 uppercase tracking-tighter w-full text-left font-bold pr-2"
                            style="font-size: 8px; line-height: 1;">
                            DOCUMENTO NO VALIDO COMO FACTURA
                        </div>
                    </div>

                    <div class="mt-2 pr-0 text-left flex flex-col justify-end items-end">
                        <table class="w-[80%] text-[9.5px] leading-[1.3] text-gray-900 border-none font-medium ">
                            <tr>
                                <td class="font-bold text-right">CUIT:</td>
                                <td class="">{{ $shipment->company?->cuit ?? '' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-bold text-right">Ing. Brutos:</td>
                                <td class="">{{ $shipment->company?->gross_income ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold text-right">Establecimiento:</td>
                                <td class="text-left">{{ $shipment->company?->establishment ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold tracking-tighter whitespace-nowrap text-right">Sede de Timbrado:
                                </td>
                                <td class="text-left">{{ $shipment->company?->stamping_headquarters ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="font-bold tracking-tighter whitespace-nowrap text-right">Fecha Inicio Activ:
                                </td>
                                <td class="text-left">{{ $shipment->company?->start_of_activities ?
                                    \Carbon\Carbon::parse($shipment->company->start_of_activities)->format('d/m/Y')
                                    : ''
                                    }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="pt-1.5">

                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="text-[10px] tracking-tight font-bold text-black uppercase">
                        IVA RESPONSABLE INSCRIPTO
                    </div>
                </div>
            </div>

            <!-- Remitente & Destinatario -->
            <div class="flex border-b-dark text-xs">
                <!-- Remitente -->
                <div class="w-1/2 border-r-dark p-2">
                    <table class="w-full">
                        <tr>
                            <td class="w-20 align-top">REMITENTE:</td>
                            <td class="font-bold uppercase">{{ $shipment->sender->name ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="align-top pt-1">DOMICILIO</td>
                            <td class="uppercase pt-1">{{ $shipment->sender->address ?? '' }}
                                @if($shipment->origin)<br>{{ $shipment->origin->nombre }}@endif</td>
                        </tr>
                        <tr>
                            <td class="align-top pt-2" colspan="2">FLETE A PAGAR EN: <span class="font-bold ml-2">{{
                                    ucfirst($shipment->flete_a_pagar_en ?? '-') }}</span></td>
                        </tr>
                    </table>
                </div>
                <!-- Destinatario -->
                <div class="w-1/2 p-2 relative">
                    <table class="w-full">
                        <tr>
                            <td class="w-24 align-top">DESTINATARIO:</td>
                            <td class="font-bold uppercase">{{ $shipment->recipient->name ?? '' }}</td>
                        </tr>
                        <tr>
                            <td class="align-top pt-1">DOMICILIO</td>
                            <td class="uppercase pt-1">{{ $shipment->recipient->address ?? '' }}
                                @if($shipment->destination)<br>{{ $shipment->destination->nombre }}@endif</td>
                        </tr>
                    </table>
                    <div class="absolute bottom-2 left-2 w-full pr-4 flex justify-between">
                        <div>I.V.A.: 0</div>
                        <div>CUIT: <span class="font-bold">{{ $shipment->recipient->identification_number ?? ''
                                }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Items Table & Importes -->
            <div class="flex border-b-dark" style="min-height: 11rem;">
                <!-- Bultos (Left 70%) -->
                <div class="w-[70%] flex flex-col">
                    <div class="flex bg-header border-b-dark font-bold text-center txt-xs py-1">
                        <div class="w-1/6">BULTOS</div>
                        <div class="w-1/6">ENV</div>
                        <div class="w-1/4">MARCA</div>
                        <div class="w-5/12">DESCRIPCION</div>
                    </div>
                    <div class="flex-1 p-1">
                        @foreach($shipment->items as $item)
                        <div class="flex text-xs mb-1 items-start">
                            <div class="w-1/6 font-bold text-center">{{ $item->cantidad }}</div>
                            <div class="w-1/6 text-center">Bultos</div>
                            <div class="w-1/4 text-center"></div>
                            <div class="w-5/12 flex flex-wrap">
                                <span class="mr-2">{{ $item->descripcion }}</span>
                                <span class="font-bold mr-2">Peso: {{ $item->peso }}</span>
                                <span class="font-bold">Vol: {{ $item->volumen ?? '' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Inner Grid Row -->
                    <div class="border-t-dark flex bg-gray-50 txt-xs p-1 h-8 items-center border-t border-gray-700">
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
                <div class="w-[30%] border-l-dark flex flex-col">
                    <div class="bg-header border-b-dark font-bold text-center txt-xs py-1">
                        IMPORTE
                    </div>
                    <div class="flex-1 text-xs">
                        <table class="w-full h-full text-[10px]" style="table-layout: fixed; line-height: 1.2;">
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
                                <td class="text-right pr-1">{{ number_format($shipment->monto_contra_reembolso, 2,
                                    ',',
                                    '.') }}</td>
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
                                <td class="text-right pr-1">{{ number_format($shipment->retencion_mercaderia, 2,
                                    ',',
                                    '.') }}</td>
                            </tr>
                            <tr class="border-b border-gray-400 font-bold">
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
                            <tr class="font-bold">
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
                <div class="w-1/4 border-r-dark p-2 txt-xs relative">
                    VALOR DECLARADO EN $
                    <div
                        class="mt-1 flex flex-col justify-end absolute bottom-1 h-full w-full left-0 pl-2 pb-1 font-bold">
                        <div class="w-full border-t border-transparent pt-3">
                            ASEGURADO POR
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
        window.addEventListener('afterprint', function() {
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