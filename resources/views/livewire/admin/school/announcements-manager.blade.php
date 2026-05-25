<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Comunicaciones</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Circulares, avisos, alertas y memorándums</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 0 1 .75.75v6.75h6.75a.75.75 0 0 1 0 1.5h-6.75v6.75a.75.75 0 0 1-1.5 0v-6.75H4.5a.75.75 0 0 1 0-1.5h6.75V4.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" /></svg>
            Nueva Comunicación
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            ✅ {{ session('message') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="flex flex-wrap gap-3 mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="🔍 Buscar por título..." class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white w-60" />
        <select wire:model.live="filterType" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todos los tipos</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterPriority" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            <option value="">Todas las prioridades</option>
            @foreach($priorities as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Lista --}}
    <div class="space-y-3">
        @forelse($announcements as $a)
            <div class="rounded-xl border bg-white p-4 shadow-sm dark:bg-gray-800 hover:shadow-md transition
                {{ $a->priority === 'urgent' ? 'border-red-300 dark:border-red-800' : ($a->priority === 'important' ? 'border-yellow-300 dark:border-yellow-800' : 'border-gray-200 dark:border-gray-700') }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm">{{ $types[$a->type] ?? '' }}</span>
                            @if($a->priority === 'urgent')
                                <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-800 dark:bg-red-900/40 dark:text-red-400">URGENTE</span>
                            @elseif($a->priority === 'important')
                                <span class="inline-flex rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-bold text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-400">IMPORTANTE</span>
                            @endif
                            @if(!$a->is_published)
                                <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600 dark:bg-gray-700 dark:text-gray-400">Borrador</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $a->title }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ Str::limit($a->body, 150) }}</p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                            <span>📅 {{ $a->publish_date->format('d/m/Y') }}</span>
                            <span>👤 {{ $a->author?->name }}</span>
                            <span>{{ $audiences[$a->audience] ?? $a->audience }}</span>
                            @if($a->gradeLevel)
                                <span>🎓 {{ $a->gradeLevel->short_name }}</span>
                            @endif
                            @if($a->requires_acknowledgment)
                                <span class="text-blue-600">📋 Requiere acuse</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <button wire:click="preview({{ $a->id }})" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Ver</button>
                        <button wire:click="edit({{ $a->id }})" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Editar</button>
                        <button wire:click="togglePublish({{ $a->id }})" class="text-sm {{ $a->is_published ? 'text-yellow-600' : 'text-green-600' }} font-medium">
                            {{ $a->is_published ? 'Ocultar' : 'Publicar' }}
                        </button>
                        <button wire:click="delete({{ $a->id }})" wire:confirm="¿Eliminar esta comunicación?" class="text-sm text-red-500 hover:text-red-700 font-medium">×</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
                <p class="text-sm text-gray-500 font-medium">No hay comunicaciones publicadas</p>
                <p class="text-xs text-gray-400 mt-1">Crea una circular, aviso o alerta</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $announcements->links() }}
    </div>

    {{-- Modal: Crear/Editar --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    {{ $editingId ? 'Editar Comunicación' : 'Nueva Comunicación' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título *</label>
                        <input type="text" wire:model="title" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: Reunión de padres - Primer Período" />
                        @error('title') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                            <select wire:model="type" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prioridad</label>
                            <select wire:model="priority" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($priorities as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Audiencia</label>
                            <select wire:model="audience" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($audiences as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grado (opcional)</label>
                            <select wire:model.live="grade_level_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Todos los grados</option>
                                @foreach($gradeLevels as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección (opcional)</label>
                            <select wire:model="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="">Todas las secciones</option>
                                @foreach($sections as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contenido *</label>
                        <textarea wire:model="body" rows="6" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Escriba el contenido de la comunicación..."></textarea>
                        @error('body') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha publicación</label>
                            <input type="date" wire:model="publish_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expiración (opcional)</label>
                            <input type="date" wire:model="expiry_date" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="requires_acknowledgment" class="rounded border-gray-300 text-blue-600">
                        Requiere acuse de recibo
                    </label>
                    <div class="flex justify-end gap-3 pt-4 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">
                            {{ $editingId ? 'Actualizar' : 'Publicar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal: Preview --}}
    @if($showPreview && $previewAnnouncement)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showPreview', false)"></div>
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <div class="flex items-center gap-2 mb-2">
                    <span>{{ $types[$previewAnnouncement->type] ?? '' }}</span>
                    @if($previewAnnouncement->priority !== 'normal')
                        <span class="inline-flex rounded-full {{ $previewAnnouncement->priority === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }} px-2 py-0.5 text-xs font-bold">
                            {{ strtoupper($previewAnnouncement->priority) }}
                        </span>
                    @endif
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ $previewAnnouncement->title }}</h3>
                <div class="text-xs text-gray-500 mb-4 flex gap-3">
                    <span>📅 {{ $previewAnnouncement->publish_date->format('d/m/Y') }}</span>
                    <span>👤 {{ $previewAnnouncement->author?->name }}</span>
                    <span>{{ $audiences[$previewAnnouncement->audience] ?? '' }}</span>
                </div>
                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $previewAnnouncement->body }}</div>
                <div class="flex justify-end pt-4 mt-4 border-t dark:border-gray-700">
                    <button wire:click="$set('showPreview', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
