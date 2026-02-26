<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud Aprobada - {{ config('app.name') }}</title>
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
        .content { padding: 40px 35px; font-size: 16px; line-height: 1.6; color: #4a4a4a; }
        .content h2 { margin-top: 0; color: #333; }
        .content p { margin-top: 0; margin-bottom: 20px; }
        .content ul { margin-top: 5px; margin-bottom: 0; padding-left: 20px; }
        .content li { margin-bottom: 5px; }
        .status-box { background-color: #ebf8ff; border: 1px solid #bee3f8; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .payment-box { margin-top: 15px; padding: 15px; border: 1px dashed #cbd5e0; background-color: #fffaf0; border-radius: 5px; }
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
                                <td class="content">
                                    <h2>Solicitud Aprobada</h2>
                                    <p>Hola {{ $request->student->first_name }},</p>
                                    
                                    <p>Tu solicitud realizada el {{ $request->created_at->format('d/m/Y') }} ha sido procesada.</p>
                                    
                                    <div class="status-box">
                                        <strong>Tipo de Solicitud:</strong> {{ ucfirst(str_replace('_', ' ', $request->type)) }}<br><br>
                                        <strong>Estado:</strong> <span style="color: green; font-weight: bold;">APROBADO</span>
                                    </div>

                                    @if($request->admin_notes)
                                        <p><strong>Nota de la administración:</strong></p>
                                        <p><i>{{ $request->admin_notes }}</i></p>
                                    @endif

                                    {{-- Detalles del pago si existe --}}
                                    @if($request->payment)
                                        <div class="payment-box">
                                            <p style="margin: 0; color: #744210; font-weight: bold;">Información de Cargo Generado:</p>
                                            <ul style="color: #2d3748;">
                                                <li><strong>Concepto:</strong> {{ $request->payment->description ?? 'Servicio' }}</li>
                                                <li><strong>Monto a Pagar:</strong> RD$ {{ number_format($request->payment->amount, 2) }}</li>
                                                <li><strong>Estado del Pago:</strong> 
                                                    <span style="color: {{ $request->payment->status == 'Completado' ? 'green' : 'red' }}; font-weight: bold;">
                                                        {{ strtoupper($request->payment->status) }}
                                                    </span>
                                                </li>
                                                @if($request->payment->due_date && $request->payment->status == 'Pendiente')
                                                    <li><strong>Fecha Límite:</strong> {{ \Carbon\Carbon::parse($request->payment->due_date)->format('d/m/Y') }}</li>
                                                @endif
                                            </ul>
                                        </div>
                                        <p style="margin-top: 20px;">Puedes realizar el pago a través de tu portal de estudiante.</p>
                                    @else
                                        <p style="margin-top: 20px;">Si esta solicitud generó algún cargo, podrás verlo reflejado en tu estado de cuenta.</p>
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