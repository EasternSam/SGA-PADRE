<div class="container mx-auto p-4 md:p-6 lg:p-8" x-data="{ activeTab: 'sections' }">

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Perfil del Profesor') }}
        </h2>
    </x-slot>

    {{-- Mensajes Flash --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="fixed top-24 right-6 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg"
            role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
            class="fixed top-24 right-6 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg"
            role="alert">
            <strong class="font-bold">¡Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Encabezado del Perfil --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="md:flex">
            <!-- Avatar e Info Básica -->
            <div class="md:w-1/3 p-6 bg-gray-50 border-b md:border-b-0 md:border-r border-gray-200 text-center">
                {{-- MODIFICADO: Uso de profile_photo_url --}}
                <img class="h-32 w-32 rounded-full mx-auto shadow-md mb-4 object-cover"
                    src="{{ $teacher->profile_photo_url }}"
                    alt="Avatar de {{ $teacher->name }}">

                <h1 class="text-2xl font-bold text-gray-900">{{ $teacher->name }}</h1>
                <p class="text-sm text-gray-600">{{ $teacher->email }}</p>

                <span class="mt-3 inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    Profesor
                </span>
            </div>

            <!-- Información Detallada y Acciones -->
            <div class="md:w-2/3 p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Detalles del Profesor</h2>
                    <div>
                        <button type="button" wire:click="edit"
                            class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition ease-in-out duration-150">
                            <i class="fas fa-user-edit mr-2"></i>Editar Profesor
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-500 block">ID de Usuario:</strong>
                        <span class="text-gray-900">{{ $teacher->id }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Miembro desde:</strong>
                        <span class="text-gray-900">{{ $teacher->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                <hr class="my-6 border-gray-200">

                <div class="flex flex-wrap gap-2">
                    <button
                        wire:click="openAssignModal"
                        type="button"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-plus-circle mr-2"></i>Asignar Nueva Sección
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Pestañas y contenido --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Pestañas -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6 px-6" aria-label="Tabs">
                <button @click="activeTab = 'sections'"
                    :class="{
                        'border-indigo-500 text-indigo-600': activeTab === 'sections',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'sections'
                    }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Secciones Asignadas
                </button>
                <button @click="activeTab = 'attendance'"
                    :class="{
                        'border-indigo-500 text-indigo-600': activeTab === 'attendance',
                        'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'attendance'
                    }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Asistencia (Próximamente)
                </button>
            </nav>
        </div>

        <!-- Contenido de Pestaña: Secciones Asignadas -->
        <div class="p-0 sm:p-6" x-show="activeTab === 'sections'" x-cloak>
            <h3 class="text-lg font-semibold text-gray-800 mb-4 px-6 sm:px-0">Secciones Asignadas</h3>

            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sección</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fechas</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($sections as $section)
                            <tr class="hover:bg-gray-50" wire:key="section-{{ $section->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $section->module->course->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $section->module->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $section->section_name ?? $section->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ is_array($section->days_of_week) ? implode(', ', $section->days_of_week) : $section->days_of_week }}
                                    <br>
                                    <span class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($section->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('h:i A') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ \Carbon\Carbon::parse($section->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($section->end_date)->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    <a href="{{ route('teacher.grades', $section) }}" wire:navigate class="text-indigo-600 hover:text-indigo-900" title="Ver Calificaciones">
                                        <i class="fas fa-graduation-cap"></i>
                                    </a>
                                    <a href="{{ route('teacher.attendance', $section) }}" wire:navigate class="text-blue-600 hover:text-blue-900" title="Ver Asistencia">
                                        <i class="fas fa-calendar-check"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    Este profesor no tiene secciones asignadas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $sections->links() }}
            </div>
        </div>

        {{-- Próximamente --}}
        <div class="p-6" x-show="activeTab === 'attendance'" x-cloak>
            <div class="bg-white shadow-sm rounded-lg p-6 text-center text-gray-500">
                Esta función (resumen de asistencias) estará disponible próximamente.
            </div>
        </div>
    </div>


    {{-- Modal para Editar Profesor --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden max-w-lg w-full p-6" @click.outside="$wire.closeModal()">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                    {{ $userId ? 'Editar Profesor' : 'Crear Nuevo Profesor' }}
                </h3>
                <form wire:submit.prevent="save">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block font-medium text-sm text-gray-700">{{ __('Nombre') }}</label>
                            <input id="name" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="name" />
                            @error('name') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="block font-medium text-sm text-gray-700">{{ __('Email') }}</label>
                            <input id="email" type="email" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="email" />
                            @error('email') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password" class="block font-medium text-sm text-gray-700">{{ __('Contraseña') }}</label>
                            <input id="password" type="password" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="password" placeholder="{{ $userId ? 'Dejar en blanco para no cambiar' : '' }}" />
                            @error('password') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block font-medium text-sm text-gray-700">{{ __('Confirmar Contraseña') }}</label>
                            <input id="password_confirmation" type="password" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" wire:model="password_confirmation" />
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                        <button wire:click="closeModal()" type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                            {{ __('Cancelar') }}
                        </button>
                        <button class="ml-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 transition ease-in-out duration-150" type="submit">
                            {{ $userId ? 'Guardar Cambios' : 'Crear Profesor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL DE ASIGNACIÓN DE CARGA ACADÉMICA --}}
    @if ($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-75">
            <div class="bg-white rounded-lg shadow-xl overflow-hidden max-w-2xl w-full p-6" @click.outside="$wire.closeAssignModal()">
                
                <form wire:submit.prevent="handleAssignment">

                    {{-- Vista de Asignar (Select) --}}
                    @if ($modalView === 'assign')
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                            Asignar Nueva Sección a {{ $teacher->name }}
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label for="scheduleToAssign" class="block font-medium text-sm text-gray-700">Secciones Disponibles</label>
                                <select id="scheduleToAssign" wire:model="scheduleToAssign" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Seleccione una sección...</option>
                                    @forelse ($availableSchedules as $schedule)
                                        <option value="{{ $schedule->id }}">
                                            {{ $schedule->module->course->name ?? 'N/A' }} - {{ $schedule->module->name ?? 'N/A' }} (Sección: {{ $schedule->section_name ?? $schedule->id }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No hay secciones sin asignar</option>
                                    @endforelse
                                </select>
                                @error('scheduleToAssign') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="text-center mt-4 pt-4 border-t">
                            <p class="text-sm text-gray-600">
                                ¿No encuentras la sección?
                                <button type="button" wire:click="switchToCreateView" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    Crear una nueva sección
                                </button>
                            </p>
                        </div>

                    {{-- Vista de Crear (Formulario Completo) --}}
                    @elseif ($modalView === 'create')
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                            Crear y Asignar Sección a {{ $teacher->name }}
                        </h3>

                        <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                            <div>
                                <label for="new_course_id" class="block font-medium text-sm text-gray-700">Curso / Carrera</label>
                                <select id="new_course_id" wire:model.live="new_course_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Seleccione un curso...</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                                @error('new_course_id') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div class="relative">
                                <label for="new_module_id" class="block font-medium text-sm text-gray-700">Materia / Módulo</label>
                                <div class="relative">
                                    <select id="new_module_id" wire:model="new_module_id" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm disabled:bg-gray-100"
                                        @if(count($modules) == 0) disabled @endif>
                                        <option value="">Seleccione un módulo...</option>
                                        @foreach ($modules as $module)
                                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                                        @endforeach
                                    </select>
                                    {{-- Indicador de carga --}}
                                    <div wire:loading wire:target="new_course_id" class="absolute right-8 top-1/2 -translate-y-1/2">
                                        <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('new_module_id') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="new_section_name" class="block font-medium text-sm text-gray-700">Nombre de Sección</label>
                                <input id="new_section_name" type="text" wire:model="new_section_name" placeholder="Ej. Matutina, Sec-01" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                @error('new_section_name') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_start_date" class="block font-medium text-sm text-gray-700">Fecha de Inicio</label>
                                    <input id="new_start_date" type="date" wire:model="new_start_date" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                    @error('new_start_date') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="new_end_date" class="block font-medium text-sm text-gray-700">Fecha de Fin</label>
                                    <input id="new_end_date" type="date" wire:model="new_end_date" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                    @error('new_end_date') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_start_time" class="block font-medium text-sm text-gray-700">Hora de Inicio</label>
                                    <input id="new_start_time" type="time" wire:model="new_start_time" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                    @error('new_start_time') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="new_end_time" class="block font-medium text-sm text-gray-700">Hora de Fin</label>
                                    <input id="new_end_time" type="time" wire:model="new_end_time" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                                    @error('new_end_time') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block font-medium text-sm text-gray-700">Días de la Semana</label>
                                <div class="mt-2 grid grid-cols-3 sm:grid-cols-4 gap-2">
                                    @foreach($weekDays as $day)
                                    <label for="day_{{ $day }}" class="flex items-center">
                                        <input id="day_{{ $day }}" type="checkbox" wire:model="new_days_of_week" value="{{ $day }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-600">{{ $day }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @error('new_days_of_week') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-end mt-6 pt-6 border-t border-gray-200">
                        @if ($modalView === 'create')
                            <button wire:click="switchToAssignView" type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                                Volver
                            </button>
                        @endif
                        <button wire:click="closeAssignModal()" type="button" class="ml-3 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                            {{ __('Cancelar') }}
                        </button>
                        <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            @if($modalView === 'assign' && count($availableSchedules) == 0) disabled @endif>
                            {{ $modalView === 'assign' ? 'Asignar Sección' : 'Crear y Asignar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>