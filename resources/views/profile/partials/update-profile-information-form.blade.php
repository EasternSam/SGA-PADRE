<section>
    <header>
        <h2 class="text-lg font-medium text-sga-text">
            {{ __('Información del Perfil') }}
        </h2>
        <p class="mt-1 text-sm text-sga-text-light">
            {{ __("Actualice la información de perfil y la dirección de correo electrónico de su cuenta.") }}
        </p>
    </header>

    <!-- --- ¡¡¡CORRECCIÓN!!! --- -->
    {{-- Convertido de 'wire:submit' a un formulario Blade estándar --}}
    {{-- AÑADIDO: enctype="multipart/form-data" para permitir subida de archivos --}}
    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        {{-- NUEVO: Sección de Foto de Perfil --}}
        <div>
            <x-input-label for="photo" :value="__('Foto de Perfil')" />
            
            <div class="mt-2 flex items-center gap-4">
                {{-- Previsualización de la imagen actual --}}
                @if ($user->profile_photo_path)
                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover border border-gray-300">
                @else
                    <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                        <span class="text-xs">Sin Foto</span>
                    </div>
                @endif

                {{-- Input de archivo --}}
                <input id="photo" name="photo" type="file" class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-sga-secondary file:text-white
                    hover:file:bg-sga-primary
                " accept="image/*" />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            {{-- Convertido de 'wire:model' a 'name/value' --}}
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Correo Electrónico')" />
            {{-- Convertido de 'wire:model' a 'name/value' --}}
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-sga-text">
                        {{ __('Su dirección de correo electrónico no está verificada.') }}

                        {{-- Convertido de 'wire:click' a formulario/botón --}}
                        {{-- NOTA: Este formulario anidado podría causar problemas si no se maneja bien en HTML, 
                             pero en Blade puro se suele separar o usar un botón submit con formaction diferente. 
                             Sin embargo, para mantener la estructura actual lo dejamos, pero ten cuidado con formularios anidados. --}}
                    </p>
                     {{-- MOVIDO FUERA: Para evitar nesting de forms, lo ideal es usar un enlace o botón con JS, o poner este form fuera del principal --}}
                </div>
            @endif
        </div>
        
        {{-- Bloque de verificación de email movido fuera del form principal visualmente o manejado como bloque independiente si es necesario --}}
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
             <div class="mt-2">
                <button form="send-verification" class="underline text-sm text-sga-text-light hover:text-sga-text rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sga-secondary">
                    {{ __('Haga clic aquí para reenviar el correo de verificación.') }}
                </button>
                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-sm text-sga-success">
                        {{ __('Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.') }}
                    </p>
                @endif
             </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Guardar') }}</x-primary-button>

            <!-- --- ¡¡¡CORRECCIÓN!!! --- -->
            {{-- Se reemplaza 'x-action-message' por un listener de 'session' de Blade --}}
            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-sga-success"
                >{{ __('Guardado.') }}</p>
            @endif
        </div>
    </form>
    
    {{-- Formulario oculto para el reenvío de verificación --}}
    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
            @csrf
        </form>
    @endif
</section>