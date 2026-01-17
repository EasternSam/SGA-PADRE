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

    {{-- Font Awesome (Íconos adicionales) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" xintegrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body class="h-full font-sans antialiased">
    <div x-data="{ open: false }" @keydown.window.escape="open = false" class="h-full">

        <!-- Navigation Sidebar -->
        @include('layouts.navigation')

        <!-- Main Content Area -->
        <div class="flex h-full flex-col lg:pl-64 transition-all duration-300">

            <!-- Top bar -->
            <header class="sticky top-0 z-10 flex h-16 flex-shrink-0 border-b border-sga-gray bg-white shadow-sm">
                <div class="flex flex-1 items-center justify-between px-4 sm:px-6 lg:px-8">
                    
                    <!-- Hamburger button (Mobile) -->
                    <button @click.stop="open = !open" type="button"
                        class="-m-2.5 p-2.5 text-sga-text-light lg:hidden hover:text-sga-primary transition-colors">
                        <span class="sr-only">Abrir menú</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <!-- Separador móvil -->
                    <div class="h-6 w-px bg-sga-gray lg:hidden ml-4" aria-hidden="true"></div>

                    <div class="flex flex-1 items-center justify-between gap-x-4 self-stretch lg:gap-x-6">
                        <!-- Header Title (Slot) -->
                        <div class="flex-1 overflow-hidden">
                            @if (isset($header))
                                <div class="flex h-full items-center text-base font-semibold leading-6 text-sga-text sm:text-xl truncate">
                                    {{ $header }}
                                </div>
                            @endif
                        </div>

                        <!-- User Menu (Desktop) -->
                        <div class="hidden lg:block">
                            <div class="flex items-center">
                                <x-dropdown align="right" width="48">
                                    <x-slot name="trigger">
                                        <button class="inline-flex items-center gap-3 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-full text-sga-text bg-white hover:bg-gray-50 focus:outline-none transition ease-in-out duration-150">
                                            <div class="text-right hidden md:block">
                                                <div class="text-sm font-semibold text-gray-700">{{ Auth::user()->name }}</div>
                                                <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                            </div>
                                            <img class="h-9 w-9 rounded-full object-cover border border-gray-200" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF" 
                                                 alt="{{ Auth::user()->name }}">
                                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-500">
                                            {{ __('Administrar Cuenta') }}
                                        </div>

                                        <x-dropdown-link :href="route('profile.edit')" wire:navigate> 
                                            {{ __('Mi Perfil') }}
                                        </x-dropdown-link>

                                        <!-- Authentication -->
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault(); this.closest('form').submit();"
                                                class="text-red-600 hover:bg-red-50 hover:text-red-700">
                                                {{ __('Cerrar Sesión') }}
                                            </x-dropdown-link>
                                        </form>
                                    </x-slot>
                                </x-dropdown>
                            </div>
                        </div>

                        <!-- Logout Button (Mobile) -->
                        <div class="flex items-center lg:hidden">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="-m-2.5 p-2.5 text-gray-400 hover:text-red-500 transition-colors"
                                    title="Cerrar Sesión">
                                    <span class="sr-only">Cerrar Sesión</span>
                                    <i class="fas fa-sign-out-alt h-6 w-6 text-xl"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-sga-background">
                <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>

</html>