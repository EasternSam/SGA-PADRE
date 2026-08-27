<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Promoción / Repitencia</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de resultados finales del año escolar</p>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">{{ session('message') }}</div>
    @endif

    <div class="flex flex-wrap gap-4 mb-6 items-end">
        <div class="flex-1 min-w-[250px]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
            <select wire:model.live="section_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Seleccionar sección...</option>
                @foreach($sections as $s)
                    <option value="{{ $s->id }}">{{ $s->gradeLevel?->short_name }} {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        @if(count($students) > 0)
            <button wire:click="autoPromote" wire:confirm="¿Ejecutar promoción automática? (≥70 = Promovido, <70 = Repitente)" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition">
                Auto-Promover
            </button>
        @endif
    </div>

    {{-- Stats --}}
    @if(count($students) > 0)
        <div class="flex gap-4 mb-4">
            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-2 text-center">
                <span class="text-lg font-bold text-blue-700 dark:text-blue-400">{{ $stats['total'] }}</span>
                <p class="text-[10px] text-blue-600">Total</p>
            </div>
            <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 px-4 py-2 text-center">
                <span class="text-lg font-bold text-green-700 dark:text-green-400">{{ $stats['promoted'] }}</span>
                <p class="text-[10px] text-green-600">Promovidos</p>
            </div>
            <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 px-4 py-2 text-center">
                <span class="text-lg font-bold text-red-700 dark:text-red-400">{{ $stats['retained'] }}</span>
                <p class="text-[10px] text-red-600">Repitentes</p>
            </div>
            @if($stats['total'] > 0)
                <div class="rounded-lg bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 px-4 py-2 text-center">
                    <span class="text-lg font-bold text-gray-700 dark:text-gray-300">{{ $stats['total'] - $stats['promoted'] - $stats['retained'] }}</span>
                    <p class="text-[10px] text-gray-500">Pendientes</p>
                </div>
            @endif
        </div>
    @endif

    @if(count($students) > 0)
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">#</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase text-gray-500">Estudiante</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Promedio Final</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Resultado</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase text-gray-500">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($students as $i => $s)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $s['name'] }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($s['average'] !== null)
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-sm font-bold 
                                        {{ $s['average'] >= 90 ? 'bg-green-100 text-green-800' : ($s['average'] >= 70 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $s['average'] }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($s['result'])
                                    <span class="text-sm font-bold" style="color: {{ \App\Models\PromotionRecord::RESULT_COLORS[$s['result']] ?? '#6b7280' }}">
                                        {{ $results[$s['result']] ?? $s['result'] }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Pendiente</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-1">
                                    <button wire:click="setResult({{ $s['id'] }}, 'promoted')" class="rounded px-2 py-1 text-xs {{ $s['result'] === 'promoted' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100' }}"></button>
                                    <button wire:click="setResult({{ $s['id'] }}, 'retained')" class="rounded px-2 py-1 text-xs {{ $s['result'] === 'retained' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}"></button>
                                    <button wire:click="setResult({{ $s['id'] }}, 'transferred')" class="rounded px-2 py-1 text-xs {{ $s['result'] === 'transferred' ? 'bg-purple-600 text-white' : 'bg-purple-50 text-purple-700 hover:bg-purple-100' }}"></button>
                                    <button wire:click="setResult({{ $s['id'] }}, 'withdrawn')" class="rounded px-2 py-1 text-xs {{ $s['result'] === 'withdrawn' ? 'bg-gray-600 text-white' : 'bg-gray-50 text-gray-700 hover:bg-gray-100' }}"></button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($section_id)
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
            <p class="text-sm text-gray-500">Sin estudiantes en esta sección</p>
        </div>
    @endif
</div>
