{{-- Eliminamos el @php de Carbon, ya que se usará en el controlador o en helpers de Blade --}}
<div classA="container mx-auto p-4 md:p-6 lg:p-8"
     x-data
     @open-new-tab.window="window.open($event.detail.url, '_blank')"> {{-- Escucha el evento para abrir PDF --}}

    {{-- Mensajes Flash (Toast) --}}
    <div x-data="{ show: false, message: '', type: 'success' }"
         x-init="
            @if (session()->has('message'))
                show = true;
                message = '{{ session('message') }}';
                type = 'success';
                setTimeout(() => show = false, 4000);
            @endif
            @if (session()->has('error'))
                show = true;
                message = '{{ session('error') }}';
                type = 'error';
                setTimeout(() => show = false, 4000);
            @endif
            Livewire.on('flashMessage', (data) => {
                message = data.message;
                type = data.type;
                show = true;
                setTimeout(() => show = false, 4000);
            });
         "
         x-show="show"
         x-transition
         :class="{ 'bg-green-100 border-green-400 text-green-700': type === 'success', 'bg-red-100 border-red-400 text-red-700': type === 'error' }"
         class="fixed top-24 right-6 z-50 px-4 py-3 rounded-lg shadow-lg"
         role="alert"
         style="display: none;">
        <strong classs="font-bold" x-text="type === 'success' ? '¡Éxito!' : '¡Error!'"></strong>
        <span class="block sm:inline" x-text="message"></span>
    </div>


    {{-- Encabezado del Perfil --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="md:flex">
            <!-- Avatar e Info Básica -->
            <div class="md:w-1/3 p-6 bg-gray-50 dark:bg-gray-700/50 border-b md:border-b-0 md:border-r border-gray-200 dark:border-gray-700 text-center">
                <img class="h-32 w-32 rounded-full mx-auto shadow-md mb-4" 
                     src="https://ui-avatars.com/api/?name={{ urlencode($student->fullName) }}&background=4f46e5&color=ffffff&size=128" 
                     alt="Avatar de {{ $student->fullName }}">
                
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $student->fullName }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $student->email }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Cédula: {{ $student->cedula ?? 'N/A' }}</p>
                <span @class([
                        'mt-3 inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium',
                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $student->status === 'Activo',
                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $student->status !== 'Activo',
                    ])>
                   {{ $student->status }}
                </span>
            </div>
            
            <!-- Información Detallada y Acciones -->
            <div class="md:w-2/3 p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Detalles del Estudiante</h2>
                    <div>
                        {{-- Este botón ahora redirige a la página de edición de perfil estándar de Breeze/Jetstream --}}
                        <a href="{{ route('profile.show') }}" class="text-sm bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg shadow-sm transition ease-in-out duration-150">
                            <i class="fas fa-user-edit mr-2"></i>Editar Perfil/Usuario
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-500 dark:text-gray-400 block">Teléfono Móvil:</strong>
                        <span class="text-gray-900 dark:text-gray-100">{{ $student->mobile_phone ?? $student->phone ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 dark:text-gray-400 block">Fecha de Nacimiento:</strong>
                        <span class="text-gray-900 dark:text-gray-100">{{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'N/A' }} ({{ $student->age }} años)</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 dark:text-gray-400 block">Dirección:</strong>
                        <span class="text-gray-900 dark:text-gray-100">{{ $student->address ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 dark:text-gray-400 block">Género:</strong>
                        <span class="text-gray-900 dark:text-gray-100">{{ $student->gender ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 dark:text-gray-400 block">Ciudad:</strong>
                        <span class="text-gray-900 dark:text-gray-100">{{ $student->city ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 dark:text-gray-400 block">Sector:</strong>
                        <span class="text-gray-900 dark:text-gray-100">{{ $student->sector ?? 'N/A' }}</span>
                    </div>
                </div>
                
                <hr class="my-6 border-gray-200 dark:border-gray-700">

                <div class="flex flex-wrap gap-2">
                    <button wire:click="openEnrollmentModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-plus-circle mr-2"></i>Inscribir a Curso
                    </button>
                    {{-- Este es el botón que dispara el evento para abrir el PDF en una nueva pestaña --}}
                    <button wire:click="generateReport" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-file-pdf mr-2"></i>Generar Reporte
                    </button>
                    
                    {{-- --- ¡BOTÓN CORREGIDO! --- --}}
                    {{-- Llama al modal 'payment-modal' que definimos en el nuevo componente --}}
                    <button wire:click="$dispatch('open-modal', 'payment-modal')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-dollar-sign mr-2"></i>Registrar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Pestañas y contenido --}}
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden">
        <!-- Pestañas -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-6 px-6" aria-label="Tabs">
                <button wire:click="$set('activeTab', 'enrollments')" 
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'enrollments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-500' }}">
                    Cursos Inscritos
                </button>
                <button wire:click="$set('activeTab', 'payments')" 
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'payments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-500' }}">
                    Historial de Pagos
                </button>
                <button wire:click="$set('activeTab', 'communication')"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'communication' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:border-gray-500' }}">
                    Comunicación (Próximamente)
                </button>
            </nav>
        </div>

        <!-- Contenido de Pestaña: Cursos Inscritos -->
        <div class="p-6" x-show="activeTab === 'enrollments'" x-cloak>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Matrículas</h3>
                <div>
                    <label for="enrollmentStatusFilter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Filtrar por estado:</label>
                    <select id="enrollmentStatusFilter" wire:model.live="enrollmentStatusFilter" class="ml-2 border-gray-300 rounded-md shadow-sm text-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                        <option value="all">Todos</option>
                        <option value="active">Activo</option>
                        <option value="completed">Completado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Cursos Inscritos -->
            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Módulo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Curso</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Profesor</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Calificación</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse ($enrollments as $enrollment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" wire:key="enrollment-{{ $enrollment->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            'bg-green-100 text-green-800' => $enrollment->status === 'active',
                                            'bg-blue-100 text-blue-800' => $enrollment->status === 'completed',
                                            'bg-yellow-100 text-yellow-800' => $enrollment->status === 'pending',
                                            'bg-red-100 text-red-800' => $enrollment->status === 'cancelled',
                                            'bg-gray-100 text-gray-800' => !in_array($enrollment->status, ['active', 'completed', 'pending', 'cancelled'])
                                        ])>
                                        {{ ucfirst($enrollment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 text-center">{{ $enrollment->final_grade ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                    @if($enrollment->status !== 'completed' && $enrollment->status !== 'cancelled')
                                        <button wire:click="confirmUnenroll({{ $enrollment->id }})" class="text-red-600 hover:text-red-900 transition ease-in-out duration-150" title="Anular Inscripción">
                                            <i class="fas fa-times-circle"></i> Anular
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No hay cursos inscritos que coincidan con el filtro.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $enrollments->links() }}
            </div>
        </div>

        {{-- Historial de Pagos --}}
        <div class="p-6" x-show="activeTab === 'payments'" x-cloak>
             <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Historial de Pagos</h3>
                @can('create payments')
                    {{-- --- ¡BOTÓN CORREGIDO! --- --}}
                    <button wire:click="$dispatch('open-modal', 'payment-modal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-plus-circle mr-2"></i>Registrar Pago
                    </button>
                @endcan
            </div>

            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Concepto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Curso/Módulo</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Método</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" wire:key="payment-{{ $payment->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $payment->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $payment->paymentConcept->name ?? $payment->description ?? 'N/A' }}
                                </span>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ $payment->enrollment->courseSchedule->module->course->name ?? '' }}
                                    <span class="text-xs text-gray-400">({{ $payment->enrollment->courseSchedule->module->name ?? 'N/A' }})</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 text-right">${{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $payment->payment_method ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            'bg-green-100 text-green-800' => $payment->status === 'Completado',
                                            'bg-yellow-100 text-yellow-800' => $payment->status === 'Pendiente',
                                            'bg-red-100 text-red-800' => $payment->status === 'Fallido' || $payment->status === 'Rechazado',
                                            'bg-gray-100 text-gray-800' => !in_array($payment->status, ['Completado', 'Pendiente', 'Fallido', 'Rechazado'])
                                        ])>
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $payment->user->name ?? 'Sistema' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    No hay pagos registrados para este estudiante.
                                </td>
                            </tr>
                        @endforelse {{-- <-- ¡CORRECCIÓN! Era @endForesle --}}
                    </tbody>
                </table>
            </div>
             <div class="mt-4">
                 {{ $payments->links() }}
            </div>
        </div>
        
        {{-- Próximamente --}}
        <div class="p-6" x-show="activeTab === 'communication'" x-cloak>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white">
                    Historial de Comunicación
                </h3>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                Esta función estará disponible próximamente.
            </div>
        </div>
    </div>


    {{-- Modal para Inscribir Estudiante (Añadir nuevo curso/sección) --}}
    <x-modal name="enroll-student-modal" maxWidth="3xl">
        <form wire:submit.prevent="enrollStudent">
            <div class="p-6 bg-white dark:bg-gray-800">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Inscribir Estudiante a Nueva Sección
                </h2>
                
                @if (session()->has('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg mb-4" role="alert">
                        <strong class="font-bold">¡Error!</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif
                
                <div class="mb-4">
                    <x-input-label for="searchCourse" value="Buscar Curso o Módulo" />
                    <x-text-input wire:model.live.debounce.300ms="searchAvailableCourse" id="searchCourse" class="block mt-1 w-full" type="text" placeholder="Escriba para buscar..." />
                </div>

                <div class="max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="w-1/12 px-6 py-3"></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Curso / Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Sección</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Profesor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($availableSchedules as $schedule)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" wire:key="schedule-{{ $schedule->id }}">
                                <td class="px-6 py-4">
                                    <input type="radio" wire:model.live="selectedScheduleId" value="{{ $schedule->id }}" id="schedule-{{ $schedule->id }}" class="text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <label for="schedule-{{ $schedule->id }}" class="cursor-pointer">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100 block">{{ $schedule->module->course->name ?? 'N/A' }}</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400 block">{{ $schedule->module->name ?? 'N/A' }}</span>
                                    </label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $schedule->section_name ?? $schedule->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">{{ $schedule->teacher->name ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    @if(strlen($searchAvailableCourse) < 3)
                                        Escriba al menos 3 caracteres para buscar...
                                    @else
                                        No se encontraron secciones (el estudiante puede ya estar inscrito).
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($selectedScheduleInfo)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                        <h4 class="font-semibold text-gray-800 dark:text-gray-100">Detalles de la Sección Seleccionada</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>Curso:</strong> {{ $selectedScheduleInfo->module->course->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>Módulo:</strong> {{ $selectedScheduleInfo->module->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>Sección:</strong> {{ $selectedScheduleInfo->section_name ?? $selectedScheduleInfo->id }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>Profesor:</strong> {{ $selectedScheduleInfo->teacher->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>Horario:</strong> {{ implode(', ', $selectedScheduleInfo->days_of_week ?? []) }} de {{ \Carbon\Carbon::parse($selectedScheduleInfo->start_time)->format('h:i A') }} a {{ \Carbon\Carbon::parse($selectedScheduleInfo->end_time)->format('h:i A') }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300"><strong>Fechas:</strong> {{ \Carbon\Carbon::parse($selectedScheduleInfo->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($selectedScheduleInfo->end_date)->format('d/m/Y') }}</p>
                    </div>
                @endif
            </div>

            <div class="flex justify-end mt-6 p-6 bg-gray-100 dark:bg-gray-800 rounded-b-lg">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-primary-button class="ms-3" type="submit" wire:loading.attr="disabled" :disabled="!$selectedScheduleId">
                    Inscribir Estudiante
                </x-primary-button>
            </div>
        </form>
    </x-modal>

    <!-- Modal de Confirmación de Anulación -->
    <x-modal name="confirm-unenroll-modal" focusable>
        <div class="p-6 bg-white dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                ¿Eliminar Inscripción?
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                ¿Estás seguro de que deseas eliminar esta inscripción? Esta acción es permanente y no se puede deshacer.
            </p> {{-- <-- ¡CORRECCIÓN! Era </Pregunta> --}}
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>
                <x-danger-button class="ms-3" wire:click="unenroll">
                    Sí, Eliminar
                </x-danger-button>
            </div>
        </div>
    </x-modal>
    
    {{-- --- ¡AÑADIDO! --- --}}
    {{-- Incluir el modal de pago en la página --}}
    @livewire('finance.payment-modal', ['student' => $student], key('payment-modal-'.$student->id))
    
    {{-- Alinear esto con el resto de la página --}}
    @can('view courses')
    <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
        {{-- Esto estaba causando que se renderizara la página de cursos aquí, lo comento --}}
        {{-- <livewire:courses.index /> --}}
    </div>
    @endcan
</div>