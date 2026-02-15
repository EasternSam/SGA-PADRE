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
            // Obtener configuración de apariencia. 
            // Si brand_primary_color no existe, usamos un degradado azul por defecto en lugar de un color sólido que podría ser rojo por error en alguna config.
            $loginBackground = \App\Models\SystemOption::getOption('brand_primary_color', 'linear-gradient(to right, #1e3a8a, #000000)');
            $logoUrl = \App\Models\SystemOption::getOption('institution_logo');
        @endphp
    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{-- Usamos style inline para el fondo. Quitamos clases de bg estáticas para evitar conflictos. --}}
        {{-- Forzamos min-h-screen y flex para centrar. --}}
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0"
             style="background: {{ $loginBackground }};">
            
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