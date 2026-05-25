<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">🔒 Bloqueo de Calificaciones</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Controla cuándo se bloquea la edición de notas por período</p>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">✅ {{ session('message') }}</div>
    @endif

    @if(count($locks) > 0)
        <div class="space-y-4">
            @foreach($locks as $i => $lock)
                <div class="rounded-xl border {{ $lock['is_locked'] ? 'border-red-300 bg-red-50 dark:bg-red-900/10 dark:border-red-800' : 'border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800' }} p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="text-2xl">
                                @if($lock['is_locked'])
                                    🔒
                                @elseif($lock['lock_date'] && \Carbon\Carbon::parse($lock['lock_date'])->isPast())
                                    🔒
                                @else
                                    🔓
                                @endif
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $lock['period_name'] }}</h3>
                                <p class="text-xs text-gray-500">
                                    @if($lock['is_locked'])
                                        <span class="text-red-600 font-bold">Bloqueado manualmente</span>
                                    @elseif($lock['lock_date'] && \Carbon\Carbon::parse($lock['lock_date'])->isPast())
                                        <span class="text-red-600">Bloqueado automáticamente desde {{ \Carbon\Carbon::parse($lock['lock_date'])->format('d/m/Y') }}</span>
                                    @elseif($lock['lock_date'])
                                        <span class="text-yellow-600">Se bloqueará el {{ \Carbon\Carbon::parse($lock['lock_date'])->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-green-600">Abierto — sin fecha de bloqueo</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div>
                                <label class="block text-[10px] font-medium text-gray-500 mb-1">Fecha de bloqueo</label>
                                <input type="date" wire:model="locks.{{ $i }}.lock_date" wire:change="saveLock({{ $i }})" class="rounded-lg border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <button wire:click="toggleLock({{ $i }})" class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $lock['is_locked'] ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}">
                                {{ $lock['is_locked'] ? '🔓 Desbloquear' : '🔒 Bloquear' }}
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
            <h4 class="text-sm font-bold text-blue-800 dark:text-blue-400 mb-2">ℹ️ ¿Cómo funciona el bloqueo?</h4>
            <ul class="text-xs text-blue-700 dark:text-blue-300 space-y-1">
                <li>• <strong>Fecha de bloqueo:</strong> Las notas se bloquean automáticamente al pasar esta fecha.</li>
                <li>• <strong>Bloqueo manual:</strong> Bloquea inmediatamente sin importar la fecha.</li>
                <li>• <strong>Cuando está bloqueado:</strong> Los docentes no pueden modificar calificaciones de ese período.</li>
                <li>• <strong>El administrador</strong> siempre puede desbloquear y modificar.</li>
            </ul>
        </div>
    @else
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
            <p class="text-sm text-gray-400">No hay períodos de evaluación configurados para el año activo</p>
        </div>
    @endif
</div>
