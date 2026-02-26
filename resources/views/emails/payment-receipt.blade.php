<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $payment->status === 'Pendiente' ? 'Aviso de Deuda Generada' : 'Confirmación de Pago' }} - {{ config('app.name') }}</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f7f6; color: #333333; }
        .wrapper { width: 100%; table-layout: fixed; background-color: #f4f7f6; padding-bottom: 40px; }
        .webkit { max-width: 600px; margin: 0 auto; }
        .outer-table { width: 100%; max-width: 600px; margin: 0 auto; border-spacing: 0; }
        .header { padding: 40px 20px 30px 20px; text-align: center; }
        .header img { max-width: 200px; height: auto; }
        .main-card { background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); overflow: hidden; border: 1px solid #eaebed; }
        .accent-bar { height: 4px; }
        .card-header { background-color: #f8f9fa; padding: 15px 35px; text-align: center; border-bottom: 1px solid #eaebed; }
        .card-header h2 { margin: 0; font-size: 20px; color: #333; }
        .content { padding: 30px 35px; font-size: 16px; line-height: 1.6; color: #4a4a4a; }
        .content p { margin-top: 0; margin-bottom: 20px; }
        .content ul { padding-left: 20px; margin-bottom: 20px; }
        .content li { margin-bottom: 8px; list-style-type: none; }
        .amount { font-size: 18px; font-weight: bold; color: #2d3748; }
        .status-pending { color: #e53e3e; font-weight: bold; }
        .status-paid { color: #38a169; font-weight: bold; }
        .footer { padding: 30px 20px; text-align: center; font-size: 13px; color: #888888; line-height: 1.5; }
        .footer a { color: #888888; text-decoration: underline; }
    </style>
</head>
<body>
    @php
        $brandColor = \App\Models\Setting::val('brand_primary_color', '#1e40af');
        
        $logo = \App\Models\Setting::get('institution_logo');
        if (!$logo && class_exists('\App\Models\SystemOption')) {
            $logo = \App\Models\SystemOption::getOption('logo');
        }
        $logoUrl = $logo ? (\Illuminate\Support\Str::startsWith($logo, 'http') ? $logo : asset($logo)) : asset('centuu.png');
    @endphp

    <center class="wrapper">
        <div class="webkit">
            <table class="outer-table">
                <!-- CABECERA C/ LOGO -->
                <tr>
                    <td class="header">
                        <img src="{{ $logoUrl }}" alt="{{ config('app.name') }}" title="{{ config('app.name') }}" style="max-width: 200px; height: auto;">
                    </td>
                </tr>

                <!-- CUERPO DEL MENSAJE (TARJETA BLANCA) -->
                <tr>
                    <td>
                        <table width="100%" class="main-card">
                            <tr>
                                <td class="accent-bar" style="background-color: {{ $brandColor }};"></td>
                            </tr>
                            <tr>
                                <td class="card-header">
                                    @if($payment->status === 'Pendiente')
                                        <h2>Aviso de Deuda Generada</h2>
                                    @else
                                        <h2>Confirmación de Pago</h2>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="content">
                                    <p>Hola {{ $payment->student->first_name }},</p>
                                    
                                    @if($payment->status === 'Pendiente')
                                        <p>Se ha generado un nuevo compromiso de pago en tu cuenta. Por favor, realiza el pago antes de la fecha límite.</p>
                                    @else
                                        <p>Hemos recibido tu pago correctamente. A continuación los detalles:</p>
                                    @endif
                                    
                                    <ul>
                                        <li><strong>Concepto:</strong> {{ $payment->paymentConcept->name ?? $payment->description }}</li>
                                        <li><strong>Monto:</strong> <span class="amount">RD$ {{ number_format($payment->amount, 2) }}</span></li>
                                        <li><strong>Fecha:</strong> {{ $payment->created_at->format('d/m/Y h:i A') }}</li>
                                        
                                        @if($payment->status === 'Pendiente')
                                            <li><strong>Estado:</strong> <span class="status-pending">PENDIENTE</span></li>
                                            @if($payment->due_date)
                                                <li><strong>Fecha Límite:</strong> {{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</li>
                                            @endif
                                        @else
                                            <li><strong>Método:</strong> {{ $payment->gateway }}</li>
                                            <li><strong>Estado:</strong> <span class="status-paid">PAGADO</span></li>
                                        @endif
                                    </ul>

                                    <p>Adjunto a este correo encontrarás el documento con el detalle en formato PDF.</p>
                                    
                                    @if($payment->status === 'Pendiente')
                                        <p>Puedes realizar el pago a través de tu portal de estudiante o en nuestras oficinas.</p>
                                    @else
                                        <p>Gracias por tu pago.</p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- PIE DE PÁGINA (FOOTER) -->
                <tr>
                    <td class="footer">
                        <p style="margin: 0 0 10px 0;">
                            <strong>{{ config('app.name') }}</strong><br>
                            Este es un mensaje automático generado por nuestro sistema administrativo.
                        </p>
                        <p style="margin: 0;">&copy; {{ date('Y') }} Todos los derechos reservados.</p>
                    </td>
                </tr>
            </table>
        </div>
    </center>
</body>
</html>