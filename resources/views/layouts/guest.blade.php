<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            // Obtener configuración de apariencia (color/degradado y logo)
            // Se usa el mismo key que en el sidebar para mantener consistencia
            $loginBackground = \App\Models\SystemOption::getOption('brand_primary_color', '#1e3a8a');
            $logoUrl = \App\Models\SystemOption::getOption('institution_logo');
        @endphp
    </head>
    <!-- --- ¡ACTUALIZADO! --- -->
    <body class="font-sans text-sga-text antialiased">
        {{-- Usamos style inline para permitir degradados CSS complejos que vienen de la BD --}}
        <div class="flex min-h-screen flex-col items-center justify-center p-4 sm:p-6"
             style="background: {{ $loginBackground }};">
            
            <!-- Logo -->
            <div>
                <a href="/" wire:navigate>
                    <div class="flex items-center gap-2 text-white">
                        @if($logoUrl)
                            {{-- Si hay logo personalizado, lo mostramos con un contenedor translúcido --}}
                            <img src="{{ asset($logoUrl) }}" 
                                 alt="{{ config('app.name') }}" 
                                 class="block h-20 w-auto object-contain bg-white/10 rounded-lg p-2 backdrop-blur-sm shadow-lg">
                        @else
                            {{-- Logo por defecto --}}
                            <x-application-logo class="block h-14 w-auto fill-current" />
                        @endif
                    </div>
                </a>
            </div>

            <!-- Contenedor/Tarjeta -->
            {{-- Tarjeta rediseñada con 'sga-card' --}}
            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-sga-card shadow-lg overflow-hidden rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>