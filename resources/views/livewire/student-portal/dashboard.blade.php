<div class="min-h-screen bg-gray-50 pb-12">
    <!-- Encabezado (Header) -->
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                    Hola, {{ explode(' ', $student?->first_name ?? Auth::user()->name)[0] }}
                </h1>
                <p class="text-sm text-gray-500 font-medium mt-1">
                    @if($student && $section)
                        {{ $section->full_name }} &bull; {{ $student->student_code }}
                    @else
                        Portal del Estudiante
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-200 rounded-full shadow-sm">
                    <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-sm font-medium text-gray-700">Estudiante Activo</span>
                </div>
                <span class="hidden sm:block text-sm font-medium text-gray-500 bg-white px-3 py-1.5 rounded-full border border-gray-200 shadow-sm">
                    {{ now()->locale('es')->isoFormat('D [de] MMMM, Y') }}
                </span>
            </div>
        </div>
    </x-slot>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        @if(!$student)
            <div class="rounded-xl bg-amber-50 p-4 border border-amber-200 shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0 text-amber-500 mt-0.5">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-amber-800">Expediente Escolar no Vinculado</h3>
                        <p class="text-xs text-amber-700 mt-1">Tu cuenta de usuario no se encuentra vinculada a un expediente de estudiante oficial en este centro educativo. Por favor, comunícate con el departamento de Registro o Administración para enlazar tu ficha académica escolar.</p>
                    </div>
                </div>
            </div>
        @else
            <!-- 1. Tarjetas de Estadísticas (KPIs) -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                
                <!-- KPI: Asignaturas -->
                <div class="workspace-panel bg-white p-6 shadow-sm rounded-xl border border-gray-200/80 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mis Asignaturas</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-900 tracking-tight">{{ $subjects->count() }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-gray-400 font-semibold">
                        Año Escolar: {{ $student->academicYear->name ?? 'N/A' }}
                    </div>
                </div>

                <!-- KPI: Asistencia -->
                <div class="workspace-panel bg-white p-6 shadow-sm rounded-xl border border-gray-200/80 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Asistencia Promedio</p>
                            <p class="mt-2 text-3xl font-extrabold text-gray-900 tracking-tight">{{ $attendancePercentage }}%</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $attendancePercentage }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- KPI: Pagos Pendientes -->
                <div class="workspace-panel bg-white p-6 shadow-sm rounded-xl border border-gray-200/80 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Pagos Pendientes</p>
                            <p class="mt-2 text-3xl font-extrabold text-red-600 tracking-tight">RD$ {{ number_format($pendingBalance, 2) }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-600 border border-rose-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 text-xs font-semibold">
                        @if($pendingPayments->count() > 0)
                            <a href="{{ route('student.payments') }}" class="text-rose-600 hover:text-rose-800 transition-colors">Tienes {{ $pendingPayments->count() }} pago(s) pendiente(s) &rarr;</a>
                        @else
                            <span class="text-gray-400">Cuenta al día</span>
                        @endif
                    </div>
                </div>

                <!-- KPI: Grado y Sección -->
                <div class="workspace-panel bg-white p-6 shadow-sm rounded-xl border border-gray-200/80 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Mi Aula</p>
                            <p class="mt-2 text-xl font-extrabold text-gray-900 tracking-tight">{{ $section->full_name ?? 'N/A' }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-600 border border-amber-100">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-gray-400 font-semibold truncate">
                        Tutor: {{ $section->homeroomTeacher->name ?? 'Por asignar' }}
                    </div>
                </div>
            </div>

            <!-- 2. Rejilla Central (Izquierda: Libreta de Calificaciones | Derecha: Asistencia y Finanzas) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- COLUMNA IZQUIERDA (Libreta de Calificaciones) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="workspace-panel bg-white rounded-xl border border-gray-200/80 shadow-sm overflow-hidden">
                        <div class="px-6 py-5 border-b border-gray-150 bg-gray-50/50">
                            <h3 class="text-base font-bold text-gray-900">Libreta Académica de Calificaciones</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Notas oficiales registradas por período de evaluación escolar.</p>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50/30">
                                    <tr>
                                        <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Asignatura</th>
                                        <th scope="col" class="py-3 px-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">P1</th>
                                        <th scope="col" class="py-3 px-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">P2</th>
                                        <th scope="col" class="py-3 px-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">P3</th>
                                        <th scope="col" class="py-3 px-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">P4</th>
                                        <th scope="col" class="py-3 px-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Prom.</th>
                                        <th scope="col" class="py-3 px-4 class=text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Docente</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($subjects as $subject)
                                        @php
                                            $subjectGrades = $grades->where('section_subject_id', $subject->id);
                                            $p1 = $subjectGrades->firstWhere('evaluationPeriod.number', 1)?->score;
                                            $p2 = $subjectGrades->firstWhere('evaluationPeriod.number', 2)?->score;
                                            $p3 = $subjectGrades->firstWhere('evaluationPeriod.number', 3)?->score;
                                            $p4 = $subjectGrades->firstWhere('evaluationPeriod.number', 4)?->score;
                                            
                                            $scores = array_filter([$p1, $p2, $p3, $p4], fn($v) => $v !== null && $v !== '');
                                            $avg = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : null;
                                        @endphp
                                        <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                                            <td class="py-4 px-4 whitespace-nowrap text-sm">
                                                <div class="font-bold text-gray-950">{{ $subject->subject->name ?? 'Asignatura' }}</div>
                                                <span class="text-[10px] text-gray-400 font-semibold block mt-0.5">{{ $subject->subject->area ?? 'General' }}</span>
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700">
                                                {{ $p1 !== null ? round($p1, 0) : '-' }}
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700">
                                                {{ $p2 !== null ? round($p2, 0) : '-' }}
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700">
                                                {{ $p3 !== null ? round($p3, 0) : '-' }}
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-center text-sm font-semibold text-gray-700">
                                                {{ $p4 !== null ? round($p4, 0) : '-' }}
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-center text-sm">
                                                @if ($avg !== null)
                                                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold
                                                        {{ $avg >= $student->gradeLevel->min_passing_score 
                                                            ? 'bg-green-50 text-green-700 border border-green-200/50' 
                                                            : 'bg-rose-50 text-rose-700 border border-rose-200/50' }}">
                                                        {{ $avg }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-4 whitespace-nowrap text-xs text-gray-500 font-medium">
                                                {{ $subject->teacher->name ?? 'Por asignar' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-8 px-4 text-center text-sm text-gray-500 font-medium">
                                                No hay asignaturas registradas en tu grado escolar en este momento.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA (Asistencia & Finanzas) -->
                <div class="lg:col-span-1 space-y-6">
                    
                    <!-- Tarjeta: Asistencias desglosadas -->
                    <div class="workspace-panel bg-white rounded-xl border border-gray-200/80 shadow-sm p-6">
                        <h3 class="text-base font-bold text-gray-900 mb-4">Mi Récord de Asistencias</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-green-500"></span>
                                    <span class="text-xs font-semibold text-gray-600">Presente</span>
                                </div>
                                <span class="text-xs font-bold text-gray-900">{{ $attendanceStats['present'] }} días</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                                    <span class="text-xs font-semibold text-gray-600">Tardanza</span>
                                </div>
                                <span class="text-xs font-bold text-gray-900">{{ $attendanceStats['late'] }} días</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-red-500"></span>
                                    <span class="text-xs font-semibold text-gray-600">Ausente</span>
                                </div>
                                <span class="text-xs font-bold text-gray-900">{{ $attendanceStats['absent'] }} días</span>
                            </div>
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full bg-blue-500"></span>
                                    <span class="text-xs font-semibold text-gray-600">Excusa</span>
                                </div>
                                <span class="text-xs font-bold text-gray-900">{{ $attendanceStats['excused'] }} días</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta: Estado de Cuenta Escolar -->
                    <div class="workspace-panel bg-white rounded-xl border border-gray-200/80 shadow-sm p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-base font-bold text-gray-900">Estado de Cuenta</h3>
                            <a href="{{ route('student.payments') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Ver todos</a>
                        </div>
                        
                        <div class="divide-y divide-gray-100 max-h-[300px] overflow-y-auto pr-1">
                            @forelse($pendingPayments as $p)
                                <div class="py-3 flex justify-between items-start gap-2">
                                    <div>
                                        <div class="text-xs font-bold text-gray-800">{{ $p->concept }}</div>
                                        <span class="text-[10px] text-gray-400 block mt-0.5">Vence: {{ $p->due_date ? $p->due_date->format('d/m/Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-red-600">RD$ {{ number_format($p->amount - $p->paid, 2) }}</div>
                                        <span class="inline-flex items-center rounded bg-red-50 px-1.5 py-0.5 text-[9px] font-medium text-red-700 border border-red-200/30 mt-1">
                                            Pendiente
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-gray-400 font-semibold py-4 text-center">No tienes mensualidades ni cargos pendientes.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>