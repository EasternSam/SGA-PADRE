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

    {{-- Font Awesome --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Livewire Styles -->
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <!-- SCRIPT DE CARDNET (TOKENIZACIÓN) -->
    <!-- Se carga dinámicamente usando la configuración corregida -->
    <script type="text/javascript" 
            src="{{ config('services.cardnet.base_uri') }}/Scripts/PWCheckout.js?key={{ config('services.cardnet.public_key') }}">
    </script>
</head>

<body class="h-full font-sans antialiased text-slate-600">
    <!-- Barra de Carga Global -->
    <div wire:loading.delay class="fixed top-0 left-0 w-full h-1 z-[60] bg-indigo-100" style="pointer-events: none;">
        <div class="h-full bg-indigo-600 animate-progress-indeterminate"></div>
    </div>

    <!-- Notificaciones Toast -->
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 z-50 flex items-end px-4 py-6 sm:items-start sm:p-6" style="pointer-events: none;">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            @if (session()->has('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
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
                                <button @click="show = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-50 focus:outline-none">
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
                                <button @click="show = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-50">
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

        @include('layouts.navigation')

        <div class="flex-1 flex flex-col min-w-0 lg:pl-64 transition-all duration-300 ease-in-out">
            <header class="sticky top-0 z-20 flex flex-col bg-white/90 backdrop-blur-md border-b border-gray-200/60 shadow-sm supports-[backdrop-filter]:bg-white/60" style="padding-top: 16px; padding-bottom: 16px;">
                <div class="flex flex-1 items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
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
                    <div class="hidden md:flex flex-1 max-w-md px-8 justify-center">
                        <livewire:global-search lazy />
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <livewire:notifications-dropdown lazy />
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200" aria-hidden="true"></div>
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

            <main class="flex-1 overflow-y-auto focus:outline-none scroll-smooth">
                <div class="min-h-full pb-6">
                    <div class="max-w-full mx-auto px-0">
                        {{ $slot }}
                    </div>
                </div>
            </main>

            <footer class="bg-white border-t border-gray-200 flex-shrink-0 z-10">
                <div class="mx-auto max-w-7xl px-6 py-4 md:flex md:items-center md:justify-between lg:px-8">
                    <div class="flex justify-center space-x-6 md:order-2">
                        <a href="#" class="text-gray-400 hover:text-gray-500 transition-colors">
                            <span class="sr-only">Soporte</span>
                            <i class="fas fa-life-ring"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-500 transition-colors">
                            <span class="sr-only">Manual</span>
                            <i class="fas fa-book"></i>
                        </a>
                    </div>
                    <div class="mt-4 md:order-1 md:mt-0">
                        <p class="text-center text-xs leading-5 text-gray-500">
                            &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos los derechos reservados. v1.0.0
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @livewireScripts
</body>
</html>