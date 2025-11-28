<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <div class="flex items-center gap-2">
            {{-- Botón para Volver --}}
            <a href="{{ route('student.dashboard') }}" wire:navigate
               class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sga-text-light transition hover:bg-sga-bg hover:text-sga-text">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>
            <h2 class="text-xl font-semibold leading-tight text-sga-text">
                {{ $enrollment->courseSchedule->module->course->name ?? 'Curso' }} - {{ $enrollment->courseSchedule->module->name ?? 'Módulo' }}
            </h2>
        </div>
    </x-slot>

    <!-- Mensajes Flash (Toast) -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             class="fixed top-24 right-6 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg flex items-center gap-2" role="alert">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <div>
                <strong class="font-bold">¡Solicitud Enviada!</strong>
                <span class="block sm:inline text-sm">{{ session('message') }}</span>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             class="fixed top-24 right-6 z-50 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg flex items-center gap-2" role="alert">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <div>
                <strong class="font-bold">¡Atención!</strong>
                <span class="block sm:inline text-sm">{{ session('error') }}</span>
            </div>
        </div>
    @endif


    <div class="space-y-6">
        <!-- Tarjeta de Información General -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-sga-text mb-4">
                    Información de la Sección
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <strong class="text-gray-500 block">Profesor:</strong>
                        <span class="text-gray-900">{{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Horario:</strong>
                        <span class="text-gray-900">{{ implode(', ', $enrollment->courseSchedule->days_of_week ?? []) }} | {{ \Carbon\Carbon::parse($enrollment->courseSchedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($enrollment->courseSchedule->end_time)->format('h:i A') }}</span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Período:</strong>
                        <span class="text-gray-900">{{ \Carbon\Carbon::parse($enrollment->courseSchedule->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($enrollment->courseSchedule->end_date)->format('d/m/Y') }}</span>
                    </div>
                    
                    {{-- NUEVO CAMPO: Modalidad --}}
                    <div>
                        <strong class="text-gray-500 block">Modalidad:</strong>
                        <span @class([
                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                            'bg-blue-100 text-blue-800' => ($enrollment->courseSchedule->modality ?? 'Presencial') === 'Presencial',
                            'bg-purple-100 text-purple-800' => ($enrollment->courseSchedule->modality ?? '') === 'Virtual',
                            'bg-orange-100 text-orange-800' => ($enrollment->courseSchedule->modality ?? '') === 'Semi-Presencial',
                            // Fallback por si acaso es otro valor
                            'bg-gray-100 text-gray-800' => !in_array(($enrollment->courseSchedule->modality ?? 'Presencial'), ['Presencial', 'Virtual', 'Semi-Presencial']),
                        ])>
                            {{ $enrollment->courseSchedule->modality ?? 'Presencial' }}
                        </span>
                    </div>
                    {{-- FIN NUEVO CAMPO --}}

                    <div>
                        <strong class="text-gray-500 block">Estado Actual:</strong>
                        <span @class([
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-green-100 text-green-800' => $enrollment->status === 'Cursando',
                                'bg-blue-100 text-blue-800' => $enrollment->status === 'Completado',
                                'bg-yellow-100 text-yellow-800' => $enrollment->status === 'Pendiente',
                                'bg-red-100 text-red-800' => $enrollment->status === 'Retirado',
                            ])>
                            {{ $enrollment->status }}
                        </span>
                    </div>
                    <div>
                        <strong class="text-gray-500 block">Calificación Final:</strong>
                        <span class="text-gray-900 font-bold">{{ $enrollment->final_grade ?? 'N/A' }}</span>
                    </div>
                </div>

                {{-- 
                   Se han eliminado los botones de acción (Cambio de Sección / Retiro) 
                   para simplificar la vista.
                --}}

            </div>
        </div>

        <!-- Tarjeta de Asistencia -->
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-medium leading-6 text-sga-text mb-4">
                    Resumen de Asistencia
                </h3>
                
                <!-- Resumen -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 bg-sga-bg rounded-lg text-center">
                        <div class="text-3xl font-bold text-sga-primary">{{ $totalClasses }}</div>
                        <div class="text-sm font-medium text-sga-text-light">Clases Totales</div>
                    </div>
                    <div class="p-4 bg-sga-success/10 ring-1 ring-sga-success/20 rounded-lg text-center">
                        <div class="text-3xl font-bold text-sga-success">{{ $attendedClasses }}</div>
                        <div class="text-sm font-medium text-sga-text-light">Presente</div>
                    </div>
                    <div class="p-4 bg-sga-warning/10 ring-1 ring-sga-warning/20 rounded-lg text-center">
                        <div class="text-3xl font-bold text-sga-warning">{{ $tardyClasses }}</div>
                        <div class="text-sm font-medium text-sga-text-light">Tardanzas</div>
                    </div>
                    <div class="p-4 bg-sga-danger/10 ring-1 ring-sga-danger/20 rounded-lg text-center">
                        <div class="text-3xl font-bold text-sga-danger">{{ $absentClasses }}</div>
                        <div class="text-sm font-medium text-sga-text-light">Ausencias</div>
                    </div>
                </div>

                <!-- Tabla de Asistencias -->
                <div class="overflow-x-auto shadow-sm rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observaciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($attendances as $attendance)
                                <tr class="hover:bg-gray-50" wire:key="attendance-{{ $attendance->id }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $attendance->attendance_date->format('d \d\e F, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span @class([
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            'bg-green-100 text-green-800' => $attendance->status === 'Presente',
                                            'bg-red-100 text-red-800' => $attendance->status === 'Ausente',
                                            'bg-yellow-100 text-yellow-800' => $attendance->status === 'Tardanza',
                                            'bg-gray-100 text-gray-800' => $attendance->status === 'Justificado'
                                        ])>
                                            {{ $attendance->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $attendance->notes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                        Aún no se ha registrado asistencia para este curso.
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