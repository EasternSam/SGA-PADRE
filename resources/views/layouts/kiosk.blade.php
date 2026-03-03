<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900 overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGA KIOSK') }} - Terminal de Autoservicio</title>

    <!-- Google Fonts: Outfit (Tecnológica, Redondeada, Moderna) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700;900&display=swap" rel="stylesheet">

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
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    animation: {
                        'blob': 'blob 15s infinite alternate',
                        'blob-slow': 'blob-slow 20s infinite alternate',
                        'blob-reverse': 'blob-reverse 25s infinite alternate',
                        'fade-in': 'fadeIn 0.8s ease-out forwards',
                        'slide-up': 'slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                        'floating': 'floating 3s ease-in-out infinite',
                        'pulse-glow': 'pulseGlow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        'blob-slow': {
                            '0%': { transform: 'translate(0px, 0px) scale(1) rotate(0deg)' },
                            '50%': { transform: 'translate(-40px, 60px) scale(1.2) rotate(180deg)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1) rotate(360deg)' },
                        },
                        'blob-reverse': {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '50%': { transform: 'translate(50px, 50px) scale(0.8)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(40px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        floating: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        pulseGlow: {
                            '0%, 100%': { opacity: '1', transform: 'scale(1)' },
                            '50%': { opacity: '.6', transform: 'scale(1.05)', filter: 'brightness(1.5)' },
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
<body class="h-full font-sans antialiased text-white select-none relative bg-[#0B0F19] overflow-hidden" 
      x-data="initKiosk()" 
      @mousemove="resetTimer()" 
      @mousedown="resetTimer()" 
      @touchstart="resetTimer()" 
      @click="resetTimer()" 
      @keydown="resetTimer()">

    <!-- Fondo Dinámico "Nivel Dios" -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        
        <!-- Grid pattern overlay -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAwIDQwIEwgNDAgNDAgNDAgMCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDMpIiBzdHJva2Utd2lkdGg9IjEiLz48L3BhdHRlcm4+PC9kZWZzPjxyZWN0IHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JpZCkiLz48L3N2Zz4=')] opacity-50"></div>

        <!-- Super Blobs Animados -->
        <div class="absolute top-[10%] left-[15%] w-[40rem] h-[40rem] rounded-full bg-gradient-to-r from-indigo-600/30 to-purple-600/30 blur-[100px] mix-blend-screen animate-blob"></div>
        <div class="absolute top-[40%] right-[10%] w-[35rem] h-[35rem] rounded-full bg-gradient-to-r from-cyan-500/20 to-blue-600/20 blur-[120px] mix-blend-screen animate-blob-slow"></div>
        <div class="absolute bottom-[-10%] left-[30%] w-[45rem] h-[45rem] rounded-full bg-gradient-to-r from-emerald-500/10 to-teal-500/10 blur-[130px] mix-blend-screen animate-blob-reverse"></div>
        
        <!-- Vignette effect for depth -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_center,transparent_0%,rgba(0,0,0,0.4)_100%)]"></div>
    </div>

    <div class="flex flex-col h-screen relative z-10 animate-fade-in">
        <!-- Header del Kiosco (Ultra Glass) -->
        <header class="bg-slate-900/40 backdrop-blur-2xl border-b border-white/10 shadow-[0_4px_30px_rgba(0,0,0,0.5)] py-4 px-10 flex justify-between items-center shrink-0 z-20">
            <div class="flex items-center gap-6">
                <!-- Logo Flotante -->
                <div class="h-16 w-16 bg-gradient-to-br from-white/20 to-white/5 backdrop-blur-md rounded-2xl flex items-center justify-center p-3 border border-white/20 shadow-[0_8px_32px_rgba(255,255,255,0.1)] relative group">
                    <div class="absolute inset-0 rounded-2xl bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <x-application-logo class="block h-auto w-full drop-shadow-[0_2px_4px_rgba(0,0,0,0.4)]" />
                </div>
                <div>
                    <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-slate-400 drop-shadow-sm uppercase tracking-widest">CENTU</h1>
                    <p class="text-indigo-300 font-bold text-sm leading-tight uppercase tracking-[0.3em]">Terminal Kiosco</p>
                </div>
            </div>

            <!-- Reloj Digital Central -->
            <div class="absolute left-1/2 transform -translate-x-1/2 hidden md:flex flex-col items-center">
                <span x-text="currentTime" class="text-2xl font-black tracking-widest text-white drop-shadow-[0_0_10px_rgba(255,255,255,0.3)] font-mono"></span>
                <span x-text="currentDate" class="text-xs uppercase tracking-[0.2em] text-indigo-200/80 font-bold"></span>
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
                <form method="POST" action="{{ route('kiosk.logout') }}" id="kiosk-logout-form">
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
    <!-- Script de Cierre de Sesión por Inactividad y Funciones Globales (Reloj) -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('initKiosk', (timeoutSeconds = 120) => ({
                timeoutId: null,
                timeLeft: timeoutSeconds,
                countdownId: null,
                isAuth: {{ Auth::check() ? 'true' : 'false' }},
                currentTime: '',
                currentDate: '',

                init() {
                    if (this.isAuth) {
                        this.startTimer();
                        this.startCountdown();
                    }
                    this.updateClock();
                    setInterval(() => this.updateClock(), 1000);
                },

                updateClock() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('es-DO', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                    this.currentDate = now.toLocaleDateString('es-DO', { weekday: 'long', day: 'numeric', month: 'short' }).replace(',', '');
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
                    }
                },

                logout() {
                    const logoutForm = document.getElementById('kiosk-logout-form');
                    if(logoutForm) {
                        logoutForm.submit();
                    } else {
                        window.location.href = "{{ route('kiosk.login') }}";
                    }
                }
            }))
        })
    </script>
</body>
</html>
