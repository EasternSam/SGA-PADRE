<x-guest-layout>
    <div class="w-full max-w-lg bg-white dark:bg-slate-900 rounded-[28px] shadow-[0_20px_50px_rgba(0,0,0,0.06)] border border-slate-100 dark:border-slate-800 p-6 sm:p-8 my-auto">
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Solicitud de Admisión</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
                Completa tus datos para iniciar tu proceso de nuevo ingreso.<br>
                <span class="font-semibold text-indigo-600 dark:text-indigo-400">Tu número de cédula será tu contraseña inicial.</span>
            </p>
        </div>

        @if (session('error'))
            <div class="mb-4 font-medium text-sm text-red-600 bg-red-50 dark:bg-red-950/40 p-3 rounded-xl border border-red-200 dark:border-red-800">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('student.register.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nombres -->
                <div>
                    <label for="first_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Nombres</label>
                    <input id="first_name" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all text-sm shadow-sm" type="text" name="first_name" value="{{ old('first_name') }}" required autofocus placeholder="Ej: Juan" />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                </div>

                <!-- Apellidos -->
                <div>
                    <label for="last_name" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Apellidos</label>
                    <input id="last_name" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all text-sm shadow-sm" type="text" name="last_name" value="{{ old('last_name') }}" required placeholder="Ej: Pérez" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                </div>
            </div>

            <!-- Cédula -->
            <div>
                <label for="cedula" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Cédula de Identidad</label>
                <input id="cedula" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all text-sm shadow-sm" type="text" name="cedula" value="{{ old('cedula') }}" required placeholder="Sin guiones (ej: 40212345678)" />
                <p class="text-[11px] text-slate-400 mt-1">Ingresa solo dígitos, sin espacios ni guiones.</p>
                <x-input-error :messages="$errors->get('cedula')" class="mt-1" />
            </div>

            <!-- Correo Electrónico -->
            <div>
                <label for="email" class="block text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Correo Electrónico Personal</label>
                <input id="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition-all text-sm shadow-sm" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="ejemplo@correo.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                <a class="text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors" href="{{ route('login') }}">
                    ¿Ya tienes cuenta? Iniciar Sesión
                </a>

                <button type="submit" 
                        class="py-3 px-6 rounded-xl text-white font-bold text-sm tracking-wide shadow-md hover:shadow-lg transition-all transform active:scale-[0.99] cursor-pointer hover:brightness-105"
                        style="background-color: var(--brand-primary, #4f46e5)">
                    Crear Cuenta
                </button>
            </div>
        </form>
    </div>
</x-guest-layout>
