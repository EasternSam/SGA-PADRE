<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Comunicación Oficial</title>
    <style>
        /* CSS Reset básico para clientes de correo */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        
        /* Estilos Base */
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7f6;
            color: #333333;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f6;
            padding-bottom: 40px;
        }
        .webkit {
            max-width: 600px;
            margin: 0 auto;
        }
        .outer-table {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border-spacing: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
        }
        .header {
            padding: 40px 20px 30px 20px;
            text-align: center;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .main-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #eaebed;
        }
        .accent-bar {
            height: 4px;
        }
        .content {
            padding: 40px 35px;
            font-size: 16px;
            line-height: 1.6;
            color: #4a4a4a;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .content p:last-child {
            margin-bottom: 0;
        }
        .footer {
            padding: 30px 20px;
            text-align: center;
            font-size: 13px;
            color: #888888;
            line-height: 1.5;
        }
        .footer a {
            color: #888888;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    @php
        $brandColor = \App\Models\Setting::val('brand_primary_color', '#1e40af'); // Azul corporativo por defecto
        $logo = \App\Models\Setting::val('brand_logo');
    @endphp

    <center class="wrapper">
        <div class="webkit">
            <table class="outer-table">
                <!-- CABECERA C/ LOGO -->
                <tr>
                    <td class="header">
                        @if($logo)
                            <img src="{{ url($logo) }}" alt="{{ config('app.name') }}" title="{{ config('app.name') }}">
                        @else
                            <!-- Fallback en caso de que borren el logo, intentamos buscar uno por defecto -->
                            <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" title="{{ config('app.name') }}" style="max-width: 200px;">
                        @endif
                    </td>
                </tr>

                <!-- CUERPO DEL MENSAJE (TARJETA BLANCA) -->
                <tr>
                    <td>
                        <table width="100%" class="main-card">
                            <!-- Barra de Acento Arriba -->
                            <tr>
                                <td class="accent-bar" style="background-color: {{ $brandColor }};"></td>
                            </tr>
                            <!-- Contenido -->
                            <tr>
                                <td class="content">
                                    {!! nl2br(e($customMessage)) !!}
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
                            Este es un mensaje automático generado por nuestro sistema administrativo. Por favor, no responda directamente a este correo.
                        </p>
                        <p style="margin: 0;">
                            &copy; {{ date('Y') }} Todos los derechos reservados.<br>
                            <a href="{{ url('/') }}">Visitar nuestro portal</a>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
    </center>
</body>
</html>