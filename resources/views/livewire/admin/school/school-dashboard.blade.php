<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            🏫 Panel Escolar
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $activeYear?->name ?? 'Sin año activo' }}
        </p>
    </div>

    {{-- KPIs Row --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
        <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $totalStudents }}</div>
            <p class="text-blue-100 text-sm mt-1">🎓 Estudiantes</p>
            <p class="text-blue-200 text-[10px]">M: {{ $males }} / F: {{ $females }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $totalSections }}</div>
            <p class="text-purple-100 text-sm mt-1">🏫 Secciones</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $totalTeachers }}</div>
            <p class="text-emerald-100 text-sm mt-1">👩‍🏫 Docentes</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $weekPct }}%</div>
            <p class="text-amber-100 text-sm mt-1">✅ Asist. Semanal</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br {{ $criticalAlerts > 0 ? 'from-red-500 to-red-600' : 'from-gray-500 to-gray-600' }} p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $alertCount }}</div>
            <p class="text-red-100 text-sm mt-1">🚨 Alertas {{ $criticalAlerts > 0 ? "($criticalAlerts críticas)" : '' }}</p>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Asistencia Hoy --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">✅ Asistencia de Hoy</h2>
            @if($todayTotal > 0)
                <div class="flex items-center justify-center mb-4">
                    <div class="relative w-28 h-28">
                        @php
                            $pct = $todayTotal > 0 ? round(($todayPresent / $todayTotal) * 100) : 0;
                        @endphp
                        <svg class="w-28 h-28 transform -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" stroke="#e5e7eb" stroke-width="10" fill="none"/>
                            <circle cx="60" cy="60" r="50" stroke="{{ $pct >= 80 ? '#10b981' : ($pct >= 60 ? '#f59e0b' : '#ef4444') }}" stroke-width="10" fill="none"
                                stroke-dasharray="{{ 2 * 3.14159 * 50 }}" stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $pct / 100) }}" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pct }}%</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center text-sm">
                    <div><span class="font-bold text-green-600">{{ $todayPresent }}</span><br><span class="text-xs text-gray-500">Presentes</span></div>
                    <div><span class="font-bold text-red-600">{{ $todayAbsent }}</span><br><span class="text-xs text-gray-500">Ausentes</span></div>
                    <div><span class="font-bold text-yellow-600">{{ $todayLate }}</span><br><span class="text-xs text-gray-500">Tardanzas</span></div>
                </div>
            @else
                <p class="text-center text-gray-400 text-sm py-6">Sin registros hoy</p>
            @endif
        </div>

        {{-- Rendimiento Académico --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">📈 Rendimiento Académico</h2>
            @if($gradeStats['total'] > 0)
                <div class="text-center mb-4">
                    <span class="text-4xl font-bold {{ ($gradeStats['avg'] ?? 0) >= 80 ? 'text-green-600' : (($gradeStats['avg'] ?? 0) >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $gradeStats['avg'] }}
                    </span>
                    <p class="text-xs text-gray-500 mt-1">Promedio General {{ $latestPeriod?->name ?? '' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-3">
                        <span class="text-lg font-bold text-green-700">{{ $gradeStats['above70'] }}</span>
                        <p class="text-[10px] text-green-600">Aprobados (≥70)</p>
                    </div>
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-3">
                        <span class="text-lg font-bold text-red-700">{{ $gradeStats['below70'] }}</span>
                        <p class="text-[10px] text-red-600">Reprobados (<70)</p>
                    </div>
                </div>
            @else
                <p class="text-center text-gray-400 text-sm py-6">Sin calificaciones cargadas</p>
            @endif
        </div>

        {{-- Disciplina --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">⚠️ Disciplina ({{ now()->translatedFormat('F') }})</h2>
            <div class="text-center mb-4">
                <span class="text-4xl font-bold {{ $monthDiscipline === 0 ? 'text-green-600' : 'text-gray-900 dark:text-white' }}">
                    {{ $monthDiscipline }}
                </span>
                <p class="text-xs text-gray-500 mt-1">Incidencias este mes</p>
            </div>
            @if($monthDiscipline === 0)
                <p class="text-center text-green-500 text-sm">🎉 ¡Sin incidencias!</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Alertas Recientes --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">🚨 Alertas Activas</h2>
                <a href="{{ route('admin.school.alerts') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Ver todas →</a>
            </div>
            @forelse($activeAlerts as $alert)
                <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <span class="text-lg">
                        {{ $alert->severity === 'critical' ? '🔴' : '🟡' }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $alert->title }}</p>
                        <p class="text-xs text-gray-500">{{ $alert->student?->full_name ?? '' }} · {{ $alert->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-green-500 text-sm py-4">🎉 Sin alertas activas</p>
            @endforelse
        </div>

        {{-- Ranking Secciones --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">🏆 Ranking Asistencia Semanal</h2>
            @if(count($sectionRanking) > 0)
                <div class="space-y-2">
                    @foreach($sectionRanking as $i => $sr)
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold {{ $i === 0 ? 'text-yellow-500' : ($i === 1 ? 'text-gray-400' : ($i === 2 ? 'text-amber-700' : 'text-gray-500')) }} w-5">
                                {{ $i < 3 ? ['🥇','🥈','🥉'][$i] : ($i + 1) }}
                            </span>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-20">{{ $sr['name'] }}</span>
                            <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-5 overflow-hidden">
                                <div class="h-5 rounded-full flex items-center justify-end pr-2 transition-all {{ ($sr['pct'] ?? 0) >= 90 ? 'bg-green-500' : (($sr['pct'] ?? 0) >= 80 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                     style="width: {{ $sr['pct'] ?? 0 }}%">
                                    <span class="text-[10px] font-bold text-white">{{ $sr['pct'] ?? '—' }}%</span>
                                </div>
                            </div>
                            <span class="text-[10px] text-gray-400">{{ $sr['students'] }} est.</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-400 text-sm py-4">Sin datos esta semana</p>
            @endif
        </div>
    </div>
</div>
