<div>
    {{-- Mensajes de Sesión (Flash) --}}
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

    {{-- Encabezado y Búsqueda --}}
    <header class="bg-white shadow-sm mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-900">Gestión Académica</h1>
            <div class="w-1/3">
                <input wire:model.debounce.300ms="search" type="text" placeholder="Buscar cursos por nombre o código..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
        </div>
    </header>

    {{-- Contenido Principal - Columnas --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Columna 1: Cursos -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Cursos</h2>
                    <button wire:click="createCourse" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-700">
                        <i class="fas fa-plus mr-1"></i> Añadir
                    </button>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-gray-200">
                        @forelse ($courses as $course)
                            <li wire:click="selectCourse({{ $course->id }})" 
                                class="p-3 cursor-pointer hover:bg-gray-100 rounded-lg {{ $selectedCourse == $course->id ? 'bg-indigo-100 border-l-4 border-indigo-500' : '' }}">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="block font-medium text-gray-900">{{ $course->name }}</span>
                                        <span class="block text-sm text-gray-500">{{ $course->code }} | {{ $course->credits }} Créditos</span>
                                    </div>
                                    <button wire:click.stop="editCourse({{ $course->id }})" class="text-gray-400 hover:text-indigo-600 text-xs">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                </div>
                            </li>
                        @empty
                            <li class="p-3 text-center text-gray-500">No se encontraron cursos.</li>
                        @endforelse
                    </ul>
                    <div class="mt-4">
                        {{ $courses->links() }}
                    </div>
                </div>
            </div>

            <!-- Columna 2: Módulos -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if ($selectedCourse)
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Módulos de: <span class="font-bold">{{ $selectedCourseName }}</span></h2>
                        <button wire:click="createModule" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-700">
                            <i class="fas fa-plus mr-1"></i> Añadir
                        </button>
                    </div>
                    <div class="p-4">
                        <ul class="divide-y divide-gray-200">
                            {{-- Usamos la variable $modules pasada por el componente --}}
                            @forelse ($modules as $module)
                                <li wire:click="selectModule({{ $module->id }})"
                                    class="p-3 cursor-pointer hover:bg-gray-100 rounded-lg {{ $selectedModule == $module->id ? 'bg-indigo-100 border-l-4 border-indigo-500' : '' }}">
                                    <div class="flex justify-between items-center">
                                        <span class="block font-medium text-gray-900">{{ $module->name }}</span>
                                        <button wire:click.stop="editModule({{ $module->id }})" class="text-gray-400 hover:text-indigo-600 text-xs">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </div>
                                </li>
                            @empty
                                <li class="p-3 text-center text-gray-500">Este curso no tiene módulos.</li>
                            @endforelse
                        </ul>
                    </div>
                @else
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Módulos</h2>
                    </div>
                    <div class="p-4 text-center text-gray-500">
                        Selecciona un curso para ver sus módulos.
                    </div>
                @endif
            </div>

            <!-- Columna 3: Secciones (Horarios) -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if ($selectedModule)
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-800">Secciones de: <span class="font-bold">{{ $selectedModuleName }}</span></h2>
                        <button wire:click="createSchedule" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-700">
                            <i class="fas fa-plus mr-1"></i> Añadir
                        </button>
                    </div>
                    <div class="p-4">
                        <ul class="divide-y divide-gray-200">
                            @forelse ($schedules as $schedule)
                                <li class="p-3 hover:bg-gray-100 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="block font-medium text-gray-900">{{ $schedule->section_name ?? ('Sección ' . $schedule->id) }}</span>
                                            <span class="block text-sm text-gray-500">Prof: {{ $schedule->teacher->name ?? 'No asignado' }}</span>
                                            <span class="block text-sm text-gray-500">
                                                {{-- ¡¡¡CORRECCIÓN!!! Leer de 'days_of_week' --}}
                                                {{ implode(', ', $schedule->days_of_week ?? []) }} | {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}
                                            </span>
                                        </div>
                                        <button wire:click.stop="editSchedule({{ $schedule->id }})" class="text-gray-400 hover:text-indigo-600 text-xs">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </div>
                                </li>
                            @empty
                                <li class="p-3 text-center text-gray-500">Este módulo no tiene secciones.</li>
                            @endforelse
                        </ul>
                    </div>
                @else
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-800">Secciones</h2>
                    </div>
                    <div class="p-4 text-center text-gray-500">
                        Selecciona un módulo para ver sus secciones.
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- MODALES --}}

    <!-- Modal de Curso -->
    <x-modal name="course-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $courseModalTitle }}</h2>
            <form wire:submit.prevent="saveCourse">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="course_code" class="block text-sm font-medium text-gray-700">Código</label>
                        <input id="course_code" wire:model.defer="course_code" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('course_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="course_credits" class="block text-sm font-medium text-gray-700">Créditos</label>
                        <input id="course_credits" wire:model.defer="course_credits" type="number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('course_credits') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <label for="course_name" class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input id="course_name" wire:model.defer="course_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('course_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end mt-6">
                    {{-- --- ¡CORRECCIÓN! --- --}}
                    <button type="button" wire:click="$dispatch('close-modal', 'course-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Guardar Curso</button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal de Módulo -->
    <x-modal name="module-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $moduleModalTitle }}</h2>
            <form wire:submit.prevent="saveModule">
                <div class="mt-4">
                    <label for="module_name" class="block text-sm font-medium text-gray-700">Nombre del Módulo</label>
                    <input id="module_name" wire:model.defer="module_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('module_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end mt-6">
                    {{-- --- ¡CORRECCIÓN! --- --}}
                    <button type="button" wire:click="$dispatch('close-modal', 'module-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Guardar Módulo</button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal de Horario (Sección) -->
    <x-modal name="schedule-modal" maxWidth="3xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $scheduleModalTitle }}</h2>
            <form wire:submit.prevent="saveSchedule">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="section_name" class="block text-sm font-medium text-gray-700">Nombre/Código de Sección (Opcional)</label>
                        <input id="section_name" wire:model.defer="section_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('section_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="teacher_id" class="block text-sm font-medium text-gray-700">Profesor</label>
                        <select id="teacher_id" wire:model.defer="teacher_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Seleccione un profesor</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Campos de fecha --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                        <input id="start_date" wire:model.defer="start_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                        <input id="end_date" wire:model.defer="end_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700">Hora de Inicio</label>
                        <input id="start_time" wire:model.defer="start_time" type="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700">Hora de Fin</label>
                        <input id="end_time" wire:model.defer="end_time" type="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('end_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Días de la Semana</label>
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-7 gap-2 mt-2">
                        @foreach(['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day)
                            <label class="flex items-center space-x-2 p-2 border rounded-md hover:bg-gray-50">
                                <input type="checkbox" wire:model.defer="days" value="{{ $day }}" class="rounded text-indigo-600">
                                <span class="text-sm">{{ $day }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('days') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end mt-6">
                    {{-- --- ¡CORRECCIÓN! --- --}}
                    <button type="button" wire:click="$dispatch('close-modal', 'schedule-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Guardar Sección</button>
                </div>
            </form>
        </div>
    </x-modal>

</div>