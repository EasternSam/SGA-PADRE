<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Academic+ | Configuración de Cliente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-scale; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        
        <!-- Header -->
        <div class="bg-blue-900 px-8 py-6 text-white text-center">
            <h1 class="text-3xl font-bold mb-2">Academic+</h1>
            <p class="text-blue-200">Asistente de Configuración y Activación de Licencia</p>
        </div>

        <!-- Formulario -->
        <div class="p-8">
            @if(session('error'))
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                    <p class="font-bold">Error en la instalación</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form action="{{ route('installer.submit') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    
                    <!-- Sección Licencia -->
                    <div>
                        <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700">1. Activación de Licencia</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Clave de Licencia (Proporcionada por Soporte)</label>
                            <input type="text" name="license_key" value="{{ old('license_key') }}" required placeholder="Ej: SGA-COLEGIO-ABCD-1234" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        </div>
                    </div>

                    <!-- Sección Base de Datos -->
                    <div>
                        <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-gray-700">2. Conexión a Base de Datos</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Host de BD</label>
                                <input type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Puerto de BD</label>
                                <input type="number" name="db_port" value="{{ old('db_port', '3306') }}" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Base de Datos</label>
                                <input type="text" name="db_name" value="{{ old('db_name') }}" required placeholder="sga_db_colegio"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Usuario de BD</label>
                                <input type="text" name="db_user" value="{{ old('db_user') }}" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña de BD</label>
                                <input type="password" name="db_password" placeholder="(En blanco si no tiene)"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg outline-none">
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-8 pt-6 border-t flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition transform hover:-translate-y-0.5">
                        Verificar e Instalar Sistema
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>