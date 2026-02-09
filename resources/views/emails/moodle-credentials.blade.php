<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; background-color: #f3f4f6; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; }
        .logo { font-size: 24px; font-weight: bold; color: #4f46e5; text-align: center; margin-bottom: 20px;}
        .box { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 15px; text-align: center; margin: 20px 0; }
        .btn { display: inline-block; background-color: #4f46e5; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">{{ config('app.name') }}</div>
        <p>Hola, <strong>{{ $user->name }}</strong> 👋</p>
        <p>Tu cuenta en el Aula Virtual ha sido creada. Aquí tienes tus credenciales:</p>
        
        <div class="box">
            <p><strong>Usuario:</strong> {{ $username }}</p>
            <p><strong>Contraseña:</strong> <span style="color:#d32f2f">{{ $password }}</span></p>
        </div>
        
        <p style="text-align: center;">
            <a href="{{ $moodleUrl }}" class="btn">Ir al Aula Virtual</a>
        </p>
    </div>
</body>
</html>