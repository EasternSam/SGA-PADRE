<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Portal de Padres — Tokens</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Genera y administra accesos para padres/tutores</p>
        </div>
        <button wire:click="generate" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700 transition">+ Generar Token</button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 5000)">{{ session('message') }}</div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="rounded-xl bg-teal-50 dark:bg-teal-900/20 border border-teal-200 dark:border-teal-800 p-4 text-center">
            <div class="text-2xl font-bold text-teal-700 dark:text-teal-400">{{ $activeTokens }}</div>
            <p class="text-xs text-teal-600">Tokens Activos</p>
        </div>
        <div class="rounded-xl bg-gray-50 dark:bg-gray-700/30 border border-gray-200 dark:border-gray-600 p-4 text-center">
            <div class="text-2xl font-bold text-gray-700 dark:text-gray-400">{{ $totalTokens }}</div>
            <p class="text-xs text-gray-600">Total Generados</p>
        </div>
    </div>

    <div class="flex gap-3 mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar estudiante..." class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white w-60" />
    </div>

    {{-- Link --}}
    <div class="mb-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 text-sm text-blue-700 dark:text-blue-400">
        URL del Portal: <a href="{{ route('parent.login') }}" target="_blank" class="underline font-medium">{{ route('parent.login') }}</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-900/50">
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Estudiante</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Padre/Tutor</th>
                    <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Token</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Estado</th>
                    <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Último Acceso</th>
                    <th class="px-3 py-2 text-right text-xs font-bold uppercase text-gray-500">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($tokens as $t)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $t->student?->full_name ?? '—' }}</td>
                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $t->guardian?->full_name ?? '—' }}</td>
                        <td class="px-3 py-2 text-xs font-mono text-gray-400">{{ Str::limit($t->token, 20) }}...</td>
                        <td class="px-3 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $t->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $t->is_active ? 'Activo' : 'Revocado' }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-center text-xs text-gray-400">
                            {{ $t->last_accessed_at?->diffForHumans() ?? 'Nunca' }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if($t->is_active)
                                <button wire:click="revoke({{ $t->id }})" wire:confirm="¿Revocar este token?" class="text-xs text-red-600 hover:text-red-800">Revocar</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Sin tokens generados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $tokens->links() }}</div>

    {{-- Generate Modal --}}
    @if($showGenerateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showGenerateModal', false)"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Generar Token de Acceso</h3>

                @if($generatedToken)
                    <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4 mb-4">
                        <p class="text-sm font-bold text-green-800 dark:text-green-400 mb-2">Token Generado:</p>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-3 font-mono text-xs break-all border">{{ $generatedToken }}</div>
                        <p class="text-sm font-bold text-green-800 dark:text-green-400 mt-3 mb-1">PIN:</p>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-3 text-2xl font-bold text-center tracking-[0.5em] border">{{ $generatedPin }}</div>
                        <p class="text-[10px] text-green-600 mt-2">Copie y entregue estas credenciales al padre/tutor. No se mostrarán de nuevo.</p>
                    </div>
                    <div class="flex justify-end">
                        <button wire:click="$set('showGenerateModal', false)" class="rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 dark:bg-gray-700 dark:text-gray-300">Cerrar</button>
                    </div>
                @else
                    <form wire:submit="createToken" class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estudiante *</label>
                            <input type="text" wire:model.live.debounce.300ms="studentSearch" placeholder="Buscar..." class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            @if($studentResults->count() > 0)
                                <select wire:model.live="student_id" class="w-full mt-1 rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($studentResults as $sr) <option value="{{ $sr->id }}">{{ $sr->full_name }}</option> @endforeach
                                </select>
                            @endif
                        </div>
                        @if($guardians->count() > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Padre/Tutor *</label>
                                <select wire:model="guardian_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="">Seleccionar...</option>
                                    @foreach($guardians as $g) <option value="{{ $g->id }}">{{ $g->full_name }} ({{ $g->relationship ?? '' }})</option> @endforeach
                                </select>
                            </div>
                        @elseif($student_id)
                            <div class="rounded-lg bg-yellow-50 p-3 text-sm text-yellow-800">Este estudiante no tiene tutores registrados. Registre uno primero.</div>
                        @endif
                        <div class="flex justify-end gap-3 pt-3 border-t dark:border-gray-700">
                            <button type="button" wire:click="$set('showGenerateModal', false)" class="rounded-lg px-4 py-2 text-sm text-gray-700 bg-gray-100 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                            <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-700">Generar</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
