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

            // Logo
            $logoUrl = SystemOption::getOption('logo');
            if (empty($logoUrl)) {
                $logoUrl = SystemOption::getOption('institution_logo');
            }
        @endphp
        
        <style>
             /* Personalización adicional si es necesaria */
             body {
                font-family: 'Figtree', sans-serif;
             }
        </style>
    </head>
    <body class="font-sans antialiased text-gray-900 bg-gray-100">
        <div class="min-h-screen flex">
            
            <!-- Sección Izquierda (Imagen de Fondo) -->
            <div class="hidden lg:block lg:w-1/2 relative">
                <img 
                    src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" 
                    alt="Montañas" 
                    class="absolute inset-0 w-full h-full object-cover"
                />
                <!-- Superposición oscura opcional para mejorar contraste si pones texto encima -->
                {{-- <div class="absolute inset-0 bg-black bg-opacity-20"></div> --}}
            </div>

            <!-- Sección Derecha (Formulario de Login) -->
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-8 bg-white">
                <div class="w-full max-w-md">
                    <!-- Logo -->
                    <div class="flex justify-center mb-8">
                        <a href="/" wire:navigate>
                            @if($logoUrl)
                                <img src="{{ asset($logoUrl) }}" alt="{{ config('app.name') }}" class="h-20 w-auto object-contain">
                            @else
                                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                            @endif
                        </a>
                    </div>

                    <!-- Slot para el contenido del formulario (Login, Registro, etc.) -->
                    {{ $slot }}

                    <!-- Footer -->
                    <div class="mt-8 text-center text-sm text-gray-500">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                    </div>
                </div>
            </div>
            
        </div>
    </body>
</html>