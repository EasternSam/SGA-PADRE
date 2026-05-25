<div class="p-4 lg:p-6" x-data="{
    animateCounter(el, target) {
        let current = 0;
        const step = Math.max(1, Math.ceil(target / 30));
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString();
            if (current >= target) clearInterval(timer);
        }, 30);
    }
}">
    {{-- Hero Header --}}
    <div class="relative mb-8 rounded-3xl overflow-hidden bg-gradient-to-r from-indigo-600 via-blue-600 to-purple-600 p-6 lg:p-8 text-white shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djZoLTZWMzRoNnptMC0zMFY0aDZWMGgtNnptMCAxMlY2aDZWMGgtNnptMCAxMlYxOGg2VjBoLTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center shadow-inner">
                        <span class="text-2xl">🏫</span>
                    </div>
                    <div>
                        <h1 class="text-2xl lg:text-3xl font-bold tracking-tight">Panel Escolar</h1>
                        <p class="text-blue-100 text-sm">{{ $activeYear?->name ?? 'Sin año activo' }} · {{ now()->translatedFormat('l, d \d\e F') }}</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.school.alerts') }}" wire:navigate class="rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2 text-sm font-medium hover:bg-white/25 transition-all duration-200 flex items-center gap-2 border border-white/20">
                    🚨 Alertas <span class="bg-white/25 rounded-lg px-2 py-0.5 text-xs font-bold">{{ $alertCount }}</span>
                </a>
                <a href="{{ route('admin.school.report-center') }}" wire:navigate class="rounded-xl bg-white/15 backdrop-blur-sm px-4 py-2 text-sm font-medium hover:bg-white/25 transition-all duration-200 flex items-center gap-2 border border-white/20">
                    📊 Reportes
                </a>
            </div>
        </div>
    </div>

    {{-- KPI Cards with animated counters --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 lg:gap-4 mb-8">
        @php
        $kpis = [
            ['value' => $totalStudents, 'label' => 'Estudiantes', 'icon' => '🎓', 'sub' => "M: {$males} / F: {$females}", 'gradient' => 'from-blue-500/10 to-blue-600/5', 'border' => 'border-blue-200 dark:border-blue-800', 'text' => 'text-blue-600 dark:text-blue-400', 'ring' => 'ring-blue-500/20'],
            ['value' => $totalSections, 'label' => 'Secciones', 'icon' => '🏫', 'sub' => null, 'gradient' => 'from-purple-500/10 to-purple-600/5', 'border' => 'border-purple-200 dark:border-purple-800', 'text' => 'text-purple-600 dark:text-purple-400', 'ring' => 'ring-purple-500/20'],
            ['value' => $totalTeachers, 'label' => 'Docentes', 'icon' => '👩‍🏫', 'sub' => null, 'gradient' => 'from-emerald-500/10 to-emerald-600/5', 'border' => 'border-emerald-200 dark:border-emerald-800', 'text' => 'text-emerald-600 dark:text-emerald-400', 'ring' => 'ring-emerald-500/20'],
            ['value' => $weekPct, 'label' => 'Asist. Semanal', 'icon' => '✅', 'sub' => null, 'gradient' => 'from-amber-500/10 to-amber-600/5', 'border' => 'border-amber-200 dark:border-amber-800', 'text' => 'text-amber-600 dark:text-amber-400', 'ring' => 'ring-amber-500/20', 'suffix' => '%'],
            ['value' => $alertCount, 'label' => 'Alertas', 'icon' => '🚨', 'sub' => $criticalAlerts > 0 ? "{$criticalAlerts} críticas" : null, 'gradient' => $criticalAlerts > 0 ? 'from-red-500/10 to-red-600/5' : 'from-gray-500/10 to-gray-600/5', 'border' => $criticalAlerts > 0 ? 'border-red-200 dark:border-red-800' : 'border-gray-200 dark:border-gray-700', 'text' => $criticalAlerts > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400', 'ring' => $criticalAlerts > 0 ? 'ring-red-500/20' : 'ring-gray-500/10'],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="group relative rounded-2xl border {{ $kpi['border'] }} bg-gradient-to-br {{ $kpi['gradient'] }} p-4 lg:p-5 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 ring-1 {{ $kpi['ring'] }}">
            <div class="flex items-start justify-between mb-2">
                <span class="text-xl opacity-80 group-hover:scale-110 transition-transform duration-300">{{ $kpi['icon'] }}</span>
            </div>
            <div class="text-2xl lg:text-3xl font-bold {{ $kpi['text'] }}" x-init="animateCounter($el, {{ $kpi['value'] }})">0</div>
            @if(isset($kpi['suffix'])) <span class="{{ $kpi['text'] }} text-lg font-bold">{{ $kpi['suffix'] }}</span> @endif
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">{{ $kpi['label'] }}</p>
            @if($kpi['sub']) <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $kpi['sub'] }}</p> @endif
        </div>
        @endforeach
    </div>

    {{-- Main Data Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 mb-6">

        {{-- Attendance Ring Chart --}}
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-700/50 dark:bg-gray-800/80 backdrop-blur-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Asistencia Hoy</h2>
                <span class="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-500 rounded-full px-2.5 py-1 font-medium">{{ now()->format('d/m') }}</span>
            </div>
            @if($todayTotal > 0)
                <div class="flex items-center justify-center mb-5">
                    <div class="relative w-32 h-32" x-data="{ pct: 0 }" x-init="setTimeout(() => pct = {{ $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100) : 0 }}, 300)">
                        @php $attPct = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100) : 0; @endphp
                        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" stroke="#e5e7eb" stroke-width="8" fill="none" class="dark:stroke-gray-700"/>
                            <circle cx="60" cy="60" r="50" 
                                stroke="{{ $attPct >= 80 ? '#10b981' : ($attPct >= 60 ? '#f59e0b' : '#ef4444') }}" 
                                stroke-width="8" fill="none"
                                stroke-dasharray="{{ 2 * 3.14159 * 50 }}" 
                                stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $attPct / 100) }}" 
                                stroke-linecap="round"
                                class="transition-all duration-1000 ease-out"
                                style="filter: drop-shadow(0 0 6px {{ $attPct >= 80 ? 'rgba(16,185,129,0.4)' : ($attPct >= 60 ? 'rgba(245,158,11,0.4)' : 'rgba(239,68,68,0.4)') }});"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $attPct }}%</span>
                            <span class="text-[10px] text-gray-400 font-medium">asistencia</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-1">
                    @foreach([['Presentes', $todayPresent, 'bg-emerald-500', 'text-emerald-700 dark:text-emerald-400'], ['Ausentes', $todayAbsent, 'bg-red-500', 'text-red-700 dark:text-red-400'], ['Tardanzas', $todayLate, 'bg-amber-500', 'text-amber-700 dark:text-amber-400']] as $att)
                    <div class="text-center rounded-xl bg-gray-50 dark:bg-gray-700/50 p-2.5">
                        <div class="w-2 h-2 rounded-full {{ $att[2] }} mx-auto mb-1.5"></div>
                        <div class="text-lg font-bold {{ $att[3] }}">{{ $att[1] }}</div>
                        <div class="text-[9px] text-gray-400 font-medium uppercase tracking-wide">{{ $att[0] }}</div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <span class="text-2xl opacity-50">📋</span>
                    </div>
                    <p class="text-sm font-medium">Sin registros hoy</p>
                    <p class="text-xs mt-1">La asistencia aún no ha sido tomada</p>
                </div>
            @endif
        </div>

        {{-- Academic Performance --}}
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-700/50 dark:bg-gray-800/80 backdrop-blur-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Rendimiento</h2>
                <span class="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-500 rounded-full px-2.5 py-1 font-medium">{{ $latestPeriod?->name ?? 'N/A' }}</span>
            </div>
            @if($gradeStats['total'] > 0)
                <div class="flex items-center justify-center mb-5">
                    <div class="relative">
                        <div class="w-28 h-28 rounded-full flex items-center justify-center border-4 {{ ($gradeStats['avg'] ?? 0) >= 80 ? 'border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20' : (($gradeStats['avg'] ?? 0) >= 70 ? 'border-amber-400 bg-amber-50 dark:bg-amber-900/20' : 'border-red-400 bg-red-50 dark:bg-red-900/20') }}" style="box-shadow: 0 0 20px {{ ($gradeStats['avg'] ?? 0) >= 80 ? 'rgba(16,185,129,0.15)' : (($gradeStats['avg'] ?? 0) >= 70 ? 'rgba(245,158,11,0.15)' : 'rgba(239,68,68,0.15)') }};">
                            <div class="text-center">
                                <span class="text-3xl font-black {{ ($gradeStats['avg'] ?? 0) >= 80 ? 'text-emerald-600' : (($gradeStats['avg'] ?? 0) >= 70 ? 'text-amber-600' : 'text-red-600') }}">{{ $gradeStats['avg'] }}</span>
                                <p class="text-[9px] text-gray-400 font-medium">PROMEDIO</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-emerald-50/80 dark:bg-emerald-900/20 p-3 text-center border border-emerald-100 dark:border-emerald-800/50">
                        <span class="text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ $gradeStats['above70'] }}</span>
                        <p class="text-[10px] text-emerald-600 font-medium mt-0.5">✅ Aprobados</p>
                    </div>
                    <div class="rounded-xl bg-red-50/80 dark:bg-red-900/20 p-3 text-center border border-red-100 dark:border-red-800/50">
                        <span class="text-xl font-bold text-red-700 dark:text-red-400">{{ $gradeStats['below70'] }}</span>
                        <p class="text-[10px] text-red-600 font-medium mt-0.5">❌ Reprobados</p>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-3">
                        <span class="text-2xl opacity-50">📊</span>
                    </div>
                    <p class="text-sm font-medium">Sin calificaciones</p>
                    <p class="text-xs mt-1">No hay datos para este período</p>
                </div>
            @endif
        </div>

        {{-- Quick Actions + Discipline --}}
        <div class="space-y-4">
            {{-- Discipline Card --}}
            <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-700/50 dark:bg-gray-800/80 backdrop-blur-sm">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Disciplina</h2>
                    <span class="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-500 rounded-full px-2.5 py-1 font-medium">{{ now()->translatedFormat('F') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center {{ $monthDiscipline === 0 ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                        <span class="text-2xl font-black {{ $monthDiscipline === 0 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $monthDiscipline }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Incidencias</p>
                        <p class="text-xs text-gray-500">{{ $monthDiscipline === 0 ? '🎉 ¡Sin incidencias este mes!' : 'Registros este mes' }}</p>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-700/50 dark:bg-gray-800/80 backdrop-blur-sm">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-3">Accesos Rápidos</h2>
                <div class="grid grid-cols-2 gap-2">
                    @foreach([
                        ['route' => 'admin.school.attendance', 'icon' => '✅', 'label' => 'Asistencia', 'color' => 'hover:bg-emerald-50 dark:hover:bg-emerald-900/20 hover:border-emerald-200'],
                        ['route' => 'admin.school.grades', 'icon' => '📝', 'label' => 'Notas', 'color' => 'hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-200'],
                        ['route' => 'admin.school.orientation', 'icon' => '🧠', 'label' => 'Orientación', 'color' => 'hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-200'],
                        ['route' => 'admin.school.payments', 'icon' => '💰', 'label' => 'Pagos', 'color' => 'hover:bg-amber-50 dark:hover:bg-amber-900/20 hover:border-amber-200'],
                    ] as $qa)
                    <a href="{{ route($qa['route']) }}" wire:navigate class="rounded-xl border border-gray-100 dark:border-gray-700 p-3 flex items-center gap-2.5 transition-all duration-200 {{ $qa['color'] }} group">
                        <span class="text-base group-hover:scale-110 transition-transform duration-200">{{ $qa['icon'] }}</span>
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $qa['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
        {{-- Alerts Feed --}}
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-700/50 dark:bg-gray-800/80 backdrop-blur-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Alertas Activas</h2>
                <a href="{{ route('admin.school.alerts') }}" wire:navigate class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold uppercase tracking-wider flex items-center gap-1">
                    Ver todas <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="space-y-1">
                @forelse($activeAlerts as $alert)
                    <div class="flex items-start gap-3 p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150 group">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $alert->severity === 'critical' ? 'bg-red-100 dark:bg-red-900/30' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                            <span class="text-sm">{{ $alert->severity === 'critical' ? '🔴' : '🟡' }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $alert->title }}</p>
                            <p class="text-[11px] text-gray-400">{{ $alert->student?->full_name ?? '' }} · {{ $alert->created_at->diffForHumans() }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-6 text-gray-400">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center mb-2">
                            <span class="text-xl">✨</span>
                        </div>
                        <p class="text-sm font-medium text-emerald-600">¡Sin alertas activas!</p>
                        <p class="text-xs mt-0.5">Todo está funcionando correctamente</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Section Ranking --}}
        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm dark:border-gray-700/50 dark:bg-gray-800/80 backdrop-blur-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Ranking Semanal</h2>
                <span class="text-[10px] bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 rounded-full px-2.5 py-1 font-bold">Asistencia</span>
            </div>
            @if(count($sectionRanking) > 0)
                <div class="space-y-2.5">
                    @foreach($sectionRanking as $i => $sr)
                        <div class="flex items-center gap-3 group">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-sm font-bold flex-shrink-0
                                {{ $i === 0 ? 'bg-amber-100 dark:bg-amber-900/30' : ($i === 1 ? 'bg-gray-100 dark:bg-gray-700' : ($i === 2 ? 'bg-orange-100 dark:bg-orange-900/30' : 'bg-gray-50 dark:bg-gray-700/50')) }}">
                                {{ $i < 3 ? ['🥇','🥈','🥉'][$i] : $i + 1 }}
                            </div>
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 w-20 truncate">{{ $sr['name'] }}</span>
                            <div class="flex-1">
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-full h-6 overflow-hidden">
                                    <div class="h-6 rounded-full flex items-center justify-end pr-2.5 transition-all duration-700 ease-out
                                        {{ ($sr['pct'] ?? 0) >= 90 ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : (($sr['pct'] ?? 0) >= 80 ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-gradient-to-r from-red-400 to-red-500') }}"
                                        style="width: {{ max($sr['pct'] ?? 0, 8) }}%">
                                        <span class="text-[10px] font-bold text-white drop-shadow-sm">{{ $sr['pct'] ?? '—' }}%</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-gray-400 font-medium w-10 text-right">{{ $sr['students'] }} est.</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-6 text-gray-400">
                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center mb-2">
                        <span class="text-xl opacity-50">🏆</span>
                    </div>
                    <p class="text-sm font-medium">Sin datos esta semana</p>
                </div>
            @endif
        </div>
    </div>
</div>
