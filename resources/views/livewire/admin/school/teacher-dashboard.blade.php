<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">👩‍🏫 Mi Panel Docente</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Resumen de tus secciones, asignaturas y actividad</p>

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $totalStudents }}</div>
            <p class="text-blue-100 text-sm mt-1">🎓 Mis Estudiantes</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $totalSubjects }}</div>
            <p class="text-purple-100 text-sm mt-1">📚 Asignaturas</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-green-500 to-green-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $todayPresent }}</div>
            <p class="text-green-100 text-sm mt-1">✅ Presentes Hoy</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-red-500 to-red-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $todayAbsent }}</div>
            <p class="text-red-100 text-sm mt-1">❌ Ausentes Hoy</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Secciones --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">🏫 Mis Secciones</h2>
            @forelse($sections as $sec)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $sec->gradeLevel?->name ?? '' }} {{ $sec->name }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ $sec->students_count }} est.</span>
                    </div>
                    <div class="flex gap-2">
                        @foreach($teacherSubjects->where('section_id', $sec->id) as $ts)
                            <span class="text-[9px] bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 rounded px-1.5 py-0.5">{{ Str::limit($ts->subject?->name ?? '', 15) }}</span>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-400 text-sm py-4">Sin secciones asignadas</p>
            @endforelse
        </div>

        {{-- Pending Grades --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">📝 Calificaciones Pendientes
                @if($currentPeriod) <span class="text-xs text-gray-400 font-normal ml-1">{{ $currentPeriod->name }}</span> @endif
            </h2>
            @if(count($pendingGrades) > 0)
                <div class="space-y-2">
                    @foreach($pendingGrades as $pg)
                        <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $pg['section'] }}</span>
                                <span class="text-xs text-gray-500 ml-1">— {{ $pg['subject'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs {{ $pg['entered'] > 0 ? 'text-yellow-600' : 'text-red-600' }} font-bold">
                                    {{ $pg['entered'] }}/{{ $pg['total'] }}
                                </span>
                                @if($pg['locked'])
                                    <span class="text-[9px] bg-red-100 text-red-800 rounded px-1.5 py-0.5">🔒 Bloqueado</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-green-500 text-sm py-4">🎉 ¡Todas las calificaciones al día!</p>
            @endif
        </div>
    </div>

    {{-- Recent Grades --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">📊 Calificaciones Recientes</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @forelse($recentGrades as $rg)
                <div class="flex items-center gap-3 py-1.5 px-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                    <span class="text-lg font-bold {{ ($rg->score ?? 0) >= 70 ? 'text-green-600' : 'text-red-600' }}">{{ $rg->score ?? '—' }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-900 dark:text-white truncate">{{ $rg->student?->full_name ?? '—' }}</p>
                        <p class="text-[10px] text-gray-500 truncate">{{ $rg->sectionSubject?->subject?->name ?? '' }}</p>
                    </div>
                    <span class="text-[9px] text-gray-400">{{ $rg->updated_at?->diffForHumans() }}</span>
                </div>
            @empty
                <p class="text-gray-400 text-sm">Sin notas recientes</p>
            @endforelse
        </div>
    </div>
</div>
