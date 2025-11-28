<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-sga-text">
            Mi Expediente
        </h2>
    </x-slot>

    <!-- ¡¡¡NUEVO!!! Bloque de Mensajes Flash -->
    <!-- Esto mostrará los errores cuando seas redirigido aquí -->
    @if (session()->has('message'))
        <div class="mb-6 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-700 shadow" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-6 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow" role="alert">
            <strong class="font-bold">¡Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    <!-- Fin de Mensajes Flash -->
    
    <!-- ============================================= -->
    <!--     NUEVO: Alerta de Pagos Pendientes         -->
    <!-- ============================================= -->
    @if($pendingPayments->count() > 0)
        <div class="mb-6 rounded-lg border border-yellow-400 bg-yellow-50 p-4 shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.485 2.495c.433-.813 1.601-.813 2.034 0l6.28 11.752c.433.813-.207 1.755-1.017 1.755H3.22c-.81 0-1.45-.942-1.017-1.755L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Tienes Pagos Pendientes</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Detectamos una o más inscripciones que están pendientes de pago. Por favor, completa el pago para activar tu(s) curso(s) y asegurar tu cupo.</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            @foreach($pendingPayments as $payment)
                                @php
                                    // Lógica para alternar entre Curso y Concepto si son N/A
                                    $rawCourse = $payment->enrollment?->courseSchedule?->module?->name;
                                    $rawConcept = $payment->paymentConcept?->name;

                                    // Normalizar "N/A" a null para que el operador ?? funcione
                                    $courseName = ($rawCourse && $rawCourse !== 'N/A') ? $rawCourse : null;
                                    $conceptName = ($rawConcept && $rawConcept !== 'N/A') ? $rawConcept : null;

                                    // Prioridad: Curso -> Concepto -> Fallback
                                    // Si Curso es válido, muestra Curso. Si es N/A, salta a Concepto.
                                    $displayText = $courseName ?? $conceptName ?? 'Concepto de Pago';
                                @endphp
                                <li>
                                    <strong>{{ $displayText }}</strong>:
                                    <span class="font-semibold">${{ number_format($payment->amount, 2) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <!-- Fin de Alerta de Pagos -->


    <!-- Contenido de la Página -->
    <div class="space-y-6">

        <!-- 1. Tarjeta de Bienvenida y Perfil -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-4 sm:p-6 md:flex">
                <!-- Avatar -->
                <div class="md:w-1/4 md:flex-shrink-0 md:text-center">
                    <img class="h-32 w-32 rounded-full mx-auto md:mx-0 md:mr-6 shadow-md"
                         src="https://placehold.co/100x100/e2e8f0/64748b?text={{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}"
                         alt="Avatar de {{ $student->fullName }}">
                </div>
                <!-- Información -->
                <div class="mt-4 md:mt-0 md:w-3/4">
                    <h2 class="text-2xl font-bold text-sga-text">¡Bienvenido, {{ $student->first_name }}!</h2>
                    <p class="mt-1 text-sga-text-light">
                        Aquí puedes ver un resumen de tu progreso académico y tu estado financiero.
                    </p>
                    
                    <!-- ============================================= -->
                    <!--     ACTUALIZADO: Tarjeta de Perfil            -->
                    <!-- ============================================= -->
                    <div class="mt-4 grid grid-cols-1 gap-4 border-t border-sga-gray pt-4 text-sm sm:grid-cols-2 md:grid-cols-4">
                        <div>
                            <strong class="text-sga-text-light block">Nombre Completo:</strong>
                            <span class_alias="text-sga-text">{{ $student->fullName }}</span>
                        </div>
                        <div>
                            <strong class="text-sga-text-light block">Matrícula:</strong>
                            <span class_alias="text-sga-text">{{ $student->student_code ?? 'Pendiente' }}</span>
                        </div>
                        <div>
                            <strong class="text-sga-text-light block">Email de Acceso:</strong>
                            {{-- Muestra el email del USER (el de @centu.edu.do) o el del student si no --}}
                            <span class="text-sga-text">{{ $student->user?->email ?? $student->email }}</span>
                        </div>
                        <div>
                            <strong class="text-sga-text-light block">Teléfono Móvil:</strong>
                            <span class="text-sga-text">{{ $student->mobile_phone ?? $student->phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <!-- Fin de Actualización -->

                </div>
            </div>
        </div>

        <!-- 2. Grid Principal (Cursos y Finanzas) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Columna Principal (Cursos Activos) -->
            <div class="space-y-6 lg:col-span-2">

                <!-- ============================================= -->
                <!--  NUEVO: Inscripciones Pendientes de Pago      -->
                <!-- ============================================= -->
                @if($pendingEnrollments->count() > 0)
                <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-yellow-600">
                            <i class="fas fa-exclamation-triangle mr-2"></i> Inscripciones Pendientes de Pago
                        </h3>
                    </div>
                    <!-- Tabla de Cursos Pendientes -->
                    <div class="flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-sga-gray">
                                    <thead class="bg-sga-bg">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-6">Curso / Módulo</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Profesor</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Horario</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-sga-card divide-y divide-sga-gray">
                                        @foreach ($pendingEnrollments as $enrollment)
                                            <tr wire:key="pending-{{ $enrollment->id }}" class="hover:bg-sga-bg">
                                                <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                    {{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                    {{-- Usar 'section_name' o 'day_of_week' como fallback --}}
                                                    <div>{{ $enrollment->courseSchedule->section_name ?? $enrollment->courseSchedule->day_of_week ?? 'N/A' }}</div>
                                                    @if($enrollment->courseSchedule->start_time && $enrollment->courseSchedule->start_time != '00:00:00')
                                                    <div>{{ \Carbon\Carbon::parse($enrollment->courseSchedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($enrollment->courseSchedule->end_time)->format('h:i A') }}</div>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
                                                        Pendiente de Pago
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!-- Fin de Inscripciones Pendientes -->

                <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-sga-text">
                            <i class="fas fa-book-open mr-2 text-sga-primary"></i> Mis Cursos Activos
                        </h3>
                    </div>
                    <!-- Tabla de Cursos Activos -->
                    <div class="flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-sga-gray">
                                    <thead class="bg-sga-bg">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-6">Curso / Módulo</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Profesor</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Horario</th>
                                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                                <span class="sr-only">Ver</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-sga-card divide-y divide-sga-gray">
                                        @forelse ($activeEnrollments as $enrollment)
                                            <tr wire:key="active-{{ $enrollment->id }}" class="hover:bg-sga-bg">
                                                <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                    {{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                    {{-- Usar 'section_name' o 'day_of_week' como fallback --}}
                                                    <div>{{ $enrollment->courseSchedule->section_name ?? $enrollment->courseSchedule->day_of_week ?? 'N/A' }}</div>
                                                    @if($enrollment->courseSchedule->start_time && $enrollment->courseSchedule->start_time != '00:00:00')
                                                    <div>{{ \Carbon\Carbon::parse($enrollment->courseSchedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($enrollment->courseSchedule->end_time)->format('h:i A') }}</div>
                                                    @endif
                                                </td>
                                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                    {{-- ¡CAMBIO! Se eliminó wire:navigate para forzar una recarga de página completa --}}
                                                    <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-sga-secondary hover:text-sga-primary font-semibold">
                                                        Ver Detalles <span aria-hidden="true">&rarr;</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
                                                    No estás inscrito en ningún curso actualmente.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna Lateral (Completados y Pagos) -->
            <div class="space-y-6 lg:col-span-1">
                
                <!-- Cursos Completados -->
                <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-sga-text">
                            <i class="fas fa-graduation-cap mr-2 text-sga-primary"></i> Cursos Completados
                        </h3>
                    </div>
                    <div class="border-t border-sga-gray p-4 sm:p-6">
                        <ul role="list" class="divide-y divide-sga-gray">
                            @forelse ($completedEnrollments as $enrollment)
                                <li wire:key="completed-{{ $enrollment->id }}" class="flex justify-between gap-x-6 py-3">
                                    <div class="flex-1">
                                        <p class="text-sm font-semibold text-sga-text">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-sga-text-light">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="text-lg font-bold text-sga-primary">{{ $enrollment->final_grade ?? 'N/A' }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="py-3 text-center text-sm text-sga-text-light">
                                    Aún no has completado ningún curso.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Historial de Pagos -->
                <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-sga-text">
                            <i class="fas fa-dollar-sign mr-2 text-sga-primary"></i> Últimos Pagos
                        </h3>
                    </div>
                    <div class="border-t border-sga-gray p-4 sm:p-6">
                         <ul role="list" class="divide-y divide-sga-gray">
                            @php
                                // ACTUALIZADO: Agregamos 'enrollment.courseSchedule.module' para poder leer el curso si el concepto es N/A
                                $allPayments = $student->payments()->with(['paymentConcept', 'enrollment.courseSchedule.module'])->orderBy('created_at', 'desc')->take(5)->get();
                            @endphp
                            @forelse ($allPayments as $payment)
                                @php
                                    // Lógica para alternar entre Concepto y Curso si son N/A
                                    $histRawConcept = $payment->paymentConcept?->name;
                                    $histRawCourse = $payment->enrollment?->courseSchedule?->module?->name;
                                    $histDesc = $payment->description;

                                    $histConcept = ($histRawConcept && $histRawConcept !== 'N/A') ? $histRawConcept : null;
                                    $histCourse = ($histRawCourse && $histRawCourse !== 'N/A') ? $histRawCourse : null;

                                    // En historial solemos priorizar Concepto, pero si es N/A, mostramos Curso.
                                    $histDisplay = $histConcept ?? $histCourse ?? $histDesc ?? 'N/A';
                                @endphp
                                <li wire:key="payment-{{ $payment->id }}" class="flex justify-between gap-x-6 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-sga-text">{{ $histDisplay }}</p>
                                        <p class="text-xs text-sga-text-light">{{ $payment->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <span class="text-sm font-medium text-sga-text">${{ number_format($payment->amount, 2) }}</span>
                                        @if($payment->status == 'Pendiente')
                                            <span class="block text-xs text-yellow-600">Pendiente</span>
                                        @elseif($payment->status == 'Completado')
                                            <span class="block text-xs text-green-600">Completado</span>
                                        @else
                                            <span class="block text-xs text-red-600">{{ $payment->status }}</span>
                                        @endif
                                    </div>
                                </li>
                            @empty
                                <li class="py-3 text-center text-sm text-sga-text-light">
                                    No se han registrado pagos.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>