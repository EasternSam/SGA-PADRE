<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SGA Padre') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            use App\Models\SystemOption;

            // 1. Intentar obtener el color configurado en el panel de diseño ('navbar_color')
            $loginBackground = SystemOption::getOption('navbar_color');

            // 2. Si no existe, intentar obtener el color de marca antiguo ('brand_primary_color')
            if (empty($loginBackground)) {
                $loginBackground = SystemOption::getOption('brand_primary_color');
            }

            // 3. Fallback final: Si todo falla o devuelve vacío, usar el degradado azul por defecto
            if (empty($loginBackground) || $loginBackground == '#') {
                $loginBackground = 'linear-gradient(to right, #1e3a8a, #000000)';
            }

            // Lógica similar para el logo: preferir 'logo' sobre 'institution_logo'
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp
    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{-- 
            Usamos style inline con !important para garantizar que sobrescriba cualquier clase CSS (como bg-red-*) 
            que pudiera estar viniendo de app.css o clases utilitarias heredadas.
        --}}
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0"
             style="background: {{ $loginBackground }} !important; background-size: cover; background-position: center;">
            
            <!-- Logo -->
            <div>
                <a href="/">
                    <div class="flex items-center gap-2">
                        @if($logoUrl)
                            {{-- Logo personalizado con fondo translúcido --}}
                            <img src="{{ asset($logoUrl) }}" 
                                 alt="{{ config('app.name') }}" 
                                 class="w-20 h-20 fill-current text-white bg-white/10 rounded-lg p-2 shadow-lg object-contain backdrop-blur-sm">
                        @else
                            {{-- Logo por defecto (SVG de Laravel/App) --}}
                            <x-application-logo class="w-20 h-20 fill-current text-white drop-shadow-md" />
                        @endif
                    </div>
                </a>
            </div>

            <!-- Tarjeta de Login -->
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>