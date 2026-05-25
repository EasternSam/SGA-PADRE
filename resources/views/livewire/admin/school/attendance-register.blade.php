<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Registro de Asistencia</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Toma de asistencia diaria por sección</p>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            ✅ {{ session('message') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/30 dark:text-red-400">
            ❌ {{ session('error') }}
        </div>
    @endif

    @if(!$activeYear)
        <div class="rounded-xl border-2 border-dashed border-yellow-300 bg-yellow-50 p-8 text-center dark:bg-yellow-900/20 dark:border-yellow-700">
            <p class="font-semibold text-yellow-800 dark:text-yellow-400">No hay año escolar activo</p>
        </div>
    @else
        {{-- Controles --}}
        <div class="flex flex-wrap gap-4 mb-6 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
                <select wire:model.live="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">Seleccionar sección...</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">{{ $section->gradeLevel?->short_name }} {{ $section->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha</label>
                <input type="date" wire:model.live="date" max="{{ now()->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
            </div>
            @if(count($attendances) > 0)
                <button wire:click="markAllPresent" class="rounded-lg bg-green-100 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-200 transition dark:bg-green-900/30 dark:text-green-400">
                    ✅ Marcar todos presente
                </button>
            @endif
        </div>

        {{-- Resumen rápido --}}
        @if(count($attendances) > 0)
            <div class="grid grid-cols-5 gap-3 mb-6">
                <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-3 text-center border border-green-200 dark:border-green-800">
                    <span class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $summary['present'] }}</span>
                    <p class="text-xs text-green-600 dark:text-green-500 mt-1">Presentes</p>
                </div>
                <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-3 text-center border border-red-200 dark:border-red-800">
                    <span class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $summary['absent'] }}</span>
                    <p class="text-xs text-red-600 dark:text-red-500 mt-1">Ausentes</p>
                </div>
                <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 p-3 text-center border border-yellow-200 dark:border-yellow-800">
                    <span class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $summary['late'] }}</span>
                    <p class="text-xs text-yellow-600 dark:text-yellow-500 mt-1">Tardanzas</p>
                </div>
                <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-3 text-center border border-blue-200 dark:border-blue-800">
                    <span class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $summary['excused'] }}</span>
                    <p class="text-xs text-blue-600 dark:text-blue-500 mt-1">Excusas</p>
                </div>
                <div class="rounded-xl bg-purple-50 dark:bg-purple-900/20 p-3 text-center border border-purple-200 dark:border-purple-800">
                    <span class="text-2xl font-bold text-purple-700 dark:text-purple-400">{{ $summary['permission'] }}</span>
                    <p class="text-xs text-purple-600 dark:text-purple-500 mt-1">Permisos</p>
                </div>
            </div>

            @if($hasExisting)
                <div class="mb-4 rounded-lg bg-blue-50 p-3 text-sm text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                    ℹ️ Ya existe un registro de asistencia para esta fecha. Los cambios actualizarán el existente.
                </div>
            @endif

            {{-- Lista de estudiantes --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 w-10">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estudiante</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Asistencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($attendances as $index => $attendance)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $attendance['status'] === 'absent' ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                                <td class="px-4 py-2 text-sm text-gray-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $attendance['student_name'] }}</span>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex justify-center gap-1">
                                        <button wire:click="setStatus({{ $index }}, 'present')" title="Presente"
                                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $attendance['status'] === 'present' ? 'bg-green-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-green-100 dark:bg-gray-700 dark:text-gray-400' }}">
                                            ✅ P
                                        </button>
                                        <button wire:click="setStatus({{ $index }}, 'absent')" title="Ausente"
                                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $attendance['status'] === 'absent' ? 'bg-red-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-red-100 dark:bg-gray-700 dark:text-gray-400' }}">
                                            ❌ A
                                        </button>
                                        <button wire:click="setStatus({{ $index }}, 'late')" title="Tardanza"
                                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $attendance['status'] === 'late' ? 'bg-yellow-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-yellow-100 dark:bg-gray-700 dark:text-gray-400' }}">
                                            ⏰ T
                                        </button>
                                        <button wire:click="setStatus({{ $index }}, 'excused')" title="Excusa"
                                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $attendance['status'] === 'excused' ? 'bg-blue-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-blue-100 dark:bg-gray-700 dark:text-gray-400' }}">
                                            📋 E
                                        </button>
                                        <button wire:click="setStatus({{ $index }}, 'permission')" title="Permiso"
                                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition {{ $attendance['status'] === 'permission' ? 'bg-purple-500 text-white shadow-sm' : 'bg-gray-100 text-gray-500 hover:bg-purple-100 dark:bg-gray-700 dark:text-gray-400' }}">
                                            📝 PM
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-3 flex items-center justify-between">
                    <span class="text-sm text-gray-500">{{ count($attendances) }} estudiantes</span>
                    <button wire:click="save" wire:loading.attr="disabled" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 transition">
                        <svg wire:loading.remove class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 0 1 .208 1.04l-9 13.5a.75.75 0 0 1-1.154.114l-6-6a.75.75 0 0 1 1.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 0 1 1.04-.208Z" clip-rule="evenodd" /></svg>
                        <svg wire:loading class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Guardar Asistencia
                    </button>
                </div>
            </div>
        @elseif($section_id)
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
                <p class="text-sm text-gray-500">No hay estudiantes inscritos en esta sección</p>
            </div>
        @endif
    @endif
</div>
