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

        <!-- Tailwind CSS CDN -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @php
            use App\Models\SystemOption;

            // --- LÓGICA DE FONDO DINÁMICO ---
            
            // 1. Obtener color configurado ('navbar_color' es el nuevo, 'brand_primary_color' el antiguo)
            $bg = SystemOption::getOption('navbar_color');
            $type = SystemOption::getOption('navbar_type');

            // 2. Auto-corrección si es un degradado pero viene como texto plano
            if ($type === 'gradient' && !str_contains($bg, 'gradient')) {
                $start = SystemOption::getOption('navbar_gradient_start', '#1e3a8a');
                $end = SystemOption::getOption('navbar_gradient_end', '#000000');
                $dir = SystemOption::getOption('navbar_gradient_direction', 'to right');
                $bg = "linear-gradient({$dir}, {$start}, {$end})";
            }

            // 3. Fallback de seguridad
            if (empty($bg) || $bg === '#' || $bg === 'red' || $bg === '#red') {
                $bg = SystemOption::getOption('brand_primary_color');
            }
            if (empty($bg) || $bg === '#') {
                $bg = 'linear-gradient(to right, #1e3a8a, #000000)';
            }

            // --- LÓGICA DE LOGO ---
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp
        
        <style>
             body { font-family: 'Figtree', sans-serif; }
             /* Clase para aplicar el fondo dinámico vía CSS inline */
             .dynamic-bg {
                background: {{ $bg }} !important;
                background-size: cover !important;
                background-position: center !important;
             }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-white">
        
        <div class="min-h-screen flex w-full">
            
            <!-- SECCIÓN IZQUIERDA: Fondo Dinámico (Visible solo en escritorio) -->
            <!-- Aquí aplicamos tu degradado/color personalizado -->
            <div class="hidden lg:flex w-1/2 flex-col justify-center items-center relative dynamic-bg text-white overflow-hidden">
                <!-- Overlay sutil para textura (opcional) -->
                <div class="absolute inset-0 bg-black/10"></div>
                
                <!-- Contenido decorativo sobre el degradado -->
                <div class="relative z-10 text-center px-12">
                    <h2 class="text-4xl font-bold mb-4 tracking-tight">Bienvenido</h2>
                    <p class="text-lg opacity-90 font-light">
                        Accede a tu plataforma de gestión académica.
                    </p>
                </div>

                <!-- Decoración de círculos sutiles (opcional, da un toque moderno) -->
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            </div>

            <!-- SECCIÓN DERECHA: Formulario de Login -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 sm:p-12 bg-white">
                <div class="w-full max-w-md space-y-8">
                    
                    <!-- Logo (Versión para fondo claro) -->
                    <div class="flex justify-center">
                        <a href="/" wire:navigate>
                            @if($logoUrl)
                                <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="h-24 w-auto object-contain hover:scale-105 transition-transform duration-300">
                            @else
                                {{-- Usamos texto gris oscuro o el color primario si pudiéramos extraerlo --}}
                                <x-application-logo class="w-20 h-20 fill-current text-gray-700" />
                            @endif
                        </a>
                    </div>

                    <!-- Slot del Formulario -->
                    <div class="mt-8">
                        {{ $slot }}
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 text-center text-xs text-gray-400">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                    </div>
                </div>
            </div>
            
        </div>
    </body>
</html>