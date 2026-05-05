<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-sga-background overscroll-none">

<head>
    <!-- Dark mode: apply BEFORE render to prevent flash -->
    <script>
        (function(){
            var d = localStorage.getItem('darkMode');
            if (d === 'true' || (!d && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ isset($branding) && !empty($branding->primary_color) ? $branding->primary_color : '#1e3a8a' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SGA">
    <meta name="mobile-web-app-capable" content="yes">

    <title>{{ config('app.name', 'SGA-PADRE') }}</title>

    {{-- PWA Manifest --}}
    <link rel="manifest" href="/manifest.json">
    @php
        $favicon = \App\Models\Setting::get('favicon');
        $appIcon = \App\Models\Setting::get('app_icon');
        $faviconUrl = $favicon ? $favicon : '/centuu.png';
        $appIconUrl = $appIcon ? $appIcon : '/centuu.png';
    @endphp
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $appIconUrl }}">
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- Font Awesome --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"></noscript>

    <!-- Scripts y estilos compilados (Vite + Tailwind) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <!-- Livewire Styles -->
    @livewireStyles

    <!-- ========================================================= -->
    <!-- PERSONALIZACIÃ“N DINÃMICA -->
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
        
        /* AnimaciÃ³n de carga */
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

    <!-- SCRIPT DE CARDNET — Carga asíncrona con retry -->
    <script>
        window.__cardnetLoaded = false;
        function loadCardnetScript(attempt) {
            attempt = attempt || 1;
            var s = document.createElement('script');
            s.src = "{{ config('services.cardnet.base_uri') }}/Scripts/PWCheckout.js?key={{ config('services.cardnet.public_key') }}&t=" + Date.now();
            s.onload = function() { window.__cardnetLoaded = true; console.log('[Cardnet] SDK cargado OK'); };
            s.onerror = function() {
                console.warn('[Cardnet] Fallo carga intento ' + attempt);
                if (attempt < 3) { setTimeout(function(){ loadCardnetScript(attempt + 1); }, 2000); }
            };
            document.head.appendChild(s);
        }
        loadCardnetScript();
    </script>
</head>

<body class="h-full font-sans antialiased text-slate-600 overflow-hidden bg-gray-50">

    <!-- Barra de Carga Global -->
    <div wire:loading class="fixed left-0 w-full h-1.5 z-[2000] bg-indigo-100/50" style="pointer-events: none; top: env(safe-area-inset-top, 0px);">
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
                                <p class="text-sm font-medium text-gray-900">Â¡Ã‰xito!</p>
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
                                <p class="text-sm font-medium text-gray-900">AtenciÃ³n</p>
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
        <!-- CORRECCIÃ“N: Eliminada clase lg:pl-64 para evitar doble espaciado -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out h-full">

            <!-- Top bar -->
            <header class="sticky top-0 z-20 bg-white/80 dark:bg-[#0a0a0f]/80 backdrop-blur-xl border-b border-gray-200/50 dark:border-white/[0.06]" style="padding-top: env(safe-area-inset-top, 0px);">
                <div class="flex items-center justify-between h-14 lg:h-16 px-3 sm:px-5 lg:px-8">
                    
                    <!-- Left: Hamburger & Page Title -->
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="min-w-0">
                            @if (isset($header))
                                <h1 class="text-base lg:text-lg font-bold text-gray-800 dark:text-gray-100 truncate tracking-tight leading-tight">
                                    {{ $header }}
                                </h1>
                            @endif
                        </div>
                    </div>

                    <!-- Center: Global Search (desktop only) -->
                    <div class="hidden md:flex flex-1 max-w-md mx-6 justify-center">
                        <livewire:global-search lazy />
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex items-center gap-1.5 sm:gap-2 lg:gap-3">
                        <!-- Dark Mode Toggle -->
                        <button 
                            x-data="{ 
                                dark: localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),
                                toggle() {
                                    this.dark = !this.dark;
                                    localStorage.setItem('darkMode', this.dark);
                                    document.documentElement.classList.toggle('dark', this.dark);
                                }
                            }"
                            x-init="document.documentElement.classList.toggle('dark', dark)"
                            @click="toggle()"
                            class="p-2 rounded-xl text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-amber-400 hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-all duration-200 active:scale-90"
                            title="Modo oscuro"
                        >
                            <!-- Sun icon (shown in dark mode) -->
                            <svg x-show="dark" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 rotate-[-90deg] scale-0" x-transition:enter-end="opacity-100 rotate-0 scale-100" class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/></svg>
                            <!-- Moon icon (shown in light mode) -->
                            <svg x-show="!dark" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 rotate-90 scale-0" x-transition:enter-end="opacity-100 rotate-0 scale-100" class="h-[18px] w-[18px]" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/></svg>
                        </button>

                        <!-- Notifications -->
                        <livewire:notifications-dropdown lazy />
                        
                        <!-- Separator (desktop) -->
                        <div class="hidden lg:block h-5 w-px bg-gray-200 dark:bg-white/10 mx-1" aria-hidden="true"></div>
                        
                        <!-- User Menu -->
                        <div class="relative">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center gap-2.5 p-1 lg:py-1.5 lg:pl-3 lg:pr-2 rounded-full lg:rounded-xl hover:bg-gray-50 dark:hover:bg-white/[0.06] transition-all duration-150 focus:outline-none active:scale-[0.97]">
                                        <div class="hidden lg:block text-right">
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 leading-none">{{ Auth::user()->name }}</p>
                                            <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 truncate max-w-[140px]">{{ Auth::user()->email }}</p>
                                        </div>
                                        <div class="relative">
                                            <img class="h-8 w-8 lg:h-9 lg:w-9 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-sm" 
                                                 src="{{ Auth::user()->profile_photo_url }}" 
                                                 alt="{{ Auth::user()->name }}"
                                                 loading="lazy">
                                            <span class="absolute -bottom-0.5 -right-0.5 block h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-white dark:ring-[#0a0a0f]"></span>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="px-4 py-3 border-b border-gray-100 dark:border-white/[0.06]">
                                        <p class="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-bold tracking-widest">Mi Cuenta</p>
                                    </div>
                                    <x-dropdown-link :href="route('profile.edit')" wire:navigate class="flex items-center gap-2.5 text-sm"> 
                                        <i class="fas fa-user-circle text-gray-400 dark:text-gray-500 text-xs"></i> {{ __('Mi Perfil') }}
                                    </x-dropdown-link>
                                    <div class="border-t border-gray-100 dark:border-white/[0.06] my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();"
                                            class="text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 flex items-center gap-2.5 text-sm">
                                            <i class="fas fa-sign-out-alt text-xs"></i> {{ __('Cerrar Sesión') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 flex flex-col overflow-y-auto focus:outline-none scroll-smooth bg-gray-50 pb-16 lg:pb-0">
                <div class="w-full">
                    <div class="w-full">
                        {{ $slot }}
                    </div>
                </div>

                <!-- FOOTER DINÃMICO -->
                <footer class="mt-auto border-t border-gray-200 py-6 w-full">
                    <div class="w-full px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="text-left w-full md:w-auto order-2 md:order-1">
                            <p class="text-xs text-gray-400 leading-relaxed">
                                {{-- NOMBRE DINÃMICO --}}
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

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MOBILE PAGE TRANSITION LOADING OVERLAY                    --}}
    {{-- Solo visible en <lg. Se activa con wire:navigate.         --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <div id="mobile-page-loader" class="fixed inset-0 z-[3000] bg-gray-50/95 dark:bg-[#050507]/95 backdrop-blur-sm flex-col items-center justify-start lg:hidden" style="display: none;">
        {{-- Top progress bar --}}
        <div class="absolute top-0 left-0 w-full h-1 bg-indigo-100 dark:bg-indigo-950 overflow-hidden">
            <div id="loader-progress-bar" class="h-full bg-gradient-to-r from-indigo-500 via-blue-500 to-indigo-400 rounded-r-full shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
        </div>

        {{-- Centered spinner + message --}}
        <div class="flex flex-col items-center justify-center flex-1 px-6 pt-24 pb-8 w-full" style="min-height: 60vh;">
            {{-- Animated logo/spinner --}}
            <div class="relative mb-6">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg" style="background: linear-gradient(135deg, #6366f1, #3b82f6);">
                    <svg class="w-7 h-7 text-white animate-spin" style="animation-duration: 1.2s;" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                {{-- Ripple ring --}}
                <div class="absolute inset-0 rounded-2xl border-2 border-indigo-400/50 animate-ping" style="animation-duration: 1.5s;"></div>
            </div>

            <p class="text-sm font-semibold text-gray-600 dark:text-gray-300 tracking-wide mb-1">Cargando página</p>
            <p class="text-xs text-gray-400 dark:text-gray-500">Un momento por favor...</p>
        </div>
    </div>

    {{-- Bottom Navigation — Solo móvil --}}
    @include('layouts.bottom-nav')

    @livewireScripts

    {{-- Service Worker Registration --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('[PWA] SW registrado:', reg.scope))
                    .catch(err => console.warn('[PWA] SW error:', err));
            });
        }
    </script>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- MOBILE PAGE LOADER SCRIPT                                 --}}
    {{-- Hooks into Livewire 3 navigate lifecycle                  --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <script>
    (function() {
        const loader = document.getElementById('mobile-page-loader');
        const progressBar = document.getElementById('loader-progress-bar');
        if (!loader || !progressBar) return;

        let progressInterval = null;
        let currentProgress = 0;
        const isMobile = () => window.innerWidth < 1024;

        function showLoader() {
            if (!isMobile()) return;
            currentProgress = 0;
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';

            // Force reflow before animating
            void progressBar.offsetWidth;

            loader.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Animate progress bar (fast start, slow crawl to 90%)
            progressBar.style.transition = 'width 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            progressBar.style.width = '30%';
            currentProgress = 30;

            clearInterval(progressInterval);
            progressInterval = setInterval(() => {
                if (currentProgress < 90) {
                    currentProgress += (90 - currentProgress) * 0.08;
                    progressBar.style.width = currentProgress + '%';
                }
            }, 200);
        }

        function hideLoader() {
            clearInterval(progressInterval);

            // Complete the progress bar first
            progressBar.style.transition = 'width 0.2s ease-out';
            progressBar.style.width = '100%';

            // Then fade out
            setTimeout(() => {
                loader.style.display = 'none';
                document.body.style.overflow = '';
                currentProgress = 0;
            }, 250);
        }

        // === Livewire 3 navigate events ===
        document.addEventListener('livewire:navigating', showLoader);
        document.addEventListener('livewire:navigated', hideLoader);

        // === Fallback: Also handle regular <a> clicks with wire:navigate ===
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[wire\\:navigate]');
            if (link && isMobile()) {
                // Only show if navigating to a different page
                const href = link.getAttribute('href');
                if (href && href !== window.location.href && href !== '#') {
                    showLoader();
                }
            }
        }, true);

        // === Safety timeout: hide after 8 seconds max ===
        document.addEventListener('livewire:navigating', function() {
            setTimeout(() => {
                if (loader.style.display === 'flex') {
                    hideLoader();
                }
            }, 8000);
        });

        // === Also show on Livewire component loading for full-page components ===
        // Handle the case where a Livewire action triggers a full re-render
        if (window.Livewire) {
            Livewire.hook('request', ({ respond, fail }) => {
                // Only for long requests — show after 600ms delay
                let timer = null;
                if (isMobile()) {
                    timer = setTimeout(() => showLoader(), 600);
                }
                respond(() => {
                    clearTimeout(timer);
                    hideLoader();
                });
                fail(() => {
                    clearTimeout(timer);
                    hideLoader();
                });
            });
        }
    })();
    </script>
</body>
</html>
