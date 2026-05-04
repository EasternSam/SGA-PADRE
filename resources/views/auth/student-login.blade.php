<x-guest-layout>
    <!-- Header Section (Mobile-Optimized) -->
    <div class="mb-6 text-center fade-in">
        <h2 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">Nuevo Ingreso</h2>
        <p class="text-sm sm:text-base text-indigo-200/80 mt-3 leading-relaxed px-2">
            Ingresa para continuar con tu solicitud o verificar el estado de tu admisión.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('student.login.store') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="text-base sm:text-lg">Correo Electrónico</label>
            <div class="relative">
                <input 
                    id="email" 
                    class="glass-input pr-12" 
                    type="email" 
                    name="email" 
                    :value="old('email')" 
                    required 
                    autofocus 
                    autocomplete="username" 
                    placeholder="tu@correo.com"
                    inputmode="email"
                />
                
                <!-- Email Icon -->
                <div class="input-icon-wrapper absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="input-icon h-6 w-6 text-indigo-300/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <label for="password" class="text-base sm:text-lg">Contraseña</label>
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
                
                <!-- Toggle Password Visibility Button -->
                <button 
                    type="button" 
                    onclick="togglePasswordStudent()" 
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-indigo-300/60 hover:text-indigo-300 transition-colors focus:outline-none focus:text-indigo-300"
                    aria-label="Mostrar contraseña"
                    tabindex="-1"
                >
                    <svg id="password-icon-hide-student" class="input-icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="password-icon-show-student" class="input-icon h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block pt-2">
            <label for="remember_me" class="inline-flex items-center cursor-pointer min-h-[44px]">
                <input 
                    id="remember_me" 
                    type="checkbox" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 bg-white/10 border-white/20" 
                    name="remember"
                >
                <span class="ml-3 text-sm sm:text-base text-gray-200 hover:text-white transition-colors font-medium">
                    {{ __('Recordarme') }}
                </span>
            </label>
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-4 pt-2">
            @if (Route::has('password.request'))
                <a 
                    class="glass-link text-sm sm:text-base font-medium text-center min-h-[44px] flex items-center justify-center" 
                    href="{{ route('password.request') }}"
                >
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif

            <button type="submit" class="glass-button">
                <span class="relative z-10">{{ __('Ingresar') }}</span>
            </button>
        </div>
        
        <!-- Register Link -->
        <div class="mt-6 pt-6 border-t-2 border-white/15 text-center">
            <p class="text-sm sm:text-base text-indigo-200/90 mb-4 px-2">
                ¿Aún no tienes cuenta?
            </p>
            <a 
                href="{{ route('student.register.link') }}" 
                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl border-2 border-indigo-400/40 bg-indigo-500/20 hover:bg-indigo-500/30 active:bg-indigo-500/25 text-indigo-200 hover:text-white transition-all text-sm sm:text-base font-semibold min-h-[52px]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Regístrate aquí
            </a>
        </div>
    </form>

    <!-- Script para toggle de contraseña -->
    <script>
        function togglePasswordStudent() {
            const passwordInput = document.getElementById('password');
            const iconHide = document.getElementById('password-icon-hide-student');
            const iconShow = document.getElementById('password-icon-show-student');
            
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