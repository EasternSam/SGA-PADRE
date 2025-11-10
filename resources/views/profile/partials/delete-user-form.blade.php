<section class="space-y-6">
    <header>
        <!-- --- ¡ACTUALIZADO! --- -->
        <h2 class="text-lg font-medium text-sga-text">
            {{ __('Delete Account') }}
        </h2>

        <!-- --- ¡ACTUALIZADO! --- -->
        <p class="mt-1 text-sm text-sga-text-light">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <!-- --- ¡ACTUALIZADO! --- -->
            <h2 class="text-lg font-medium text-sga-text">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <!-- --- ¡ACTUALIZADO! --- -->
            <p class="mt-1 text-sm text-sga-text-light">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <!-- --- ¡CORRECCIÓN! --- -->
                {{-- Se cambia el 'for' a 'delete_password' para evitar ID duplicado --}}
                <x-input-label for="delete_password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    <!-- --- ¡CORRECCIÓN! --- -->
                    {{-- Se cambia el 'id' a 'delete_password' para evitar ID duplicado --}}
                    id="delete_password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>