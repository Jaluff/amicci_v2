<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualización de tu Envío</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f5f7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f5f7; padding: 20px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #ffffff; padding: 30px 20px; border-bottom: 3px solid #dc8a18;">
                            @if(file_exists(public_path('images/logo_amicci.png')))
                                <img src="{{ $message->embed(public_path('images/logo_amicci.png')) }}" alt="Transporte AMICCI" style="max-width: 180px; height: auto; display: block;">
                            @else
                                <span style="font-size: 24px; font-weight: bold; color: #dc8a18; letter-spacing: 1px;">Transporte AMICCI</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px; color: #2d3748;">
                            <h2 style="margin-top: 0; margin-bottom: 16px; font-size: 20px; color: #1a202c; font-weight: 700;">¡Hola!</h2>
                            <p style="margin-top: 0; margin-bottom: 24px; font-size: 15px; line-height: 1.6; color: #4a5568;">
                                Te informamos que tu envío (Guía <strong>#{{ $shipment->numero }}</strong>) se encuentra en la siguiente etapa del proceso:
                            </p>

                            <!-- Stepper / Visual Progress Card -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 40px; background-color: #f8fafc; border: 1px solid #edf2f7; border-radius: 8px; padding: 24px 16px;">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; max-width: 500px;">
                                            <!-- Circles Row -->
                                            <tr>
                                                @php
                                                    $stages = [
                                                        ['key' => 'Dto origen', 'label' => 'Recibimos tu paquete'],
                                                        ['key' => 'En transito', 'label' => 'En viaje'],
                                                        ['key' => 'Dto destino', 'label' => 'En destino'],
                                                        ['key' => 'En reparto', 'label' => 'En reparto'],
                                                        ['key' => 'Entregado', 'label' => 'Entregado'],
                                                    ];
                                                    
                                                    $currentStageIndex = 0;
                                                    foreach ($stages as $idx => $stg) {
                                                        if ($shipment->ubicacion_actual === $stg['key']) {
                                                            $currentStageIndex = $idx;
                                                            break;
                                                        }
                                                    }
                                                @endphp

                                                @foreach($stages as $index => $stage)
                                                    @php
                                                        $isActive = $index === $currentStageIndex;
                                                        $isCompleted = $index < $currentStageIndex;
                                                        
                                                        if ($isActive) {
                                                            $circleBg = '#dc8a18';
                                                            $circleColor = '#ffffff';
                                                            $borderStyle = 'border: 3px solid #fceea7; box-shadow: 0 0 0 2px #dc8a18;';
                                                            $circleSize = '30px';
                                                            $lineHeight = '24px';
                                                        } elseif ($isCompleted) {
                                                            $circleBg = '#dc8a18';
                                                            $circleColor = '#ffffff';
                                                            $borderStyle = '';
                                                            $circleSize = '24px';
                                                            $lineHeight = '24px';
                                                        } else {
                                                            $circleBg = '#e2e8f0';
                                                            $circleColor = '#a0aec0';
                                                            $borderStyle = '';
                                                            $circleSize = '24px';
                                                            $lineHeight = '24px';
                                                        }
                                                    @endphp

                                                    <!-- Circle Node -->
                                                    <td align="center" style="width: 40px; vertical-align: middle;">
                                                        <div style="width: {{ $circleSize }}; height: {{ $circleSize }}; line-height: {{ $lineHeight }}; border-radius: 50%; background-color: {{ $circleBg }}; color: {{ $circleColor }}; font-weight: bold; font-size: 11px; text-align: center; display: inline-block; box-sizing: border-box; {{ $borderStyle }}">
                                                            @if($isCompleted)
                                                                ✓
                                                            @else
                                                                {{ $index + 1 }}
                                                            @endif
                                                        </div>
                                                    </td>

                                                    <!-- Connection Line (except after the last node) -->
                                                    @if($index < 4)
                                                        @php
                                                            $lineBg = ($index < $currentStageIndex) ? '#dc8a18' : '#e2e8f0';
                                                        @endphp
                                                        <td style="padding: 0; vertical-align: middle;">
                                                            <div style="height: 4px; background-color: {{ $lineBg }}; font-size: 1px; line-height: 1px; border-radius: 2px;">&nbsp;</div>
                                                        </td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                            
                                            <!-- Space Row -->
                                            <tr style="height: 12px;">
                                                <td colspan="9" style="height: 12px; font-size: 1px; line-height: 1px;">&nbsp;</td>
                                            </tr>
                                            
                                            <!-- Labels Row -->
                                            <tr>
                                                @foreach($stages as $index => $stage)
                                                    @php
                                                        $isActive = $index === $currentStageIndex;
                                                        $labelColor = $isActive ? '#dc8a18' : ($index < $currentStageIndex ? '#2d3748' : '#a0aec0');
                                                        $fontWeight = $isActive ? 'bold' : 'normal';
                                                    @endphp
                                                    <td align="center" style="width: 40px; vertical-align: top;">
                                                        <div style="font-size: 9px; line-height: 1.2; color: {{ $labelColor }}; font-weight: {{ $fontWeight }}; text-transform: uppercase; letter-spacing: 0.01em;">
                                                            {{ $stage['label'] }}
                                                        </div>
                                                    </td>
                                                    
                                                    @if($index < 4)
                                                        <!-- Empty space under line -->
                                                        <td style="width: auto;">&nbsp;</td>
                                                    @endif
                                                @endforeach
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Details Table -->
                            <h3 style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #718096; margin-top: 0; margin-bottom: 12px; font-weight: 600;">Detalles del Envío</h3>
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin-bottom: 30px; font-size: 14px;">
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #718096; width: 35%;">Remitente:</td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #2d3748; font-weight: 600; text-transform: uppercase;">{{ $shipment->sender->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #718096;">Destinatario:</td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #2d3748; font-weight: 600; text-transform: uppercase;">{{ $shipment->recipient->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #718096;">Origen:</td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #2d3748;">{{ $shipment->origin->nombre ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #718096;">Destino:</td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #2d3748;">{{ $shipment->destination->nombre ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #718096;">Fecha de Actualización:</td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #2d3748; font-weight: 600;">{{ $shipment->updated_at->timezone('America/Argentina/Mendoza')->format('d/m/Y H:i') }} hs</td>
                                </tr>
                                @if($shipment->fecha_entrega && $shipment->ubicacion_actual === 'Entregado')
                                <tr>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #718096;">Fecha de Entrega:</td>
                                    <td style="padding: 10px 0; border-bottom: 1px solid #edf2f7; color: #2d3748; font-weight: 600;">{{ $shipment->fecha_entrega->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                            </table>

                            <!-- Call to Action -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center" style="padding: 10px 0 20px 0;">
                                        <a href="https://amicci.com.ar/seguimiento/{{ $shipment->numero }}" target="_blank" style="background-color: #dc8a18; color: #ffffff; text-decoration: none; padding: 14px 28px; font-size: 15px; font-weight: 700; border-radius: 6px; display: inline-block; box-shadow: 0 4px 6px rgba(220, 138, 24, 0.2); transition: background-color 0.2s;">
                                            Seguir mi Envío
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin-top: 20px; margin-bottom: 0; font-size: 13px; color: #718096; line-height: 1.5; text-align: center;">
                                Si tienes alguna duda sobre tu envío, por favor contáctanos a través de nuestros <a href="https://www.transporteamicci.com.ar" target="_blank" style="color: #dc8a18; text-decoration: underline; font-weight: 600;">canales oficiales de atención</a>.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f7fafc; padding: 30px; text-align: center; border-top: 1px solid #edf2f7; color: #a0aec0; font-size: 12px; line-height: 1.6;">
                            <p style="margin: 0 0 8px 0; font-weight: 600; color: #718096;">Transporte AMICCI S.A.</p>
                            <p style="margin: 0 0 8px 0;">Servicio de carga y logística de confianza.</p>
                            <p style="margin: 0;">Este es un correo automático. Por favor no lo respondas directamente.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
