<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <div class="flex items-center gap-2">
             {{-- Botón para Volver --}}
            <a href="{{ route('teacher.dashboard') }}" wire:navigate
               class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sga-text-light transition hover:bg-sga-bg hover:text-sga-text">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>
            <h2 class="text-xl font-semibold leading-tight text-sga-text">
                Tomar Asistencia
            </h2>
        </div>
    </x-slot>

    <!-- --- ¡NUEVO DISEÑO DE PÁGINA! --- -->
    <div class="space-y-6">

        <!-- 1. Tarjeta de Cabecera (Información del Curso) -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-4 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-sga-text">
                            {{ $courseSchedule->module->course->name ?? 'N/A' }}
                        </h3>
                        <p class="mt-1 text-sm text-sga-text-light">
                            Módulo: {{ $courseSchedule->module->name ?? 'N/A' }}
                        </p>
                        <p class="mt-1 text-sm text-sga-text-light">
                            Horario: {{ $courseSchedule->day_of_week }}, {{ $courseSchedule->start_time }} - {{ $courseSchedule->end_time }}
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <label for="attendance_date" class="block text-sm font-medium text-sga-text-light">Fecha de Asistencia</label>
                        <x-text-input id="attendance_date" type="date" class="mt-1 block w-full"
                            wire:model.live="attendanceDate" />
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Tarjeta de Contenido (Lista de Estudiantes) -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            
            <!-- Contenedor de la Tabla -->
            <div class="flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-sga-gray">
                            <thead class="bg-sga-bg">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-6">Estudiante</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Estado</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Registrar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sga-gray bg-sga-card">
                                @forelse ($students as $student)
                                    <tr wire:key="student-{{ $student->id }}">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <img class="h-10 w-10 rounded-full" 
                                                         src="https://placehold.co/100x100/e2e8f0/64748b?text={{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}" 
                                                         alt="Avatar de {{ $student->first_name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-sga-text">{{ $student->fullName }}</div>
                                                    <div class="text-sga-text-light">{{ $student->student_id }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-sga-text-light">
                                            @php
                                                $status = $attendanceStatus[$student->id] ?? 'no_registrado';
                                                $statusClass = [
                                                    'presente' => 'bg-sga-success/10 text-sga-success',
                                                    'ausente' => 'bg-sga-danger/10 text-sga-danger',
                                                    'tardanza' => 'bg-sga-warning/10 text-sga-warning',
                                                    'no_registrado' => 'bg-sga-gray/10 text-sga-text-light',
                                                ][$status];
                                                $statusText = [
                                                    'presente' => 'Presente',
                                                    'ausente' => 'Ausente',
                                                    'tardanza' => 'Tardanza',
                                                    'no_registrado' => 'N/A',
                                                ][$status];
                                            @endphp
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <div class="flex items-center gap-2">
                                                <!-- Botones de Asistencia -->
                                                <button type="button" 
                                                        wire:click="updateAttendance({{ $student->id }}, 'presente')"
                                                        @class([
                                                            'rounded-md px-2.5 py-1 text-xs font-semibold shadow-sm ring-1 ring-inset',
                                                            'bg-sga-success/10 text-sga-success ring-sga-success/20' => $status == 'presente',
                                                            'bg-sga-card text-sga-text-light ring-sga-gray hover:bg-sga-bg' => $status != 'presente'
                                                        ])>
                                                    Presente
                                                </button>
                                                <button type="button" 
                                                        wire:click="updateAttendance({{ $student->id }}, 'ausente')"
                                                        @class([
                                                            'rounded-md px-2.5 py-1 text-xs font-semibold shadow-sm ring-1 ring-inset',
                                                            'bg-sga-danger/10 text-sga-danger ring-sga-danger/20' => $status == 'ausente',
                                                            'bg-sga-card text-sga-text-light ring-sga-gray hover:bg-sga-bg' => $status != 'ausente'
                                                        ])>
                                                    Ausente
                                                </button>
                                                <button type="button" 
                                                        wire:click="updateAttendance({{ $student->id }}, 'tardanza')"
                                                        @class([
                                                            'rounded-md px-2.5 py-1 text-xs font-semibold shadow-sm ring-1 ring-inset',
                                                            'bg-sga-warning/10 text-sga-warning ring-sga-warning/20' => $status == 'tardanza',
                                                            'bg-sga-card text-sga-text-light ring-sga-gray hover:bg-sga-bg' => $status != 'tardanza'
                                                        ])>
                                                    Tardanza
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
                                            No hay estudiantes inscritos en esta sección.
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
</div>