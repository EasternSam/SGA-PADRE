<div class="container mx-auto p-4 md:p-6 lg:p-8" x-data="{ activeTab: 'enrollments' }">

    <x-action-message on="message" />

    {{-- Encabezado del Perfil --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-100">
        <div class="md:flex">
            <!-- Avatar e Info Básica -->
            <div class="md:w-1/3 p-6 bg-gradient-to-b from-gray-50 to-white border-b md:border-b-0 md:border-r border-gray-200 text-center">
                <div class="relative inline-block mb-4">
                    <img class="h-32 w-32 rounded-full mx-auto shadow-md object-cover border-4 border-white"
                         src="https://ui-avatars.com/api/?name={{ urlencode($student->fullName) }}&background=4f46e5&color=ffffff&size=128"
                         alt="Avatar">
                    <div class="absolute bottom-1 right-1 h-5 w-5 bg-green-500 border-2 border-white rounded-full" title="Activo"></div>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 leading-tight">{{ $student->fullName }}</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">{{ $student->student_code ?? 'Sin Matrícula' }}</p>

                {{-- MOSTRAR CARRERA ACTIVA --}}
                @if($activeCareer)
                    <div class="mt-4 px-4 py-2 bg-indigo-50 rounded-lg border border-indigo-100 inline-block">
                        <p class="text-xs text-indigo-500 uppercase font-bold tracking-wide">Carrera Universitaria</p>
                        <p class="text-indigo-900 font-bold text-sm mt-0.5">{{ $activeCareer->name }}</p>
                    </div>
                @endif
            </div>

            <!-- Detalles y Acciones -->
            <div class="md:w-2/3 p-6 lg:p-8">
                <div class="flex flex-col h-full justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            Información de Contacto
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-8 text-sm">
                            <div class="flex justify-between sm:block">
                                <span class="text-gray-500 block">Correo Institucional</span>
                                <span class="text-gray-900 font-medium">{{ $student->email }}</span>
                            </div>
                            <div class="flex justify-between sm:block">
                                <span class="text-gray-500 block">Teléfono</span>
                                <span class="text-gray-900 font-medium">{{ $student->mobile_phone ?? $student->phone ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between sm:block">
                                <span class="text-gray-500 block">Cédula / ID</span>
                                <span class="text-gray-900 font-medium">{{ $student->cedula ?? $student->identification_id ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between sm:block">
                                <span class="text-gray-500 block">Estado</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $student->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex flex-wrap gap-3">
                        <button wire:click="openEnrollmentModal" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold py-2.5 px-4 rounded-lg shadow-sm transition flex justify-center items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                            Nueva Inscripción
                        </button>
                        <button wire:click="$dispatch('openPaymentModal')" class="flex-1 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-bold py-2.5 px-4 rounded-lg shadow-sm transition flex justify-center items-center">
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                            Realizar Pago
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pestañas y contenido --}}
    <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
        <div class="border-b border-gray-200 bg-gray-50/50">
            <nav class="-mb-px flex space-x-8 px-6 overflow-x-auto" aria-label="Tabs">
                <button @click="activeTab = 'enrollments'"
                        :class="{ 'border-indigo-500 text-indigo-600 font-bold': activeTab === 'enrollments', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'enrollments' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Mis Clases
                </button>
                <button @click="activeTab = 'history'"
                        :class="{ 'border-indigo-500 text-indigo-600 font-bold': activeTab === 'history', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'history' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Historial Académico
                </button>
                <button @click="activeTab = 'payments'"
                        :class="{ 'border-indigo-500 text-indigo-600 font-bold': activeTab === 'payments', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'payments' }"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Pagos
                </button>
            </nav>
        </div>

        <div class="p-6 min-h-[400px]">
            
            {{-- TAB: INSCRIPCIONES (CLASES ACTUALES) --}}
            <div x-show="activeTab === 'enrollments'" x-cloak class="space-y-8">
                
                {{-- Sección 1: Pendientes de Pago (Prioridad) --}}
                @if($pendingEnrollments->count() > 0)
                    <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-4">
                        <h3 class="text-sm font-bold text-yellow-800 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            Inscripciones por Pagar
                        </h3>
                        <div class="overflow-x-auto bg-white rounded-md border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Materia / Curso</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($pendingEnrollments as $enrollment)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ $enrollment->courseSchedule->module->name ?? 'N/A' }}
                                                <span class="text-gray-500 text-xs block">{{ $enrollment->courseSchedule->module->course->name ?? '' }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right font-bold">
                                                ${{ number_format($enrollment->payment->amount ?? 0, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button wire:click="$dispatch('payEnrollment', { enrollmentId: {{ $enrollment->id }} })" class="text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded transition">
                                                    Pagar Ahora
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Sección 2: Materias de la Carrera (UNIVERSITARIAS) --}}
                @if($activeDegreeEnrollments->count() > 0)
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-l-4 border-indigo-500 pl-3">
                            Materias en Curso ({{ $activeCareer->name ?? 'Universidad' }})
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($activeDegreeEnrollments as $enrollment)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-2 py-0.5 rounded">
                                            {{ $enrollment->courseSchedule->section_name ?? 'Sec. '.$enrollment->courseSchedule->id }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ $enrollment->courseSchedule->module->code ?? '' }}</span>
                                    </div>
                                    <h4 class="text-md font-bold text-gray-900 mb-1">{{ $enrollment->courseSchedule->module->name }}</h4>
                                    <p class="text-sm text-gray-600 mb-3">{{ $enrollment->courseSchedule->teacher->name ?? 'Por asignar' }}</p>
                                    
                                    <div class="border-t border-gray-100 pt-3 mt-2">
                                        <div class="flex justify-between text-xs">
                                            <span class="text-gray-500">Horario:</span>
                                            <span class="font-medium text-gray-900">{{ implode(', ', $enrollment->courseSchedule->days_of_week ?? []) }}</span>
                                        </div>
                                        <div class="mt-2 text-right">
                                            <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-sm text-indigo-600 font-bold hover:text-indigo-800 hover:underline">
                                                Ver Aula Virtual &rarr;
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Sección 3: Cursos Técnicos / Libres --}}
                @if($activeCourseEnrollments->count() > 0)
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center border-l-4 border-green-500 pl-3">
                            Cursos Técnicos y Educación Continuada
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($activeCourseEnrollments as $enrollment)
                                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-0.5 rounded">Curso</span>
                                    </div>
                                    <h4 class="text-md font-bold text-gray-900 mb-1">{{ $enrollment->courseSchedule->module->name }}</h4>
                                    <p class="text-xs text-gray-500 mb-2">{{ $enrollment->courseSchedule->module->course->name }}</p>
                                    <p class="text-sm text-gray-600 mb-3">{{ $enrollment->courseSchedule->teacher->name ?? 'Por asignar' }}</p>
                                    
                                    <div class="border-t border-gray-100 pt-3 mt-2 text-right">
                                        <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-sm text-green-600 font-bold hover:text-green-800 hover:underline">
                                            Entrar al Curso &rarr;
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($activeDegreeEnrollments->count() == 0 && $activeCourseEnrollments->count() == 0 && $pendingEnrollments->count() == 0)
                    <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tienes clases activas</h3>
                        <p class="mt-1 text-sm text-gray-500">Inscríbete en un curso o materia para comenzar.</p>
                        <div class="mt-6">
                            <button wire:click="openEnrollmentModal" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                Inscribirse
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- TAB: HISTORIAL --}}
            <div x-show="activeTab === 'history'" x-cloak>
                 <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Materia / Curso</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Profesor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Calificación</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($completedEnrollments as $enrollment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $enrollment->courseSchedule->module->course->name ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $enrollment->courseSchedule->teacher->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $enrollment->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-center font-bold">
                                        {{ $enrollment->final_grade ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">Historial vacío.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAB: PAGOS (Simplificado) --}}
            <div x-show="activeTab === 'payments'" x-cloak>
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
                                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                        {{ $payment->paymentConcept->name ?? $payment->description ?? 'Pago' }}
                                        <span class="text-xs text-gray-500 block">Ref: {{ $payment->id }}</span>
                                    </td>
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

        </div>
    </div>
    
    {{-- Modales --}}
    @livewire('finance.payment-modal', ['student' => $student], key('payment-modal-'.$student->id))
    
    <x-modal name="enroll-student-modal" maxWidth="3xl">
        <form wire:submit.prevent="enrollStudent" class="p-6">
             <h2 class="text-lg font-medium text-gray-900 mb-4">Nueva Inscripción</h2>
             <div class="mb-4">
                <x-input-label value="Buscar Materia o Curso" />
                <x-text-input wire:model.live.debounce.300ms="searchAvailableCourse" class="w-full" placeholder="Escribe el nombre de la materia..." />
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                        <x-text-input id="mobile_phone" type="text" class="mt-1 block w-full" wire:model="mobile_phone" />
                        @error('mobile_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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