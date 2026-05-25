<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inscripción y Matrícula</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $activeYear?->name ?? 'Sin año activo' }} — Gestión de inscripciones escolares</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>
            Nueva Inscripción
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 4000)">
            {{ session('message') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/30 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 p-4 text-center border border-gray-200 dark:border-gray-700">
            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</span>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
        <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 p-4 text-center border border-yellow-200 dark:border-yellow-800">
            <span class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $stats['pending'] }}</span>
            <p class="text-xs text-yellow-600 mt-1">Pendientes</p>
        </div>
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4 text-center border border-blue-200 dark:border-blue-800">
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $stats['approved'] }}</span>
            <p class="text-xs text-blue-600 mt-1">Aprobadas</p>
        </div>
        <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 text-center border border-green-200 dark:border-green-800">
            <span class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $stats['enrolled'] }}</span>
            <p class="text-xs text-green-600 mt-1">Matriculados</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar estudiante..." class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white w-60" />
        <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos los estados</option>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterGrade" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos los grados</option>
            @foreach($gradeLevels as $level)
                <option value="{{ $level->id }}">{{ $level->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterType" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos los tipos</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabla --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Código</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Estudiante</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Grado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500">Sección</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Tipo</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Docs</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase text-gray-500">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($enrollments as $enrollment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-2 text-sm font-mono text-blue-600 dark:text-blue-400">{{ $enrollment->enrollment_code }}</td>
                        <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $enrollment->student?->full_name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $enrollment->gradeLevel?->short_name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $enrollment->section?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                {{ $types[$enrollment->enrollment_type] ?? $enrollment->enrollment_type }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <button wire:click="manageDocs({{ $enrollment->id }})" class="text-sm font-medium {{ $enrollment->documents_percentage >= 100 ? 'text-green-600' : 'text-yellow-600' }} hover:underline">
                                {{ $enrollment->documents_completed }}/{{ $enrollment->documents_total }}
                            </button>
                        </td>
                        <td class="px-4 py-2 text-center">
                            @php $color = \App\Models\SchoolEnrollment::STATUS_COLORS[$enrollment->status] ?? 'gray'; @endphp
                            <span class="inline-flex rounded-full bg-{{ $color }}-100 px-2.5 py-0.5 text-xs font-medium text-{{ $color }}-800 dark:bg-{{ $color }}-900/40 dark:text-{{ $color }}-400">
                                {{ $statuses[$enrollment->status] ?? $enrollment->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-right text-sm space-x-1">
                            @if($enrollment->status === 'pending')
                                <button wire:click="approve({{ $enrollment->id }})" class="text-blue-600 hover:text-blue-800 font-medium">Aprobar</button>
                            @elseif($enrollment->status === 'approved')
                                <button wire:click="enroll({{ $enrollment->id }})" class="text-green-600 hover:text-green-800 font-medium">Matricular</button>
                            @endif
                            <button wire:click="delete({{ $enrollment->id }})" wire:confirm="¿Eliminar esta inscripción?" class="text-red-500 hover:text-red-700 font-medium">×</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <p class="text-sm font-medium">No hay inscripciones registradas</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50">
            {{ $enrollments->links() }}
        </div>
    </div>

    {{-- Modal: Nueva Inscripción --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Nueva Inscripción</h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estudiante *</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Buscar por nombre..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        @if($searchStudents->count() > 0 && !$student_id)
                            <div class="mt-1 rounded-lg border border-gray-200 bg-white shadow-lg max-h-40 overflow-y-auto dark:bg-gray-700 dark:border-gray-600">
                                @foreach($searchStudents as $st)
                                    <button type="button" wire:click="$set('student_id', {{ $st->id }}); $set('studentSearch', '{{ addslashes($st->full_name) }}')" class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-50 dark:hover:bg-gray-600">
                                        {{ $st->full_name }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grado *</label>
                            <select wire:model.live="grade_level_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Seleccionar...</option>
                                @foreach($gradeLevels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
                            <select wire:model="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Sin asignar</option>
                                @foreach($sections as $sec)
                                    <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Inscripción</label>
                        <select wire:model="enrollment_type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($enrollment_type === 'transfer')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Escuela Anterior</label>
                            <input type="text" wire:model="previous_school" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notas</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Inscribir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Documentos --}}
    @if($showDocsModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showDocsModal', false)"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Checklist de Documentos</h3>
                <form wire:submit="saveDocs" class="space-y-3">
                    @foreach($requiredDocs as $field => $label)
                        <label class="flex items-center gap-3 p-3 rounded-lg border {{ ($docs[$field] ?? false) ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/50' }} cursor-pointer transition">
                            <input type="checkbox" wire:model="docs.{{ $field }}" class="rounded border-gray-300 text-green-600 focus:ring-green-500" />
                            <span class="text-sm font-medium {{ ($docs[$field] ?? false) ? 'text-green-800 dark:text-green-400' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ ($docs[$field] ?? false) ? '' : '⬜' }} {{ $label }}
                            </span>
                        </label>
                    @endforeach
                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showDocsModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
