<x-guest-layout>
    @php
        $logoUrl = null;
        $loginBanner = null;
        $primaryColor = 'linear-gradient(to bottom right, #5a1256, #1a0a53)';

        if (isset($branding)) {
            $logoUrl = $branding->logo_url ?? null;
            $primaryColor = $branding->primary_color ?? $primaryColor;
            $loginBanner = $branding->login_banner ?? null;
        } else {
            try {
                $logoUrl = \App\Models\Setting::get('institution_logo') ?? \App\Models\SystemOption::getOption('logo');
                $loginBanner = \App\Models\Setting::get('login_banner');
                $color = \App\Models\Setting::get('brand_primary_color');
                if ($color) {
                    $primaryColor = $color;
                }
            } catch (\Exception $e) {}
        }

        $bannerSrc = $loginBanner 
            ? (Str::startsWith($loginBanner, 'http') ? $loginBanner : asset($loginBanner))
            : 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&q=80';

        $isGradient = str_contains($primaryColor, 'gradient');
    @endphp

    <!-- ── Contenedor Principal Centrado en Pantalla ── -->
    <div class="w-full max-w-6xl xl:max-w-7xl bg-white dark:bg-slate-900 shadow-[0_25px_70px_-15px_rgba(0,0,0,0.4)] border border-white/20 flex flex-col lg:flex-row min-h-0 lg:min-h-[680px] my-auto mb-16 sm:mb-auto backdrop-blur-xl"
         style="border-radius: 32px !important; overflow: hidden !important;">
        
        <!-- ── Columna Izquierda: Showcase con Logo Centrado y Banner de Ancho Completo ── -->
        <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-between select-none border-r border-white/10"
             style="background: {{ $primaryColor }}; overflow: hidden !important;">
            
            <!-- Atmospheric Image Backdrop inside lateral column -->
            <div class="absolute inset-0 w-full h-full pointer-events-none -z-0"
                 style="background-image: url('{{ $bannerSrc }}'); background-size: cover; background-position: center; filter: blur(0px); mix-blend-mode: multiply; opacity: 0.10;">
            </div>

            <!-- Ambient Glows -->
            <div class="absolute -right-24 -top-24 w-96 h-96 bg-pink-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-24 -bottom-24 w-96 h-96 bg-indigo-500/25 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Centro: Logo Centrado en el espacio superior/medio -->
            <div class="relative z-10 p-8 sm:p-12 lg:p-14 flex flex-col items-center justify-center my-auto flex-1">
                @if($logoUrl)
                    <div class="flex items-center justify-center w-full py-6">
                        <img src="{{ Str::startsWith($logoUrl, 'http') ? $logoUrl : asset($logoUrl) }}" 
                             alt="{{ config('app.name') }}" 
                             class="w-auto max-w-[380px] object-contain drop-shadow-2xl" 
                             style="height: 5rem !important;">
                    </div>
                @endif
            </div>

            <!-- Bottom Banner Image (Ocupa todo el ancho y tiene degradado superior suave para difuminar el límite) -->
            <div class="relative w-full mt-auto overflow-hidden pointer-events-none select-none z-10 flex items-end">
                <img src="{{ $bannerSrc }}" 
                     alt="Portal Académico" 
                     class="w-full h-72 sm:h-80 lg:h-96 object-cover object-top"
                     style="-webkit-mask-image: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.5) 20%, black 60%, black 100%); mask-image: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.5) 20%, black 60%, black 100%);">
            </div>

        </div>

        <!-- ── Columna Derecha: Formulario de Autenticación ── -->
        <div class="w-full lg:flex-1 p-6 sm:p-10 lg:p-14 xl:p-16 flex flex-col justify-center bg-white dark:bg-slate-900 relative">
            
            <!-- Formulario Central -->
            <div class="w-full max-w-sm sm:max-w-md mx-auto my-auto py-6">
                
                <!-- En Móviles: Logo con fondo de marca para que logos blancos resalten siempre -->
                @if($logoUrl)
                    <div class="lg:hidden mb-6 flex justify-start">
                        <div class="p-2.5 px-3.5 shadow-md inline-block" style="background: {{ $primaryColor }}; border-radius: 16px !important;">
                            <img src="{{ Str::startsWith($logoUrl, 'http') ? $logoUrl : asset($logoUrl) }}" 
                                 alt="{{ config('app.name') }}" 
                                 class="h-8 sm:h-9 w-auto object-contain">
                        </div>
                    </div>
                @endif

                <!-- Encabezado del Formulario -->
                <div class="mb-6">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                        Iniciar Sesión
                    </h1>
                    <p class="mt-1.5 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Ingresa tus credenciales para acceder al portal
                    </p>
                </div>

                <!-- Session Status / Mensajes de estado -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
                    @csrf

                    {{-- Honeypot anti-bot --}}
                    <div style="position:absolute;left:-9999px;top:-9999px;opacity:0;height:0;width:0;overflow:hidden;" aria-hidden="true" tabindex="-1">
                        <label for="website">Website</label>
                        <input type="text" name="website" id="website" value="" autocomplete="off" tabindex="-1" />
                    </div>

                    <!-- Email o Matrícula -->
                    <div>
                        <label for="login" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Email o Matrícula
                        </label>
                        <div class="relative">
                            <input 
                                id="login" 
                                class="w-full px-4 py-3.5 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all text-sm shadow-sm" 
                                style="border-radius: 14px !important;"
                                type="text" 
                                name="login" 
                                value="{{ old('login') }}" 
                                required 
                                autofocus 
                                autocomplete="username" 
                                placeholder="2024-0001 o usuario@correo.com"
                                inputmode="text"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('login')" class="mt-1.5" />
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                            Contraseña
                        </label>
                        <div class="relative">
                            <input 
                                id="password" 
                                class="w-full px-4 py-3.5 pr-11 border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/80 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all text-sm shadow-sm" 
                                style="border-radius: 14px !important; padding-right: 46px !important;"
                                type="password" 
                                name="password" 
                                required 
                                autocomplete="current-password" 
                                placeholder="••••••••"
                            />
                            
                            <!-- Botón para alternar visibilidad -->
                            <button 
                                type="button" 
                                onclick="togglePassword()" 
                                class="absolute inset-y-0 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors focus:outline-none"
                                style="right: 14px !important;"
                                aria-label="Mostrar u ocultar contraseña"
                                tabindex="-1"
                            >
                                <svg id="password-icon-hide" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="password-icon-show" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5" />

                        <!-- Nota informativa para nuevo ingreso (integrada, sutil y limpia) -->
                        <div class="mt-2 flex items-center gap-2 px-1 text-xs text-slate-500 dark:text-slate-400">
                            <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span>Si eres <strong class="text-slate-700 dark:text-slate-300 font-semibold">nuevo ingreso</strong>, tu clave es tu cédula <em>(sin guiones)</em>.</span>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex flex-wrap items-center justify-between gap-y-2 pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input 
                                id="remember_me" 
                                type="checkbox" 
                                name="remember" 
                                class="w-4 h-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-slate-800 dark:border-slate-700"
                            >
                            <span class="ml-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                                Mantener sesión activa
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-xs sm:text-sm font-semibold hover:underline transition-colors text-indigo-600 dark:text-indigo-400" 
                               href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Botón Principal -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full py-4 px-4 text-white font-bold text-sm tracking-wide shadow-lg hover:shadow-xl transition-all transform active:scale-[0.99] cursor-pointer flex items-center justify-center gap-2 hover:brightness-110"
                            style="background: {{ $primaryColor }}; border-radius: 14px !important;"
                        >
                            <span>Ingresar al Portal</span>
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

    <!-- ── Esquina Inferior Izquierda del Viewport: Copyright ── -->
    <div class="fixed bottom-6 left-6 lg:bottom-8 lg:left-8 z-40 hidden md:block pointer-events-none select-none" style="position: fixed !important; bottom: 24px !important; left: 24px !important;">
        <p class="text-xs font-light drop-shadow-md" style="color: rgba(255, 255, 255, 0.75) !important;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </p>
    </div>

    <!-- ── Esquina Inferior Derecha de la Vista Completa (Pantalla/Viewport) ── -->
    @if(\App\Helpers\SaaS::showCareers() && in_array((string) \App\Models\Setting::get('admissions_open', '0'), ['1', 'true'], true))
        <div class="fixed bottom-6 right-6 lg:bottom-8 lg:right-8 z-40 flex items-center gap-3 select-none" style="position: fixed !important; bottom: 24px !important; right: 24px !important;">
            <span class="text-xs sm:text-sm font-medium drop-shadow-md hidden sm:inline" style="color: #ffffff !important;">¿Aún no eres estudiante?</span>
            <a href="{{ route('student.register.link') }}" 
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-white hover:bg-slate-50 text-slate-900 font-bold text-xs sm:text-sm shadow-[0_10px_30px_rgba(0,0,0,0.35)] hover:shadow-[0_15px_35px_rgba(0,0,0,0.45)] transition-all transform hover:scale-105 active:scale-95"
               style="border-radius: 14px !important; background-color: #ffffff !important;">
                <span style="color: #1e1b4b !important;">Solicitar Admisión</span>
                <svg class="w-4 h-4" style="color: #4f46e5 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    @endif

    <!-- Script para alternar visibilidad de la contraseña -->
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const iconHide = document.getElementById('password-icon-hide');
            const iconShow = document.getElementById('password-icon-show');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                iconHide.classList.add('hidden');
                iconShow.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                iconHide.classList.remove('hidden');
                iconShow.classList.add('hidden');
            }

            if ('vibrate' in navigator) {
                navigator.vibrate(5);
            }
        }
    </script>
</x-guest-layout>
