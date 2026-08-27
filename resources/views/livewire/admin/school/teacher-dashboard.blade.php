<div class="p-4 lg:p-6">
    {{-- Personal Header --}}
    <div class="relative mb-8 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h1 class="gb-page-title !mb-0">{{ auth()->user()->name }}</h1>
                    <p class="gb-page-subtitle">Panel docente · Año {{ now()->year }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.school.attendance') }}" wire:navigate class="gb-btn gb-btn-secondary text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Tomar Asistencia
                </a>
                <a href="{{ route('admin.school.grades') }}" wire:navigate class="gb-btn gb-btn-primary text-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Cargar Notas
                </a>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['value' => $totalStudents, 'label' => 'Mis Estudiantes', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>', 'bg' => 'bg-blue-50 dark:bg-blue-900/20', 'color' => 'text-blue-600 dark:text-blue-400'],
            ['value' => $totalSubjects, 'label' => 'Asignaturas', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>', 'bg' => 'bg-purple-50 dark:bg-purple-900/20', 'color' => 'text-purple-600 dark:text-purple-400'],
            ['value' => $todayPresent, 'label' => 'Presentes Hoy', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'color' => 'text-emerald-600 dark:text-emerald-400'],
            ['value' => $todayAbsent, 'label' => 'Ausentes Hoy', 'icon' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'bg' => $todayAbsent > 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-gray-700', 'color' => $todayAbsent > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500'],
        ] as $kpi)
        <div class="gb-metric gb-lift">
            <div class="flex items-center gap-3">
                <div class="gb-metric-icon {{ $kpi['bg'] }} {{ $kpi['color'] }}">{!! $kpi['icon'] !!}</div>
                <div>
                    <div class="gb-metric-value !text-2xl !mt-0">{{ $kpi['value'] }}</div>
                    <div class="gb-metric-label !mt-0">{{ $kpi['label'] }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6 mb-6">
        {{-- Sections --}}
        <div class="workspace-panel">
            <div class="flex items-center justify-between mb-4">
                <h2 class="gb-section-title">Mis Secciones</h2>
                <span class="text-[10px] bg-gray-100 dark:bg-gray-700 text-gray-500 rounded-full px-2.5 py-1 font-medium">{{ $sections->count() }} secciones</span>
            </div>
            <div class="space-y-1">
                @forelse($sections as $sec)
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors group">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $sec->gradeLevel?->short_name ?? '?' }}</div>
                            <div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $sec->gradeLevel?->name ?? '' }} {{ $sec->name }}</span>
                                <span class="text-xs text-gray-400 ml-1">· {{ $sec->students_count }} est.</span>
                            </div>
                        </div>
                        <div class="flex gap-1.5 flex-wrap justify-end max-w-[180px]">
                            @foreach($teacherSubjects->where('section_id', $sec->id) as $ts)
                                <span class="gb-badge gb-badge-info !py-0.5 !px-2 !text-[9px]">{{ Str::limit($ts->subject?->name ?? '', 12) }}</span>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="gb-empty-state !py-8">
                        <div class="gb-empty-icon !w-12 !h-12"></div>
                        <div class="gb-empty-title !text-sm">Sin secciones asignadas</div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pending Grades --}}
        <div class="workspace-panel">
            <div class="flex items-center justify-between mb-4">
                <h2 class="gb-section-title">Calificaciones Pendientes</h2>
                @if($currentPeriod) <span class="text-[10px] bg-amber-50 dark:bg-amber-900/20 text-amber-600 rounded-full px-2.5 py-1 font-bold">{{ $currentPeriod->name }}</span> @endif
            </div>
            @if(count($pendingGrades) > 0)
                <div class="space-y-1">
                    @foreach($pendingGrades as $pg)
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg {{ $pg['entered'] === 0 ? 'bg-red-50 dark:bg-red-900/20' : 'bg-amber-50 dark:bg-amber-900/20' }} flex items-center justify-center">
                                    <svg class="w-4 h-4 {{ $pg['entered'] === 0 ? 'text-red-500' : 'text-amber-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </div>
                                <div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $pg['section'] }}</span>
                                    <span class="text-xs text-gray-500 block">{{ $pg['subject'] }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                {{-- Progress --}}
                                <div class="w-16 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $pg['entered'] === 0 ? 'bg-red-400' : 'bg-amber-400' }}" style="width: {{ $pg['total'] > 0 ? ($pg['entered'] / $pg['total']) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $pg['entered'] === 0 ? 'text-red-600' : 'text-amber-600' }}">{{ $pg['entered'] }}/{{ $pg['total'] }}</span>
                                @if($pg['locked'])
                                    <span class="gb-badge gb-badge-danger !py-0.5 !px-1.5 !text-[8px]"></span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center mb-2">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-semibold text-emerald-600">¡Todas al día!</p>
                    <p class="text-xs text-gray-400 mt-0.5">No hay calificaciones pendientes</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Grades --}}
    <div class="workspace-panel">
        <div class="flex items-center justify-between mb-4">
            <h2 class="gb-section-title">Actividad Reciente</h2>
            <span class="text-[10px] text-gray-400 font-medium">Últimas calificaciones registradas</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @forelse($recentGrades as $rg)
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50/80 dark:bg-gray-700/30 border border-gray-100 dark:border-gray-700/50 hover:border-gray-200 dark:hover:border-gray-600 transition-colors">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg font-black flex-shrink-0
                        {{ ($rg->score ?? 0) >= 89 ? 'bg-emerald-100 dark:bg-emerald-900/20 text-emerald-700' : (($rg->score ?? 0) >= 70 ? 'bg-blue-100 dark:bg-blue-900/20 text-blue-700' : 'bg-red-100 dark:bg-red-900/20 text-red-700') }}">
                        {{ $rg->score ?? '—' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $rg->student?->full_name ?? '—' }}</p>
                        <p class="text-[11px] text-gray-400 truncate">{{ $rg->sectionSubject?->subject?->name ?? '' }}</p>
                    </div>
                    <span class="text-[10px] text-gray-400 flex-shrink-0">{{ $rg->updated_at?->diffForHumans() }}</span>
                </div>
            @empty
                <div class="col-span-2 gb-empty-state !py-6">
                    <div class="gb-empty-icon !w-10 !h-10 !text-base"></div>
                    <div class="gb-empty-desc">Sin notas recientes</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
