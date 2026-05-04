<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        {{-- Honeypot anti-bot: campo invisible que debe llegar vacío --}}
        <div style="position:absolute;left:-9999px;top:-9999px;opacity:0;height:0;width:0;overflow:hidden;" aria-hidden="true" tabindex="-1">
            <label for="website">Website</label>
            <input type="text" name="website" id="website" value="" autocomplete="off" tabindex="-1" />
        </div>

        <!-- Login Field (Email o Matrícula) -->
        <div class="space-y-2">
            <label for="login">{{ __('Email o Matrícula') }}</label>
            <div class="relative">
                <input 
                    id="login" 
                    class="glass-input pr-12" 
                    type="text" 
                    name="login" 
                    :value="old('login')" 
                    required 
                    autofocus 
                    autocomplete="username" 
                    placeholder="2024-0001 o usuario@correo.com"
                    inputmode="text"
                />
                
                <!-- Icono de Usuario (Mejorado) -->
                <div class="input-icon-wrapper absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="input-icon h-6 w-6 text-indigo-300/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <label for="password">{{ __('Contraseña') }}</label>
            <div class="relative">
                <input 
                    id="password" 
                    class="glass-input pr-12" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="current-password" 
                    placeholder="••••••••"
                />
                
                <!-- Botón para mostrar/ocultar contraseña (Mobile-Friendly) -->
                <button 
                    type="button" 
                    onclick="togglePassword()" 
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-indigo-300/60 hover:text-indigo-300 transition-colors focus:outline-none focus:text-indigo-300"
                    aria-label="Mostrar contraseña"
                    tabindex="-1"
                >
                    <svg id="password-icon-hide" class="input-icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="password-icon-show" class="input-icon h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            
            <!-- Nota informativa mejorada para móvil -->
            <div class="info-box mt-3 flex items-start gap-3 text-sm text-indigo-100/90 bg-white/8 p-4 rounded-xl border border-white/15 leading-relaxed">
                <svg class="w-5 h-5 text-indigo-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Si eres nuevo ingreso, tu contraseña inicial es tu número de cédula <strong class="text-white">(sin guiones)</strong>.</span>
            </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-6 pt-2">
            <label for="remember_me" class="inline-flex items-center cursor-pointer !mb-0 min-h-[44px] sm:min-h-0" style="display: flex;">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    name="remember" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 bg-white/10 border-white/20"
                >
                <span class="ml-3 text-sm sm:text-base text-gray-200 hover:text-white transition-colors font-medium">
                    {{ __('Mantener sesión activa') }}
                </span>
            </label>

            @if (Route::has('password.request'))
                <a class="glass-link text-sm sm:text-base font-medium min-h-[44px] sm:min-h-0 flex items-center justify-center sm:justify-start" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="glass-button">
                <span class="relative z-10">{{ __('Ingresar al Portal') }}</span>
            </button>
        </div>
        
        @if(\App\Helpers\SaaS::showCareers())
            <!-- Separador -->
            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-white/15"></div>
                <span class="flex-shrink-0 mx-4 text-slate-300 text-xs sm:text-sm uppercase tracking-wider font-semibold">
                    {{ __('¿Aún no eres estudiante?') }}
                </span>
                <div class="flex-grow border-t border-white/15"></div>
            </div>

            <!-- Registration Link (Mejorado para móvil) -->
            <a 
                href="{{ route('student.register.link') }}" 
                class="flex justify-center items-center gap-3 w-full py-4 px-5 rounded-xl border-2 border-white/15 bg-white/8 hover:bg-white/12 active:bg-white/10 text-indigo-200 hover:text-white transition-all text-sm sm:text-base font-semibold group min-h-[56px]"
            >
                <svg class="w-6 h-6 text-indigo-400/80 group-hover:text-indigo-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                <span>Solicitar Admisión / Nuevo Ingreso</span>
            </a>
        @endif
    </form>

    <!-- Script para toggle de contraseña -->
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

            // Haptic feedback
            if ('vibrate' in navigator) {
                navigator.vibrate(5);
            }
        }
    </script>
</x-guest-layout>