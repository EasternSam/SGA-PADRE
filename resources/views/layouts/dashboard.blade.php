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

    {{-- Link para que funcionen los íconos (fas fa-eye, etc.) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    {{-- Esta directiva de Vite carga tu app.css y app.js --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Carga los estilos de Livewire (para modales, etc.) -->
    @livewireStyles
    
    <style>
        /* Custom Scrollbar for Sidebar */
        .sidebar-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="h-full font-sans antialiased text-sga-text bg-sga-background">
    
    <div x-data="{ open: false }" @keydown.window.escape="open = false" class="min-h-screen flex flex-col lg:flex-row">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="open" class="fixed inset-0 z-40 lg:hidden" role="dialog" aria-modal="true" x-cloak>
            <div x-show="open" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80" @click="open = false"></div>

            <div x-show="open" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex w-full max-w-xs flex-1 flex-col bg-white pt-5 pb-4">
                
                <div x-show="open" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute top-0 right-0 -mr-12 pt-2">
                    <button type="button" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" @click="open = false">
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex flex-shrink-0 items-center px-4">
                    <x-application-logo class="h-8 w-auto text-sga-blue" />
                    <span class="ml-3 text-lg font-bold text-sga-blue tracking-tight">SGA PADRE</span>
                </div>
                
                <div class="mt-5 h-0 flex-1 overflow-y-auto">
                    <nav class="space-y-1 px-2">
                        @include('layouts.partials.sidebar-links')
                    </nav>
                </div>
            </div>
        </div>

        <!-- Desktop Sidebar -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-72 lg:flex-col border-r border-gray-200 bg-white shadow-[4px_0_24px_rgba(0,0,0,0.02)] z-30">
            <div class="flex min-h-0 flex-1 flex-col overflow-y-auto sidebar-scrollbar">
                <div class="flex h-16 flex-shrink-0 items-center px-6 border-b border-gray-100">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sga-blue text-white shadow-md group-hover:bg-sga-blue-light transition-colors">
                            <i class="fas fa-graduation-cap text-lg"></i>
                        </div>
                        <span class="text-lg font-bold text-sga-blue tracking-tight">SGA PADRE</span>
                    </a>
                </div>
                <nav class="flex-1 flex flex-col px-4 py-4 space-y-1">
                    @include('layouts.partials.sidebar-links')
                    
                    <!-- Sidebar Footer (User Mini Profile) -->
                    <div class="mt-auto pt-6 pb-4 border-t border-gray-100">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-x-4 px-2 py-3 text-sm font-semibold leading-6 text-sga-text hover:bg-gray-50 rounded-md transition-colors group">
                            <div class="h-8 w-8 rounded-full bg-sga-blue flex items-center justify-center text-white font-bold text-xs ring-2 ring-white shadow-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <span class="sr-only">Your profile</span>
                            <div class="flex flex-col truncate">
                                <span aria-hidden="true" class="truncate">{{ Auth::user()->name }}</span>
                                <span class="text-xs font-normal text-gray-500 truncate">{{ Auth::user()->email }}</span>
                            </div>
                        </a>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col lg:pl-72 transition-all duration-300">
            <!-- Top bar -->
            <header class="sticky top-0 z-10 flex h-16 flex-shrink-0 border-b border-sga-gray bg-white shadow-sm">
                <div class="flex flex-1 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <!-- Hamburger button -->
                    <button @click.stop="open = !open" type="button" class="-m-2.5 p-2.5 text-sga-text-light lg:hidden hover:text-sga-blue transition-colors">
                        <span class="sr-only">Open sidebar</span>
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Separador (solo se muestra en móvil) -->
                    <div class="h-6 w-px bg-sga-gray lg:hidden mx-4" aria-hidden="true"></div>

                    <div class="flex flex-1 items-center justify-between gap-x-4 self-stretch lg:gap-x-6">
                        <!-- Título de la página (slot 'header') -->
                        <div class="flex-1">
                            @if (isset($header))
                                <div class="flex h-full items-center text-base font-semibold leading-6 text-sga-text sm:text-xl">
                                    {{ $header }}
                                </div>
                            @endif
                        </div>

                        <!-- Menú de usuario (Desktop) -->
                        <div class="hidden lg:block">
                            <div class="hidden sm:flex sm:items-center">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-sga-text-light bg-white hover:text-sga-blue focus:outline-none transition ease-in-out duration-150">
                                            <div>{{ Auth::user()->name }}</div>
                                            <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-sga-blue font-bold border border-gray-200">
                                                {{ substr(Auth::user()->name, 0, 1) }}
                                            </div>
                                            <i class="fas fa-chevron-down text-xs ml-1"></i>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <x-dropdown-link :href="route('profile.edit')" wire:navigate class="flex items-center gap-2">
                                            <i class="fas fa-user-circle text-gray-400"></i>
                                            {{ __('Mi Perfil') }}
                                        </x-dropdown-link>

                                        <div class="border-t border-gray-100"></div>

                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault(); this.closest('form').submit();"
                                                class="text-sga-danger hover:bg-red-50 hover:text-red-700 flex items-center gap-2">
                                                <i class="fas fa-sign-out-alt"></i>
                                                {{ __('Cerrar Sesión') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>

                        {{-- Botón de Cerrar Sesión para MÓVIL (lg:hidden) --}}
                        <div class="flex items-center lg:hidden gap-3">
                             <a href="{{ route('profile.edit') }}" class="text-sga-text-light hover:text-sga-blue">
                                <i class="fas fa-user-circle text-xl"></i>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="-m-2.5 p-2.5 text-gray-400 hover:text-sga-danger transition-colors"
                                    title="Cerrar Sesión">
                                    <span class="sr-only">Cerrar Sesión</span>
                                    <i class="fas fa-sign-out-alt text-xl"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-sga-background p-4 sm:p-6 lg:p-8">
                 {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Carga los scripts de Livewire (para wire:click, etc.) -->
    @livewireScripts
</body>

</html>