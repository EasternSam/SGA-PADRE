<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <div class="flex items-center gap-2">
            {{-- Botón para Volver (Regresa a la página anterior, ej: perfil del profesor) --}}
            <a href="{{ url()->previous() }}"
               class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sga-text-light transition hover:bg-sga-bg hover:text-sga-text">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>
            <h2 class="text-xl font-semibold leading-tight text-sga-text">
                Registrar Calificaciones
            </h2>
        </div>
    </x-slot>

    <!-- Mensaje de Éxito -->
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700" role="alert">
            {{ session('message') }}
        </div>
    @endif
    <!-- Mensaje de Error -->
    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <!-- Contenido de la Página -->
    <div class="space-y-6">

        <!-- 1. Tarjeta de Cabecera (Información del Curso) -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-4 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-sga-text">
                            {{ $section->module->course->name ?? 'N/A' }}
                        </h3>
                        <p class="mt-1 text-sm text-sga-text-light">
                            Módulo: {{ $section->module->name ?? 'N/A' }}
                        </p>
                        <p class="mt-1 text-sm text-sga-text-light">
                            Horario: {{ implode(', ', $section->days_of_week ?? []) }}, {{ \Carbon\Carbon::parse($section->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('h:i A') }}
                        </p>
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
                                    <th scope="col" class="relative w-40 px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Calificación Final (0-100)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sga-gray bg-sga-card">
                                @forelse ($enrollments as $enrollment)
                                    <tr wire:key="enrollment-{{ $enrollment->id }}">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <img class="h-10 w-10 rounded-full" 
                                                         src="https://placehold.co/100x100/e2e8f0/64748b?text={{ substr($enrollment->student->first_name, 0, 1) }}{{ substr($enrollment->student->last_name, 0, 1) }}" 
                                                         alt="Avatar de {{ $enrollment->student->first_name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->student->fullName }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->student->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            {{-- Input para la calificación --}}
                                            <x-text-input 
                                                type="number" 
                                                wire:model.defer="grades.{{ $enrollment->id }}" 
                                                id="grade-{{ $enrollment->id }}"
                                                class="block w-full text-right"
                                                min="0"
                                                max="100"
                                                step="0.01"
                                                placeholder="N/A"
                                            />
                                            @error('grades.' . $enrollment->id) 
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p> 
                                            @enderror
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
                                            No hay estudiantes inscritos en esta sección.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Pie de la Tarjeta (Botón de Guardar) -->
            <div class="border-t border-sga-gray bg-sga-bg px-4 py-4 sm:px-6">
                <div class="flex justify-end">
                    <button type="button" 
                            wire:click="saveGrades"
                            wire:loading.attr="disabled"
                            wire:target="saveGrades"
                            class="rounded-md bg-sga-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sga-primary/80 disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveGrades">
                            Guardar Calificaciones
                        </span>
                        <span wire:loading wire:target="saveGrades">
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>