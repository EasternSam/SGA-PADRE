<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        <div class="font-bold text-red-600 mb-2">⚠️ Acción Requerida</div>
        {{ __('Por seguridad, debes cambiar tu contraseña temporal antes de continuar en el sistema.') }}
    </div>

    <form method="POST" action="{{ route('password.force_update') }}">
        @csrf

        <!-- Nueva Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Nueva Contraseña')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar Contraseña -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Cambiar Contraseña e Ingresar') }}
            </x-primary-button>
        </div>
    </form>
    
    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-600 hover:text-gray-900 underline">
            {{ __('Cerrar Sesión') }}
        </button>
    </form>
</x-guest-layout>