<div>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ url()->previous() }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full text-sga-text-light transition hover:bg-sga-bg hover:text-sga-text">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>
            <h2 class="text-xl font-semibold leading-tight text-sga-text">
                Registrar Calificaciones
            </h2>
        </div>
    </x-slot>

    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        <!-- Info Curso -->
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
                            Horario: {{ is_array($section->days_of_week) ? implode(', ', $section->days_of_week) : $section->days_of_week }}, 
                            {{ \Carbon\Carbon::parse($section->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('h:i A') }}
                        </p>
                    </div>
                    @if($isLocked)
                        <div class="rounded-md bg-red-50 p-2 text-red-700 border border-red-200 text-sm font-bold">
                            🔒 {{ $lockReason }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-sga-gray">
                            <thead class="bg-sga-bg">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-6">Estudiante</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Estado Actual</th>
                                    <th scope="col" class="relative w-40 px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Nota (0-100)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sga-gray bg-sga-card">
                                @forelse ($enrollments as $enrollment)
                                    <tr wire:key="enrollment-{{ $enrollment->id }}">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 text-gray-500 font-bold">
                                                        {{ substr($enrollment->student->first_name, 0, 1) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->student->fullName }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->student->student_code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium 
                                                {{ $enrollment->status === 'Aprobado' ? 'bg-green-100 text-green-800' : 
                                                  ($enrollment->status === 'Reprobado' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ $enrollment->status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                                            <x-text-input 
                                                type="number" 
                                                wire:model.defer="grades.{{ $enrollment->id }}" 
                                                class="block w-full text-right {{ $isLocked ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                                min="0" max="100" step="0.01"
                                                :disabled="$isLocked"
                                            />
                                            @error('grades.' . $enrollment->id) 
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p> 
                                            @enderror
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="whitespace-nowrap px-3 py-4 text-center text-sm text-sga-text-light">
                                            No hay estudiantes activos en esta sección.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @if(!$isLocked)
                <div class="border-t border-sga-gray bg-sga-bg px-4 py-4 sm:px-6">
                    <div class="flex justify-end">
                        <button type="button" 
                                wire:click="saveGrades"
                                wire:loading.attr="disabled"
                                class="rounded-md bg-sga-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sga-primary/80">
                            Guardar Calificaciones
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>