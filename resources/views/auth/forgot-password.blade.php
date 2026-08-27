<x-guest-layout>
    <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-[28px] shadow-[0_20px_50px_rgba(0,0,0,0.06)] border border-slate-100 dark:border-slate-800 p-6 sm:p-8 my-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Recuperar Contraseña</h1>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">
                Ingresa tu correo electrónico y te enviaremos un enlace seguro para restablecer tu contraseña.
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                    Correo Electrónico
                </label>
                <input id="email" 
                       class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all text-sm shadow-sm" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       placeholder="usuario@correo.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full py-3.5 px-4 rounded-xl text-white font-bold text-sm tracking-wide shadow-md hover:shadow-lg transition-all transform active:scale-[0.99] cursor-pointer flex items-center justify-center hover:brightness-105"
                        style="background-color: var(--brand-primary, #4f46e5)">
                    Enviar Enlace de Recuperación
                </button>
            </div>

            <div class="pt-3 text-center border-t border-slate-100 dark:border-slate-800">
                <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors">
                    ← Volver al Inicio de Sesión
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
