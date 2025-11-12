{{-- Eliminamos el @php de Carbon, ya que se usará en el controlador o en helpers de Blade --}}
{{-- --- ¡MODIFICACIÓN! Se quita el listener 'open-new-tab' --- --}}
<div class="container mx-auto p-4 md:p-6 lg:p-8"
     x-data="{ activeTab: 'enrollments' }">

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
       <strong class="font-bold" x-text="type === 'success' ? '¡Éxito!' : '¡Error!'"></strong>
       <span class="block sm:inline" x-text="message"></span>
    </div>


    {{-- Encabezado del Perfil --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="md:flex">
            <!-- Avatar e Info Básica -->
            <div class="md:w-1/3 p-6 bg-gray-50 border-b md:border-b-0 md:border-r border-gray-200 text-center">
                <img class="h-32 w-32 rounded-full mx-auto shadow-md mb-4"
                     src="https://ui-avatars.com/api/?name={{ urlencode($student->fullName) }}&background=4f46e5&color=ffffff&size=128"
                     alt="Avatar de {{ $student->fullName }}">

                <h1 class="text-2xl font-bold text-gray-900">{{ $student->fullName }}</h1>
                <p class="text-sm text-gray-600">{{ $student->email }}</p>
                <p class="text-sm text-gray-600">Cédula: {{ $student->cedula ?? 'N/A' }}</p>
                <span @class([
                        'mt-3 inline-flex items-center px-3 py-0.5 rounded-full text-xs font-medium',
                        'bg-green-100 text-green-800' => $student->status === 'Activo',
                        'bg-red-100 text-red-800' => $student->status !== 'Activo',
                    ])>
                    {{ $student->status }}
                </span>
            </div>

            <!-- Información Detallada y Acciones -->
            <div class="md:w-2/3 p-6">
                <div class="flex justify-between items-start mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Detalles del Estudiante</h2>
                    <div>
                        {{-- --- ¡¡¡BOTÓN MODIFICADO!!! --- --}}
                        {{-- Llama al método 'editStudent' del componente PHP --}}
                        <button 
                            type="button" 
                            wire:click="editStudent"
                            class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition ease-in-out duration-150">
                            <i class="fas fa-user-edit mr-2"></i>Editar Estudiante
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-500 block">Teléfono Móvil:</strong>
                        <span class="text-gray-900">{{ $student->mobile_phone ?? $student->phone ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Fecha de Nacimiento:</strong>
                        <span class="text-gray-900">{{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'N/A' }} ({{ $student->age }} años)</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Dirección:</strong>
                        <span class="text-gray-900">{{ $student->address ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Género:</strong>
                        <span class="text-gray-900">{{ $student->gender ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Ciudad:</strong>
                        <span class="text-gray-900">{{ $student->city ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Sector:</strong>
                        <span class="text-gray-900">{{ $student->sector ?? 'N/A' }}</span>
                    </div>
                </div>

                <hr class="my-6 border-gray-200">

                <div class="flex flex-wrap gap-2">
                    <button wire:click="openEnrollmentModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-plus-circle mr-2"></i>Inscribir a Curso
                    </button>
                    
                    {{-- Este es el botón que dispara el evento para abrir el PDF en el modal --}}
                    <button wire:click="generateReport" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-file-pdf mr-2"></i>Generar Reporte
                    </button>

                    {{-- Llama al modal 'payment-modal' que está en su propio componente --}}
                    <button wire:click="$dispatch('open-modal', 'payment-modal')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-dollar-sign mr-2"></i>Registrar Pago
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
                {{-- Se usa '@click' de Alpine para manejar las pestañas --}}
                <button @click="activeTab = 'enrollments'"
                        :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'enrollments', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'enrollments' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Cursos Inscritos
                </button>
                <button @click="activeTab = 'payments'"
                        :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'payments', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'payments' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Historial de Pagos
                </button>
                <button @click="activeTab = 'communication'"
                        :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'communication', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'communication' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Comunicación (Próximamente)
                </button>
            </nav>
        </div>

        <!-- Contenido de Pestaña: Cursos Inscritos -->
        <div class="p-6" x-show="activeTab === 'enrollments'" x-cloak>
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
                        @forelse ($enrollments as $enrollment)
                            <tr class="hover:bg-gray-50" wire:key="enrollment-{{ $enrollment->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}</td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center">{{ $enrollment->final_grade ?? 'N/A' }}</td>
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
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
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
                <h3 class="text-lg font-semibold text-gray-800">Historial de Pagos</h3>
                @can('create payments')
                    <button wire:click="$dispatch('open-modal', 'payment-modal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-plus-circle mr-2"></i>Registrar Pago
                    </button>
                @endcan
            </div>

            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso/Módulo</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado por</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-gray-50" wire:key="payment-{{ $payment->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $payment->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $payment->paymentConcept->name ?? $payment->description ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payment->enrollment->courseSchedule->module->course->name ?? '' }}
                                    <span class="text-xs text-gray-400">({{ $payment->enrollment->courseSchedule->module->name ?? 'N/A' }})</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_method ?? 'N/A' }}</td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->user->name ?? 'Sistema' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    No hay pagos registrados para este estudiante.
                                </td>
                            </tr>
                        @endforelse
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
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    Historial de Comunicación
                </h3>
            </div>
            <div class="bg-white shadow-sm rounded-lg p-6 text-center text-gray-500">
                Esta función estará disponible próximamente.
            </div>
        </div>
    </div>


    {{-- Modal para Inscribir Estudiante (Añadir nuevo curso/sección) --}}
    <x-modal name="enroll-student-modal" maxWidth="3xl">
        <form wire:submit.prevent="enrollStudent">
            <div class="p-6 bg-white">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
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

                <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-1/12 px-6 py-3"></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso / Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sección</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesor</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($availableSchedules as $schedule)
                            <tr class="hover:bg-gray-50" wire:key="schedule-{{ $schedule->id }}">
                                <td class="px-6 py-4">
                                    <input type="radio" wire:model.live="selectedScheduleId" value="{{ $schedule->id }}" id="schedule-{{ $schedule->id }}" class="text-indigo-600 focus:ring-indigo-500">
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
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
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
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="font-semibold text-gray-800">Detalles de la Sección Seleccionada</h4>
                        <p class="text-sm text-gray-600"><strong>Curso:</strong> {{ $selectedScheduleInfo->module->course->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600"><strong>Módulo:</strong> {{ $selectedScheduleInfo->module->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600"><strong>Sección:</strong> {{ $selectedScheduleInfo->section_name ?? $selectedScheduleInfo->id }}</p>
                        <p class="text-sm text-gray-600"><strong>Profesor:</strong> {{ $selectedScheduleInfo->teacher->name ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-600"><strong>Horario:</strong> {{ implode(', ', $selectedScheduleInfo->days_of_week ?? []) }} de {{ \Carbon\Carbon::parse($selectedScheduleInfo->start_time)->format('h:i A') }} a {{ \Carbon\Carbon::parse($selectedScheduleInfo->end_time)->format('h:i A') }}</p>
                        <p class="text-sm text-gray-600"><strong>Fechas:</strong> {{ \Carbon\Carbon::parse($selectedScheduleInfo->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($selectedScheduleInfo->end_date)->format('d/m/Y') }}</p>
                    </div>
                @endif
            </div>

            <div class="flex justify-end mt-6 p-6 bg-gray-100 rounded-b-lg">
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
        <div class="p-6 bg-white">
            <h2 class="text-lg font-medium text-gray-900">
                ¿Eliminar Inscripción?
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                ¿Estás seguro de que deseas eliminar esta inscripción? Esta acción es permanente y no se puede deshacer.
            </p>
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

    {{-- Incluir el modal de pago en la página --}}
    @livewire('finance.payment-modal', ['student' => $student], key('payment-modal-'.$student->id))

    {{-- Alinear esto con el resto de la página --}}
    @can('view courses')
    <div class="mt-6 bg-white overflow-hidden shadow-xl sm:rounded-lg">
        {{-- Esto estaba causando que se renderizara la página de cursos aquí, lo comento --}}
        {{-- <livewire:courses.index /> --}}
    </div>
    @endcan

    
    {{-- --- ¡¡¡INICIO DEL CÓDIGO AÑADIDO PARA MODAL DE ESTUDIANTE!!! --- --}}
    {{-- Este es el HTML del modal de edición, copiado de Students/index.blade.php --}}
    <x-modal name="student-form-modal" maxWidth="4xl" focusable>
        <form wire:submit.prevent="saveStudent">
            <div class="p-6 bg-white">
                <h2 class="text-lg font-medium text-gray-900 mb-6">
                    {{ $modalTitle }}
                </h2>

                {{-- Contenedor Flex para dividir el formulario en dos columnas --}}
                <div class="flex flex-col md:flex-row gap-6">

                    {{-- Columna 1: Información Personal --}}
                    <div class="flex-1 space-y-4">
                        <h3 class="text-md font-semibold text-gray-700 border-b pb-2">Información Personal</h3>
                        
                        {{-- Fila para Nombre y Apellido --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="first_name" value="Nombre" />
                                <x-text-input wire:model="first_name" id="first_name" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="last_name" value="Apellido" />
                                <x-text-input wire:model="last_name" id="last_name" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Fila para Cédula y Email --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="cedula" value="Cédula/DNI" />
                                <x-text-input wire:model="cedula" id="cedula" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('cedula')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="email" value="Correo Electrónico" />
                                <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Fila para Teléfonos --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                                <x-text-input wire:model="mobile_phone" id="mobile_phone" class="block mt-1 w-full" type="tel" />
                                <x-input-error :messages="$errors->get('mobile_phone')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="home_phone" value="Teléfono Casa (Opcional)" />
                                <x-text-input wire:model="home_phone" id="home_phone" class="block mt-1 w-full" type="tel" />
                                <x-input-error :messages="$errors->get('home_phone')" class="mt-2" />
                            </div>
                        </div>
                        
                        {{-- Fila para Dirección --}}
                        <div>
                            <x-input-label for="address" value="Dirección (Opcional)" />
                            <x-text-input wire:model="address" id="address" class="block mt-1 w-full" type="text" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        {{-- Fila para Ciudad y Sector --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="city" value="Ciudad (Opcional)" />
                                <x-text-input wire:model="city" id="city" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('city')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="sector" value="Sector (Opcional)" />
                                <x-text-input wire:model="sector" id="sector" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('sector')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Fila para Fecha Nacimiento y Género --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                                <x-text-input wire:model="birth_date" id="birth_date" class="block mt-1 w-full" type="date" />
                                <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="gender" value="Género (Opcional)" />
                                <select wire:model="gender" id="gender" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                            </div>
                        </div>

                        {{-- Fila para Nacionalidad y Cómo se enteró --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="nationality" value="Nacionalidad (Opcional)" />
                                <x-text-input wire:model="nationality" id="nationality" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="how_found" value="¿Cómo nos encontró? (Opcional)" />
                                <x-text-input wire:model="how_found" id="how_found" class="block mt-1 w-full" type="text" />
                                <x-input-error :messages="$errors->get('how_found')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Columna 2: Información del Tutor --}}
                    <div class="flex-1 space-y-4">
                        <h3 class="text-md font-semibold text-gray-700 border-b pb-2">Información del Tutor</h3>
                        
                        {{-- Checkbox para ¿Es menor? --}}
                        <div class="block">
                            <label for="is_minor" class="inline-flex items-center">
                                <input wire:model.live="is_minor" id="is_minor" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ms-2 text-sm text-gray-600">¿El estudiante es menor de edad?</span>
                            </label>
                        </div>

                        {{-- Campos del Tutor (condicionales) --}}
                        @if ($is_minor)
                            <div class="space-y-4 p-4 bg-gray-50 rounded-lg border">
                                <div>
                                    <x-input-label for="tutor_name" value="Nombre Completo del Tutor" />
                                    <x-text-input wire:model="tutor_name" id="tutor_name" class="block mt-1 w-full" type="text" />
                                    <x-input-error :messages="$errors->get('tutor_name')" class="mt-2" />
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="tutor_cedula" value="Cédula del Tutor (Opcional)" />
                                        <x-text-input wire:model="tutor_cedula" id="tutor_cedula" class="block mt-1 w-full" type="text" />
                                        <x-input-error :messages="$errors->get('tutor_cedula')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="tutor_phone" value="Teléfono del Tutor" />
                                        <x-text-input wire:model="tutor_phone" id="tutor_phone" class="block mt-1 w-full" type="tel" />
                                        <x-input-error :messages="$errors->get('tutor_phone')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="tutor_relationship" value="Parentesco (Opcional)" />
                                    <x-text-input wire:model="tutor_relationship" id="tutor_relationship" class="block mt-1 w-full" type="text" />
                                    <x-input-error :messages="$errors->get('tutor_relationship')" class="mt-2" />
                                </div>
                            </div>
                        @else
                            <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-lg border">
                                El estudiante es mayor de edad. No se requiere información del tutor.
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- Pie del Modal --}}
            <div class="flex justify-end mt-6 p-6 bg-gray-100 rounded-b-lg">
                {{-- ¡¡¡MODIFICADO!!! Llama a closeStudentModal --}}
                <x-secondary-button x-on:click="$dispatch('close')" wire:click="closeStudentModal">
                    Cancelar
                </x-secondary-button>

                <x-primary-button class="ms-3" type="submit" wire:loading.attr="disabled">
                    Guardar Cambios
                </x-primary-button>
            </div>
        </form>
    </x-modal>
    {{-- --- ¡¡¡FIN DEL CÓDIGO AÑADIDO PARA MODAL DE ESTUDIANTE!!! --- --}}
    
    
    {{-- --- ¡¡¡INICIO DEL CÓDIGO AÑADIDO PARA MODAL DE PDF!!! --- --}}
    <div
        x-data="{ show: false, pdfUrl: '' }"
        @open-pdf-modal.window="
            console.log('Evento open-pdf-modal recibido.');
            pdfUrl = $event.detail.url;
            console.log('URL recibida:', pdfUrl);
            show = true;
        "
        x-show="show"
        x-on:keydown.escape.window="show = false; pdfUrl = ''"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
        style="display: none;"
    >
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <!-- Fondo oscuro -->
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="show = false; pdfUrl = ''" aria-hidden="true"></div>

            <!-- Contenedor del Modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                x-show="show"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block w-full max-w-6xl p-4 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg"
            >
                <!-- Encabezado del Modal -->
                <div class="flex justify-between items-center pb-3 border-b">
                    <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                        Visor de Reporte
                    </h3>
                    <button @click="show = false; pdfUrl = ''" class="text-gray-400 hover:text-gray-600">
                        <span class="sr-only">Cerrar</span>
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Contenido del Modal (iframe) -->
                <div class="mt-4" style="width: 100%; height: 75vh;">
                    <iframe :src="pdfUrl" frameborder="0" width="100%" height="100%">
                        Tu navegador no soporta iframes. Por favor, descarga el reporte.
                    </iframe>
                </div>

                <!-- Pie del Modal -->
                <div class="flex justify-end pt-4 mt-4 border-t">
                    <a :href="pdfUrl" download class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                        Descargar
                    </a>
                    <button @click="show = false; pdfUrl = ''" type="button" class="ml-3 inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- --- ¡¡¡FIN DEL CÓDIGO AÑADIDO PARA MODAL DE PDF!!! --- --}}

</div>