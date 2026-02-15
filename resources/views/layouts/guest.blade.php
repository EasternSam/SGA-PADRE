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

            // 1. Intentar obtener el color configurado ('navbar_color')
            $bg = SystemOption::getOption('navbar_color');

            // 2. Si está vacío, intentar 'brand_primary_color'
            if (empty($bg) || $bg === '#') {
                $bg = SystemOption::getOption('brand_primary_color');
            }

            // 3. Validación y Fallback
            // Si sigue vacío, es inválido, o es explícitamente 'red' o '#red' (valores que causan el problema), usar default.
            // También verificamos si es un hexadecimal corto inválido o similar.
            if (empty($bg) || $bg === '#' || $bg === 'red' || $bg === '#red') {
                $bg = 'linear-gradient(to right, #1e3a8a, #000000)'; // Azul oscuro a negro
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
                min-height: 100vh; /* Asegurar altura mínima */
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        {{-- Depuración: Ver en código fuente --}}
        <!-- DEBUG COLOR: "{{ $bg }}" -->

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 dynamic-bg">
            
            <!-- Logo -->
            <div class="mb-6">
                <a href="/" wire:navigate>
                    <div class="flex items-center justify-center">
                        @if($logoUrl)
                            {{-- Logo personalizado --}}
                            <img src="{{ asset($logoUrl) }}" 
                                 alt="{{ config('app.name') }}" 
                                 class="h-24 w-auto object-contain bg-white/10 rounded-xl p-3 backdrop-blur-md shadow-xl border border-white/20">
                        @else
                            {{-- Logo por defecto --}}
                            <div class="p-3 bg-white/10 rounded-full backdrop-blur-sm">
                                <x-application-logo class="w-20 h-20 fill-current text-white drop-shadow-lg" />
                            </div>
                        @endif
                    </div>
                </a>
            </div>

            <!-- Tarjeta de Login -->
            <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-xl border border-gray-100">
                {{ $slot }}
            </div>
            
            <!-- Footer simple -->
            <div class="mt-8 text-white/60 text-xs text-center">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
            </div>
        </div>
    </body>
</html>