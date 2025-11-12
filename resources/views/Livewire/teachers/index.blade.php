<div class-="container mx-auto p-4 md:p-6 lg:p-8">

    {{-- Slot del Encabezado (para que aparezca "Gestión de Profesores" en el layout) --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Gestión de Profesores') }}
        </h2>
    </x-slot>

    {{-- Alertas de Éxito o Error --}}
    <div class="fixed top-24 right-6 z-50">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
                <strong class="font-bold">¡Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
    </div>

    {{-- Barra de Título y Botón de Añadir --}}
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">
                Profesores
            </h1>
            <button wire:click="create()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                <i class="fas fa-plus-circle mr-2"></i>Crear Nuevo Profesor
            </button>
        </div>

        {{-- Barra de Búsqueda --}}
        <div class="mt-6">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Buscar por nombre o email..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
        </div>
    </div>

    {{-- Tabla de Profesores --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($teachers as $teacher)
                        <tr class="hover:bg-gray-50" wire:key="teacher-{{ $teacher->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($teacher->name) }}&background=4f46e5&color=ffffff&size=128" alt="">
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $teacher->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $teacher->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                
                                {{-- ENLACE AL PERFIL --}}
                                <a href="{{ route('admin.teachers.profile', $teacher->id) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver Perfil" wire:navigate>
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- Botón Editar --}}
                                <button wire:click="edit({{ $teacher->id }})" class="text-blue-600 hover:text-blue-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                {{-- Botón Eliminar --}}
                                <button wire:click="delete({{ $teacher->id }})" wire:confirm="¿Está seguro que desea eliminar este profesor? Esta acción también eliminará su cuenta de usuario." class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                No se encontraron profesores que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endForesle
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="p-6 border-t border-gray-200">
            {{ $teachers->links('vendor.livewire.tailwind') }}
        </div>
    </div>

    {{-- Modal de Crear/Editar Profesor --}}
    <x-modal name="teacher-modal" focusable>
        <form wire:submit.prevent="save">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-6">
                    {{ $modalTitle }}
                </h2>

                <div class="space-y-4">
                    {{-- Nombre --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input id="name" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="name" />
                        @error('name') <span class="text-sm text-red-600 mt-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" type="email" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="email" />
                        @error('email') <span class="text-sm text-red-600 mt-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input id="password" type="password" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="password" 
                                 placeholder="{{ $userId ? 'Dejar en blanco para no cambiar' : '' }}" />
                        @error('password') <span class="text-sm text-red-600 mt-2">{{ $message }}</span> @enderror
                    </div>

                    {{-- Confirmar Contraseña --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</Flabel>
                        <input id="password_confirmation" type="password" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="password_confirmation" />
                    </div>
                </div>
            </div>

            {{-- Footer del modal con botones --}}
            <div class="flex justify-end mt-6 p-6 bg-gray-100 rounded-b-lg">
                <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150" wire:loading.attr="disabled">
                    {{ $userId ? 'Actualizar Profesor' : 'Crear Profesor' }}
                </button>
            </div>
        </form>
        @endif
    </x-modal>
</div>