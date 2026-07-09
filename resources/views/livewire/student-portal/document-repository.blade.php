<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
    <!-- Banner Superior -->
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_120%,rgba(99,102,241,0.15),transparent)] pointer-events-none"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 text-xs font-semibold uppercase tracking-wider rounded-full border border-indigo-500/30">
                    Mis Recursos
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2 text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-200">
                    Documentos Académicos
                </h1>
                <p class="text-slate-400 mt-2 text-sm sm:text-base">
                    Descarga materiales de apoyo, silabarios y lecturas subidas por tus profesores.
                </p>
            </div>
        </div>
    </div>

    <!-- Si no hay materias cursando -->
    @if ($enrollments->isEmpty())
        <div class="bg-white border border-slate-100 rounded-2xl p-12 text-center max-w-lg mx-auto space-y-4 shadow-sm">
            <div class="inline-flex p-4 rounded-full bg-slate-50 text-slate-400">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-800">No tienes materias inscritas</h3>
            <p class="text-sm text-slate-500">
                Inscríbete en tus asignaturas correspondientes para poder acceder a los materiales compartidos por los profesores.
            </p>
            <a href="{{ route('student.selection') }}" class="inline-block px-5 py-2.5 bg-indigo-600 text-white font-semibold text-sm rounded-lg shadow hover:bg-indigo-500 transition">
                Ir a Selección de Materias
            </a>
        </div>
    @else
        <!-- Lista de Materias del Estudiante -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($enrollments as $enrollment)
                @php
                    $sched = $enrollment->courseSchedule;
                    $mod = $sched->module;
                    $docs = $documentsGrouped[$mod->id] ?? [];
                @endphp
                
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between hover:border-indigo-100 transition duration-300">
                    <!-- Header de la Tarjeta de Materia -->
                    <div class="p-5 bg-slate-50/50 border-b border-slate-100 flex items-start justify-between">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $mod->code }}</span>
                            <h3 class="text-base font-bold text-slate-800 mt-0.5" title="{{ $mod->name }}">{{ $mod->name }}</h3>
                            <p class="text-xs text-slate-400 mt-1">
                                Sección: <span class="text-slate-600 font-bold">{{ $sched->section_name }}</span> | 
                                Créditos: <span class="text-slate-600 font-bold">{{ $mod->credits }}</span>
                            </p>
                        </div>
                        @if ($sched->teacher)
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded-full">Docente</span>
                                <p class="text-xs font-semibold text-slate-700 mt-1">{{ $sched->teacher->name }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Cuerpo de la Tarjeta: Documentos -->
                    <div class="p-5 flex-1 space-y-4">
                        @if (empty($docs))
                            <div class="py-8 text-center text-slate-400 space-y-2">
                                <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293H8.586a1 1 0 01-.707-.293L5.465 13.3A1 1 0 004.757 13H2" />
                                </svg>
                                <p class="text-xs font-medium">Aún no hay archivos compartidos para esta asignatura.</p>
                            </div>
                        @else
                            <div class="divide-y divide-slate-100">
                                @foreach ($docs as $doc)
                                    <div class="py-3 flex items-center justify-between gap-4 first:pt-0 last:pb-0">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-slate-800 truncate" title="{{ $doc->name }}">{{ $doc->name }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                Subido por {{ $doc->uploader->name }} · {{ $doc->created_at->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-2 flex-shrink-0">
                                            <span class="uppercase font-bold text-[9px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">{{ $doc->file_type }}</span>
                                            <a href="{{ $doc->file_path }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-xs rounded-lg transition" title="Descargar">
                                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                </svg>
                                                Descargar
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
