<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva Calificación - {{ config('app.name') }}</title>
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
        .content h2 { color: #333; margin-top: 0; }
        .content p { margin-top: 0; margin-bottom: 20px; }
        .content p:last-child { margin-bottom: 0; }
        .grade-box { background-color: #f0fff4; border: 1px solid #c6f6d5; padding: 15px; text-align: center; margin: 20px 0; border-radius: 5px; }
        .grade-value { font-size: 24px; font-weight: bold; color: #2f855a; }
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
                                    <h2>Nueva Calificación Publicada</h2>
                                    <p>Hola {{ $enrollment->student->first_name }},</p>
                                    
                                    <p>El profesor ha publicado tu calificación final para el módulo:</p>
                                    <p><strong>{{ $enrollment->courseSchedule->module->name }}</strong></p>
                                    
                                    <div class="grade-box">
                                        Tu Calificación Final:
                                        <div class="grade-value">{{ number_format($enrollment->final_grade, 2) }}</div>
                                    </div>

                                    <p>Puedes ver más detalles ingresando a tu portal de estudiante.</p>
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