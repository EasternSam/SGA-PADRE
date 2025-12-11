<div> {{-- <--- ESTE ES EL DIV RAÍZ OBLIGATORIO QUE ABRE --}}


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
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar cursos por nombre o código..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
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
                    <div class="flex space-x-2">
                        <!-- Botón de Limpieza -->
                        <button wire:click="confirmClearUnusedCourses" class="bg-red-500 hover:bg-red-700 text-white px-2 py-1 rounded-lg text-xs font-medium shadow-sm" title="Eliminar cursos sin estudiantes">
                            <i class="fas fa-trash-alt mr-1"></i> Limpiar
                        </button>
                        <!-- Botón Añadir -->
                        <button wire:click="createCourse" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-700">
                            <i class="fas fa-plus mr-1"></i> Añadir
                        </button>
                    </div>
                </div>
                <div class="p-4">
                    <ul class="divide-y divide-gray-200">
                        @forelse ($courses as $course)
                            <li wire:click="selectCourse({{ $course->id }})" 
                                class="p-3 cursor-pointer hover:bg-gray-100 rounded-lg {{ $selectedCourse == $course->id ? 'bg-indigo-100 border-l-4 border-indigo-500' : '' }}">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="block font-medium text-gray-900">{{ $course->name }}</span>
                                        <span class="block text-sm text-gray-500">{{ $course->code }}</span>
                                        
                                        <!-- Mostrar precios en lista -->
                                        <span class="block text-xs text-gray-500 mt-1">
                                            Insc: ${{ number_format($course->registration_fee, 2) }} | Mes: ${{ number_format($course->monthly_fee, 2) }}
                                        </span>

                                        <!-- Indicador Secuencial -->
                                        @if($course->is_sequential)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                                                <i class="fas fa-list-ol mr-1"></i> Secuencial
                                            </span>
                                        @endif

                                        @if($course->mapping)
                                            <span class="block text-xs text-green-600 mt-1" title="ID de WP: {{ $course->mapping->wp_course_id }}">
                                                <i class="fas fa-check-circle mr-1"></i>Enlazado: {{ $course->mapping->wp_course_name }}
                                            </span>
                                        @else
                                            <span class="block text-xs text-gray-400 italic mt-1">
                                                <i class="fas fa-times-circle mr-1"></i>No enlazado
                                            </span>
                                        @endif

                                    </div>
                                    <div class="flex items-center">
                                        <button wire:click.stop="openLinkModal({{ $course->id }})" class="text-gray-400 hover:text-blue-600 text-xs mr-2" title="Enlazar con WordPress">
                                            <i class="fas fa-link"></i>
                                        </button>
                                        <button wire:click.stop="editCourse({{ $course->id }})" class="text-gray-400 hover:text-indigo-600 text-xs">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                    </div>
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
                            @forelse ($modules as $module)
                                <li wire:click="selectModule({{ $module->id }})"
                                    class="p-3 cursor-pointer hover:bg-gray-100 rounded-lg {{ $selectedModule == $module->id ? 'bg-indigo-100 border-l-4 border-indigo-500' : '' }}">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="block font-medium text-gray-900">{{ $module->name }}</span>
                                            {{-- Eliminado: Mostrar precio del módulo aquí --}}
                                        </div>
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
                                                {{ implode(', ', $schedule->days_of_week ?? []) }} | {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') : 'N/A' }} - {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') : 'N/A' }}
                                            </span>
                                            
                                            {{-- Etiqueta de Modalidad --}}
                                            <span class="block text-xs font-semibold mt-1 {{ $schedule->modality === 'Virtual' ? 'text-purple-600' : ($schedule->modality === 'Semi-Presencial' ? 'text-orange-600' : 'text-blue-600') }}">
                                                {{ $schedule->modality ?? 'Presencial' }}
                                            </span>
                                            
                                            @if($schedule->mapping)
                                                <span class="block text-xs text-green-600" title="WP Horario: {{ $schedule->mapping->wp_schedule_data }}">
                                                    <i class="fas fa-check-circle mr-1"></i>Sección Enlazada
                                                </span>
                                            @endif

                                        </div>
                                        
                                        <div class="flex items-center">
                                            @if($selectedCourseObject?->mapping)
                                                @php
                                                    $scheduleMapping = $schedule->mapping; 
                                                @endphp
                                                <button wire:click.stop="openMapSectionModal({{ $schedule->id }})" 
                                                        class="text-gray-400 hover:text-blue-600 text-xs mr-2 {{ $scheduleMapping ? 'text-blue-600' : '' }}" 
                                                        title="{{ $scheduleMapping ? 'Modificar Enlace WP de Sección' : 'Enlazar Sección con WP' }}">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                            @else
                                                <button class="text-gray-300 text-xs mr-2 cursor-not-allowed" disabled title="Enlace el curso principal primero">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                            @endif

                                            <button wire:click.stop="editSchedule({{ $schedule->id }})" class="text-gray-400 hover:text-indigo-600 text-xs">
                                                <i class="fas fa-pencil-alt"></i>
                                            </button>
                                        </div>

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

    <!-- Modal de Confirmación de Limpieza -->
    <x-modal name="confirm-clear-unused-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-red-600 mb-4">⚠️ Confirmar Limpieza Masiva</h2>
            
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            Estás a punto de eliminar <strong class="text-lg">{{ $unusedCoursesCount }}</strong> curso(s).
                        </p>
                        <p class="text-xs text-red-600 mt-1">
                            Esta acción eliminará permanentemente los cursos que <strong>no tienen estudiantes inscritos</strong> en ninguno de sus módulos.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'confirm-clear-unused-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                    Cancelar
                </button>
                <button type="button" wire:click="clearUnusedCourses" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700">
                    Sí, eliminar cursos vacíos
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Modal de Curso -->
    <x-modal name="course-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $courseModalTitle }}</h2>
            <form wire:submit.prevent="saveCourse">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="course_code" value="Código" />
                        <x-text-input wire:model="course_code" id="course_code" class="block mt-1 w-full" type="text" />
                        @error('course_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <x-input-label for="course_name" value="Nombre" />
                    <x-text-input wire:model="course_name" id="course_name" class="block mt-1 w-full" type="text" />
                    @error('course_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- NUEVOS CAMPOS DE PRECIOS -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <x-input-label for="registration_fee" value="Precio de Inscripción" />
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <x-text-input wire:model="registration_fee" id="registration_fee" class="block w-full pl-7" type="number" step="0.01" />
                        </div>
                        @error('registration_fee') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-input-label for="monthly_fee" value="Mensualidad" />
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <x-text-input wire:model="monthly_fee" id="monthly_fee" class="block w-full pl-7" type="number" step="0.01" />
                        </div>
                        @error('monthly_fee') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Checkbox para Módulos Secuenciales -->
                <div class="mt-4 flex items-center">
                    <input type="checkbox" id="is_sequential" wire:model="is_sequential" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_sequential" class="ml-2 block text-sm text-gray-900">
                        Módulos Requeridos (Secuencial)
                        <span class="text-xs text-gray-500 block">Si se activa, los estudiantes deben aprobar el módulo 1 para inscribirse en el 2, etc.</span>
                    </label>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button type="button" x-on:click="$dispatch('close-modal', 'course-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
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
                    <input id="module_name" wire:model="module_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('module_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- ELIMINADO: Campo de Precio de Módulo --}}
                
                <div class="flex justify-end mt-6">
                    <button type="button" x-on:click="$dispatch('close-modal', 'module-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
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
                        <input id="section_name" wire:model="section_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('section_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="teacher_id" class="block text-sm font-medium text-gray-700">Profesor</label>
                        <select id="teacher_id" wire:model="teacher_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Seleccione un profesor</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                        @error('teacher_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- CAMPO MODALIDAD -->
                <div class="mt-4">
                    <label for="modality" class="block text-sm font-medium text-gray-700">Modalidad</label>
                    <select id="modality" wire:model="modality" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="Presencial">Presencial</option>
                        <option value="Virtual">Virtual</option>
                        <option value="Semi-Presencial">Semi-Presencial</option>
                    </select>
                    @error('modality') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">Fecha de Inicio</label>
                        <input id="start_date" wire:model="start_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">Fecha de Fin</label>
                        <input id="end_date" wire:model="end_date" type="date" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700">Hora de Inicio</label>
                        <input id="start_time" wire:model="start_time" type="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('start_time') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700">Hora de Fin</label>
                        <input id="end_time" wire:model="end_time" type="time" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
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
                    <button type="button" x-on:click="$dispatch('close-modal', 'schedule-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Guardar Sección</button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal Enlace WP (Sin cambios, solo contexto) -->
    <x-modal name="link-wp-modal" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Enlazar Curso con WordPress</h2>
            
            @if($currentLinkingCourse)
                <p class="text-sm text-gray-600 mb-4">
                    Estás enlazando el curso: <strong class="text-gray-900">{{ $currentLinkingCourse->name }}</strong>
                </p>
            @endif
            
            <div wire:loading wire:target="openLinkModal, saveLink" class="w-full">
                <div class="flex items-center p-3 text-sm text-blue-700 bg-blue-50 rounded-lg" role="alert">
                    <svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="font-medium">Cargando cursos de WordPress...</span>
                </div>
            </div>

            @if($linkFeedbackMessage)
                <div class="p-3 text-sm text-green-700 bg-green-50 rounded-lg mb-4" role="alert">
                    {{ $linkFeedbackMessage }}
                </div>
            @endif
            @if($linkErrorMessage)
                <div class="p-3 text-sm text-red-700 bg-red-50 rounded-lg mb-4" role="alert">
                    <span class="font-medium">Error:</span> {{ $linkErrorMessage }}
                </div>
            @endif

            <div wire:loading.remove wire:target="openLinkModal">
                @if(!$linkErrorMessage && !empty($wpCourses))
                    <div class="mt-4">
                        <label for="wp_course_select" class="block text-sm font-medium text-gray-700">Selecciona el curso de WordPress</label>
                        <select id="wp_course_select" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" wire:model="selectedWpCourseId">
                            <option value="">-- Ninguno (Quitar enlace) --</option>
                            @foreach($wpCourses as $wpCourse)
                                <option value="{{ $wpCourse['wp_course_id'] }}">
                                    {{ $wpCourse['wp_course_name'] }} (ID: {{ $wpCourse['wp_course_id'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif(!$linkErrorMessage)
                    <p class="mt-4 text-sm text-gray-500 italic">No se encontraron cursos en WordPress.</p>
                @endif
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" x-on:click="$dispatch('close-modal', 'link-wp-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                <button type="button" wire:click="saveLink()" wire:loading.attr="disabled" wire:target="saveLink" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Guardar Enlace</button>
            </div>
        </div>
    </x-modal>

    <!-- Modal Enlace Sección (Sin cambios, solo contexto) -->
    <x-modal name="link-section-modal" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Enlazar Sección con WordPress</h2>

            @if($currentLinkingSection)
                <p class="text-sm text-gray-600 mb-4">
                    Estás enlazando la sección: <strong class="text-gray-900">{{ $currentLinkingSection->section_name ?? 'Sección ' . $currentLinkingSection->id }}</strong>
                </p>
            @endif

            <div wire:loading wire:target="openMapSectionModal, saveSectionLink" class="w-full">
                <div class="flex items-center p-3 text-sm text-blue-700 bg-blue-50 rounded-lg" role="alert">
                    <svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="font-medium">Cargando horarios de WordPress...</span>
                </div>
            </div>
            
            @if($sectionLinkErrorMessage)
                <div class="p-3 text-sm text-red-700 bg-red-50 rounded-lg mb-4" role="alert">
                    <span class="font-medium">Error:</span> {{ $sectionLinkErrorMessage }}
                </div>
            @endif

            <div wire:loading.remove wire:target="openMapSectionModal">
                @if(!$sectionLinkErrorMessage && !empty($wpSchedules))
                    <div class="mt-4">
                        <label for="wp_schedule_select" class="block text-sm font-medium text-gray-700">Selecciona el horario de WordPress</label>
                        <select id="wp_schedule_select" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" wire:model="selectedWpScheduleId">
                            <option value="">-- Ninguno (Quitar enlace) --</option>
                            @foreach($wpSchedules as $wpSchedule)
                                @php
                                    $day = $wpSchedule['day'] ?? 'Día no def.';
                                    $start = isset($wpSchedule['start_time']) ? \Carbon\Carbon::parse($wpSchedule['start_time'])->format('h:i A') : 'N/A';
                                    $end = isset($wpSchedule['end_time']) ? \Carbon\Carbon::parse($wpSchedule['end_time'])->format('h:i A') : 'N/A';
                                    $displayText = ucfirst($day) . " de {$start} a {$end}";
                                @endphp
                                <option value="{{ $wpSchedule['id'] }}">
                                    {{ $displayText }} (ID: {{ $wpSchedule['id'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif(!$sectionLinkErrorMessage)
                    <p class="mt-4 text-sm text-gray-500 italic">No se encontraron horarios definidos en WordPress para el curso enlazado.</p>
                @endif
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" x-on:click="$dispatch('close-modal', 'link-section-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                @if(!empty($wpSchedules) || !empty($selectedWpScheduleId))
                <button type="button" wire:click="saveSectionLink()" wire:loading.attr="disabled" wire:target="saveSectionLink" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg">Guardar Enlace</button>
                @endif
            </div>
        </div>
    </x-modal>

</div> {{-- <--- ESTE ES EL DIV RAÍZ OBLIGATORIO QUE CIERRA --}}