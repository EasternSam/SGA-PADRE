<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #4f46e5; }
        .content { color: #374151; line-height: 1.6; }
        .credentials-box { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin: 20px 0; text-align: center; }
        .credential-label { font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .credential-value { font-size: 18px; font-weight: bold; color: #111827; margin-bottom: 12px; font-family: monospace; }
        .btn { display: inline-block; background-color: #4f46e5; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 20px; }
        .btn:hover { background-color: #4338ca; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
        </div>
        
        <div class="content">
            <p>Hola, <strong>{{ $user->name }}</strong> 👋</p>
            
            <p>Tu cuenta en nuestra <strong>Aula Virtual (Moodle)</strong> ha sido creada o actualizada exitosamente.</p>
            <p>Aquí tienes tus credenciales de acceso para ingresar a tus clases:</p>
            
            <div class="credentials-box">
                <div class="credential-label">Usuario / Correo</div>
                <div class="credential-value">{{ $user->email }}</div>
                
                <div class="credential-label">Contraseña Temporal</div>
                <div class="credential-value" style="color: #ef4444;">{{ $password }}</div>
            </div>
            
            <p style="text-align: center;">
                <a href="{{ $moodleUrl }}" class="btn">Acceder al Aula Virtual</a>
            </p>
            
            <p style="font-size: 13px; color: #6b7280; text-align: center; margin-top: 20px;">
                <em>Te recomendamos cambiar tu contraseña al iniciar sesión por primera vez.</em>
            </p>
        </div>
        
        <div class="footer">
            <p>Este es un correo automático, por favor no respondas a esta dirección.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>