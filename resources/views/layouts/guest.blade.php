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

            // 1. Obtener valores base
            $bg = SystemOption::getOption('navbar_color');
            $type = SystemOption::getOption('navbar_type'); // 'solid' o 'gradient'

            // 2. Lógica de AUTO-CORRECCIÓN:
            if ($type === 'gradient' && !str_contains($bg, 'gradient')) {
                $start = SystemOption::getOption('navbar_gradient_start', '#1e3a8a');
                $end = SystemOption::getOption('navbar_gradient_end', '#000000');
                $dir = SystemOption::getOption('navbar_gradient_direction', 'to right');
                
                $bg = "linear-gradient({$dir}, {$start}, {$end})";
            }

            // 3. Fallback de seguridad final
            if (empty($bg) || $bg === '#') {
                $bg = 'linear-gradient(to right, #1e3a8a, #000000)';
            }
            
            // Logo
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp
        
        <!-- Estilos críticos en línea -->
        <style>
            .dynamic-bg {
                background: {{ $bg }} !important;
                background-size: cover !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                min-height: 100vh;
            }
            /* Efecto Glassmorphism para la tarjeta */
            .glass-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.5);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{-- Contenedor Principal con Fondo Dinámico --}}
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 dynamic-bg">
            
            <!-- Logo con efecto flotante -->
            <div class="mb-8 transform hover:scale-105 transition-transform duration-300">
                <a href="/" wire:navigate>
                    <div class="flex items-center justify-center">
                        @if($logoUrl)
                            {{-- Logo personalizado --}}
                            <img src="{{ asset($logoUrl) }}" 
                                 alt="{{ config('app.name') }}" 
                                 class="h-28 w-auto object-contain bg-white/20 rounded-2xl p-4 backdrop-blur-md shadow-2xl border border-white/30">
                        @else
                            {{-- Logo por defecto --}}
                            <div class="p-4 bg-white/20 rounded-full backdrop-blur-md shadow-xl border border-white/30">
                                <x-application-logo class="w-20 h-20 fill-current text-white drop-shadow-lg" />
                            </div>
                        @endif
                    </div>
                </a>
            </div>

            <!-- Tarjeta de Login Mejorada -->
            <div class="w-full sm:max-w-md px-8 py-10 glass-card shadow-2xl overflow-hidden sm:rounded-2xl relative">
                <!-- Decoración sutil superior -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-gray-200 to-transparent opacity-50"></div>
                
                {{ $slot }}
            </div>
            
            <!-- Footer simple -->
            <div class="mt-8 text-white/80 text-sm font-medium text-center drop-shadow-md">
                &copy; {{ date('Y') }} {{ config('app.name') }}. <span class="opacity-75 font-normal">Todos los derechos reservados.</span>
            </div>
        </div>
    </body>
</html>