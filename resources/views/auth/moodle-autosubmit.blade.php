<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conectando al Aula Virtual...</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    
    <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-indigo-500 border-t-transparent mb-4"></div>
        <h2 class="text-xl font-semibold text-gray-700">Entrando al Aula Virtual...</h2>
        <p class="text-gray-500 text-sm mt-2">Por favor espere, le estamos identificando.</p>
    </div>

    {{-- Formulario Oculto que se envía automáticamente --}}
    <form id="moodle-login-form" action="{{ $action }}" method="POST" class="hidden">
        <input type="text" name="username" value="{{ $username }}">
        <input type="password" name="password" value="{{ $password }}">
    </form>

    <script>
        // Enviar el formulario automáticamente al cargar
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('moodle-login-form').submit();
            }, 500); // Pequeño delay para asegurar carga
        });
    </script>
</body>
</html>