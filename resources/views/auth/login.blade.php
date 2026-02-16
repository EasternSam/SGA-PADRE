<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Login Field (Email o Matrícula) -->
        <div class="space-y-1">
            <label for="login">{{ __('Email o Matrícula') }}</label>
            <div class="relative">
                <input id="login" class="glass-input" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" placeholder="Ej: 2024-0001 o usuario@correo.com" />
                
                <!-- Icono de Usuario -->
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-indigo-300/50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="space-y-1">
            <label for="password">{{ __('Contraseña') }}</label>
            <div class="relative">
                <input id="password" class="glass-input" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                
                <!-- Icono de Candado -->
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-indigo-300/50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
            
            <!-- Nota informativa estilizada -->
            <div class="mt-3 flex items-start gap-2 text-xs text-indigo-200/80 bg-white/5 p-3 rounded-lg border border-white/10 leading-relaxed">
                <svg class="w-4 h-4 text-indigo-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Si eres nuevo ingreso, tu contraseña inicial es tu número de cédula (sin guiones).</span>
            </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between mt-4">
            <label for="remember_me" class="inline-flex items-center cursor-pointer !mb-0" style="display: flex;">
                <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 bg-white/10 border-white/20">
                <span class="ml-2 text-sm text-gray-300 hover:text-white transition-colors">{{ __('Mantener sesión activa') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="glass-link text-sm" href="{{ route('password.request') }}">
                    {{ __('¿Olvidaste tu contraseña?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" class="glass-button">
                {{ __('Ingresar al Portal') }}
            </button>
        </div>
        
        <!-- Separador -->
        <div class="relative flex py-4 items-center">
            <div class="flex-grow border-t border-white/10"></div>
            <span class="flex-shrink-0 mx-4 text-slate-400 text-xs uppercase tracking-wider font-medium">{{ __('¿Aún no eres estudiante?') }}</span>
            <div class="flex-grow border-t border-white/10"></div>
        </div>

        <!-- Registration Link -->
        <a href="{{ route('student.register.link') }}" class="flex justify-center items-center gap-2 w-full py-3 px-4 rounded-xl border border-white/10 bg-white/5 hover:bg-white/10 text-indigo-300 hover:text-white transition-all text-sm font-medium group">
            <svg class="w-5 h-5 text-indigo-400/70 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            Solicitar Admisión / Nuevo Ingreso
        </a>
    </form>
</x-guest-layout>