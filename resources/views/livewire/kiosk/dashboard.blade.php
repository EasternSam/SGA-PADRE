<div class="h-full flex flex-col items-center justify-center p-8 relative z-10">
    
    <div class="text-center mb-16 relative">
        <h2 class="text-4xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-slate-400 mb-4 drop-shadow-2xl tracking-tight">¿Qué deseas consultar?</h2>
        <p class="text-xl md:text-2xl text-indigo-200/90 font-medium tracking-widest uppercase">Toca una opción en la pantalla</p>
    </div>

    <!-- Menú de Bloques Gigantes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-5xl">
        
        <!-- Boletín de Notas -->
        <a href="{{ route('kiosk.grades') }}" wire:navigate class="group relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] hover:shadow-[0_0_60px_rgba(245,158,11,0.3)] transition-all duration-500 transform hover:-translate-y-4 active:translate-y-2 active:scale-95 flex flex-col items-center justify-center text-center border border-white/10 hover:border-amber-400/60 overflow-hidden isolate animate-[slideUp_0.8s_ease-out_forwards]">
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-amber-500/20 rounded-full blur-[50px] group-hover:bg-amber-400/40 transition-colors duration-500 animate-pulse-glow"></div>

            <div class="bg-gradient-to-br from-amber-500/20 to-amber-900/40 p-6 rounded-[2rem] mb-8 shadow-[0_0_30px_rgba(245,158,11,0.2)] group-hover:scale-110 transition-transform duration-500 border border-amber-500/40 relative z-10 animate-floating">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-amber-300 drop-shadow-[0_4px_10px_rgba(245,158,11,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide group-hover:text-amber-300 transition-colors duration-300 relative z-10">Mis Calificaciones</h3>
            <p class="text-amber-100/80 text-xl font-medium relative z-10">Consulta tus notas por período y asignatura</p>
        </a>

        <!-- Horario de Clases -->
        <a href="{{ route('kiosk.schedule') }}" wire:navigate class="group relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] hover:shadow-[0_0_60px_rgba(99,102,241,0.3)] transition-all duration-500 transform hover:-translate-y-4 active:translate-y-2 active:scale-95 flex flex-col items-center justify-center text-center border border-white/10 hover:border-indigo-400/60 overflow-hidden isolate animate-[slideUp_0.8s_ease-out_0.1s_forwards] opacity-0">
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-[50px] group-hover:bg-indigo-400/40 transition-colors duration-500 animate-pulse-glow"></div>

            <div class="bg-gradient-to-br from-indigo-500/20 to-indigo-900/40 p-6 rounded-[2rem] mb-8 shadow-[0_0_30px_rgba(99,102,241,0.2)] group-hover:scale-110 transition-transform duration-500 border border-indigo-500/40 relative z-10 animate-floating" style="animation-delay: 0.5s">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-indigo-300 drop-shadow-[0_4px_10px_rgba(99,102,241,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide group-hover:text-indigo-300 transition-colors duration-300 relative z-10">Mi Horario</h3>
            <p class="text-indigo-100/80 text-xl font-medium relative z-10">Tu horario de clases semanal con profesores y aulas</p>
        </a>

        <!-- Asistencia -->
        <a href="{{ route('kiosk.attendance') }}" wire:navigate class="group relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] hover:shadow-[0_0_60px_rgba(16,185,129,0.3)] transition-all duration-500 transform hover:-translate-y-4 active:translate-y-2 active:scale-95 flex flex-col items-center justify-center text-center border border-white/10 hover:border-emerald-400/60 overflow-hidden isolate animate-[slideUp_0.8s_ease-out_0.2s_forwards] opacity-0">
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <div class="absolute -top-10 -left-10 w-48 h-48 bg-emerald-500/20 rounded-full blur-[50px] group-hover:bg-emerald-400/40 transition-colors duration-500 animate-pulse-glow"></div>

            <div class="bg-gradient-to-br from-emerald-500/20 to-emerald-900/40 p-6 rounded-[2rem] mb-8 shadow-[0_0_30px_rgba(16,185,129,0.2)] group-hover:scale-110 transition-transform duration-500 border border-emerald-500/40 relative z-10 animate-floating" style="animation-delay: 1s">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-emerald-300 drop-shadow-[0_4px_10px_rgba(16,185,129,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide group-hover:text-emerald-300 transition-colors duration-300 relative z-10">Mi Asistencia</h3>
            <p class="text-emerald-100/80 text-xl font-medium relative z-10">Revisa tu récord de asistencia, faltas y tardanzas</p>
        </a>

        <!-- Estado de Cuenta -->
        <a href="{{ route('kiosk.finances') }}" wire:navigate class="group relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] hover:shadow-[0_0_60px_rgba(139,92,246,0.3)] transition-all duration-500 transform hover:-translate-y-4 active:translate-y-2 active:scale-95 flex flex-col items-center justify-center text-center border border-white/10 hover:border-violet-400/60 overflow-hidden isolate animate-[slideUp_0.8s_ease-out_0.3s_forwards] opacity-0">
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-violet-500/20 rounded-full blur-[50px] group-hover:bg-violet-400/40 transition-colors duration-500 animate-pulse-glow"></div>

            <div class="bg-gradient-to-br from-violet-500/20 to-violet-900/40 p-6 rounded-[2rem] mb-8 shadow-[0_0_30px_rgba(139,92,246,0.2)] group-hover:scale-110 transition-transform duration-500 border border-violet-500/40 relative z-10 animate-floating" style="animation-delay: 1.5s">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-violet-300 drop-shadow-[0_4px_10px_rgba(139,92,246,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide group-hover:text-violet-300 transition-colors duration-300 relative z-10">Estado de Cuenta</h3>
            <p class="text-violet-100/80 text-xl font-medium relative z-10">Ver pagos pendientes de mensualidad y materiales</p>
        </a>

    </div>
</div>
