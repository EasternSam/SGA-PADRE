<div class="min-h-screen bg-gray-50 pb-8">
    
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
    <header class="bg-white shadow-sm mb-6 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-900">Gestión Académica</h1>
            <div class="w-1/3 relative">
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Buscar cursos por nombre o código..." 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                
                <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <svg class="animate-spin h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
            </div>
        </div>
        
        <div wire:loading class="absolute bottom-0 left-0 w-full h-1 bg-indigo-100 overflow-hidden">
            <div class="h-full bg-indigo-500 animate-progress origin-left"></div>
        </div>
    </header>

    {{-- Contenido Principal - Columnas --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-[calc(100vh-140px)] items-start">

            <!-- Columna 1: Cursos -->
            <div class="bg-white rounded-lg shadow border border-gray-200 flex flex-col h-full overflow-hidden">
                <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-800">Cursos</h2>
                    <div class="flex space-x-2">
                        <button wire:click="confirmClearUnusedCourses" wire:loading.attr="disabled" class="bg-red-500 hover:bg-red-700 text-white px-2 py-1 rounded-lg text-xs font-medium shadow-sm transition ease-in-out duration-150 disabled:opacity-50" title="Eliminar cursos sin estudiantes">
                            <i class="fas fa-trash-alt mr-1"></i> Limpiar
                        </button>
                        <button wire:click="createCourse" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-700 transition ease-in-out duration-150 flex items-center disabled:opacity-50">
                            <svg wire:loading wire:target="createCourse" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span wire:loading.remove wire:target="createCourse"><i class="fas fa-plus mr-1"></i> Añadir</span>
                            <span wire:loading wire:target="createCourse">Cargando...</span>
                        </button>
                    </div>
                </div>
                <div class="p-2 overflow-y-auto flex-1 custom-scrollbar">
                    <ul class="space-y-1">
                        @forelse ($courses as $course)
                            <li wire:key="course-{{ $course->id }}"
                                wire:click="selectCourse({{ $course->id }})" 
                                class="p-3 cursor-pointer rounded-lg border border-transparent transition-all duration-150 relative group
                                     {{ $selectedCourse == $course->id ? 'bg-indigo-50 border-indigo-200 shadow-sm' : 'hover:bg-gray-50 hover:border-gray-200' }}">
                                
                                <div wire:loading.flex wire:target="selectCourse({{ $course->id }})" class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center rounded-lg backdrop-blur-[1px]">
                                    <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>

                                <div class="flex justify-between items-center">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="block font-medium text-gray-900 truncate {{ $selectedCourse == $course->id ? 'text-indigo-700' : '' }}">{{ $course->name }}</span>
                                            @if($course->is_sequential)
                                                <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800">
                                                    Seq
                                                </span>
                                            @endif
                                        </div>
                                        <span class="block text-sm text-gray-500 font-mono">{{ $course->code }}</span>
                                        <span class="block text-xs text-gray-400 mt-1">
                                            Insc: ${{ number_format($course->registration_fee, 2) }} | Mes: ${{ number_format($course->monthly_fee, 2) }}
                                        </span>
                                        @if($course->mapping)
                                            <span class="block text-xs text-green-600 mt-1 truncate" title="ID de WP: {{ $course->mapping->wp_course_id }}">
                                                <i class="fas fa-check-circle mr-1"></i>Enlazado WP
                                            </span>
                                        @endif
                                        @if($course->moodle_course_id)
                                            <span class="block text-xs text-orange-600 mt-1 truncate" title="Moodle ID: {{ $course->moodle_course_id }}">
                                                <i class="fas fa-graduation-cap mr-1"></i>Moodle
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center pl-2 space-x-1">
                                        <!-- Enlace WP -->
                                        <button wire:click.stop="openLinkModal({{ $course->id }})" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors" title="Enlazar con WordPress">
                                            <i class="fas fa-link text-xs"></i>
                                        </button>
                                        <!-- Enlace Moodle (NUEVO: Nivel Curso) -->
                                        <button wire:click.stop="openMoodleLinkModal('course', {{ $course->id }})" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded transition-colors" title="Enlazar con Moodle">
                                            <i class="fas fa-graduation-cap text-xs"></i>
                                        </button>
                                        
                                        <button wire:click.stop="editCourse({{ $course->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                            <i class="fas fa-pencil-alt text-xs"></i>
                                        </button>
                                        <!-- BOTÓN ELIMINAR CURSO -->
                                        <button wire:click.stop="confirmDelete('course', {{ $course->id }})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Eliminar Curso">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="p-4 text-center text-gray-500 italic">No se encontraron cursos.</li>
                        @endforelse
                    </ul>
                    <div class="mt-4 px-2 pb-2">
                        {{ $courses->links(data: ['scrollTo' => false]) }}
                    </div>
                </div>
            </div>

            <!-- Columna 2: Módulos -->
            <div class="bg-white rounded-lg shadow border border-gray-200 flex flex-col h-full overflow-hidden relative">
                
                <div wire:loading.flex wire:target="selectCourse" class="absolute inset-0 bg-white/80 z-20 flex-col items-center justify-center backdrop-blur-sm transition-opacity">
                    <svg class="animate-spin h-8 w-8 text-indigo-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-sm font-medium text-gray-600">Cargando módulos...</span>
                </div>

                @if ($selectedCourse)
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-800 truncate pr-2">Módulos: <span class="font-bold text-indigo-700">{{ $selectedCourseName }}</span></h2>
                        <button wire:click="createModule" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-700 transition ease-in-out duration-150 flex-shrink-0 disabled:opacity-50">
                            <i class="fas fa-plus mr-1"></i> Añadir
                        </button>
                    </div>
                    <div class="p-2 overflow-y-auto flex-1 custom-scrollbar">
                        <ul class="space-y-1">
                            @forelse ($modules as $module)
                                <li wire:key="module-{{ $module->id }}"
                                    wire:click="selectModule({{ $module->id }})"
                                    class="p-3 cursor-pointer rounded-lg border border-transparent transition-all duration-150 relative group
                                           {{ $selectedModule == $module->id ? 'bg-indigo-50 border-indigo-200 shadow-sm' : 'hover:bg-gray-50 hover:border-gray-200' }}">
                                    
                                    <div wire:loading.flex wire:target="selectModule({{ $module->id }})" class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center rounded-lg backdrop-blur-[1px]">
                                        <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </div>

                                    <div class="flex justify-between items-center">
                                        <div>
                                            <span class="block font-medium text-gray-900 {{ $selectedModule == $module->id ? 'text-indigo-700' : '' }}">{{ $module->name }}</span>
                                            @if($module->moodle_course_id)
                                                <span class="block text-xs text-orange-600 mt-1 truncate" title="Moodle ID: {{ $module->moodle_course_id }}">
                                                    <i class="fas fa-graduation-cap mr-1"></i>Moodle
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <!-- Enlace Moodle (NUEVO: Nivel Módulo) -->
                                            <button wire:click.stop="openMoodleLinkModal('module', {{ $module->id }})" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded transition-colors" title="Enlazar con Moodle">
                                                <i class="fas fa-graduation-cap text-xs"></i>
                                            </button>

                                            <button wire:click.stop="editModule({{ $module->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                                <i class="fas fa-pencil-alt text-xs"></i>
                                            </button>
                                            <!-- BOTÓN ELIMINAR MÓDULO -->
                                            <button wire:click.stop="confirmDelete('module', {{ $module->id }})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Eliminar Módulo">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="p-4 text-center text-gray-500 italic">Este curso no tiene módulos.</li>
                            @endforelse
                        </ul>
                    </div>
                @else
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-800">Módulos</h2>
                    </div>
                    <div class="p-4 text-center text-gray-500 flex-1 flex flex-col items-center justify-center opacity-50">
                        <i class="fas fa-arrow-left text-4xl mb-3 text-gray-300"></i>
                        <p>Selecciona un curso</p>
                    </div>
                @endif
            </div>

            <!-- Columna 3: Secciones (Horarios) -->
            <div class="bg-white rounded-lg shadow border border-gray-200 flex flex-col h-full overflow-hidden relative">
                
                <div wire:loading.flex wire:target="selectModule" class="absolute inset-0 bg-white/80 z-20 flex-col items-center justify-center backdrop-blur-sm transition-opacity">
                    <svg class="animate-spin h-8 w-8 text-indigo-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-sm font-medium text-gray-600">Cargando secciones...</span>
                </div>

                @if ($selectedModule)
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-800 truncate pr-2">Secciones: <span class="font-bold text-indigo-700">{{ $selectedModuleName }}</span></h2>
                        <button wire:click="createSchedule" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-700 transition ease-in-out duration-150 flex-shrink-0 disabled:opacity-50">
                            <i class="fas fa-plus mr-1"></i> Añadir
                        </button>
                    </div>
                    <div class="p-2 overflow-y-auto flex-1 custom-scrollbar">
                        <ul class="space-y-1">
                            @forelse ($schedules as $schedule)
                                <li wire:key="schedule-{{ $schedule->id }}" class="p-3 hover:bg-gray-50 rounded-lg border border-gray-100 transition-colors duration-150 group">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1 min-w-0">
                                            <span class="block font-medium text-gray-900">{{ $schedule->section_name ?? ('Sección ' . $schedule->id) }}</span>
                                            <span class="block text-xs text-gray-500 mt-0.5">Prof: {{ $schedule->teacher->name ?? 'No asignado' }}</span>
                                            <span class="block text-xs text-gray-500 mt-0.5 flex items-center">
                                                <i class="far fa-clock mr-1"></i>
                                                {{ implode(', ', $schedule->days_of_week ?? []) }} | {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') : 'N/A' }} - {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') : 'N/A' }}
                                            </span>
                                            
                                            <span class="inline-block px-1.5 py-0.5 mt-1 text-[10px] font-semibold rounded {{ $schedule->modality === 'Virtual' ? 'bg-purple-100 text-purple-700' : ($schedule->modality === 'Semi-Presencial' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                                                {{ $schedule->modality ?? 'Presencial' }}
                                            </span>

                                            @if($schedule->classroom)
                                                <span class="inline-block ml-1 px-1.5 py-0.5 mt-1 text-[10px] font-semibold rounded bg-gray-100 text-gray-700 border border-gray-200">
                                                    {{ $schedule->classroom->name }}
                                                </span>
                                            @endif
                                            
                                            @if($schedule->mapping)
                                                <span class="block text-xs text-green-600 mt-1" title="WP Horario: {{ $schedule->mapping->wp_schedule_data }}">
                                                    <i class="fas fa-check-circle mr-1"></i> WP Enlazado
                                                </span>
                                            @endif

                                            @if($schedule->moodle_course_id)
                                                <span class="block text-xs text-orange-600 mt-1 truncate" title="Moodle ID: {{ $schedule->moodle_course_id }}">
                                                    <i class="fas fa-graduation-cap mr-1"></i> Moodle
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="flex items-center space-x-1 pl-2">
                                            <!-- Enlace Moodle (NUEVO: Nivel Sección) -->
                                            <button wire:click.stop="openMoodleLinkModal('schedule', {{ $schedule->id }})" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded transition-colors" title="Enlazar con Moodle">
                                                <i class="fas fa-graduation-cap text-xs"></i>
                                            </button>

                                            <!-- Enlace WP (Existente) -->
                                            @if($selectedCourseObject?->mapping)
                                                @php
                                                    $scheduleMapping = $schedule->mapping; 
                                                @endphp
                                                <button wire:click.stop="openMapSectionModal({{ $schedule->id }})" 
                                                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors {{ $scheduleMapping ? 'text-blue-600' : '' }}" 
                                                        title="{{ $scheduleMapping ? 'Modificar Enlace WP de Sección' : 'Enlazar Sección con WP' }}">
                                                    <i class="fas fa-link text-xs"></i>
                                                </button>
                                            @else
                                                <button class="p-1.5 text-gray-300 cursor-not-allowed" disabled title="Enlace el curso principal primero">
                                                    <i class="fas fa-link text-xs"></i>
                                                </button>
                                            @endif

                                            <button wire:click.stop="editSchedule({{ $schedule->id }})" class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded transition-colors">
                                                <i class="fas fa-pencil-alt text-xs"></i>
                                            </button>
                                            
                                            <!-- BOTÓN ELIMINAR SECCIÓN -->
                                            <button wire:click.stop="confirmDelete('schedule', {{ $schedule->id }})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors" title="Eliminar Sección">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="p-4 text-center text-gray-500 italic">Este módulo no tiene secciones.</li>
                            @endforelse
                        </ul>
                    </div>
                @else
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-800">Secciones</h2>
                    </div>
                    <div class="p-4 text-center text-gray-500 flex-1 flex flex-col items-center justify-center opacity-50">
                        <i class="fas fa-arrow-left text-4xl mb-3 text-gray-300"></i>
                        <p>Selecciona un módulo</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- MODALES --}}

    <!-- Modal de Confirmación de Limpieza (EXISTENTE) -->
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

    <!-- Modal de Confirmación de Eliminación Individual (NUEVO) -->
    <x-modal name="confirm-delete-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-red-600 mb-4">⚠️ Confirmar Eliminación</h2>
            
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            {{ $deleteMessage }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6 space-x-3">
                <button type="button" x-on:click="$dispatch('close-modal', 'confirm-delete-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">
                    Cancelar
                </button>
                <button type="button" wire:click="deleteItem" class="bg-red-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-red-700">
                    Eliminar
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Modal de Curso (EXISTENTE) -->
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
                <div class="mt-4 flex items-center">
                    <input type="checkbox" id="is_sequential" wire:model="is_sequential" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <label for="is_sequential" class="ml-2 block text-sm text-gray-900">
                        Módulos Requeridos (Secuencial)
                        <span class="text-xs text-gray-500 block">Si se activa, los estudiantes deben aprobar el módulo 1 para inscribirse en el 2, etc.</span>
                    </label>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="button" x-on:click="$dispatch('close-modal', 'course-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                        <svg wire:loading wire:target="saveCourse" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Guardar Curso
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal de Módulo (EXISTENTE) -->
    <x-modal name="module-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $moduleModalTitle }}</h2>
            <form wire:submit.prevent="saveModule">
                <div class="mt-4">
                    <label for="module_name" class="block text-sm font-medium text-gray-700">Nombre del Módulo</label>
                    <input id="module_name" wire:model="module_name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    @error('module_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end mt-6">
                    <button type="button" x-on:click="$dispatch('close-modal', 'module-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                    <button type="submit" wire:loading.attr="disabled" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                        <svg wire:loading wire:target="saveModule" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Guardar Módulo
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal de Horario (Sección) (EXISTENTE) -->
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
                <div class="mt-4">
                    <label for="modality" class="block text-sm font-medium text-gray-700">Modalidad</label>
                    <select id="modality" wire:model="modality" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="Presencial">Presencial</option>
                        <option value="Virtual">Virtual</option>
                        <option value="Semi-Presencial">Semi-Presencial</option>
                    </select>
                    @error('modality') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mt-4">
                    <x-input-label for="classroom_id" :value="__('Aula / Laboratorio')" />
                    <select wire:model="classroom_id" id="classroom_id" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Sin Asignar / Virtual --</option>
                        @if(!empty($classroomsGrouped))
                            @foreach($classroomsGrouped as $buildingName => $classrooms)
                                <optgroup label="{{ $buildingName }}">
                                    @foreach($classrooms as $room)
                                        <option value="{{ $room->id }}">
                                            {{ $room->name }} (Cap: {{ $room->capacity }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-xs text-gray-500 mt-1">El sistema verificará disponibilidad de horario automáticamente.</p>
                    <x-input-error :messages="$errors->get('classroom_id')" class="mt-2" />
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
                    <button type="submit" wire:loading.attr="disabled" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                        <svg wire:loading wire:target="saveSchedule" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Guardar Sección
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Modal Enlace WP (EXISTENTE) -->
    <x-modal name="link-wp-modal" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Enlazar Curso con WordPress</h2>
            @if($currentLinkingCourse)
                <p class="text-sm text-gray-600 mb-4">Estás enlazando el curso: <strong class="text-gray-900">{{ $currentLinkingCourse->name }}</strong></p>
            @endif
            <div wire:loading wire:target="openLinkModal, saveLink" class="w-full">
                <div class="flex items-center p-3 text-sm text-blue-700 bg-blue-50 rounded-lg" role="alert">
                    <svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="font-medium">Cargando cursos de WordPress...</span>
                </div>
            </div>
            @if($linkFeedbackMessage) <div class="p-3 text-sm text-green-700 bg-green-50 rounded-lg mb-4" role="alert">{{ $linkFeedbackMessage }}</div> @endif
            @if($linkErrorMessage) <div class="p-3 text-sm text-red-700 bg-red-50 rounded-lg mb-4" role="alert"><span class="font-medium">Error:</span> {{ $linkErrorMessage }}</div> @endif
            <div wire:loading.remove wire:target="openLinkModal">
                @if(!$linkErrorMessage && !empty($wpCourses))
                    <div class="mt-4">
                        <label for="wp_course_select" class="block text-sm font-medium text-gray-700">Selecciona el curso de WordPress</label>
                        <select id="wp_course_select" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" wire:model="selectedWpCourseId">
                            <option value="">-- Ninguno (Quitar enlace) --</option>
                            @foreach($wpCourses as $wpCourse)
                                <option value="{{ $wpCourse['wp_course_id'] }}">{{ $wpCourse['wp_course_name'] }} (ID: {{ $wpCourse['wp_course_id'] }})</option>
                            @endforeach
                        </select>
                    </div>
                @elseif(!$linkErrorMessage)
                    <p class="mt-4 text-sm text-gray-500 italic">No se encontraron cursos en WordPress.</p>
                @endif
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" x-on:click="$dispatch('close-modal', 'link-wp-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                <button type="button" wire:click="saveLink()" wire:loading.attr="disabled" wire:target="saveLink" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <svg wire:loading wire:target="saveLink" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Guardar Enlace
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Modal Enlace Moodle (FLEXIBLE) -->
    <x-modal name="link-moodle-modal" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Enlazar con Moodle</h2>
            <p class="text-sm text-gray-600 mb-4">Estás configurando: <strong class="text-gray-900">{{ $moodleLinkingTitle }}</strong></p>
            
            <div wire:loading wire:target="openMoodleLinkModal, saveMoodleLink" class="w-full mb-4">
                <div class="flex items-center p-3 text-sm text-orange-700 bg-orange-50 rounded-lg">
                    <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Cargando cursos de Moodle...</span>
                </div>
            </div>

            @if($moodleLinkErrorMessage) <div class="p-3 text-sm text-red-700 bg-red-50 rounded-lg mb-4">Error: {{ $moodleLinkErrorMessage }}</div> @endif
            
            <div wire:loading.remove wire:target="openMoodleLinkModal">
                @if(!$moodleLinkErrorMessage && !empty($moodleCourses))
                    <div class="mt-4">
                        <label for="moodle_course_select" class="block text-sm font-medium text-gray-700">Selecciona el curso de Moodle</label>
                        <select id="moodle_course_select" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mt-1 block w-full" wire:model="selectedMoodleCourseId">
                            <option value="">-- Ninguno (Quitar enlace) --</option>
                            @foreach($moodleCourses as $moodleCourse)
                                <option value="{{ $moodleCourse['id'] }}">{{ $moodleCourse['fullname'] ?? $moodleCourse['shortname'] }} (ID: {{ $moodleCourse['id'] }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-2">
                            @if($moodleLinkingType === 'schedule')
                                Nota: Este enlace tiene prioridad sobre los enlaces de Módulo y Curso.
                            @elseif($moodleLinkingType === 'module')
                                Nota: Este enlace tiene prioridad sobre el enlace del Curso, pero puede ser sobreescrito por una Sección.
                            @endif
                        </p>
                    </div>
                @elseif(!$moodleLinkErrorMessage)
                    <p class="mt-4 text-sm text-gray-500 italic">No se encontraron cursos en Moodle.</p>
                @endif
            </div>
            
            <div class="flex justify-end mt-6">
                <button x-on:click="$dispatch('close-modal', 'link-moodle-modal')" class="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Cancelar</button>
                <button wire:click="saveMoodleLink()" wire:loading.attr="disabled" class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded-lg">Guardar Enlace</button>
            </div>
        </div>
    </x-modal>

    <!-- Modal Enlace Sección (EXISTENTE) -->
    <x-modal name="link-section-modal" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Enlazar Sección con WordPress</h2>
            @if($currentLinkingSection)
                <p class="text-sm text-gray-600 mb-4">Estás enlazando la sección: <strong class="text-gray-900">{{ $currentLinkingSection->section_name ?? 'Sección ' . $currentLinkingSection->id }}</strong></p>
            @endif
            <div wire:loading wire:target="openMapSectionModal, saveSectionLink" class="w-full">
                <div class="flex items-center p-3 text-sm text-blue-700 bg-blue-50 rounded-lg" role="alert">
                    <svg class="w-4 h-4 mr-2 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="font-medium">Cargando horarios de WordPress...</span>
                </div>
            </div>
            @if($sectionLinkErrorMessage) <div class="p-3 text-sm text-red-700 bg-red-50 rounded-lg mb-4" role="alert"><span class="font-medium">Error:</span> {{ $sectionLinkErrorMessage }}</div> @endif
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
                                <option value="{{ $wpSchedule['id'] }}">{{ $displayText }} (ID: {{ $wpSchedule['id'] }})</option>
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
                <button type="button" wire:click="saveSectionLink()" wire:loading.attr="disabled" wire:target="saveSectionLink" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <svg wire:loading wire:target="saveSectionLink" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Guardar Enlace
                </button>
                @endif
            </div>
        </div>
    </x-modal>

</div>