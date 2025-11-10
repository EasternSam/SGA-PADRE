{{-- ¡¡¡AQUÍ ESTÁ LA SOLUCIÓN!!! --}}
{{-- Añadimos x-data y el listener @open-new-tab.window --}}
<div class="container mx-auto p-4 md:p-6 lg:p-8"
     x-data
     @open-new-tab.window="window.open($event.detail, '_blank')">

    {{-- ¡¡¡REPARACIÓN!!! Se añade el bloque de mensajes flash --}}
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

    {{-- Encabezado del Perfil --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
        <div class="md:flex">
            <!-- Avatar e Info Básica -->
            <div class="md:w-1/3 p-6 bg-gray-50 border-b md:border-b-0 md:border-r border-gray-200 text-center">
                <img class="h-32 w-32 rounded-full mx-auto shadow-md mb-4" 
                     src="https://ui-avatars.com/api/?name={{ urlencode($student->fullName) }}&background=1e3a8a&color=ffffff&size=128" 
                     alt="Avatar de {{ $student->fullName }}">
                
                <h1 class="text-2xl font-bold text-gray-900">{{ $student->fullName }}</h1>
                <p class="text-sm text-gray-600">{{ $student->email }}</p>
                <p class="text-sm text-gray-600">Cédula: {{ $student->cedula ?? 'N/A' }}</p>
                <span class="mt-3 inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium 
                   {{ $student->status === 'Activa' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                   {{ $student->status }}
                </span>
            </div>
            
            <!-- Información Detallada y Acciones -->
            <div class="md:w-2/3 p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Detalles del Estudiante</h2>
                    <div>
                        <button wire:click="openEditModal" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-sm transition ease-in-out duration-150">
                            <i class="fas fa-edit mr-2"></i>Editar Perfil
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-600 block">Teléfono Móvil:</strong>
                        <span class="text-gray-800">{{ $student->mobile_phone ?? $student->phone ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-600 block">Fecha de Nacimiento:</strong>
                        <span class="text-gray-800">{{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'N/A' }} ({{ $student->age }} años)</span>
                    </div>
                    <div>
                        <strong class="text-gray-600 block">Dirección:</strong>
                        <span class="text-gray-800">{{ $student->address ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-600 block">Género:</strong>
                        <span class="text-gray-800">{{ $student->gender ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <hr class="my-6 border-gray-200">

                <div class="flex justify-start space-x-3">
                    <button wire:click="openEnrollModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-plus-circle mr-2"></i>Inscribir a Curso
                    </button>
                    {{-- Este es el botón que falla --}}
                    <button wire:click="generateReport" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-file-pdf mr-2"></i>Generar Reporte
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Pestañas de Cursos y Pagos --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Pestañas (Simuladas, puedes añadir JS para esto si lo deseas) -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-6 px-6">
                <span class="whitespace-nowrap py-4 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600">
                    Cursos Inscritos
                </span>
                <span class="whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 cursor-pointer">
                    Historial de Pagos (Próximamente)
                </span>
            </nav>
        </div>

        <!-- Contenido de Pestaña: Cursos Inscritos -->
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Matrículas</h3>
                <div>
                    <label for="enrollmentStatusFilter" class="text-sm font-medium text-gray-700">Filtrar por estado:</label>
                    <select id="enrollmentStatusFilter" wire:model.live="enrollmentStatusFilter" class="ml-2 border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="all">Todos</option>
                        <option value="active">Activo</option>
                        <option value="completed">Completado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Cursos Inscritos -->
            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Calificación</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        
                        {{-- ¡¡¡REPARACIÓN 1!!! Se cambió $student->enrollments por $enrollments --}}
                        @forelse ($enrollments as $enrollment)
                            {{-- ¡¡¡REPARACIÓN CLAVE!!! Añadir wire:key para que Livewire actualice la fila correctamente --}}
                            <tr class="hover:bg-gray-50" wire:key="enrollment-{{ $enrollment->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span classpx-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $enrollment->status === 'active' ? 'bg-green-100 text-green-800' : 
                                           ($enrollment->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center">{{ $enrollment->final_grade ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($enrollment->status === 'active')
                                        <button wire:click="confirmUnenroll({{ $enrollment->id }})" class="text-red-600 hover:text-red-900 transition ease-in-out duration-150" title="Anular Inscripción">
                                            <i class="fas fa-times-circle"></i> Anular
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No hay cursos inscritos que coincidan con el filtro.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{-- ¡¡¡REPARACIÓN 2!!! Se cambió $student->enrollments por $enrollments --}}
                {{ $enrollments->links('vendor.livewire.tailwind') }}
            </div>
        </div>
    </div>

    {{-- Modales --}}
    
    <x-modal name="edit-student-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Editar Perfil (Próximamente)
            </h2>
            <p class="text-gray-700">
                Aquí irá el formulario para editar la información del estudiante.
            </p>
            <div class="flex justify-end">
                {{-- ¡¡¡REPARACIÓN!!! Se especifica el modal a cerrar --}}
                <button type="button" wire:click="$dispatch('close-modal', 'edit-student-modal')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg">
                    Cerrar
                </button>
            </div>
        </div>
    </x-modal>

    <x-modal name="enroll-student-modal" maxWidth="3xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Inscribir a Nueva Sección
            </h2>
            
            {{-- ¡¡¡REPARACIÓN!!! Añadir mensajes de error DENTRO del modal --}}
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg mb-4" role="alert">
                    <strong class="font-bold">¡Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            
            <input wire:model.live.debounce.300ms="searchAvailableCourse" type="text" placeholder="Buscar curso o módulo..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm mb-4">

            <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="w-1/12"></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso / Módulo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sección</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($availableSchedules as $schedule)
                        {{-- ¡¡¡REPARACIÓN!!! Añadir wire:key también a este bucle --}}
                        <tr class="hover:bg-gray-50" wire:key="schedule-{{ $schedule->id }}">
                            <td class="px-6 py-4">
                                {{-- ¡¡¡REPARACIÓN!!! Se añade .live para que el botón se active al instante --}}
                                <input type="radio" wire:model.live="selectedScheduleId" value="{{ $schedule->id }}" id="schedule-{{ $schedule->id }}" class="text-indigo-600">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <label for="schedule-{{ $schedule->id }}" class="cursor-pointer">
                                    <span class="text-sm font-medium text-gray-900 block">{{ $schedule->module->course->name ?? 'N/A' }}</span>
                                    <span class="text-sm text-gray-600 block">{{ $schedule->module->name ?? 'N/A' }}</span>
                                </label>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $schedule->section_name ?? $schedule->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $schedule->teacher->name ?? 'N/A' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">No se encontraron secciones disponibles.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end mt-6">
                {{-- ¡¡¡REPARACIÓN!!! Este botón ahora llama a closeEnrollModal, que tiene el dispatch específico --}}
                <button type="button" wire:click="closeEnrollModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <button type="button" wire:click="enrollStudent" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150" @if(empty($selectedScheduleId)) disabled @endif>
                    Inscribir Estudiante
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Modal de Confirmación de Anulación -->
    {{-- ¡¡¡REPARACIÓN!!! Se cambia :show por name, para que funcione con eventos como los otros modales --}}
    <x-modal name="confirm-unenroll-modal">
        <div class="p-6">
            {{-- ¡¡¡REPARACIÓN!!! Texto actualizado a "Eliminar" --}}
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                ¿Eliminar Inscripción?
            </h2>
            {{-- ¡¡¡REPARACIÓN!!! Texto actualizado para reflejar la eliminación --}}
            <p class="text-gray-700 mb-6">
                ¿Estás seguro de que deseas eliminar esta inscripción? Esta acción es permanente y no se puede deshacer.
            </p>
            <div class="flex justify-end">
                {{-- ¡¡¡REPARACIÓN!!! Se especifica el modal a cerrar --}}
                <button type="button" wire:click="$dispatch('close-modal', 'confirm-unenroll-modal')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <button type="button" wire:click="unenroll" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                    {{-- ¡¡¡REPARACIÓN!!! Texto actualizado a "Eliminar" --}}
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </x-modal>

</div>