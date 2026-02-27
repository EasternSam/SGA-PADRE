<div class="h-full flex flex-col items-center justify-center p-8 relative z-10">
    
    <div class="text-center mb-16 relative">
        <h2 class="text-4xl md:text-5xl font-black text-white mb-4 drop-shadow-lg tracking-wide">¿Qué deseas hacer hoy?</h2>
        <p class="text-xl text-indigo-100/80 font-medium tracking-wider">Selecciona una de las opciones tocando el botón en pantalla.</p>
    </div>

    <!-- Menú de Bloques Gigantes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full max-w-5xl">
        
        <!-- Estado de Cuenta / Pagos -->
        <a href="{{ route('kiosk.finances') }}" wire:navigate class="group relative bg-white/10 backdrop-blur-xl backdrop-saturate-150 rounded-[2rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.3)] hover:shadow-[0_0_40px_rgba(16,185,129,0.3)] transition-all duration-300 transform hover:-translate-y-2 active:translate-y-1 active:scale-95 flex flex-col items-center justify-center text-center border border-white/20 hover:border-emerald-400/50 overflow-hidden">
            <!-- Glass Highlight -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <!-- Glow Effect -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500/20 rounded-full blur-[40px] group-hover:bg-emerald-400/30 transition-colors duration-300"></div>
            
            <div class="bg-emerald-500/20 p-5 rounded-3xl mb-6 shadow-[0_0_15px_rgba(16,185,129,0.2)] group-hover:scale-110 transition-transform duration-300 border border-emerald-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-emerald-300 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-3 drop-shadow-sm tracking-wide group-hover:text-emerald-300 transition-colors duration-300">Pagar Mensualidad</h3>
            <p class="text-emerald-100/80 text-lg font-medium">Ver tu estado de cuenta y realizar pagos con tarjeta</p>
        </a>

        <!-- Horario de Clases -->
        <a href="{{ route('kiosk.schedule') }}" wire:navigate class="group relative bg-white/10 backdrop-blur-xl backdrop-saturate-150 rounded-[2rem] p-10 shadow-[0_8px_32px_rgba(0,0,0,0.3)] hover:shadow-[0_0_40px_rgba(99,102,241,0.3)] transition-all duration-300 transform hover:-translate-y-2 active:translate-y-1 active:scale-95 flex flex-col items-center justify-center text-center border border-white/20 hover:border-indigo-400/50 overflow-hidden">
            <!-- Glass Highlight -->
            <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            <!-- Glow Effect -->
             <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-indigo-500/20 rounded-full blur-[40px] group-hover:bg-indigo-400/30 transition-colors duration-300"></div>

            <div class="bg-indigo-500/20 p-5 rounded-3xl mb-6 shadow-[0_0_15px_rgba(99,102,241,0.2)] group-hover:scale-110 transition-transform duration-300 border border-indigo-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-24 h-24 text-indigo-300 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            
            <h3 class="text-4xl font-black text-white mb-3 drop-shadow-sm tracking-wide group-hover:text-indigo-300 transition-colors duration-300">Mi Horario</h3>
            <p class="text-indigo-100/80 text-lg font-medium">Consultar tus aulas, profesores y horas de clase</p>
        </a>

        <!-- Opción Futura 1 (Inactiva momentáneamente) -->
        <div class="bg-white/5 backdrop-blur-sm rounded-[2rem] p-10 flex flex-col items-center justify-center text-center border-2 border-dashed border-white/10 opacity-60">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-white/30 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
            </svg>
            <h3 class="text-2xl font-bold text-white/50 tracking-wide">Récord de Notas</h3>
            <span class="mt-3 bg-white/10 text-white/60 border border-white/10 shadow-sm text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest">Próximamente</span>
        </div>
        
        <!-- Opción Futura 2 (Inactiva momentáneamente) -->
         <div class="bg-white/5 backdrop-blur-sm rounded-[2rem] p-10 flex flex-col items-center justify-center text-center border-2 border-dashed border-white/10 opacity-60">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-20 h-20 text-white/30 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 class="text-2xl font-bold text-white/50 tracking-wide">Oferta Académica</h3>
            <span class="mt-3 bg-white/10 text-white/60 border border-white/10 shadow-sm text-xs font-bold px-4 py-1.5 rounded-full uppercase tracking-widest">Próximamente</span>
        </div>

    </div>
</div>
