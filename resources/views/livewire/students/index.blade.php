<div class="min-h-screen bg-gray-50/50 pb-12">
    
    {{-- Mensajes Flash --}}
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
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Gestión de Estudiantes</h1>
                    <p class="text-sm text-gray-500 mt-1">Administra la información de todos los alumnos registrados.</p>
                </div>
                <button wire:click="create()" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-500 hover:shadow-indigo-300 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200 active:scale-[0.98]">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo Estudiante
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
                    wire:model.live.debounce.500ms="search" 
                    type="text" 
                    placeholder="Buscar por nombre, apellido, matrícula, email..." 
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
        
        {{-- Tabla de Estudiantes --}}
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                Estudiante / Matrícula
                            </th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">
                                Identificación
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
                        @forelse ($students as $student)
                            <tr class="hover:bg-gray-50/80 transition-colors duration-150 group" wire:key="student-row-{{ $student->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover ring-2 ring-white shadow-sm" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . ' ' . $student->last_name) }}&background=6366f1&color=ffffff&size=128&bold=true" 
                                                 alt="{{ $student->first_name }}">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                                {{ $student->first_name }} {{ $student->last_name }}
                                            </div>
                                            <div class="flex flex-col gap-0.5">
                                                <div class="text-xs text-gray-500">{{ $student->email }}</div>
                                                {{-- AQUI SE MUESTRA LA MATRÍCULA --}}
                                                @if($student->student_code)
                                                    <div class="text-xs font-medium text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded w-fit mt-1">
                                                        Mat: {{ $student->student_code }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                        {{ $student->cedula ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                        </svg>
                                        {{ $student->mobile_phone ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.students.profile', $student->id) }}" 
                                           class="rounded-lg p-2 text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition-colors" 
                                           title="Ver Perfil">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>

                                        <button wire:click="edit({{ $student->id }})" 
                                                class="rounded-lg p-2 text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors" 
                                                title="Editar">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>

                                        <button wire:click="delete({{ $student->id }})" 
                                                wire:confirm="¿Estás seguro de que quieres eliminar a este estudiante?" 
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
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="h-14 w-14 rounded-full bg-gray-50 flex items-center justify-center mb-3">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-bold text-gray-900">No se encontraron estudiantes</h3>
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
                {{ $students->links() }}
            </div>
        </div>
    </div>

    {{-- Modal de Crear/Editar Estudiante --}}
    <x-modal name="student-form-modal" maxWidth="4xl">
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

            <div class="p-6">
                <form wire:submit.prevent="saveStudent">
                    <div class="space-y-8">
                        
                        <!-- Información Personal -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 ring-1 ring-indigo-100">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </span>
                                Información Personal
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="first_name" value="Nombres" />
                                    <x-text-input wire:model.defer="first_name" id="first_name" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="last_name" value="Apellidos" />
                                    <x-text-input wire:model.defer="last_name" id="last_name" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="cedula" value="Cédula/DNI" />
                                    <x-text-input wire:model.defer="cedula" id="cedula" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                    <x-input-error :messages="$errors->get('cedula')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                                    <x-text-input wire:model.defer="birth_date" id="birth_date" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="date" />
                                    <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="gender" value="Género" />
                                    <select wire:model.defer="gender" id="gender" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm">
                                        <option value="">Seleccione...</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="nationality" value="Nacionalidad" />
                                    <x-text-input wire:model.defer="nationality" id="nationality" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                    <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100"></div>

                        <!-- Información de Contacto -->
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                </span>
                                Información de Contacto
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="email" value="Correo Electrónico" />
                                    <x-text-input wire:model.defer="email" id="email" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="email" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                                    <x-text-input wire:model.defer="mobile_phone" id="mobile_phone" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                    <x-input-error :messages="$errors->get('mobile_phone')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="home_phone" value="Teléfono Casa (Opcional)" />
                                    <x-text-input wire:model.defer="home_phone" id="home_phone" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                    <x-input-error :messages="$errors->get('home_phone')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="address" value="Dirección (Opcional)" />
                                    <x-text-input wire:model.defer="address" id="address" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="city" value="Ciudad (Opcional)" />
                                    <x-text-input wire:model.defer="city" id="city" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                    <x-input-error :messages="$errors->get('city')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100"></div>

                        <!-- Información del Tutor (Condicional) -->
                        <div>
                            <div class="flex items-center mb-4">
                                <input wire:model.live="is_minor" id="is_minor" type="checkbox" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 transition duration-150 ease-in-out">
                                <label for="is_minor" class="ml-2 block text-sm font-medium text-gray-900 select-none">
                                    ¿Es el estudiante menor de edad?
                                </label>
                            </div>

                            <div x-data="{ showTutor: $wire.is_minor }" x-show="showTutor" x-init="$watch('$wire.is_minor', value => showTutor = value)"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-2">
                                
                                <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-50 text-amber-600 ring-1 ring-amber-100">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </span>
                                    Información del Tutor
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-amber-50/50 p-6 rounded-xl border border-amber-100">
                                    <div>
                                        <x-input-label for="tutor_name" value="Nombre del Tutor" />
                                        <x-text-input wire:model.defer="tutor_name" id="tutor_name" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                        <x-input-error :messages="$errors->get('tutor_name')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="tutor_phone" value="Teléfono del Tutor" />
                                        <x-text-input wire:model.defer="tutor_phone" id="tutor_phone" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                        <x-input-error :messages="$errors->get('tutor_phone')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="tutor_cedula" value="Cédula del Tutor (Opcional)" />
                                        <x-text-input wire:model.defer="tutor_cedula" id="tutor_cedula" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                        <x-input-error :messages="$errors->get('tutor_cedula')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="tutor_relationship" value="Parentesco (Opcional)" />
                                        <x-text-input wire:model.defer="tutor_relationship" id="tutor_relationship" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" />
                                        <x-input-error :messages="$errors->get('tutor_relationship')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Seguridad (Solo al editar y si se desea cambiar) -->
                        @if(isset($studentId) && $studentId)
                        <div class="border-t border-gray-100"></div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-rose-50 text-rose-600 ring-1 ring-rose-100">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                </span>
                                Seguridad (Opcional)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-rose-50/50 p-6 rounded-xl border border-rose-100">
                                <div>
                                    <x-input-label for="password" value="Nueva Contraseña" />
                                    <x-text-input wire:model.defer="password" id="password" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="password_confirmation" value="Confirmar Contraseña" />
                                    <x-text-input wire:model.defer="password_confirmation" id="password_confirmation" class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" />
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" wire:click="closeModal" class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600 transition-all duration-200">
                            Cancelar
                        </button>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 hover:bg-indigo-500 hover:shadow-indigo-300 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200 active:scale-[0.98]">
                            {{ isset($studentId) && $studentId ? 'Actualizar Estudiante' : 'Guardar Estudiante' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-modal>

</div>