<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-sga-background">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- ¡¡¡AQUÍ ESTÁ LA CORRECCIÓN!!! --}}
    {{-- Link para que funcionen los íconos (fas fa-eye, etc.) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    {{-- Esta directiva de Vite carga tu app.css y app.js --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- ¡¡¡CORRECCIÓN AÑADIDA!!! -->
    <!-- Carga los estilos de Livewire (para modales, etc.) -->
    @livewireStyles

</head>

<body class="h-full font-sans antialiased">
    <div x-data="{ open: false }" @keydown.window.escape="open = false" class="h-full">

        <!-- Navigation -->
        @include('layouts.navigation')

        <!-- Main Content Area -->
        <div class="flex h-full flex-col lg:pl-64">

            <!-- Top bar -->
            <header class="sticky top-0 z-10 flex h-16 flex-shrink-0 border-b border-sga-gray bg-white shadow-sm">
                <div class="flex flex-1 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <!-- Hamburger button -->
                    <button @click.stop="open = !open" type="button"
                        class="-m-2.5 p-2.5 text-sga-text-light lg:hidden">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <!-- Separador (solo se muestra en móvil) -->
                    <div class="h-6 w-px bg-sga-gray lg:hidden" aria-hidden="true"></div>

                    <div class="flex flex-1 items-center justify-between gap-x-4 self-stretch lg:gap-x-6">
                        <!-- Título de la página (slot 'header') -->
                        <div class="flex-1">
                            @if (isset($header))
                                <div
                                    class="flex h-full items-center text-base font-semibold leading-6 text-sga-text sm:text-xl">
                                    {{ $header }}
                                </div>
                            @endif
                        </div>

                        <!-- Menú de usuario (opcional en la barra superior) -->
                        <div class="hidden lg:block">
                            <div class="hidden sm:flex sm:items-center">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-sga-text-light bg-white hover:text-sga-text focus:outline-none transition ease-in-out duration-150">
                                            <div>{{ Auth::user()->name }}</div>
                                            <!-- Placeholder para imagen de perfil -->
                                            <img class="h-8 w-8 rounded-full object-cover" src="https://placehold.co/100x100/e2e8f0/64748b?text={{ substr(Auth::user()->name, 0, 1) }}" alt="Avatar">
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('profile.edit')" wire:navigate> 
                                            {{ __('Mi Perfil') }}
                                        </x-dropdown-link>

                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')"
                                                    onclick="event.preventDefault();
                                                                this.closest('form').submit();"
                                                    class="text-sga-danger hover:bg-red-50">
                                                {{ __('Cerrar Sesión') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto">
                <div class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- ¡¡¡CORRECCIÓN AÑADIDA!!! -->
    <!-- Carga los scripts de Livewire (para wire:click, etc.) -->
    @livewireScripts
</body>

</html>