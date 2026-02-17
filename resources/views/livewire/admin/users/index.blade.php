<div>
    {{-- Título y Botón --}}
    <header class="bg-white shadow-sm mb-6 rounded-lg border border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Gestión de Personal</h1>
                <p class="text-sm text-gray-500">Administra usuarios, roles y accesos al sistema.</p>
            </div>
            <button 
                wire:click="create"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition ease-in-out duration-150 flex items-center text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Usuario
            </button>
        </div>
    </header>

    {{-- Contenido Principal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Mensajes Flash --}}
        @if (session()->has('message'))
            <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Filtros y Tabla --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                <div class="relative max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o correo..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm transition duration-150 ease-in-out">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol / Departamento</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Registro</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            {{-- MODIFICADO: Usar profile_photo_url en lugar de substr --}}
                                            <img class="h-10 w-10 rounded-full object-cover bg-gray-100" 
                                                 src="{{ $user->profile_photo_url }}" 
                                                 alt="{{ $user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            {{ match($role->name) {
                                                'Admin' => 'bg-purple-100 text-purple-800',
                                                'Profesor' => 'bg-blue-100 text-blue-800',
                                                'Estudiante' => 'bg-green-100 text-green-800',
                                                'Contabilidad' => 'bg-amber-100 text-amber-800',
                                                'Caja' => 'bg-emerald-100 text-emerald-800',
                                                'Registro' => 'bg-cyan-100 text-cyan-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            } }}">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $user->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    @if(auth()->id() !== $user->id)
                                        <button wire:click="confirmDeletion({{ $user->id }})" class="text-red-600 hover:text-red-900 transition-colors" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron usuarios.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    {{-- Modal para Usuario --}}
    <x-modal name="user-modal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                {{ $userId ? 'Editar' : 'Crear' }} Usuario
            </h2>
            
            <form wire:submit.prevent="store">
                <div class="space-y-4">
                    <!-- Nombre -->
                    <div>
                        <x-input-label for="name" value="Nombre Completo" />
                        <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="Ej: Juan Pérez" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    
                    <!-- Correo -->
                    <div>
                        <x-input-label for="email" value="Correo Electrónico" />
                        <x-text-input id="email" wire:model="email" type="email" class="mt-1 block w-full" placeholder="correo@ejemplo.com" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Rol -->
                    <div>
                        <x-input-label for="role" value="Rol / Departamento" />
                        <select wire:model="role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Seleccione un rol...</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <x-input-label for="password" value="{{ $userId ? 'Nueva Contraseña (Opcional)' : 'Contraseña' }}" />
                        <x-text-input id="password" wire:model="password" type="password" class="mt-1 block w-full" placeholder="********" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                </div>

                <div class="flex justify-end mt-6 gap-3">
                    <x-secondary-button wire:click="$dispatch('close-modal', 'user-modal')" type="button">
                        Cancelar
                    </x-secondary-button>
                    <x-primary-button>
                        {{ $userId ? 'Actualizar' : 'Guardar' }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Modal de Confirmación --}}
    <x-modal name="confirm-user-deletion" focusable>
        <div class="p-6">
            <h2 class="text-lg font-bold text-red-600 mb-4">¿Eliminar Usuario?</h2>
            <p class="text-gray-700 mb-6">
                Esta acción eliminará el acceso de este usuario al sistema. ¿Deseas continuar?
            </p>
            <div class="flex justify-end gap-3">
                <x-secondary-button wire:click="$dispatch('close-modal', 'confirm-user-deletion')">
                    Cancelar
                </x-secondary-button>
                <x-danger-button wire:click="delete">
                    Sí, Eliminar
                </x-danger-button>
            </div>
        </div>
    </x-modal>
</div>