<div class="container mx-auto p-4 md:p-6 lg:p-8">

    {{-- Mensajes Flash --}}
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

    {{-- Encabezado y Acciones --}}
    <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">
                Gestión de Estudiantes
            </h1>
            {{-- ¡REPARACIÓN! Este botón llama a 'create()' que ahora dispara el evento --}}
            <button wire:click="create()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                <i class="fas fa-plus-circle mr-2"></i>Crear Nuevo Estudiante
            </button>
        </div>

        {{-- Barra de Búsqueda --}}
        <div class="mt-6">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Buscar por nombre, apellido, email o cédula..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
        </div>
    </div>

    {{-- Tabla de Estudiantes --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Cédula
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contacto
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($students as $student)
                        <tr class="hover:bg-gray-50" wire:key="student-{{ $student->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        {{-- Usamos el accesor 'fullName' (o 'name' si no existe) --}}
                                        <img class="h-10 w-10 rounded-full" src="https://ui-avatars.com/api/?name={{ urlencode($student->fullName ?? $student->first_name) }}&background=1e3a8a&color=ffffff&size=128" alt="">
                                    </div>
                                    <div class="ml-4">
                                        {{-- Usamos el accesor 'fullName' --}}
                                        <div class="text-sm font-medium text-gray-900">{{ $student->fullName }}</div>
                                        <div class="text-sm text-gray-500">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $student->cedula ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $student->mobile_phone ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                
                                {{-- ¡¡¡AQUÍ ESTÁ LA CORRECCIÓN!!! --}}
                                {{-- La ruta correcta es 'admin.students.profile' (con 's') según web.php --}}
                                <a href="{{ route('admin.students.profile', $student) }}" class="text-indigo-600 hover:text-indigo-900" title="Ver Perfil">
                                    <i class="fas fa-eye"></i>
                                </a>

                                {{-- ¡REPARACIÓN! Este botón llama a 'edit()' que ahora dispara el evento --}}
                                <button wire:click="edit({{ $student->id }})" class="text-blue-600 hover:text-blue-900" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="delete({{ $student->id }})" wire:confirm="¿Estás seguro de que quieres eliminar a este estudiante?" class="text-red-600 hover:text-red-900" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No se encontraron estudiantes que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="p-6 border-t border-gray-200">
            {{ $students->links('vendor.livewire.tailwind') }}
        </div>
    </div>

    {{-- Modal de Crear/Editar Estudiante --}}
    
    {{-- ¡¡¡REPARACIÓN!!! --}}
    {{-- Cambiado :show por 'name' y se usa el 'maxWidth' más grande --}}
    <x-modal name="student-form-modal" maxWidth="5xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-6">
                {{-- Usamos tu $modalTitle --}}
                {{ $modalTitle }}
            </h2>

            <form wire:submit.prevent="saveStudent">
                <div class="space-y-6">
                    
                    <!-- Información Personal -->
                    <fieldset class="border border-gray-300 p-4 rounded-lg">
                        <legend class="text-md font-medium text-gray-800 px-2">Información Personal</legend>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                            <div>
                                <x-input-label for="first_name" value="Nombres" />
                                <x-text-input wire:model.defer="first_name" id="first_name" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="last_name" value="Apellidos" />
                                <x-text-input wire:model.defer="last_name" id="last_name" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="cedula" value="Cédula/DNI" />
                                <x-text-input wire:model.defer="cedula" id="cedula" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('cedula')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                                <x-text-input wire:model.defer="birth_date" id="birth_date" class="block mt-1 w-full" type="date" />
                                <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="gender" value="Género" />
                                <select wire:model.defer="gender" id="gender" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="nationality" value="Nacionalidad" />
                                <x-text-input wire:model.defer="nationality" id="nationality" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
                            </div>
                        </div>
                    </fieldset>

                    <!-- Información de Contacto -->
                    <fieldset class="border border-gray-300 p-4 rounded-lg">
                        <legend class="text-md font-medium text-gray-800 px-2">Información de Contacto</legend>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                            <div>
                                <x-input-label for="email" value="Correo Electrónico" />
                                <x-text-input wire:model.defer="email" id="email" class="block mt-1 w-full" type="email" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                                <x-text-input wire:model.defer="mobile_phone" id="mobile_phone" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('mobile_phone')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="home_phone" value="Teléfono Casa (Opcional)" />
                                <x-text-input wire:model.defer="home_phone" id="home_phone" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('home_phone')" class="mt-2" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="address" value="Dirección (Opcional)" />
                                <x-text-input wire:model.defer="address" id="address" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="city" value="Ciudad (Opcional)" />
                                <x-text-input wire:model.defer="city" id="city" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>
                        </div>
                    </fieldset>

                    <!-- Información del Tutor (Condicional) -->
                    <div class="flex items-center">
                        {{-- ¡REPARACIÓN! Añadimos .live para que el bloque de tutor aparezca al instante --}}
                        <input wire:model.live="is_minor" id="is_minor" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <label for="is_minor" class="ml-2 block text-sm text-gray-900">
                            ¿Es el estudiante menor de edad?
                        </label>
                    </div>

                    {{-- ¡REPARACIÓN! Usamos $wire.is_minor para que Alpine reaccione al cambio de Livewire --}}
                    <fieldset x-data="{ showTutor: $wire.is_minor }" x-show="showTutor" x-init="$watch('$wire.is_minor', value => showTutor = value)"
                              class="border border-gray-300 p-4 rounded-lg"
                              x-transition:enter="transition ease-out duration-300"
                              x-transition:enter-start="opacity-0 scale-95"
                              x-transition:enter-end="opacity-100 scale-100"
                              x-transition:leave="transition ease-in duration-200"
                              x-transition:leave-start="opacity-100 scale-100"
                              x-transition:leave-end="opacity-0 scale-95">
                        
                        <legend class="text-md font-medium text-gray-800 px-2">Información del Tutor</legend>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <x-input-label for="tutor_name" value="Nombre del Tutor" />
                                <x-text-input wire:model.defer="tutor_name" id="tutor_name" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('tutor_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="tutor_phone" value="Teléfono del Tutor" />
                                <x-text-input wire:model.defer="tutor_phone" id="tutor_phone" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('tutor_phone')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="tutor_cedula" value="Cédula del Tutor (Opcional)" />
                                <x-text-input wire:model.defer="tutor_cedula" id="tutor_cedula" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('tutor_cedula')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="tutor_relationship" value="Parentesco (Opcional)" />
                                <x-text-input wire:model.defer="tutor_relationship" id="tutor_relationship" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('tutor_relationship')" class="mt-2" />
                            </div>
                        </div>
                    </fieldset>

                </div>

                {{-- Botones del Modal --}}
                <div class="flex justify-end mt-8 pt-6 border-t border-gray-200">
                    {{-- ¡¡¡REPARACIÓN!!! --}}
                    {{-- Este botón ahora llama a 'closeModal', que dispara el evento correcto --}}
                    <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        {{ $student_id ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

</div>