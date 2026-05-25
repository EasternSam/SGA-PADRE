<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Padres — Acceso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="text-6xl mb-3">🎓</div>
            <h1 class="text-3xl font-bold text-white">Portal de Padres</h1>
            <p class="text-blue-200 text-sm mt-2">Consulta las calificaciones, asistencia y pagos de tu hijo/a</p>
        </div>

        <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 shadow-2xl border border-white/20">
            @if($errors->any())
                <div class="bg-red-500/20 border border-red-400/30 rounded-lg p-3 mb-4 text-sm text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('parent.authenticate') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-blue-100 mb-1">Token de Acceso</label>
                    <input type="text" name="token" required placeholder="Ingrese su token..."
                        class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-white placeholder-blue-300/50 focus:border-blue-400 focus:ring-blue-400" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-100 mb-1">PIN (6 dígitos)</label>
                    <input type="password" name="pin" required maxlength="6" placeholder="••••••"
                        class="w-full rounded-xl bg-white/10 border border-white/20 px-4 py-3 text-white placeholder-blue-300/50 focus:border-blue-400 focus:ring-blue-400 text-center text-2xl tracking-[0.5em]" />
                </div>
                <button type="submit" class="w-full rounded-xl bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 text-sm transition shadow-lg">
                    🔑 Acceder al Portal
                </button>
            </form>
        </div>

        <p class="text-center text-blue-300/60 text-xs mt-6">
            Solicite sus credenciales de acceso en la administración del centro educativo.
        </p>
    </div>
</body>
</html>
