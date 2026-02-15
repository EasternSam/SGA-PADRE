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

            // 1. Obtener valores base de configuración
            $bg = SystemOption::getOption('navbar_color');
            $type = SystemOption::getOption('navbar_type'); // 'solid' o 'gradient'

            // 2. Lógica de AUTO-CORRECCIÓN para degradados mal formados
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
    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{-- 
            Contenedor Principal con Fondo Dinámico.
            Usamos style inline para el background dinámico (PHP), 
            pero clases Tailwind para el posicionamiento y tamaño.
        --}}
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-cover bg-center bg-no-repeat"
             style="background: {{ $bg }} !important;">
            
            <!-- Logo con efectos Tailwind -->
            <div class="mb-8 transform hover:scale-105 transition-transform duration-300 ease-in-out">
                <a href="/" wire:navigate>
                    <div class="flex items-center justify-center">
                        @if($logoUrl)
                            {{-- Logo personalizado --}}
                            <img src="{{ asset($logoUrl) }}" 
                                 alt="{{ config('app.name') }}" 
                                 class="h-28 w-auto object-contain bg-white/10 rounded-2xl p-4 backdrop-blur-md shadow-2xl border border-white/20 ring-1 ring-white/10">
                        @else
                            {{-- Logo por defecto --}}
                            <div class="p-4 bg-white/10 rounded-full backdrop-blur-md shadow-xl border border-white/20 ring-1 ring-white/10">
                                <x-application-logo class="w-20 h-20 fill-current text-white drop-shadow-md" />
                            </div>
                        @endif
                    </div>
                </a>
            </div>

            <!-- Tarjeta de Login con "Glassmorphism" usando Tailwind -->
            <div class="w-full sm:max-w-md px-8 py-10 bg-white/90 backdrop-blur-sm border border-white/50 shadow-2xl overflow-hidden sm:rounded-2xl relative ring-1 ring-black/5">
                <!-- Decoración sutil superior -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-gray-300/50 to-transparent"></div>
                
                {{ $slot }}
            </div>
            
            <!-- Footer -->
            <div class="mt-8 text-white/90 text-sm font-medium text-center drop-shadow-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }}. <span class="opacity-80 font-normal">Todos los derechos reservados.</span>
            </div>
        </div>
    </body>
</html>