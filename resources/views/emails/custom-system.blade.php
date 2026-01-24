<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #f9fafb; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 5px; }
        .footer { font-size: 12px; color: #6b7280; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ config('app.name') }} - Comunicación Oficial</h2>
        </div>
        
        <div class="content">
            <!-- nl2br permite que los saltos de línea en el textarea se vean en el correo -->
            {!! nl2br(e($customMessage)) !!}
        </div>

        <div class="footer">
            <p>Este correo fue enviado desde la plataforma administrativa de {{ config('app.name') }}.</p>
            <p>&copy; {{ date('Y') }} Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>