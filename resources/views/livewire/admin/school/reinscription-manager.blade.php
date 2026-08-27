<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Reinscripción Masiva</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Promueve y reinscribe estudiantes para el siguiente año escolar</p>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400">{{ session('message') }}</div>
    @endif

    <div class="flex flex-wrap gap-4 mb-6 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Año Origen</label>
            <select wire:model="fromYearId" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Seleccionar...</option>
                @foreach($years as $y) <option value="{{ $y->id }}">{{ $y->name }}</option> @endforeach
            </select>
        </div>
        <div class="text-2xl text-gray-400 pb-2">→</div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Año Destino</label>
            <select wire:model="toYearId" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Seleccionar...</option>
                @foreach($years as $y) <option value="{{ $y->id }}">{{ $y->name }}</option> @endforeach
            </select>
        </div>
        <button wire:click="generatePreview" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Vista Previa</button>
    </div>

    @if(count($preview) > 0)
        {{-- Summary --}}
        @php
            $promoteCount = collect($preview)->where('action', 'promote')->count();
            $retainCount = collect($preview)->where('action', 'retain')->count();
            $excludeCount = collect($preview)->whereIn('action', ['exclude', 'graduated', 'skip'])->count();
        @endphp
        <div class="flex gap-4 mb-4">
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-2 text-center">
                <span class="text-lg font-bold text-green-700">{{ $promoteCount }}</span>
                <p class="text-[10px] text-green-600">A promover</p>
            </div>
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-2 text-center">
                <span class="text-lg font-bold text-red-700">{{ $retainCount }}</span>
                <p class="text-[10px] text-red-600">Repitentes</p>
            </div>
            <div class="rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-4 py-2 text-center">
                <span class="text-lg font-bold text-gray-700">{{ $excludeCount }}</span>
                <p class="text-[10px] text-gray-500">Excluidos</p>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden mb-4">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-3 py-2 text-left text-xs font-bold uppercase text-gray-500">Estudiante</th>
                        <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Grado Actual</th>
                        <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Prom.</th>
                        <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">Resultado</th>
                        <th class="px-3 py-2 text-center text-xs font-bold uppercase text-gray-500">→ Próx. Grado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($preview as $p)
                        <tr class="{{ $p['action'] === 'exclude' || $p['action'] === 'graduated' ? 'opacity-50' : '' }}">
                            <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-white">{{ $p['student_name'] }}</td>
                            <td class="px-3 py-2 text-center text-sm text-gray-600">{{ $p['current_grade'] }} {{ $p['current_section'] }}</td>
                            <td class="px-3 py-2 text-center text-sm font-bold {{ ($p['avg'] ?? 0) >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ $p['avg'] ?? '—' }}</td>
                            <td class="px-3 py-2 text-center text-xs">{{ $p['result'] }}</td>
                            <td class="px-3 py-2 text-center text-sm font-bold text-blue-600">{{ $p['next_grade'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button wire:click="processReinscription" wire:confirm="¿Confirma la reinscripción masiva? Esta acción actualizará los grados de los estudiantes."
            class="rounded-lg bg-green-600 px-6 py-3 text-sm font-bold text-white hover:bg-green-700 transition" {{ $isProcessing ? 'disabled' : '' }}>
            {{ $isProcessing ? 'Procesando...' : 'Ejecutar Reinscripción' }}
        </button>
    @endif
</div>
