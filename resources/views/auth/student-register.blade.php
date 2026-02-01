<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Nuevo Ingreso</h2>
        <p class="text-sm text-gray-600 mt-2">
            Completa tus datos para iniciar la solicitud. 
            <br>
            <span class="font-medium text-indigo-600">Tu cédula será tu contraseña temporal.</span>
        </p>
    </div>

    <!-- Mostrar errores generales del sistema si los hay -->
    @if (session('error'))
        <div class="mb-4 font-medium text-sm text-red-600 text-center bg-red-50 p-3 rounded border border-red-200">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('student.register.store') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Nombres -->
            <div>
                <x-input-label for="first_name" value="Nombres" />
                <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus placeholder="Ej: Juan" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>

            <!-- Apellidos -->
            <div>
                <x-input-label for="last_name" value="Apellidos" />
                <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required placeholder="Ej: Pérez" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <!-- Cédula -->
        <div class="mt-4">
            <x-input-label for="cedula" value="Cédula de Identidad" />
            <x-text-input id="cedula" class="block mt-1 w-full" type="text" name="cedula" :value="old('cedula')" required placeholder="Sin guiones (ej: 40212345678)" />
            <x-input-error :messages="$errors->get('cedula')" class="mt-2" />
            <p class="text-xs text-gray-500 mt-1">Ingresa solo números, sin guiones.</p>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" value="Correo Electrónico Personal" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="ejemplo@correo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-8 border-t border-gray-100 pt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('student.login.link') }}">
                ¿Ya tienes cuenta?
            </a>

            <x-primary-button class="ml-4 bg-indigo-600 hover:bg-indigo-700">
                {{ __('Crear Cuenta') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>