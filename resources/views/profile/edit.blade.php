<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-sga-text leading-tight">
            {{ __('Mi Perfil') }}
        </h2>
    </x-slot>

    <!-- --- ¡ACTUALIZADO! --- -->
    {{-- Se elimina 'py-12' ya que 'layouts.dashboard' ahora maneja el padding --}}
    {{-- Se cambian los 'divs' para que usen 'bg-sga-card' y el nuevo padding --}}
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Tarjeta: Información de Perfil -->
        <div class="p-4 sm:p-6 bg-sga-card shadow rounded-lg">
            <div class="max-w-xl">
                <!-- --- ¡¡¡CORRECCIÓN!!! --- -->
                {{-- Se revierte a @include. El componente no es Livewire --}}
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Tarjeta: Actualizar Contraseña -->
        <div class="p-4 sm:p-6 bg-sga-card shadow rounded-lg">
            <div class="max-w-xl">
                <!-- --- ¡¡¡CORRECCIÓN!!! --- -->
                {{-- Se revierte a @include. El componente no es Livewire --}}
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Tarjeta: Eliminar Cuenta -->
        <div class="p-4 sm:p-6 bg-sga-card shadow rounded-lg">
            <div class="max-w-xl">
                <!-- --- CORRECTO --- -->
                {{-- Este formulario SÍ es un include normal (no es Livewire) --}}
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>