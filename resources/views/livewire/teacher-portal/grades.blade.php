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
                Registrar Calificaciones
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

    <!-- Info Asignatura & Grado -->
    <div class="workspace-panel overflow-hidden rounded-xl bg-white p-6 border border-gray-200/80 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 mb-2">
                    {{ $sectionSubject->subject->area ?? 'General' }}
                </span>
                <h3 class="text-xl font-extrabold text-gray-900 tracking-tight">
                    {{ $sectionSubject->subject->name ?? 'N/A' }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 font-medium">
                    Grado y Sección: {{ $sectionSubject->section->full_name ?? 'N/A' }}
                </p>
                <p class="mt-0.5 text-xs text-gray-400">
                    Año Lectivo: {{ $sectionSubject->section->academicYear->name ?? 'N/A' }}
                </p>
            </div>
            <div class="flex flex-col items-end gap-2">
                @if($isLocked)
                    <div class="rounded-lg bg-red-50 p-3 text-red-700 border border-red-200 text-xs font-bold max-w-sm">
                        {{ $lockReason }}
                    </div>
                @else
                    <div class="rounded-lg bg-green-50 px-3 py-2 text-green-700 border border-green-200 text-xs font-bold flex items-center gap-1.5 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></span>
                        Notas Abiertas (Edición Disponible)
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Selector de Período Evaluativo (Segmented Control Style) -->
    @if(count($periods) > 0)
        <div class="workspace-panel bg-white p-4 rounded-xl border border-gray-200/80 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-3">Periodo Académico Evaluativo</span>
            <div class="inline-flex flex-wrap p-1 bg-gray-50 border border-gray-200 rounded-xl gap-1">
                @foreach($periods as $period)
                    <button type="button" 
                            wire:click="selectPeriod({{ $period->id }})"
                            class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg transition-all duration-150 flex items-center gap-2
                            {{ $selectedPeriodId === $period->id 
                                ? 'bg-white text-gray-900 shadow-sm border border-gray-200' 
                                : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100/50' }}">
                        {{ $period->name }}
                        @if($period->status === 'active')
                            <span class="h-1.5 w-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Récord de Notas -->
    <div class="workspace-panel overflow-hidden rounded-xl bg-white border border-gray-200/80 shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider sm:pl-6">Estudiante</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Desempeño MINERD</th>
                        <th scope="col" class="relative w-40 px-3 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nota (0-100)</th>
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
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @php
                                    $scoreVal = $grades[$student->id] ?? null;
                                    $perfLabel = 'Sin evaluar';
                                    $perfColor = 'bg-gray-50 text-gray-400 border-gray-200';
                                    
                                    if ($scoreVal !== null && $scoreVal !== '') {
                                        $scoreNum = (float)$scoreVal;
                                        if ($scoreNum >= 89) {
                                            $perfLabel = 'Destacado';
                                            $perfColor = 'bg-green-50 text-green-700 border-green-200/50';
                                        } elseif ($scoreNum >= 77) {
                                            $perfLabel = 'Logro Evidenciado';
                                            $perfColor = 'bg-blue-50 text-blue-700 border-blue-200/50';
                                        } elseif ($scoreNum >= 65) {
                                            $perfLabel = 'En Proceso';
                                            $perfColor = 'bg-amber-50 text-amber-700 border-amber-200/50';
                                        } else {
                                            $perfLabel = 'Insuficiente';
                                            $perfColor = 'bg-red-50 text-red-700 border-red-200/50';
                                        }
                                    }
                                @endphp
                                <span class="inline-flex items-center rounded-lg border px-2.5 py-0.5 text-xs font-semibold {{ $perfColor }}">
                                    {{ $perfLabel }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <input 
                                    type="number" 
                                    wire:model="grades.{{ $student->id }}" 
                                    class="block w-full text-right rounded-lg border border-gray-200 px-3 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 {{ $isLocked ? 'bg-gray-50 cursor-not-allowed text-gray-400' : '' }}"
                                    min="0" max="100" step="0.01"
                                    {{ $isLocked ? 'disabled' : '' }}
                                />
                                @error('grades.' . $student->id) 
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p> 
                                @enderror
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="whitespace-nowrap px-3 py-12 text-center text-sm text-gray-500 font-medium">
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
                        wire:click="saveGrades"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                    Guardar Calificaciones
                </button>
            </div>
        @endif
    </div>
</div>