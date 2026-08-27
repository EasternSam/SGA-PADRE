<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Horario del Docente</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Vista de clases asignadas por día de la semana</p>
        </div>
        @if($totalClasses > 0)
            <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 px-4 py-2">
                <span class="text-sm font-bold text-blue-700 dark:text-blue-400">{{ $totalClasses }} clases/semana</span>
            </div>
        @endif
    </div>

    @if(!$isTeacherOnly)
        <div class="mb-6 max-w-sm">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Docente</label>
            <select wire:model.live="teacher_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Seleccionar docente...</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if(count($scheduleGrid) > 0)
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr class="bg-gradient-to-r from-emerald-600 to-teal-600">
                        <th class="px-3 py-3 text-left text-xs font-bold uppercase text-white w-32">Hora</th>
                        @foreach($dayLabels as $key => $label)
                            <th class="px-3 py-3 text-center text-xs font-bold uppercase text-white">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($scheduleGrid as $row)
                        <tr class="{{ $row['type'] !== 'class' ? 'bg-gray-50 dark:bg-gray-900/50' : '' }}">
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700">
                                <div class="text-xs font-bold text-gray-900 dark:text-white">{{ $row['block_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $row['time_range'] }}</div>
                            </td>
                            @foreach($days as $day)
                                <td class="px-2 py-2 text-center border-r border-gray-100 dark:border-gray-700/50">
                                    @if($row['type'] === 'class')
                                        @php $cell = $row['cells'][$day]; @endphp
                                        @if($cell['has_class'])
                                            <div class="rounded-lg bg-emerald-50 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 p-2 text-left">
                                                <div class="text-xs font-bold text-emerald-800 dark:text-emerald-300">{{ $cell['subject'] }}</div>
                                                <div class="text-[10px] text-gray-600 dark:text-gray-400 mt-0.5">{{ $cell['section'] }}</div>
                                                @if($cell['classroom'])
                                                    <div class="text-[10px] text-gray-500">{{ $cell['classroom'] }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-300">—</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400 italic">{{ \App\Models\TimeBlock::TYPES[$row['type']] ?? '' }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @elseif($teacher_id)
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center dark:border-gray-600">
            <p class="text-sm text-gray-500">Sin horario asignado para este docente</p>
        </div>
    @else
        <div class="rounded-xl border-2 border-dashed border-gray-300 p-12 text-center dark:border-gray-600">
            <p class="text-lg text-gray-400 mb-1">Selecciona un docente</p>
            <p class="text-sm text-gray-400">para ver su horario de clases</p>
        </div>
    @endif
</div>
