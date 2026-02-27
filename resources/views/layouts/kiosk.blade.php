<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900 overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGA KIOSK') }} - Terminal de Autoservicio</title>

    <!-- Usaremos TailwindCSS vía CDN en Modo Kiosco para garantizar un aislamiento visual y estilos gigantes sin interferir con el CSS central si no lo requerimos -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        kiosk: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine JS ya está incluido dentro de Livewire 3, por lo que no debemos cargarlo doble -->

    <style>
        /* Desactivar selección de texto, zoom y toques prolongados en pantallas públicas */
        body {
            -webkit-user-select: none;
            -ms-user-select: none;
            user-select: none;
            touch-action: pan-y; /* Solo permite scroll vertical nativo, bloquea zoom */
            -webkit-touch-callout: none;
        }

        /* Ocultar scrollbars groseros */
        ::-webkit-scrollbar {
            width: 12px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1; 
        }
        ::-webkit-scrollbar-thumb {
            background: #888; 
            border-radius: 6px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555; 
        }
    </style>

    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-white select-none relative bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-950" 
      x-data="inactivityLogout(120)" 
      @mousemove="resetTimer()" 
      @mousedown="resetTimer()" 
      @touchstart="resetTimer()" 
      @click="resetTimer()" 
      @keydown="resetTimer()">

    <!-- Formas de fondo abstractas para dar profundidad al glassmorphism -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-indigo-600/20 blur-[120px]"></div>
        <div class="absolute top-[60%] -right-[10%] w-[40%] h-[60%] rounded-full bg-blue-600/10 blur-[100px]"></div>
        <div class="absolute bottom-[-20%] left-[20%] w-[60%] h-[40%] rounded-full bg-emerald-600/10 blur-[120px]"></div>
    </div>

    <div class="flex flex-col h-screen">
        <!-- Header del Kiosco (Glass) -->
        <header class="bg-white/5 backdrop-blur-md border-b border-white/10 shadow-lg py-5 px-10 flex justify-between items-center shrink-0 z-10 transition-all duration-300">
            <div class="flex items-center gap-5">
                <div class="h-16 w-16 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center p-2.5 border border-white/20 shadow-[0_0_15px_rgba(255,255,255,0.05)]">
                    <x-application-logo class="block h-auto w-full drop-shadow-md" />
                </div>
                <div>
                    <h1 class="text-3xl font-black tracking-widest text-white drop-shadow-sm uppercase">CENTU</h1>
                    <p class="text-indigo-200 font-medium text-lg leading-tight uppercase tracking-[0.2em] opacity-80">Terminal de Autoservicio</p>
                </div>
            </div>

            @auth
            <div class="flex items-center gap-8">
                <!-- Info del Usuario -->
                <div class="flex flex-col text-right">
                    <span class="text-2xl font-bold text-white tracking-wide">{{ Auth::user()->name }}</span>
                    <span class="text-indigo-300 font-medium text-sm uppercase tracking-wider flex items-center justify-end gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)] animate-pulse"></div>
                        Estudiante Activo
                    </span>
                </div>
                <!-- Botón de Salir (Glass effect) -->
                <form method="POST" action="{{ route('logout') }}" id="kiosk-logout-form">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ route('kiosk.login') }}">
                    <button type="submit" class="bg-red-500/20 hover:bg-red-500/40 active:bg-red-500/60 text-red-100 border border-red-500/50 backdrop-blur-md font-bold py-3.5 px-8 rounded-2xl text-xl shadow-[0_8px_32px_rgba(220,38,38,0.15)] transition-all transform active:scale-95 flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                        SALIR
                    </button>
                </form>
            </div>
            @endauth
        </header>

        <!-- Main Content (Full Screen) -->
        <main class="flex-1 overflow-y-auto relative z-0">
            
            <!-- Indicador de Tiempo Restante (Discreto) -->
            @auth
                <div class="absolute top-4 right-6 bg-slate-900/40 backdrop-blur-sm border border-white/5 rounded-full px-4 py-1.5 text-slate-300 font-mono text-xs z-50 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Cierre automático en: <span x-text="timeLeft" class="font-bold text-white"></span>s</span>
                </div>
            @endauth

            <!-- Slot Content con Padding extra para respirar -->
            <div class="h-full w-full p-4 md:p-8">
                {{ $slot }}
            </div>
            
        </main>
        
        <!-- Footer simple (Glass) -->
        <footer class="bg-white/5 backdrop-blur-md py-4 text-center text-indigo-200/60 text-sm font-medium shrink-0 border-t border-white/5 z-10 tracking-widest">
            &copy; {{ date('Y') }} ACADEMIA CENTU &bull; TOQUE LA PANTALLA CUIDADOSAMENTE
        </footer>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Script de Cierre de Sesión por Inactividad -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('inactivityLogout', (timeoutSeconds = 120) => ({
                timeoutId: null,
                timeLeft: timeoutSeconds,
                countdownId: null,
                isAuth: {{ Auth::check() ? 'true' : 'false' }},

                init() {
                    if (this.isAuth) {
                        this.startTimer();
                        this.startCountdown();
                    }
                },

                startTimer() {
                    clearTimeout(this.timeoutId);
                    this.timeoutId = setTimeout(() => {
                        this.logout();
                    }, timeoutSeconds * 1000);
                },

                startCountdown() {
                    clearInterval(this.countdownId);
                    this.countdownId = setInterval(() => {
                        if(this.timeLeft > 0) {
                            this.timeLeft--;
                        }
                    }, 1000);
                },

                resetTimer() {
                    if (this.isAuth) {
                        this.timeLeft = timeoutSeconds;
                        this.startTimer();
                        // El contador se resetea automáticamente porque timeLeft vuelve al máximo
                    }
                },

                logout() {
                    const logoutForm = document.getElementById('kiosk-logout-form');
                    if(logoutForm) {
                        // Opcional: Mostrar un Toast de desconexión antes del post
                        logoutForm.submit();
                    } else {
                        // Si por error no hay form de logout en la vista, forzar recarga al home
                        window.location.href = "{{ route('kiosk.login') }}";
                    }
                }
            }))
        })
    </script>
</body>
</html>
