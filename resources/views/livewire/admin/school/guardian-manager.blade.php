<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Padres / Tutores</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Registro y vinculación con estudiantes</p>
        </div>
        <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
            + Nuevo Tutor
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">{{ session('message') }}</div>
    @endif

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, cédula o teléfono..." class="w-full max-w-md rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white mb-4" />

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Relación</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Contacto</th>
                    <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Estudiantes</th>
                    <th class="px-4 py-3 text-right text-xs font-bold uppercase text-gray-500">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($guardians as $g)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $g->full_name }}</div>
                            @if($g->cedula) <div class="text-xs text-gray-500">{{ $g->cedula }}</div> @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $relationships[$g->relationship] ?? $g->relationship }}</td>
                        <td class="px-4 py-3">
                            @if($g->phone) <div class="text-sm text-gray-700 dark:text-gray-300">{{ $g->phone }}</div> @endif
                            @if($g->email) <div class="text-xs text-gray-500">{{ $g->email }}</div> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded-full {{ $g->students_count > 0 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' }} px-2.5 py-0.5 text-xs font-bold">
                                {{ $g->students_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="openLink({{ $g->id }})" class="text-sm text-purple-600 hover:text-purple-800 font-medium mr-1">Vincular</button>
                            <button wire:click="edit({{ $g->id }})" class="text-sm text-blue-600 hover:text-blue-800 font-medium mr-1">Editar</button>
                            <button wire:click="delete({{ $g->id }})" wire:confirm="¿Eliminar este tutor?" class="text-sm text-red-500 hover:text-red-700">×</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Sin tutores registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $guardians->links() }}</div>

    {{-- Modal CRUD --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">{{ $editingId ? 'Editar' : 'Nuevo' }} Tutor</h3>
                <form wire:submit="save" class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre *</label>
                            <input type="text" wire:model="first_name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            @error('first_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apellido *</label>
                            <input type="text" wire:model="last_name" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Relación *</label>
                            <select wire:model="relationship" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                @foreach($relationships as $k => $v) <option value="{{ $k }}">{{ $v }}</option> @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cédula</label>
                            <input type="text" wire:model="cedula" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="000-0000000-0" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                            <input type="text" wire:model="phone" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="809-000-0000" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tel. Alternativo</label>
                            <input type="text" wire:model="phone_alt" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                            <input type="email" wire:model="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
                        <textarea wire:model="address" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ocupación</label>
                            <input type="text" wire:model="occupation" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Lugar de Trabajo</label>
                            <input type="text" wire:model="workplace" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="is_emergency_contact" class="rounded border-gray-300 text-blue-600">
                        Contacto de emergencia
                    </label>
                    <div class="flex justify-end gap-3 pt-3 border-t dark:border-gray-700">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">{{ $editingId ? 'Actualizar' : 'Registrar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Vincular Estudiantes --}}
    @if($showLinkModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showLinkModal', false)"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Vincular Estudiantes</h3>
                <p class="text-sm text-gray-500 mb-4">{{ $linkGuardianName }}</p>

                @if(session()->has('link-message'))
                    <div class="mb-3 rounded-lg bg-green-50 p-2 text-xs text-green-800 dark:bg-green-900/30 dark:text-green-400">{{ session('link-message') }}</div>
                @endif

                {{-- Estudiantes vinculados --}}
                @if(count($linkedStudents) > 0)
                    <div class="mb-4 space-y-1">
                        @foreach($linkedStudents as $ls)
                            <div class="flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-700 p-2">
                                <span class="text-sm text-gray-900 dark:text-white">
                                    {{ $ls['name'] }}
                                    @if($ls['is_primary']) <span class="text-xs text-blue-600 font-bold">(Principal)</span> @endif
                                </span>
                                <button wire:click="unlinkStudent({{ $ls['id'] }})" class="text-xs text-red-500 hover:text-red-700">Desvincular</button>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Buscar y agregar --}}
                <div class="space-y-3 border-t pt-3 dark:border-gray-700">
                    <input type="text" wire:model.live.debounce.300ms="linkStudentSearch" placeholder="Buscar estudiante..." class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />

                    @if($studentResults->count() > 0)
                        <select wire:model="linkStudentId" class="w-full rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Seleccionar...</option>
                            @foreach($studentResults as $sr)
                                <option value="{{ $sr->id }}">{{ $sr->full_name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" wire:model="linkIsPrimary" class="rounded border-gray-300 text-blue-600">
                        Tutor principal
                    </label>

                    <button wire:click="linkStudent" class="w-full rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">Vincular</button>
                </div>

                <div class="flex justify-end pt-3 mt-3 border-t dark:border-gray-700">
                    <button wire:click="$set('showLinkModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
