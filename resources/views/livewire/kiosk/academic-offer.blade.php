<div class="h-full flex flex-col p-8 max-w-7xl mx-auto w-full relative z-10">
    
    <!-- Encabezado con Botón Volver -->
    <div class="flex items-center justify-between mb-10 shrink-0 animate-[slideUp_0.4s_ease-out_forwards]">
        <div>
            <h2 class="text-4xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-slate-400 drop-shadow-2xl tracking-tight">Oferta Académica</h2>
            <p class="text-xl md:text-2xl text-indigo-200/90 mt-2 font-medium tracking-wide">Módulos disponibles para preselección e inscripción</p>
        </div>
        <button wire:click="goBack" class="bg-white/[0.03] hover:bg-white/10 active:bg-white/20 backdrop-blur-2xl text-white border border-white/20 hover:border-white/40 font-black tracking-widest py-4 px-8 rounded-[1.5rem] text-2xl shadow-[0_8px_32px_rgba(0,0,0,0.3)] transition-all duration-300 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 flex items-center gap-3 drop-shadow-sm touch-manipulation">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            VOLVER
        </button>
    </div>

    <!-- Lista de Materias (Scrollable) -->
    <div class="flex-1 overflow-y-auto pr-4 space-y-6 pb-20 custom-kiosk-scrollbar">
        @forelse($availableSchedules as $index => $schedule)
            <div class="bg-white/[0.05] backdrop-blur-2xl rounded-[2.5rem] p-8 shadow-[0_8px_32px_rgba(0,0,0,0.3)] flex flex-col xl:flex-row items-center justify-between gap-6 border border-white/10 relative overflow-hidden group hover:bg-white/[0.08] transition-colors duration-300 animate-[slideUp_0.6s_ease-out_{{ $index * 0.1 }}s_forwards] opacity-0">
                
                <!-- Highlight lateral -->
                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-cyan-500 to-blue-500 rounded-l-[2rem] group-hover:w-3 border-r border-cyan-400/50 shadow-[0_0_15px_rgba(6,182,212,0.5)] transition-all duration-300"></div>

                <!-- Info Materia Módulo Principal -->
                <div class="flex-1 w-full pl-4 z-10 flex flex-col justify-center">
                    <p class="text-cyan-200/80 font-black text-sm mb-1 uppercase tracking-widest drop-shadow-sm">{{ $schedule['course_name'] }}</p>
                    <h3 class="text-3xl md:text-4xl font-black text-white drop-shadow-lg tracking-tight mb-2">{{ $schedule['module_name'] }}</h3>
                    <div class="flex items-center gap-2 text-indigo-200/80">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                        <span class="font-medium text-lg">{{ $schedule['teacher_name'] }}</span>
                    </div>
                </div>

                <!-- Info Horario y Precio -->
                <div class="flex flex-col sm:flex-row gap-4 shrink-0 z-10 w-full xl:w-auto mt-4 xl:mt-0">
                    
                    <!-- Bloque Horario Glass -->
                    <div class="bg-white/[0.04] backdrop-blur-xl rounded-[1.5rem] p-6 border border-white/10 flex items-center justify-center gap-4 flex-1 xl:flex-none shadow-[0_4px_16px_rgba(0,0,0,0.2)]">
                        <div>
                            <p class="text-indigo-200/90 font-black text-xs md:text-sm mb-1 uppercase tracking-widest drop-shadow-sm text-center">Horario Programado</p>
                            <p class="text-white text-xl md:text-2xl font-black drop-shadow-md tracking-tight text-center">{{ $schedule['schedule_str'] }}</p>
                        </div>
                    </div>

                    <!-- Botón de Inscripción -->
                    <button wire:click="enroll({{ $schedule['id'] }})" 
                            wire:loading.attr="disabled"
                            class="relative overflow-hidden bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 active:from-cyan-600 active:to-blue-700 border border-cyan-400/50 text-white font-black tracking-widest py-5 px-8 rounded-[1.5rem] text-2xl shadow-[0_8px_32px_rgba(6,182,212,0.5)] transition-all duration-300 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed group/btn touch-manipulation flex-1 xl:flex-none">
                        
                        <!-- Shine effect -->
                        <div class="absolute inset-0 -translate-x-full group-hover/btn:animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none"></div>

                        <span wire:loading.remove wire:target="enroll({{ $schedule['id'] }})" class="drop-shadow-md whitespace-nowrap">
                            ¡INSCRIBIRME!
                        </span>
                        
                        <span wire:loading wire:target="enroll({{ $schedule['id'] }})" class="flex items-center gap-2 drop-shadow-md">
                            <svg class="animate-spin -ml-1 h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            ...
                        </span>
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white/[0.03] backdrop-blur-2xl rounded-[2.5rem] p-16 flex flex-col items-center justify-center text-center border border-white/10 h-64 animate-[slideUp_0.6s_ease-out_forwards]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-32 h-32 text-indigo-300/30 mb-6 drop-shadow-md animate-floating" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <h3 class="text-3xl font-black text-white/70 tracking-wide">No hay oferta disponible ahora mismo.</h3>
                <p class="text-indigo-200/50 text-xl font-medium mt-3">Mantente atento a las próximas programaciones.</p>
            </div>
        @endforelse
    </div>

    <!-- Toast de Notificación -->
    <div x-data="{ show: false, message: '', type: 'success' }" 
         @kiosk-notification.window="show = true; message = $event.detail[0].message; type = $event.detail[0].type; setTimeout(() => show = false, 4000)"
         x-show="show" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 transform translate-y-10" 
         x-transition:enter-end="opacity-100 transform translate-y-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 transform translate-y-0" 
         x-transition:leave-end="opacity-0 transform translate-y-10" 
         class="fixed bottom-10 left-1/2 transform -translate-x-1/2 z-50 pointer-events-none" style="display: none;">
        
        <div :class="{
                'bg-emerald-500/90 border-emerald-400': type === 'success',
                'bg-red-500/90 border-red-400': type === 'error'
             }"
             class="backdrop-blur-xl border text-white px-8 py-5 rounded-[1.5rem] shadow-[0_10px_40px_rgba(0,0,0,0.5)] flex items-center gap-4">
            
            <svg x-show="type === 'success'" class="w-10 h-10 text-emerald-100 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <svg x-show="type === 'error'" class="w-10 h-10 text-red-100 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            
            <span class="text-2xl font-black tracking-wide drop-shadow-md" x-text="message"></span>
        </div>
    </div>
</div>
