<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Credenciales Aula Virtual - {{ config('app.name') }}</title>
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
        .content p { margin-top: 0; margin-bottom: 20px; }
        .content p:last-child { margin-bottom: 0; }
        .box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; text-align: center; margin: 20px 0; border-radius: 5px; }
        .btn { display: inline-block; color: #ffffff !important; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { padding: 30px 20px; text-align: center; font-size: 13px; color: #888888; line-height: 1.5; }
        .footer a { color: #888888; text-decoration: underline; }
    </style>
</head>
<body>
    @php
        $brandColor = \App\Models\Setting::val('brand_primary_color', '#1e40af');
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
                            <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" title="{{ config('app.name') }}" style="max-width: 200px;">
                        @endif
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
                                    <p>Hola, <strong>{{ $user->name }}</strong> 👋</p>
                                    <p>Tu cuenta en el Aula Virtual ha sido creada. Aquí tienes tus credenciales:</p>
                                    
                                    <div class="box">
                                        <p style="margin-bottom:10px;"><strong>Usuario:</strong> {{ $username }}</p>
                                        <p style="margin-bottom:0;"><strong>Contraseña:</strong> <span style="color:#d32f2f; font-weight: bold;">{{ $password }}</span></p>
                                    </div>
                                    
                                    <p style="text-align: center; margin-top: 30px;">
                                        <!-- Envolver el color inline porque el cliente de correo podría limpiar clases -->
                                        <a href="{{ $moodleUrl }}" class="btn" style="background-color: {{ $brandColor }};">Ir al Aula Virtual</a>
                                    </p>
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