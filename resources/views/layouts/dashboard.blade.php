<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-sga-background overscroll-none">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGA-PADRE') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Font Awesome --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Scripts locales (Desactivados por ahora para usar CDN) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    <!-- ========================================================= -->
    <!-- PERSONALIZACIÓN DINÁMICA -->
    <!-- ========================================================= -->
    <style>
        [x-cloak] { display: none !important; }
        
        :root {
            /* Inyectamos el color desde la variable global $branding */
            --color-primary: {{ isset($branding) && property_exists($branding, 'primary_rgb') ? $branding->primary_rgb : '30 58 138' }};
        }

        /* Clases de utilidad para forzar el color si Tailwind falla */
        .bg-sga-primary { background-color: rgb(var(--color-primary)) !important; }
        .text-sga-primary { color: rgb(var(--color-primary)) !important; }
        .border-sga-primary { border-color: rgb(var(--color-primary)) !important; }
        
        /* Animación de carga */
        @keyframes progress-indeterminate {
            0% { left: -35%; right: 100%; }
            60% { left: 100%; right: -90%; }
            100% { left: 100%; right: -90%; }
        }
        .animate-progress-indeterminate {
            position: relative;
            animation: progress-indeterminate 2s infinite linear;
            width: 100%; 
        }
    </style>

    <!-- SCRIPT DE CARDNET -->
    <script type="text/javascript" 
            src="{{ config('services.cardnet.base_uri') }}/Scripts/PWCheckout.js?key={{ config('services.cardnet.public_key') }}">
    </script>
</head>

<body class="h-full font-sans antialiased text-slate-600 overflow-hidden bg-gray-50">

    <!-- Barra de Carga Global -->
    <div wire:loading class="fixed top-0 left-0 w-full h-1.5 z-[2000] bg-indigo-100/50" style="pointer-events: none;">
        <div class="h-full bg-sga-primary animate-progress-indeterminate shadow-[0_0_10px_rgba(79,70,229,0.5)]"></div>
    </div>

    <!-- Indicador Flotante -->
    <div wire:loading class="fixed bottom-6 right-6 z-[2000] pointer-events-none transition-opacity duration-200 ease-in-out" style="pointer-events: none;">
        <div class="bg-white/95 backdrop-blur-md border border-indigo-100 shadow-2xl rounded-full px-5 py-3 flex items-center gap-3 animate-in fade-in slide-in-from-bottom-3 duration-200 ring-1 ring-indigo-50">
            <svg class="animate-spin h-5 w-5 text-sga-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-xs font-bold text-gray-700 tracking-wide">PROCESANDO...</span>
        </div>
    </div>

    <!-- Notificaciones Toast -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 z-[1500] flex items-end px-4 py-6 sm:items-start sm:p-6">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            @if (session()->has('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
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
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-red-500 ring-opacity-50 border-l-4 border-red-500">
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
         class="h-full flex overflow-hidden">

        <!-- Navigation Sidebar -->
        @include('layouts.navigation')

        <!-- Main Content Area -->
        <!-- CORRECCIÓN: Eliminada clase lg:pl-64 para evitar doble espaciado -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out h-full">

            <!-- Top bar -->
            <header class="sticky top-0 z-20 flex bg-white/90 backdrop-blur-md border-b border-gray-200/60 shadow-sm">
                <div class="flex flex-1 items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    
                    <!-- Left: Hamburger & Page Title -->
                    <div class="flex items-center gap-4">
                        <button @click.stop="open = !open" type="button"
                            class="-m-2.5 p-2.5 text-gray-500 lg:hidden hover:text-sga-primary transition-colors rounded-md hover:bg-gray-100">
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
                        <livewire:notifications-dropdown lazy />
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
                                                 src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=FFFFFF&background={{ isset($branding) && property_exists($branding, 'primary_color') ? str_replace('#', '', $branding->primary_color) : '1E3A8A' }}&bold=true" 
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

            <!-- Page Content -->
            <main class="flex-1 flex flex-col overflow-y-auto focus:outline-none scroll-smooth bg-gray-50">
                <div class="py-6 sm:py-8 w-full">
                    <div class="w-full px-4 sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </div>

                <!-- FOOTER DINÁMICO -->
                <footer class="mt-auto border-t border-gray-200 py-6 w-full">
                    <div class="w-full px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="text-left w-full md:w-auto order-2 md:order-1">
                            <p class="text-xs text-gray-400 leading-relaxed">
                                {{-- NOMBRE DINÁMICO --}}
                                &copy; {{ date('Y') }} <span class="font-medium text-gray-600">{{ config('app.name', 'SGA Academic+') }}</span>. 
                                Todos los derechos reservados.
                            </p>
                        </div>
                        <div class="flex items-center justify-center md:justify-end gap-6 w-full md:w-auto order-1 md:order-2">
                            <a href="#" class="group flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-sga-primary transition-colors duration-200">
                                <i class="fas fa-life-ring text-gray-400 group-hover:text-sga-primary"></i> Soporte
                            </a>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>