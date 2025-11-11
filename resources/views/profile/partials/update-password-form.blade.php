<section>
    <header>
        <h2 class="text-lg font-medium text-sga-text">
            {{ __('Update Password') }}
        </h2>
        <p class="mt-1 text-sm text-sga-text-light">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <!-- --- ¡¡¡CORRECCIÓN!!! --- -->
    {{-- Convertido de 'wire:submit' a un formulario Blade estándar --}}
    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="current_password" :value="__('Current Password')" />
            {{-- Convertido de 'wire:model' a 'name' --}}
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New Password')" />
            {{-- Convertido de 'wire:model' a 'name' --}}
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            {{-- Convertido de 'wire:model' a 'name' --}}
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <!-- --- ¡¡¡CORRECCIÓN!!! --- -->
            {{-- Se reemplaza 'x-action-message' por un listener de 'session' de Blade --}}
            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-sga-success"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>