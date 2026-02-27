<div class="h-full flex flex-col p-8 max-w-7xl mx-auto w-full relative z-10">
    
    <!-- Encabezado con Botón Volver -->
    <div class="flex items-center justify-between mb-10 shrink-0 animate-[slideUp_0.4s_ease-out_forwards]">
        <div>
            <h2 class="text-4xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-slate-400 drop-shadow-2xl tracking-tight">Mi Horario de Clases</h2>
            <p class="text-xl md:text-2xl text-indigo-200/90 mt-2 font-medium tracking-wide">Aulas y horarios de tus materias activas</p>
        </div>
        <button wire:click="goBack" class="bg-white/[0.03] hover:bg-white/10 active:bg-white/20 backdrop-blur-2xl text-white border border-white/20 hover:border-white/40 font-black tracking-widest py-4 px-8 rounded-[1.5rem] text-2xl shadow-[0_8px_32px_rgba(0,0,0,0.3)] transition-all duration-300 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 flex items-center gap-3 drop-shadow-sm touch-manipulation">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            VOLVER
        </button>
    </div>

    <!-- Lista de Materias (Scrollable) -->
    <div class="flex-1 overflow-y-auto pr-4 space-y-6 pb-20 custom-kiosk-scrollbar">
        @forelse($activeEnrollments as $index => $enrollment)
            @php
                $schedule = $enrollment->courseSchedule;
                $module = $schedule?->module;
                $course = $module?->course;
                $teacher = $schedule?->teacher;
                $classroom = $schedule?->classroom;
                
                // Formatear días
                $days = $schedule?->days_of_week ?? [];
                if (is_string($days)) $days = json_decode($days, true) ?? [];
                $daysStr = is_array($days) ? implode(', ', $days) : ($schedule?->days_of_week ?? 'No definido');

                // Formatear horas
                $start = $schedule?->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') : '--:--';
                $end = $schedule?->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') : '--:--';
            @endphp

            @if($schedule && $module)
            <div class="bg-white/[0.05] backdrop-blur-2xl rounded-[2.5rem] p-8 shadow-[0_8px_32px_rgba(0,0,0,0.3)] flex flex-col md:flex-row items-center justify-between gap-6 border border-white/10 relative overflow-hidden group hover:bg-white/[0.08] transition-colors duration-300 animate-[slideUp_0.6s_ease-out_{{ $index * 0.1 }}s_forwards] opacity-0">
                
                <!-- Highlight lateral -->
                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-indigo-500 to-cyan-500 rounded-l-[2rem] group-hover:w-3 border-r border-indigo-400/50 shadow-[0_0_15px_rgba(99,102,241,0.5)] transition-all duration-300"></div>

                <!-- Info Materia -->
                <div class="flex-1 w-full pl-4 z-10">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="bg-indigo-500/20 text-indigo-200 text-sm font-bold px-3 py-1 rounded-full border border-indigo-500/30 backdrop-blur-md shadow-sm">
                            {{ $course?->name ?? 'Curso' }}
                        </span>
                        <span class="bg-white/10 text-white/80 text-sm font-bold px-3 py-1 rounded-full border border-white/10 backdrop-blur-sm">
                            Sección {{ $schedule->section_name ?? 'N/A' }}
                        </span>
                    </div>
                    <h3 class="text-3xl font-black text-white mb-2 drop-shadow-md">{{ $module->name }}</h3>
                    <p class="text-xl text-indigo-100/70 flex items-center gap-2 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        Prof. {{ $teacher ? $teacher->first_name . ' ' . $teacher->last_name : 'Por asignar' }}
                    </p>
                </div>

                <!-- Info Horario y Aula -->
                <div class="flex flex-col md:flex-row gap-4 shrink-0 z-10">
                    <!-- Bloque Horario Glass -->
                    <div class="bg-white/[0.04] backdrop-blur-xl rounded-[1.5rem] p-6 border border-white/10 flex items-center gap-5 min-w-[280px] shadow-[0_4px_16px_rgba(0,0,0,0.2)]">
                        <div class="bg-indigo-500/20 p-4 rounded-full text-indigo-300 border border-indigo-400/30 shadow-[0_0_20px_rgba(99,102,241,0.3)] animate-pulse-glow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 drop-shadow-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div>
                            <p class="text-indigo-200/90 font-black text-sm mb-1 uppercase tracking-widest drop-shadow-sm">{{ $daysStr }}</p>
                            <p class="text-white text-3xl font-black drop-shadow-md tracking-tight">{{ $start }} <span class="text-indigo-400 opacity-70 px-1">-</span> {{ $end }}</p>
                        </div>
                    </div>

                    <!-- Bloque Aula Glass -->
                    <div class="bg-white/[0.04] backdrop-blur-xl rounded-[1.5rem] p-6 border border-white/10 flex items-center gap-5 min-w-[220px] shadow-[0_4px_16px_rgba(0,0,0,0.2)] relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-32 h-32 bg-cyan-500/20 rounded-full blur-[30px] pointer-events-none group-hover:bg-cyan-400/30 transition-colors duration-300"></div>

                        <div class="bg-cyan-500/20 p-4 rounded-full text-cyan-300 border border-cyan-400/30 shadow-[0_0_20px_rgba(6,182,212,0.3)] animate-pulse-glow" style="animation-delay: 1s">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-9 w-9 drop-shadow-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        </div>
                        <div class="z-10 relative">
                            <p class="text-cyan-200/90 font-black text-sm mb-1 uppercase tracking-widest drop-shadow-sm">Aula</p>
                            <p class="text-white text-4xl font-black truncate drop-shadow-md tracking-tight">{{ $classroom?->name ?? 'TBA' }}</p>
                        </div>
                    </div>
                </div>

            </div>
            @endif
        @empty
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-[2rem] p-16 text-center shadow-[0_8px_32px_rgba(0,0,0,0.2)]">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-[80px] pointer-events-none"></div>
                
                <div class="bg-indigo-500/20 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 border border-indigo-400/30 shadow-[0_0_30px_rgba(99,102,241,0.2)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-indigo-300 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <h3 class="text-3xl font-black text-white mb-2 drop-shadow-md tracking-wide">No tienes clases activas</h3>
                <p class="text-xl text-indigo-100/80 font-medium tracking-wide">Actualmente no estás inscrito en ninguna materia en curso.</p>
            </div>
        @endforelse
    </div>

</div>
