<div class="h-full flex flex-col items-center justify-center p-8 relative z-10">
    
    <div class="text-center mb-16 relative">
        <h2 class="text-4xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-slate-400 mb-4 drop-shadow-2xl tracking-tight">¿Qué deseas hacer hoy?</h2>
        <p class="text-xl md:text-2xl text-indigo-200/90 font-medium tracking-widest uppercase">Selecciona una de las opciones tocando la pantalla.</p>
    </div>

    <!-- Menú de Bloques Gigantes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-5xl">
        
        <!-- Estado de Cuenta / Pagos -->
        <a href="{{ route('kiosk.finances') }}" wire:navigate class="group relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] hover:shadow-[0_0_60px_rgba(16,185,129,0.3)] transition-all duration-500 transform hover:-translate-y-4 active:translate-y-2 active:scale-95 flex flex-col items-center justify-center text-center border border-white/10 hover:border-emerald-400/60 overflow-hidden isolate animate-[slideUp_0.8s_ease-out_forwards]">
            <!-- Glass Highlight -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <!-- Glow Effect -->
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-emerald-500/20 rounded-full blur-[50px] group-hover:bg-emerald-400/40 transition-colors duration-500 animate-pulse-glow"></div>
            <!-- Shimmer Effect -->
            <div class="absolute inset-0 -translate-x-full group-hover:animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/10 to-transparent pointer-events-none z-20"></div>

            <div class="bg-gradient-to-br from-emerald-500/20 to-emerald-900/40 p-6 rounded-[2rem] mb-8 shadow-[0_0_30px_rgba(16,185,129,0.2)] group-hover:scale-110 transition-transform duration-500 border border-emerald-500/40 relative z-10 animate-floating">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-emerald-300 drop-shadow-[0_4px_10px_rgba(16,185,129,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide group-hover:text-emerald-300 transition-colors duration-300 relative z-10">Estado de Cuenta</h3>
            <p class="text-emerald-100/80 text-xl font-medium relative z-10">Ver balances pendientes y realizar pagos con tarjeta</p>
        </a>

        <!-- Horario de Clases -->
        <a href="{{ route('kiosk.schedule') }}" wire:navigate class="group relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] hover:shadow-[0_0_60px_rgba(99,102,241,0.3)] transition-all duration-500 transform hover:-translate-y-4 active:translate-y-2 active:scale-95 flex flex-col items-center justify-center text-center border border-white/10 hover:border-indigo-400/60 overflow-hidden isolate animate-[slideUp_0.8s_ease-out_0.1s_forwards] opacity-0">
            <!-- Glass Highlight -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <!-- Glow Effect -->
             <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-indigo-500/20 rounded-full blur-[50px] group-hover:bg-indigo-400/40 transition-colors duration-500 animate-pulse-glow"></div>
             <!-- Shimmer Effect -->
            <div class="absolute inset-0 -translate-x-full group-hover:animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/10 to-transparent pointer-events-none z-20"></div>

            <div class="bg-gradient-to-br from-indigo-500/20 to-indigo-900/40 p-6 rounded-[2rem] mb-8 shadow-[0_0_30px_rgba(99,102,241,0.2)] group-hover:scale-110 transition-transform duration-500 border border-indigo-500/40 relative z-10 animate-floating" style="animation-delay: 0.5s">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-indigo-300 drop-shadow-[0_4px_10px_rgba(99,102,241,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide group-hover:text-indigo-300 transition-colors duration-300 relative z-10">Mi Horario</h3>
            <p class="text-indigo-100/80 text-xl font-medium relative z-10">Consultar tus aulas, profesores y horas de clase</p>
        </a>

        <!-- Récord de Notas -->
        <a href="{{ route('kiosk.grades') }}" wire:navigate class="group relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] hover:shadow-[0_0_60px_rgba(245,158,11,0.3)] transition-all duration-500 transform hover:-translate-y-4 active:translate-y-2 active:scale-95 flex flex-col items-center justify-center text-center border border-white/10 hover:border-amber-400/60 overflow-hidden isolate animate-[slideUp_0.8s_ease-out_0.2s_forwards] opacity-0">
            <!-- Glass Highlight -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <!-- Glow Effect -->
             <div class="absolute -top-10 -left-10 w-48 h-48 bg-amber-500/20 rounded-full blur-[50px] group-hover:bg-amber-400/40 transition-colors duration-500 animate-pulse-glow"></div>
             <!-- Shimmer Effect -->
            <div class="absolute inset-0 -translate-x-full group-hover:animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/10 to-transparent pointer-events-none z-20"></div>

            <div class="bg-gradient-to-br from-amber-500/20 to-amber-900/40 p-6 rounded-[2rem] mb-8 shadow-[0_0_30px_rgba(245,158,11,0.2)] group-hover:scale-110 transition-transform duration-500 border border-amber-500/40 relative z-10 animate-floating" style="animation-delay: 1s">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-amber-300 drop-shadow-[0_4px_10px_rgba(245,158,11,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide group-hover:text-amber-300 transition-colors duration-300 relative z-10">Récord de Notas</h3>
            <p class="text-amber-100/80 text-xl font-medium relative z-10">Solicita y envía a tu correo tu historial académico</p>
        </a>
        
        <!-- Oferta Académica / Inscripción -->
        <a href="{{ route('kiosk.academic-offer') }}" wire:navigate class="group relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.5)] hover:shadow-[0_0_60px_rgba(6,182,212,0.3)] transition-all duration-500 transform hover:-translate-y-4 active:translate-y-2 active:scale-95 flex flex-col items-center justify-center text-center border border-white/10 hover:border-cyan-400/60 overflow-hidden isolate animate-[slideUp_0.8s_ease-out_0.3s_forwards] opacity-0">
            <!-- Glass Highlight -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/20 via-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            <!-- Glow Effect -->
             <div class="absolute -bottom-10 -right-10 w-48 h-48 bg-cyan-500/20 rounded-full blur-[50px] group-hover:bg-cyan-400/40 transition-colors duration-500 animate-pulse-glow"></div>
             <!-- Shimmer Effect -->
            <div class="absolute inset-0 -translate-x-full group-hover:animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/10 to-transparent pointer-events-none z-20"></div>

            <div class="bg-gradient-to-br from-cyan-500/20 to-cyan-900/40 p-6 rounded-[2rem] mb-8 shadow-[0_0_30px_rgba(6,182,212,0.2)] group-hover:scale-110 transition-transform duration-500 border border-cyan-500/40 relative z-10 animate-floating" style="animation-delay: 1.5s">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-28 h-28 text-cyan-300 drop-shadow-[0_4px_10px_rgba(6,182,212,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide group-hover:text-cyan-300 transition-colors duration-300 relative z-10">Oferta Académica</h3>
            <p class="text-cyan-100/80 text-xl font-medium relative z-10">Consulta horarios disponibles preinscríbete tocando</p>
        </a>

    </div>
</div>
