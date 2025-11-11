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
    </head>
    <!-- --- ¡ACTUALIZADO! --- -->
    {{-- Fondo rediseñado para 'sga-primary' --}}
    <body class="font-sans text-sga-text antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-sga-primary p-4 sm:p-6">
            
            <!-- Logo -->
            <div>
                <a href="/" wire:navigate>
                    {{-- --- ¡¡¡CORRECCIÓN!!! --- --}}
                    {{-- Se eliminó el <span> con el texto y se aseguró que el logo herede el color --}}
                    <div class="flex items-center gap-2 text-white">
                        {{-- --- ¡ACTUALIZADO! --- Cambiado de h-10 a h-14 --}}
                        <x-application-logo class="block h-14 w-auto fill-current" />
                        {{-- <span class="text-2xl font-semibold text-white">{{ config('app.name', 'Laravel') }}</span> --}}
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