<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-sga-text">
            {{ __('Mi Expediente') }}
        </h2>
    </x-slot>

    <!-- --- ¡NUEVO DISEÑO DE PÁGINA! --- -->
    <div class="space-y-6">

        <!-- 1. Tarjeta de Bienvenida -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-6">
                <div class="flex flex-col items-center gap-6 sm:flex-row">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <img class="h-20 w-20 rounded-full" 
                             src="https://placehold.co/200x200/e2e8f0/64748b?text={{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}" 
                             alt="Avatar de {{ $student->first_name }}">
                    </div>
                    <!-- Información -->
                    <div class="flex-1 text-center sm:text-left">
                        <h3 class="text-2xl font-bold text-sga-text">¡Hola, {{ $student->first_name }}!</h3>
                        <p class="text-sga-text-light">Te damos la bienvenida a tu portal de estudiante.</p>
                        <p class="mt-1 text-sm text-sga-text-light"><strong>ID de Estudiante:</strong> {{ $student->student_id }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Contenido (Cursos y Pagos) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Columna Izquierda: Mis Cursos -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow lg:col-span-2">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-sga-text">
                        Mis Cursos Inscritos
                    </h3>
                    
                    <!-- Tabla de Cursos -->
                    <div class="mt-4 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-sga-gray">
                                    <thead class="bg-sga-bg">
                                        <tr>
                                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-6">Curso / Módulo</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Profesor</th>
                                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-sga-gray bg-sga-card">
                                        @forelse ($enrollments as $enrollment)
                                            <tr wire:key="enroll-{{ $enrollment->id }}">
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">{{ $enrollment->courseSchedule->teacher->name ?? 'N/A' }}</td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    <span class="inline-flex items-center rounded-full bg-sga-success/10 px-2.5 py-0.5 text-xs font-medium text-sga-success">
                                                        {{ $enrollment->status ?? 'Activo' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
                                                    No estás inscrito en ningún curso.
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

            <!-- Columna Derecha: Pagos Pendientes -->
            <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                <div class="p-4 sm:p-6">
                    <h3 class="text-lg font-medium leading-6 text-sga-text">
                        Pagos Pendientes
                    </h3>
                    <div class="mt-4">
                        <ul role="list" class="divide-y divide-sga-gray">
                            @forelse ($pendingPayments as $payment)
                                <li wire:key="payment-{{ $payment->id }}" class="flex items-center justify-between py-3">
                                    <div class="min-w-0">
                                        <p class="font-medium text-sga-text">{{ $payment->paymentConcept->name }}</p>
                                        <p class="text-sm text-sga-text-light">{{ $payment->description ?? 'Pago de curso' }}</p>
                                    </div>
                                    <div class="flex-shrink-0 text-right">
                                        <p class="font-semibold text-sga-danger">${{ number_format($payment->amount_due, 2) }}</p>
                                        <p class="text-xs text-sga-text-light">Vence: {{ $payment->due_date->format('d/m/Y') }}</p>
                                    </div>
                                </li>
                            @empty
                                <li class="py-3 text-center text-sm text-sga-text-light">
                                    ¡Estás al día con tus pagos!
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="mt-4">
                        <x-primary-button class="w-full">
                            Realizar un Pago
                        </x-primary-button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>