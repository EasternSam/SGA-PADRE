<div class="p-4 lg:p-6">
    {{-- Page Header --}}
    <div class="gb-page-header">
        <div>
            <h1 class="gb-page-title">Orientación y Psicología</h1>
            <p class="gb-page-subtitle">Seguimiento de casos, entrevistas y bienestar estudiantil</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="create" class="gb-btn gb-btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo Registro
            </button>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-3 text-sm text-emerald-800 dark:text-emerald-400 flex items-center gap-2" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="gb-metric gb-lift">
            <div class="flex items-center gap-3">
                <div class="gb-metric-icon bg-amber-50 dark:bg-amber-900/20 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <div class="gb-metric-value !text-xl !mt-0">{{ $openCases }}</div>
                    <div class="gb-metric-label !mt-0">Casos Abiertos</div>
                </div>
            </div>
        </div>
        <div class="gb-metric gb-lift">
            <div class="flex items-center gap-3">
                <div class="gb-metric-icon {{ $urgentCases > 0 ? 'bg-red-50 dark:bg-red-900/20 text-red-600' : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="gb-metric-value !text-xl !mt-0 {{ $urgentCases > 0 ? 'text-red-600' : '' }}">{{ $urgentCases }}</div>
                    <div class="gb-metric-label !mt-0">Urgentes</div>
                </div>
            </div>
        </div>
        <div class="gb-metric gb-lift">
            <div class="flex items-center gap-3">
                <div class="gb-metric-icon {{ $followupDue > 0 ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-600' : 'bg-blue-50 dark:bg-blue-900/20 text-blue-600' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="gb-metric-value !text-xl !mt-0 {{ $followupDue > 0 ? 'text-orange-600' : '' }}">{{ $followupDue }}</div>
                    <div class="gb-metric-label !mt-0">Seguimiento Vencido</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="gb-table-outer">
        <div class="gb-table-toolbar">
            <div class="flex items-center gap-3 flex-1 flex-wrap">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar..." class="gb-input pl-9 w-48" />
                </div>
                {{-- Segmented Filter --}}
                <div class="gb-segmented">
                    <button wire:click="$set('filterStatus', '')" class="gb-segment {{ $filterStatus === '' ? 'active' : '' }}">Todos</button>
                    <button wire:click="$set('filterStatus', 'open')" class="gb-segment {{ $filterStatus === 'open' ? 'active' : '' }}">Abiertos</button>
                    <button wire:click="$set('filterStatus', 'in_progress')" class="gb-segment {{ $filterStatus === 'in_progress' ? 'active' : '' }}">En Proceso</button>
                    <button wire:click="$set('filterStatus', 'resolved')" class="gb-segment {{ $filterStatus === 'resolved' ? 'active' : '' }}">Resueltos</button>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="filterPriority" class="gb-input w-auto text-xs">
                    <option value="">Prioridad</option>
                    @foreach($priorities as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
                <select wire:model.live="filterType" class="gb-input w-auto text-xs">
                    <option value="">Tipo</option>
                    @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="gb-table">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Caso</th>
                        <th class="text-center">Prioridad</th>
                        <th class="text-center">Estado</th>
                        <th>Consejero</th>
                        <th class="text-center">Seguimiento</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-xs font-bold text-purple-700 dark:text-purple-400 flex-shrink-0">
                                        {{ strtoupper(substr($r->student?->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($r->student?->last_name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="user-name">{{ $r->student?->full_name ?? '—' }}</div>
                                        @if($r->is_confidential) <span class="text-[9px] text-red-500 font-bold uppercase">🔒 Confidencial</span> @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-[10px] text-gray-400 font-medium uppercase">{{ $types[$r->type] ?? '' }}</span>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $r->title }}</div>
                            </td>
                            <td class="text-center">
                                @php
                                    $prColors = ['urgent' => 'gb-badge-danger', 'high' => 'gb-badge-warning', 'medium' => 'gb-badge-info', 'low' => 'gb-badge-success'];
                                @endphp
                                <span class="gb-badge {{ $prColors[$r->priority] ?? 'gb-badge-info' }}">{{ $priorities[$r->priority] ?? $r->priority }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $stColors = ['open' => 'gb-badge-warning', 'in_progress' => 'gb-badge-info', 'resolved' => 'gb-badge-success', 'closed' => 'gb-badge-danger'];
                                @endphp
                                <span class="gb-badge {{ $stColors[$r->status] ?? 'gb-badge-info' }}">{{ $statuses[$r->status] ?? $r->status }}</span>
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-400">{{ $r->counselor?->name ?? '—' }}</td>
                            <td class="text-center text-xs">
                                @if($r->next_followup)
                                    <span class="{{ $r->next_followup->isPast() && in_array($r->status, ['open', 'in_progress']) ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                        {{ $r->next_followup->format('d/m/Y') }}
                                    </span>
                                    @if($r->next_followup->isPast() && in_array($r->status, ['open', 'in_progress']))
                                        <span class="inline-block ml-1 w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if(in_array($r->status, ['open', 'in_progress']))
                                        <button wire:click="resolve({{ $r->id }})" class="gb-btn-icon !w-8 !h-8 !border-emerald-200 text-emerald-600 hover:!bg-emerald-50" title="Resolver">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    @endif
                                    <button wire:click="edit({{ $r->id }})" class="gb-btn-icon !w-8 !h-8" title="Editar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $r->id }})" wire:confirm="¿Eliminar?" class="gb-btn-icon !w-8 !h-8 text-red-400 hover:text-red-600 hover:!border-red-200 hover:!bg-red-50" title="Eliminar">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="gb-empty-state">
                                    <div class="gb-empty-icon">🧠</div>
                                    <div class="gb-empty-title">Sin registros de orientación</div>
                                    <div class="gb-empty-desc">Crea un nuevo registro para comenzar el seguimiento</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
        <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700/50">{{ $records->links() }}</div>
        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-gray-800 shadow-2xl border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $editId ? 'Editar Registro' : 'Nuevo Registro' }}</h3>
                    <button wire:click="$set('showModal', false)" class="gb-btn-icon !w-8 !h-8">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="save" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                    @if(!$editId)
                    <div>
                        <label class="gb-section-title mb-2 block">Estudiante *</label>
                        <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Buscar..." class="gb-input" />
                        @if($studentResults->count() > 0)
                            <select wire:model="student_id" class="gb-input mt-2">
                                <option value="">Seleccionar...</option>
                                @foreach($studentResults as $sr)
                                    <option value="{{ $sr->id }}">{{ $sr->full_name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="gb-section-title mb-2 block">Tipo *</label>
                            <select wire:model="type" class="gb-input">
                                @foreach($types as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="gb-section-title mb-2 block">Prioridad</label>
                            <select wire:model="priority" class="gb-input">
                                @foreach($priorities as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">Título *</label>
                        <input type="text" wire:model="title" class="gb-input" placeholder="Descripción breve del caso" />
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">Descripción</label>
                        <textarea wire:model="description" rows="3" class="gb-input" placeholder="Detalle del caso..."></textarea>
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">Hallazgos</label>
                        <textarea wire:model="findings" rows="2" class="gb-input" placeholder="Observaciones encontradas..."></textarea>
                    </div>
                    <div>
                        <label class="gb-section-title mb-2 block">Recomendaciones</label>
                        <textarea wire:model="recommendations" rows="2" class="gb-input" placeholder="Acciones sugeridas..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="gb-section-title mb-2 block">Estado</label>
                            <select wire:model="status" class="gb-input">
                                @foreach($statuses as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="gb-section-title mb-2 block">Próximo Seguimiento</label>
                            <input type="date" wire:model="next_followup" class="gb-input" />
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="is_confidential" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300 font-medium">🔒 Caso Confidencial</span>
                    </label>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="gb-btn gb-btn-secondary">Cancelar</button>
                        <button type="submit" class="gb-btn gb-btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
