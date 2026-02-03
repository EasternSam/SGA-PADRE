<div class="container mx-auto p-4 md:p-6 lg:p-8" x-data="{ activeTab: 'enrollments' }">

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
                        <button 
                            type="button" 
                            wire:click="openProfileModal"
                            class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm transition ease-in-out duration-150">
                            <i class="fas fa-user-edit mr-2"></i>Editar Perfil
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-500 block">Matrícula (Student Code):</strong>
                        <span class="text-gray-900 font-semibold">{{ $student->student_code ?? 'Pendiente' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Carrera / Programa:</strong>
                        @if($activeCareer)
                             <span class="text-indigo-700 font-bold">{{ $activeCareer->name }}</span>
                        @else
                             <span class="text-gray-500 italic">Cursos Técnicos</span>
                        @endif
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Teléfono Móvil:</strong>
                        <span class="text-gray-900">{{ $student->mobile_phone ?? $student->phone ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Fecha de Nacimiento:</strong>
                        <span class="text-gray-900">{{ $student->birth_date ? $student->birth_date->format('d/m/Y') : 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Dirección:</strong>
                        <span class="text-gray-900">{{ $student->address ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Ciudad/Sector:</strong>
                        <span class="text-gray-900">{{ $student->city ?? '' }} {{ $student->sector ? '- '.$student->sector : '' }}</span>
                    </div>
                </div>

                <hr class="my-6 border-gray-200">

                <div class="flex flex-wrap gap-2">
                    <button wire:click="openEnrollmentModal" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        <i class="fas fa-plus-circle mr-2"></i>Inscribir a Curso
                    </button>
                    
                    <button wire:click="$dispatch('openPaymentModal')" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
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
                    Comunicación
                </button>
            </nav>
        </div>

        <div class="p-6 space-y-8" x-show="activeTab === 'enrollments'" x-cloak>
            
            {{-- 1. Inscripciones Pendientes de Pago (SI EXISTEN) --}}
            @if($pendingEnrollments->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-yellow-700 mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Inscripciones Pendientes de Pago
                </h3>
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Materia / Curso</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($pendingEnrollments as $enrollment)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-600">{{ $enrollment->courseSchedule->module->course->name ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($enrollment->payment->amount ?? 0, 2) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pendiente Pago
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button wire:click="$dispatch('payEnrollment', { enrollmentId: {{ $enrollment->id }} })" class="text-yellow-600 hover:text-yellow-900 font-bold">
                                            Pagar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- 2. Materias de Carrera (SI EXISTEN) --}}
            @if($activeDegreeEnrollments->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-indigo-800 mb-4 flex items-center">
                    <i class="fas fa-university mr-2"></i> Materias en Curso ({{ $activeCareer->name ?? 'Universidad' }})
                </h3>
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Materia / Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profesor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horario</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($activeDegreeEnrollments as $enrollment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $enrollment->courseSchedule->section_name ?? 'Sección '.$enrollment->courseSchedule->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ implode(', ', $enrollment->courseSchedule->days_of_week ?? []) }} <br>
                                        <span class="text-xs text-gray-500">{{ $enrollment->courseSchedule->start_time }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold text-sm">Ver Aula</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- 3. Cursos Técnicos (SI EXISTEN) --}}
            @if($activeCourseEnrollments->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-green-800 mb-4 flex items-center">
                    <i class="fas fa-book-open mr-2"></i> Cursos Técnicos y Educación Continua
                </h3>
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Curso / Módulo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profesor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Horario</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($activeCourseEnrollments as $enrollment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $enrollment->courseSchedule->module->course->name ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        {{ implode(', ', $enrollment->courseSchedule->days_of_week ?? []) }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-green-600 hover:text-green-900 font-bold text-sm">Entrar</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- 4. Mensaje Vacío --}}
            @if($activeDegreeEnrollments->isEmpty() && $activeCourseEnrollments->isEmpty() && $pendingEnrollments->isEmpty())
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-gray-500">No tienes inscripciones activas actualmente.</p>
                    <button wire:click="openEnrollmentModal" class="mt-4 text-indigo-600 hover:underline">Inscribirse ahora</button>
                </div>
            @endif

            {{-- 5. Cursos Completados (Historial) --}}
            @if($completedEnrollments->count() > 0)
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-graduation-cap mr-2"></i> Cursos Completados
                </h3>
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Módulo / Sección</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Calificación Final</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($completedEnrollments as $enrollment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-600">{{ $enrollment->courseSchedule->module->course->name ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Completado
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-900">{{ $enrollment->final_grade ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Historial de Pagos --}}
        <div class="p-6" x-show="activeTab === 'payments'" x-cloak>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Historial de Pagos</h3>
                <button wire:click="$dispatch('openPaymentModal')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow">
                    <i class="fas fa-plus-circle mr-2"></i>Registrar Pago
                </button>
            </div>
            <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Concepto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($paymentHistory as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $payment->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $payment->paymentConcept->name ?? $payment->description ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($payment->amount, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $payment->status === 'Completado' ? 'bg-green-100 text-green-800' : 
                                           ($payment->status === 'Pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">No hay pagos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Próximamente --}}
        <div class="p-6" x-show="activeTab === 'communication'" x-cloak>
            <div class="bg-white shadow-sm rounded-lg p-6 text-center text-gray-500">
                Esta función estará disponible próximamente.
            </div>
        </div>
    </div>

    {{-- MODALES --}}
    @livewire('finance.payment-modal', ['student' => $student], key('payment-modal-'.$student->id))

    <x-modal name="enroll-student-modal" maxWidth="3xl">
        <form wire:submit.prevent="enrollStudent" class="p-6">
             <h2 class="text-lg font-medium text-gray-900 mb-4">Nueva Inscripción</h2>
             <div class="mb-4">
                <x-input-label value="Buscar Curso o Materia" />
                <x-text-input wire:model.live.debounce.300ms="searchAvailableCourse" class="w-full" placeholder="Escribe el nombre..." />
             </div>
             
             @if(count($availableSchedules) > 0)
                 <ul class="border border-gray-200 rounded-md divide-y divide-gray-200 max-h-60 overflow-y-auto">
                     @foreach($availableSchedules as $schedule)
                         <li class="p-3 hover:bg-gray-50 flex items-center justify-between cursor-pointer" wire:click="$set('selectedScheduleId', {{ $schedule->id }})">
                             <div>
                                 <p class="font-bold text-sm text-gray-900">{{ $schedule->module->name }}</p>
                                 <p class="text-xs text-gray-500">{{ $schedule->module->course->name }} - {{ $schedule->teacher->name ?? 'Sin profesor' }}</p>
                                 <p class="text-xs text-gray-400">Horario: {{ implode(',', $schedule->days_of_week ?? []) }}</p>
                             </div>
                             <div class="flex items-center">
                                 <input type="radio" name="schedule" value="{{ $schedule->id }}" wire:model.live="selectedScheduleId" class="text-indigo-600">
                             </div>
                         </li>
                     @endforeach
                 </ul>
             @elseif(strlen($searchAvailableCourse) > 2)
                 <p class="text-sm text-gray-500 text-center py-4">No se encontraron resultados.</p>
             @endif

             <div class="mt-6 flex justify-end gap-3">
                 <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                 <x-primary-button disabled="{{ !$selectedScheduleId }}">Inscribir</x-primary-button>
             </div>
        </form>
    </x-modal>

    <x-modal name="complete-profile-modal" :show="$showProfileModal" focusable>
        <div class="p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-2">📝 Completa tu Perfil</h2>
            <form wire:submit.prevent="saveProfile" class="space-y-4">
                {{-- Campos del formulario (igual al original) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                        <x-text-input id="mobile_phone" type="text" class="mt-1 block w-full" wire:model="mobile_phone" />
                    </div>
                    <div>
                        <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                        <x-text-input id="birth_date" type="date" class="mt-1 block w-full" wire:model="birth_date" />
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="address" value="Dirección" />
                        <x-text-input id="address" type="text" class="mt-1 block w-full" wire:model="address" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button wire:click="closeProfileModal">Más tarde</x-secondary-button>
                    <x-primary-button>Guardar</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>