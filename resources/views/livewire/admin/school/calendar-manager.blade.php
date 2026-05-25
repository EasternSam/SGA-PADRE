<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Calendario Escolar</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Gestión de días lectivos, feriados, eventos y vacaciones</p>
        </div>
        <button wire:click="generateSchoolDays" wire:confirm="¿Generar días lectivos automáticamente (L-V)?" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition">
            ⚡ Auto-Generar Días
        </button>
    </div>

    @if(session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-400" x-data x-init="setTimeout(() => $el.remove(), 3000)">
            ✅ {{ session('message') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/30 dark:text-red-400">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4 text-center border border-blue-200 dark:border-blue-800">
            <span class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $stats['school_days'] }}</span>
            <p class="text-xs text-blue-600 mt-1">📚 Días Lectivos</p>
        </div>
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-4 text-center border border-red-200 dark:border-red-800">
            <span class="text-2xl font-bold text-red-700 dark:text-red-400">{{ $stats['holidays'] }}</span>
            <p class="text-xs text-red-600 mt-1">🎉 Feriados</p>
        </div>
        <div class="rounded-xl bg-cyan-50 dark:bg-cyan-900/20 p-4 text-center border border-cyan-200 dark:border-cyan-800">
            <span class="text-2xl font-bold text-cyan-700 dark:text-cyan-400">{{ $stats['vacations'] }}</span>
            <p class="text-xs text-cyan-600 mt-1">🏖️ Vacaciones</p>
        </div>
        <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 text-center border border-green-200 dark:border-green-800">
            <span class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $stats['events'] }}</span>
            <p class="text-xs text-green-600 mt-1">🎭 Eventos</p>
        </div>
    </div>

    {{-- Calendar Navigation --}}
    <div class="flex items-center justify-between mb-4">
        <button wire:click="prevMonth" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
            ← Anterior
        </button>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white capitalize">{{ $monthName }}</h2>
        <button wire:click="nextMonth" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">
            Siguiente →
        </button>
    </div>

    {{-- Calendar Grid --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden">
        {{-- Day headers --}}
        <div class="grid grid-cols-7 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            @foreach(['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'] as $dayH)
                <div class="py-2 text-center text-xs font-bold uppercase text-gray-500">{{ $dayH }}</div>
            @endforeach
        </div>

        {{-- Day cells --}}
        <div class="grid grid-cols-7 divide-x divide-y divide-gray-100 dark:divide-gray-700">
            @foreach($calendarDays as $cd)
                <button
                    @if($cd['is_current_month'] && !$cd['is_weekend'])
                        wire:click="openDay('{{ $cd['date'] }}')"
                    @endif
                    class="min-h-[80px] p-1.5 text-left transition relative
                        {{ !$cd['is_current_month'] ? 'bg-gray-50 dark:bg-gray-900/30 opacity-40' : '' }}
                        {{ $cd['is_weekend'] ? 'bg-gray-100 dark:bg-gray-900/50' : 'hover:bg-blue-50 dark:hover:bg-blue-900/20 cursor-pointer' }}
                        {{ $cd['is_today'] ? 'ring-2 ring-inset ring-blue-500' : '' }}"
                >
                    <span class="text-xs font-bold {{ $cd['is_today'] ? 'text-blue-600' : ($cd['is_weekend'] ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300') }}">
                        {{ $cd['day'] }}
                    </span>

                    @if($cd['type'] && $cd['type'] !== 'weekend')
                        <div class="mt-0.5 rounded px-1 py-0.5 text-[10px] font-medium text-white truncate"
                             style="background-color: {{ $cd['color'] }}">
                            {{ \App\Models\SchoolCalendar::TYPES[$cd['type']] ?? '' }}
                        </div>
                        @if($cd['name'])
                            <div class="text-[9px] text-gray-600 dark:text-gray-400 mt-0.5 truncate">{{ $cd['name'] }}</div>
                        @endif
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Leyenda --}}
    <div class="mt-4 flex flex-wrap gap-3 text-xs">
        @foreach(\App\Models\SchoolCalendar::TYPES as $type => $label)
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded" style="background-color: {{ \App\Models\SchoolCalendar::TYPE_COLORS[$type] ?? '#9ca3af' }}"></span>
                {{ $label }}
            </span>
        @endforeach
    </div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                    {{ \Carbon\Carbon::parse($editDate)->translatedFormat('l, d \\d\\e F \\d\\e Y') }}
                </h3>
                <p class="text-sm text-gray-500 mb-4">Asignar tipo de día</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo de Día</label>
                        <select wire:model="editType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre (opcional)</label>
                        <input type="text" wire:model="editName" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Ej: Día de la Restauración" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                        <textarea wire:model="editDescription" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                    </div>
                </div>
                <div class="flex justify-between gap-3 pt-4 mt-4 border-t dark:border-gray-700">
                    @if($editId)
                        <button wire:click="deleteEntry" class="rounded-lg px-3 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-400">
                            🗑️ Eliminar
                        </button>
                    @else
                        <div></div>
                    @endif
                    <div class="flex gap-2">
                        <button wire:click="$set('showModal', false)" class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancelar</button>
                        <button wire:click="saveEntry" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 transition">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
