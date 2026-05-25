<div class="p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            🏫 {{ $schoolConfig?->school_name ?? 'Centro Educativo' }}
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $activeYear?->name ?? 'Sin año activo' }}
            @if($schoolConfig?->minerd_code) · Código MINERD: {{ $schoolConfig->minerd_code }} @endif
            @if($schoolConfig?->shift) · Tanda: {{ ucfirst(str_replace('_', ' ', $schoolConfig->shift)) }} @endif
        </p>
    </div>

    {{-- KPIs Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $enrollment['total_students'] }}</div>
            <p class="text-blue-100 text-sm mt-1">🎓 Estudiantes Activos</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $enrollment['total_sections'] }}</div>
            <p class="text-purple-100 text-sm mt-1">🏫 Secciones</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $enrollment['total_subjects'] }}</div>
            <p class="text-emerald-100 text-sm mt-1">📚 Asignaturas</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 p-5 text-white shadow-lg">
            <div class="text-3xl font-bold">{{ $enrollment['grade_levels'] }}</div>
            <p class="text-amber-100 text-sm mt-1">📊 Grados</p>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Asistencia Hoy --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">✅ Asistencia de Hoy</h2>
            @if($todayAttendance['total'] > 0)
                <div class="flex items-center justify-center mb-4">
                    <div class="relative w-28 h-28">
                        @php
                            $pct = $todayAttendance['total'] > 0 ? round(($todayAttendance['present'] / $todayAttendance['total']) * 100) : 0;
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
                    <div><span class="font-bold text-green-600">{{ $todayAttendance['present'] }}</span><br><span class="text-xs text-gray-500">Presentes</span></div>
                    <div><span class="font-bold text-red-600">{{ $todayAttendance['absent'] }}</span><br><span class="text-xs text-gray-500">Ausentes</span></div>
                    <div><span class="font-bold text-yellow-600">{{ $todayAttendance['late'] }}</span><br><span class="text-xs text-gray-500">Tardanzas</span></div>
                </div>
            @else
                <p class="text-center text-gray-400 text-sm py-6">Sin registros hoy</p>
            @endif
        </div>

        {{-- Estudiantes por Grado --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">📊 Estudiantes por Grado</h2>
            @if(count($studentsByGrade) > 0)
                <div class="space-y-2">
                    @php $maxCount = collect($studentsByGrade)->max('count') ?: 1; @endphp
                    @foreach($studentsByGrade as $sg)
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-300 w-12 text-right">{{ $sg['name'] }}</span>
                            <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-5 overflow-hidden">
                                <div class="bg-blue-500 h-5 rounded-full flex items-center justify-end pr-2 transition-all duration-500" style="width: {{ ($sg['count'] / $maxCount) * 100 }}%">
                                    <span class="text-[10px] font-bold text-white">{{ $sg['count'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-400 text-sm py-6">Sin datos</p>
            @endif
        </div>

        {{-- Disciplina Este Mes --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">⚠️ Disciplina ({{ now()->translatedFormat('F') }})</h2>
            <div class="text-center mb-4">
                <span class="text-4xl font-bold {{ $disciplineMonth['total'] === 0 ? 'text-green-600' : 'text-gray-900 dark:text-white' }}">
                    {{ $disciplineMonth['total'] }}
                </span>
                <p class="text-xs text-gray-500 mt-1">Incidencias este mes</p>
            </div>
            @if($disciplineMonth['total'] > 0)
                <div class="grid grid-cols-3 gap-2 text-center text-sm">
                    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-2">
                        <span class="font-bold text-yellow-700 dark:text-yellow-400">{{ $disciplineMonth['leve'] }}</span>
                        <p class="text-[10px] text-yellow-600">Leves</p>
                    </div>
                    <div class="rounded-lg bg-orange-50 dark:bg-orange-900/20 p-2">
                        <span class="font-bold text-orange-700 dark:text-orange-400">{{ $disciplineMonth['grave'] }}</span>
                        <p class="text-[10px] text-orange-600">Graves</p>
                    </div>
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-2">
                        <span class="font-bold text-red-700 dark:text-red-400">{{ $disciplineMonth['muy_grave'] }}</span>
                        <p class="text-[10px] text-red-600">Muy Graves</p>
                    </div>
                </div>
            @else
                <p class="text-center text-green-500 text-sm">🎉 ¡Sin incidencias!</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Inscripciones --}}
        @if(count($enrollmentStatus) > 0)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">📋 Estado de Inscripciones</h2>
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="rounded-xl bg-yellow-50 dark:bg-yellow-900/20 p-4">
                    <span class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ $enrollmentStatus['pending'] }}</span>
                    <p class="text-xs text-yellow-600 mt-1">⏳ Pendientes</p>
                </div>
                <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4">
                    <span class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ $enrollmentStatus['approved'] }}</span>
                    <p class="text-xs text-blue-600 mt-1">✅ Aprobadas</p>
                </div>
                <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4">
                    <span class="text-2xl font-bold text-green-700 dark:text-green-400">{{ $enrollmentStatus['enrolled'] }}</span>
                    <p class="text-xs text-green-600 mt-1">🎓 Matriculados</p>
                </div>
            </div>
        </div>
        @endif

        {{-- Comunicaciones Recientes --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">📢 Comunicaciones Recientes</h2>
                <a href="{{ route('admin.school.announcements') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Ver todas →</a>
            </div>
            @forelse($recentAnnouncements as $a)
                <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
                    <span class="text-lg">{{ \App\Models\SchoolAnnouncement::TYPES[$a->type] ? explode(' ', \App\Models\SchoolAnnouncement::TYPES[$a->type])[0] : '📢' }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $a->title }}</p>
                        <p class="text-xs text-gray-500">{{ $a->publish_date->format('d/m/Y') }}</p>
                    </div>
                    @if($a->priority === 'urgent')
                        <span class="text-xs text-red-600 font-bold">🔴</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-gray-400 text-sm py-4">Sin comunicaciones</p>
            @endforelse
        </div>

        {{-- Rendimiento Académico --}}
        @if(count($gradePerformance) > 0)
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:col-span-2">
            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">📈 Rendimiento Académico por Grado</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($gradePerformance as $gp)
                    <div class="rounded-xl text-center p-4 {{ $gp['avg'] >= 80 ? 'bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800' : ($gp['avg'] >= 70 ? 'bg-yellow-50 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800' : 'bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800') }}">
                        <div class="text-xl font-bold {{ $gp['avg'] >= 80 ? 'text-green-700 dark:text-green-400' : ($gp['avg'] >= 70 ? 'text-yellow-700 dark:text-yellow-400' : 'text-red-700 dark:text-red-400') }}">
                            {{ $gp['avg'] }}
                        </div>
                        <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mt-1">{{ $gp['name'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
