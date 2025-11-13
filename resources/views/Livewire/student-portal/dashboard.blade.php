<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-sga-text">
            Mi Expediente
        </h2>
    </x-slot>

    <!-- Contenido de la Página -->
    <div class="space-y-6">

        <!-- 1. Tarjeta de Bienvenida y Perfil -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-4 sm:p-6 md:flex">
                <!-- Avatar -->
                <div class="md:w-1/4 md:flex-shrink-0 md:text-center">
                    <img class="h-24 w-24 rounded-full mx-auto md:mx-0 md:mr-6 shadow-md"
                         src="https://placehold.co/100x100/e2e8f0/64748b?text={{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}"
                         alt="Avatar de {{ $student->fullName }}">
                </div>
                <!-- Información -->
                <div class="mt-4 md:mt-0 md:w-3/4">
                    <h2 class="text-2xl font-bold text-sga-text">¡Bienvenido, {{ $student->first_name }}!</h2>
                    <p class="mt-1 text-sga-text-light">
                        Aquí puedes ver un resumen de tu progreso académico y tu estado financiero.
                    </p>
                    
                    <div class="mt-4 grid grid-cols-1 gap-4 border-t border-sga-gray pt-4 text-sm sm:grid-cols-3">
                        <div>
                            <strong class="block text-sga-text-light">Nombre Completo:</strong>
                            <span class="text-sga-text">{{ $student->fullName }}</span>
                        </div>
                        <div>
                            <strong class="block text-sga-text-light">Correo Electrónico:</strong>
                            <span class="text-sga-text">{{ $student->email }}</span>
                        </div>
                        <div>
                            <strong class="block text-sga-text-light">Teléfono Móvil:</strong>
                            <span class="text-sga-text">{{ $student->mobile_phone ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Grid Principal (Cursos y Finanzas) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Columna Principal (Cursos Activos) -->
            <div class="space-y-6 lg:col-span-2">
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
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-sga-gray bg-sga-card">
                                        @forelse ($activeEnrollments as $enrollment)
                                            <tr wire:key="active-{{ $enrollment->id }}">
                                                <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                    {{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                                    <div>{{ implode(', ', $enrollment->courseSchedule->days_of_week ?? []) }}</div>
                                                    <div>{{ \Carbon\Carbon::parse($enrollment->courseSchedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($enrollment->courseSchedule->end_time)->format('h:i A') }}</div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
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
                            @forelse ($payments as $payment)
                                <li wire:key="payment-{{ $payment->id }}" class="flex justify-between gap-x-6 py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-sga-text">{{ $payment->paymentConcept->name ?? $payment->description ?? 'N/A' }}</p>
                                        <p class="text-xs text-sga-text-light">{{ $payment->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="text-sm font-medium text-sga-text">${{ number_format($payment->amount, 2) }}</span>
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