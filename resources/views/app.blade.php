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

    <!-- Estilos de Livewire -->
    @livewireStyles
</head>

<!-- --- MEJORA: Alpine.js 'open' controla el sidebar --- -->
<body class="font-sans antialiased" x-data="{ open: window.innerWidth > 1024 ? true : false }">
    {{-- MEJORA: Fondo de página temático 'sga-bg' (de tailwind.config.js) --}}
    <div class="min-h-screen bg-sga-bg">

        <!-- --- MEJORA: Incluir el sidebar (navigation.blade.php) --- -->
        @include('Layouts.navigation')

        <!-- --- MEJORA: Contenido principal con padding izquierdo (en escritorio) --- -->
        <div class="flex min-h-screen flex-col transition-all duration-300"
             :class="open ? 'lg:pl-64' : 'lg:pl-0'">

            <!-- Encabezado superior (Header) -->
            {{-- MEJORA: Header rediseñado para coincidir con la inspiración --}}
            <header class="sticky top-0 z-20 border-b border-sga-gray bg-sga-card shadow-sm">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        
                        <!-- Lado Izquierdo: Botón Hamburger (móvil) y Título (desktop) -->
                        <div class="flex items-center gap-4">
                            <!-- Botón para abrir/cerrar sidebar en móvil -->
                            <button @click="open = !open"
                                class="inline-flex items-center justify-center rounded-md p-2 text-sga-text-light transition hover:bg-sga-bg hover:text-sga-text focus:bg-sga-bg focus:text-sga-text focus:outline-none lg:hidden">
                                <span class="sr-only">Open sidebar</span>
                                <!-- Icono: Barras (Hamburger) -->
                                <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>

                            <!-- Título de la página (slot 'header') -->
                            <div class="hidden lg:block">
                                @if (isset($header))
                                    <div>
                                        {{ $header }}
                                    </div>
                                @endif
                            </div>
                        </div>


                        <!-- Lado Derecho: Buscador y Menú de Usuario -->
                        <div class="flex items-center gap-4">
                            
                            <!-- Placeholder Buscador (como en la imagen) -->
                            <div class="relative hidden md:block">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-sga-text-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                    </svg>
                                </span>
                                <input type="text" placeholder="Search..."
                                    class="w-full rounded-md border-sga-gray py-2 pl-10 pr-4 text-sm text-sga-text focus:border-sga-secondary focus:ring-sga-secondary">
                            </div>

                            <!-- --- ¡¡¡MOVIDO!!! --- -->
                            <!-- Menú de Usuario (movido desde el sidebar) -->
                            <div class="hidden sm:flex sm:items-center">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-sga-text-light bg-sga-card hover:text-sga-text focus:outline-none transition ease-in-out duration-150">
                                            <div>{{ Auth::user()->name }}</div>
                                            <!-- Placeholder para imagen de perfil -->
                                            <img class="h-8 w-8 rounded-full object-cover" src="https://placehold.co/100x100/e2e8f0/64748b?text={{ substr(Auth::user()->name, 0, 1) }}" alt="Avatar">
                                        </button>
                                    </x-slot>
                        
                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('profile.edit')" wire:navigate>
                                            {{ __('Profile') }}
                                        </x-dropdown-link>
                        
                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')"
                                                    onclick="event.preventDefault();
                                                                this.closest('form').submit();"
                                                    class="text-sga-danger hover:bg-red-50">
                                                {{ __('Log Out') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Título de la página (Visible en móvil, debajo del header) -->
                @if (isset($header))
                    <div class="lg:hidden p-4 border-b border-sga-gray bg-sga-card">
                        {{ $header }}
                    </div>
                @endif
            </header>

            <!-- Contenido de la Página (Slot principal) -->
            {{-- MEJORA: Padding añadido al contenedor principal --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

        </div> <!-- Fin de 'lg:pl-64' -->
    </div>
    
    <!-- Scripts de Livewire -->
    @livewireScripts
</body>

</html>