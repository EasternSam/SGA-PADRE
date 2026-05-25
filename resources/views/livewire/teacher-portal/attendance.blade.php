<div class="space-y-6">
    <!-- Encabezado -->
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.dashboard') }}" wire:navigate class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 transition hover:bg-gray-100 hover:text-gray-900">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
            </a>
            <h2 class="text-xl font-bold text-gray-900">
                Control de Asistencia Diario
            </h2>
        </div>
    </x-slot>

    @if (session()->has('message'))
        <div class="rounded-lg bg-green-50 p-4 border border-green-200 text-sm font-semibold text-green-800">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-lg bg-red-50 p-4 border border-red-200 text-sm font-semibold text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Info Sección y Fecha -->
    <div class="workspace-panel overflow-hidden rounded-xl bg-white p-6 border border-gray-200/80 shadow-sm">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 mb-2">
                    {{ $section->gradeLevel->name ?? 'Grado Escolar' }}
                </span>
                <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">
                    {{ $section->full_name }}
                </h3>
                <p class="mt-0.5 text-xs text-gray-400">
                    Año Lectivo: {{ $section->academicYear->name ?? 'N/A' }}
                </p>
            </div>
            
            <div class="flex flex-col sm:items-end gap-3">
                <div class="flex items-center gap-3">
                    <label for="attendanceDate" class="text-xs font-bold text-gray-400 uppercase tracking-wider">Fecha del Registro:</label>
                    <input 
                        type="date" 
                        id="attendanceDate" 
                        wire:model.live="attendanceDate"
                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                    />
                </div>
                
                @if($isLocked)
                    <div class="rounded-lg bg-red-50 px-3 py-2 text-red-700 border border-red-200 text-xs font-bold shadow-sm">
                        {{ $errorMessage }}
                    </div>
                @else
                    <div class="rounded-lg bg-green-50 px-3 py-2 text-green-700 border border-green-200 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                        Periodo Habilitado para Edición
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Leyenda de Estados -->
    <div class="workspace-panel bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm flex flex-wrap gap-4 items-center">
        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Leyenda:</span>
        <div class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded bg-green-500"></span>
            <span class="text-xs font-semibold text-gray-600">P = Presente</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded bg-red-500"></span>
            <span class="text-xs font-semibold text-gray-600">A = Ausente</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded bg-amber-500"></span>
            <span class="text-xs font-semibold text-gray-600">T = Tardanza</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="h-3 w-3 rounded bg-blue-500"></span>
            <span class="text-xs font-semibold text-gray-600">E = Excusa</span>
        </div>
    </div>

    <!-- Registro de Estudiantes -->
    <div class="workspace-panel overflow-hidden rounded-xl bg-white border border-gray-200/80 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider sm:pl-6">Estudiante</th>
                        <th scope="col" class="px-3 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider pr-6">Marcar Asistencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($students as $student)
                        <tr wire:key="student-{{ $student->id }}" class="hover:bg-gray-50/50 transition-colors duration-150">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-600 font-bold border border-gray-200">
                                            {{ substr($student->first_name, 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-bold text-gray-900">{{ $student->fullName }}</div>
                                        <div class="text-gray-400 text-xs mt-0.5">{{ $student->student_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-right pr-6">
                                <div class="inline-flex rounded-lg p-0.5 bg-gray-150 border border-gray-200">
                                    <button type="button" 
                                            wire:click="$set('attendanceData.{{ $student->id }}', 'present')" 
                                            class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150
                                            {{ ($attendanceData[$student->id] ?? 'present') === 'present' ? 'bg-green-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}"
                                            {{ $isLocked ? 'disabled' : '' }}>
                                        P
                                    </button>
                                    <button type="button" 
                                            wire:click="$set('attendanceData.{{ $student->id }}', 'absent')" 
                                            class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150
                                            {{ ($attendanceData[$student->id] ?? 'present') === 'absent' ? 'bg-red-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}"
                                            {{ $isLocked ? 'disabled' : '' }}>
                                        A
                                    </button>
                                    <button type="button" 
                                            wire:click="$set('attendanceData.{{ $student->id }}', 'late')" 
                                            class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150
                                            {{ ($attendanceData[$student->id] ?? 'present') === 'late' ? 'bg-amber-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}"
                                            {{ $isLocked ? 'disabled' : '' }}>
                                        T
                                    </button>
                                    <button type="button" 
                                            wire:click="$set('attendanceData.{{ $student->id }}', 'excused')" 
                                            class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-150
                                            {{ ($attendanceData[$student->id] ?? 'present') === 'excused' ? 'bg-blue-500 text-white shadow-sm' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}"
                                            {{ $isLocked ? 'disabled' : '' }}>
                                        E
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="whitespace-nowrap px-3 py-12 text-center text-sm text-gray-500 font-medium">
                                No hay estudiantes inscritos en esta sección.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if(!$isLocked && count($students) > 0)
            <div class="border-t border-gray-150 bg-gray-50 px-4 py-4 sm:px-6 flex justify-end">
                <button type="button" 
                        wire:click="saveAttendance"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                    Guardar Registro Diario
                </button>
            </div>
        @endif
    </div>
</div>