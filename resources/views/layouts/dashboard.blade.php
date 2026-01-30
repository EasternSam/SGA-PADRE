<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-sga-background overscroll-none">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Font Awesome (Carga diferida para no bloquear renderizado) --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        
        /* Animación personalizada para la barra de progreso indeterminada */
        @keyframes progress-indeterminate {
            0% { left: -35%; right: 100%; }
            60% { left: 100%; right: -90%; }
            100% { left: 100%; right: -90%; }
        }
        .animate-progress-indeterminate {
            position: relative;
            animation: progress-indeterminate 2s infinite linear;
            width: 100%; /* Asegura que ocupe el ancho */
        }
    </style>

    <!-- SCRIPT DE CARDNET (TOKENIZACIÓN) -->
    <!-- Se carga dinámicamente usando la configuración corregida -->
    <script type="text/javascript" 
            src="{{ config('services.cardnet.base_uri') }}/Scripts/PWCheckout.js?key={{ config('services.cardnet.public_key') }}">
    </script>
</head>

<body class="h-full font-sans antialiased text-slate-600 overscroll-none">

    <!-- 1. Barra de Carga Global (Inmediata y Z-Index Alto) -->
    <div wire:loading class="fixed top-0 left-0 w-full h-1.5 z-[2000] bg-indigo-100/50" style="pointer-events: none;">
        <div class="h-full bg-indigo-600 animate-progress-indeterminate shadow-[0_0_10px_rgba(79,70,229,0.5)]"></div>
    </div>

    <!-- 2. Indicador Flotante Moderno (Toast) - Inmediato -->
    <!-- Sin .delay para feedback instantáneo en modales -->
    <div wire:loading class="fixed bottom-6 right-6 z-[2000] pointer-events-none transition-opacity duration-200 ease-in-out" style="pointer-events: none;">
        <div class="bg-white/95 backdrop-blur-md border border-indigo-100 shadow-2xl rounded-full px-5 py-3 flex items-center gap-3 animate-in fade-in slide-in-from-bottom-3 duration-200 ring-1 ring-indigo-50">
            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs font-bold text-gray-700 tracking-wide">PROCESANDO...</span>
        </div>
    </div>

    <!-- 3. Sistema de Notificaciones Toast -->
    <!-- style="pointer-events: none;" para asegurar que no bloquee clics -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 z-[1500] flex items-end px-4 py-6 sm:items-start sm:p-6" style="pointer-events: none;">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            @if (session()->has('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition:enter="transform ease-out duration-300 transition"
                     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5"
                     style="pointer-events: auto;">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400 text-xl"></i>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-gray-900">¡Éxito!</p>
                                <p class="mt-1 text-sm text-gray-500">{{ session('success') }}</p>
                            </div>
                            <div class="ml-4 flex flex-shrink-0">
                                <button @click="show = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <span class="sr-only">Cerrar</span>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transform ease-out duration-300 transition"
                     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                     class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-red-500 ring-opacity-50 border-l-4 border-red-500"
                     style="pointer-events: auto;">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                            </div>
                            <div class="ml-3 w-0 flex-1 pt-0.5">
                                <p class="text-sm font-medium text-gray-900">Atención</p>
                                <p class="mt-1 text-sm text-gray-500">{{ session('error') }}</p>
                            </div>
                            <div class="ml-4 flex flex-shrink-0">
                                <button @click="show = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Layout Wrapper -->
    <div x-data="{ open: false }" 
         @keydown.window.escape="open = false" 
         class="h-full flex overflow-hidden bg-gray-50">

        <!-- Navigation Sidebar -->
        @include('layouts.navigation')

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64 transition-all duration-300 ease-in-out h-full">

            <!-- Top bar -->
            <header class="sticky top-0 z-20 flex flex-col bg-white/90 backdrop-blur-md border-b border-gray-200/60 shadow-sm supports-[backdrop-filter]:bg-white/60" style="padding-top: 16px; padding-bottom: 16px;">
                <div class="flex flex-1 items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    
                    <!-- Left: Hamburger & Page Title -->
                    <div class="flex items-center gap-4">
                        <button @click.stop="open = !open" type="button"
                            class="-m-2.5 p-2.5 text-gray-500 lg:hidden hover:text-indigo-600 transition-colors rounded-md hover:bg-gray-100">
                            <span class="sr-only">Abrir menú</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>

                        <div class="flex flex-col">
                            @if (isset($header))
                                <h1 class="text-lg font-bold leading-6 text-gray-900 sm:text-xl truncate tracking-tight">
                                    {{ $header }}
                                </h1>
                            @endif
                        </div>
                    </div>

                    <!-- Center: Global Search -->
                    <div class="hidden md:flex flex-1 max-w-md px-8 justify-center">
                        <livewire:global-search lazy />
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        
                        <!-- Notifications -->
                        <livewire:notifications-dropdown lazy />

                        <!-- Separator -->
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200" aria-hidden="true"></div>

                        <!-- User Menu -->
                        <div class="relative">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center gap-3 p-1.5 rounded-full hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                                        <div class="hidden lg:block text-right">
                                            <p class="text-sm font-semibold text-gray-700 leading-none">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5 truncate max-w-[150px]">{{ Auth::user()->email }}</p>
                                        </div>
                                        <div class="relative">
                                            <img class="h-9 w-9 rounded-full object-cover border-2 border-white shadow-sm ring-1 ring-gray-100" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=7F9CF5&background=EBF4FF&bold=true" 
                                                 alt="{{ Auth::user()->name }}"
                                                 loading="lazy">
                                            <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full bg-green-400 ring-2 ring-white"></span>
                                        </div>
                                        <i class="fas fa-chevron-down text-gray-400 text-xs hidden lg:block ml-1"></i>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider">Mi Cuenta</p>
                                    </div>

                                    <x-dropdown-link :href="route('profile.edit')" wire:navigate class="flex items-center gap-2"> 
                                        <i class="fas fa-user-circle text-gray-400"></i> {{ __('Mi Perfil') }}
                                    </x-dropdown-link>

                                    <div class="border-t border-gray-100 my-1"></div>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();"
                                            class="text-red-600 hover:bg-red-50 hover:text-red-700 flex items-center gap-2">
                                            <i class="fas fa-sign-out-alt"></i> {{ __('Cerrar Sesión') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>

                    </div>
                </div>
            </header>

            <!-- Page Content (Scrollable) -->
            <main class="flex-1 overflow-y-auto focus:outline-none scroll-smooth">
                <!-- Content Container -->
                <div class="min-h-full pb-6">
                    <div class="max-w-full mx-auto px-0">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <!-- 6. Footer Mejorado -->
            <footer class="bg-white border-t border-gray-200 flex-shrink-0 z-10 py-6">
                <div class="mx-auto max-w-7xl px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                    
                    {{-- Copyright alineado a la izquierda --}}
                    <div class="text-left w-full md:w-auto order-2 md:order-1">
                        <p class="text-xs text-gray-400 leading-relaxed">
                            &copy; {{ date('Y') }} <span class="font-medium text-gray-600">SGA CENTU | Academic+</span>. 
                            Todos los derechos reservados. Versión 1.0.0
                        </p>
                    </div>

                    {{-- Enlaces de Soporte --}}
                    <div class="flex items-center justify-center md:justify-end gap-6 w-full md:w-auto order-1 md:order-2">
                        <a href="#" class="group flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-indigo-600 transition-colors duration-200">
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Soporte
                        </a>
                        <span class="text-gray-200 h-3 w-px bg-gray-300"></span>
                        <a href="#" class="group flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-indigo-600 transition-colors duration-200">
                            <svg class="w-3.5 h-3.5 text-gray-400 group-hover:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Manual de Usuario
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>

</html>