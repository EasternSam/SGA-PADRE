<div class="min-h-screen bg-gray-50/50 pb-12">

    {{-- Alertas de Éxito o Error (Diseño Moderno) --}}
    <div class="fixed top-24 right-6 z-50">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 p-4 flex items-start gap-3 shadow-lg relative overflow-hidden" role="alert">
                <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></div>
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold text-emerald-800">
                    <strong class="block font-bold">¡Éxito!</strong>
                    {{ session('message') }}
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 class="mb-4 rounded-xl bg-rose-50 border border-rose-100 p-4 flex items-start gap-3 shadow-lg relative overflow-hidden" role="alert">
                <div class="absolute inset-y-0 left-0 w-1 bg-rose-500"></div>
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-sm font-semibold text-rose-800">
                    <strong class="block font-bold">¡Error!</strong>
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Encabezado y Acciones --}}
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Gestión de Profesores</h1>
                    <p class="text-sm text-gray-500 mt-1">Administra el equipo docente y sus accesos.</p>
                </div>
                <button wire:click="create()" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-500 hover:shadow-indigo-300 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200 active:scale-[0.98]">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo Profesor
                </button>
            </div>

            {{-- Barra de Búsqueda OPTIMIZADA --}}
            <div class="mt-6 relative max-w-2xl">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Buscar por nombre o email..." 
                    class="block w-full pl-10 pr-10 py-3 border-gray-300 rounded-xl leading-5 bg-gray-50 text-gray-900 placeholder-gray-500 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition duration-150 ease-in-out shadow-sm"
                >
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido Principal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">

        {{-- Tabla de Profesores --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                Profesor
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                Contacto
                            </th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($teachers as $teacher)
                            <tr class="hover:bg-gray-50/80 transition-colors duration-150 group" wire:key="teacher-{{ $teacher->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow-sm" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($teacher->name) }}&background=6366f1&color=ffffff&size=128&bold=true" 
                                                 alt="{{ $teacher->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                {{ $teacher->name }}
                                            </div>
                                            <div class="text-xs text-gray-500">ID: {{ $teacher->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                        </svg>
                                        {{ $teacher->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.teachers.profile', $teacher->id) }}" 
                                           class="rounded-lg p-2 text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" 
                                           title="Ver Perfil" wire:navigate>
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>

                                        <button wire:click="edit({{ $teacher->id }})" 
                                                class="rounded-lg p-2 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors" 
                                                title="Editar">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>

                                        <button wire:click="delete({{ $teacher->id }})" 
                                                wire:confirm="¿Está seguro que desea eliminar este profesor? Esta acción también eliminará su cuenta de usuario." 
                                                class="rounded-lg p-2 text-gray-400 hover:bg-red-50 hover:text-red-600 transition-colors" 
                                                title="Eliminar">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-14 w-14 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900">No se encontraron profesores</h3>
                                        <p class="mt-1 text-sm text-gray-500">Prueba ajustando los términos de búsqueda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $teachers->links('vendor.livewire.tailwind') }}
            </div>
        </div>
    </div>

    {{-- Modal de Crear/Editar Profesor --}}
    <x-modal name="teacher-modal" focusable maxWidth="2xl">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $modalTitle }}
                </h2>
                <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="p-6 space-y-6">
                    {{-- Nombre --}}
                    <div>
                        <x-input-label for="name" value="Nombre Completo" />
                        <x-text-input id="name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" wire:model="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Correo Electrónico" />
                        <x-text-input id="email" type="email" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" wire:model="email" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Contraseña --}}
                        <div>
                            <x-input-label for="password" value="Contraseña" />
                            <x-text-input id="password" type="password" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" wire:model="password"
                                          placeholder="{{ $userId ? 'Opcional si edita' : '' }}" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        {{-- Confirmar Contraseña --}}
                        <div>
                            <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
                            <x-text-input id="password_confirmation" type="password" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" wire:model="password_confirmation" />
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" wire:click="closeModal" class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600 transition-all duration-200">
                        Cancelar
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-500 hover:shadow-indigo-300 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200 active:scale-[0.98]" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            {{ $userId ? 'Actualizar Profesor' : 'Crear Profesor' }}
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</div>