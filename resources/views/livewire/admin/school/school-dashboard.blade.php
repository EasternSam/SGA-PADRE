<div class="p-4 lg:p-6 space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">Panel Escolar</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $activeYear?->name ?? 'Sin año activo' }} · {{ now()->translatedFormat('l, d \d\e F') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.school.alerts') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Alertas
                @if($alertCount > 0)
                    <span class="bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded-full px-2 py-0.5 text-xs font-bold">{{ $alertCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.school.report-center') }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Reportes
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @php
        $kpis = [
            ['value' => $totalStudents, 'label' => 'Estudiantes', 'sub' => "M: {$males} / F: {$females}", 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'],
            ['value' => $totalSections, 'label' => 'Secciones', 'sub' => null, 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
            ['value' => $totalTeachers, 'label' => 'Docentes', 'sub' => null, 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>'],
            ['value' => $weekPct, 'label' => 'Asistencia Semanal', 'sub' => null, 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>', 'suffix' => '%'],
            ['value' => $alertCount, 'label' => 'Alertas Activas', 'sub' => $criticalAlerts > 0 ? "{$criticalAlerts} críticas" : null, 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>', 'alert' => $criticalAlerts > 0],
        ];
        @endphp
        @foreach($kpis as $kpi)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="text-gray-400 dark:text-gray-500">{!! $kpi['icon'] !!}</div>
                @if(isset($kpi['alert']) && $kpi['alert'])
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                @endif
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($kpi['value']) }}@if(isset($kpi['suffix']))<span class="text-lg">{{ $kpi['suffix'] }}</span>@endif</div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $kpi['label'] }}</p>
            @if($kpi['sub']) <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $kpi['sub'] }}</p> @endif
        </div>
        @endforeach
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Asistencia Hoy --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Asistencia Hoy</h2>
                <span class="text-xs text-gray-400">{{ now()->format('d/m') }}</span>
            </div>
            @if($todayTotal > 0)
                @php $attPct = round(($todayPresent / $todayTotal) * 100); @endphp
                <div class="flex items-center justify-center mb-5">
                    <div class="relative w-28 h-28">
                        <svg class="w-28 h-28 transform -rotate-90" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" stroke="currentColor" stroke-width="8" fill="none" class="text-gray-100 dark:text-gray-700"/>
                            <circle cx="60" cy="60" r="50"
                                stroke="{{ $attPct >= 80 ? '#4f46e5' : ($attPct >= 60 ? '#d97706' : '#dc2626') }}"
                                stroke-width="8" fill="none"
                                stroke-dasharray="{{ 2 * 3.14159 * 50 }}"
                                stroke-dashoffset="{{ 2 * 3.14159 * 50 * (1 - $attPct / 100) }}"
                                stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attPct }}%</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-2">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $todayPresent }}</div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wide">Presentes</div>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-2">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $todayAbsent }}</div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wide">Ausentes</div>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-700/50 p-2">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $todayLate }}</div>
                        <div class="text-[10px] text-gray-500 uppercase tracking-wide">Tardanzas</div>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-10 h-10 mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-sm font-medium">Sin registros hoy</p>
                    <p class="text-xs mt-1">La asistencia aún no ha sido tomada</p>
                </div>
            @endif
        </div>

        {{-- Rendimiento Académico --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Rendimiento</h2>
                <span class="text-xs text-gray-400">{{ $latestPeriod?->name ?? 'N/A' }}</span>
            </div>
            @if($gradeStats['total'] > 0)
                <div class="text-center mb-5">
                    <div class="text-4xl font-bold text-gray-900 dark:text-white">{{ $gradeStats['avg'] }}</div>
                    <p class="text-xs text-gray-500 mt-1">Promedio General</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-lg border border-gray-100 dark:border-gray-700 p-3 text-center">
                        <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $gradeStats['above_passing'] }}</span>
                        <p class="text-[10px] text-green-600 dark:text-green-400 font-medium mt-0.5">Aprobados</p>
                    </div>
                    <div class="rounded-lg border border-gray-100 dark:border-gray-700 p-3 text-center">
                        <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $gradeStats['below_passing'] }}</span>
                        <p class="text-[10px] text-red-600 dark:text-red-400 font-medium mt-0.5">Reprobados</p>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <svg class="w-10 h-10 mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-sm font-medium">Sin calificaciones</p>
                    <p class="text-xs mt-1">No hay datos para este período</p>
                </div>
            @endif
        </div>

        {{-- Disciplina + Quick Actions --}}
        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Disciplina</h2>
                    <span class="text-xs text-gray-400">{{ now()->translatedFormat('F') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                        <span class="text-xl font-bold text-gray-900 dark:text-white">{{ $monthDiscipline }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Incidencias</p>
                        <p class="text-xs text-gray-500">{{ $monthDiscipline === 0 ? 'Sin incidencias este mes' : 'Registros este mes' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Accesos Rápidos</h2>
                <div class="grid grid-cols-2 gap-2">
                    @foreach([
                        ['route' => 'admin.school.attendance', 'label' => 'Asistencia', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>'],
                        ['route' => 'admin.school.grades', 'label' => 'Notas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'],
                        ['route' => 'admin.school.orientation', 'label' => 'Orientación', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>'],
                        ['route' => 'admin.school.payments', 'label' => 'Pagos', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>'],
                    ] as $qa)
                    <a href="{{ route($qa['route']) }}" wire:navigate class="rounded-lg border border-gray-100 dark:border-gray-700 p-3 flex items-center gap-2 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors text-gray-600 dark:text-gray-400">
                        {!! $qa['icon'] !!}
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $qa['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Alertas Activas --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Alertas Activas</h2>
                <a href="{{ route('admin.school.alerts') }}" wire:navigate class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium flex items-center gap-1">
                    Ver todas <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="space-y-1">
                @forelse($activeAlerts as $alert)
                    <div class="flex items-start gap-3 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ $alert->severity === 'critical' ? 'bg-red-100 dark:bg-red-900/30 text-red-600' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-600' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $alert->title }}</p>
                            <p class="text-xs text-gray-400">{{ $alert->student?->full_name ?? '' }} · {{ $alert->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                        <svg class="w-8 h-8 mb-2 text-green-300 dark:text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Sin alertas activas</p>
                        <p class="text-xs mt-0.5">Todo funcionando correctamente</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Ranking Semanal --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Ranking Semanal</h2>
                <span class="text-xs text-gray-400">Asistencia por sección</span>
            </div>
            @if(count($sectionRanking) > 0)
                <div class="space-y-3">
                    @foreach($sectionRanking as $i => $sr)
                        <div class="flex items-center gap-3">
                            <span class="w-6 text-xs font-bold text-gray-400 text-right">{{ $i + 1 }}.</span>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 w-20 truncate">{{ $sr['name'] }}</span>
                            <div class="flex-1">
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-full h-5 overflow-hidden">
                                    <div class="h-5 rounded-full bg-indigo-500 dark:bg-indigo-400 flex items-center justify-end pr-2 transition-all duration-500"
                                        style="width: {{ max($sr['pct'] ?? 0, 8) }}%; opacity: {{ max(0.4, 1 - ($i * 0.08)) }}">
                                        <span class="text-[10px] font-bold text-white">{{ $sr['pct'] ?? '—' }}%</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-gray-400 w-10 text-right">{{ $sr['students'] }} est.</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                    <svg class="w-8 h-8 mb-2 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm font-medium">Sin datos esta semana</p>
                </div>
            @endif
        </div>
    </div>
</div>
