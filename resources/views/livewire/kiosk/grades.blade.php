<div class="h-full flex flex-col p-8 max-w-7xl mx-auto w-full relative z-10">
    
    <!-- Encabezado con Botón Volver -->
    <div class="flex items-center justify-between mb-10 shrink-0 animate-[slideUp_0.4s_ease-out_forwards]">
        <div>
            <h2 class="text-4xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-slate-400 drop-shadow-2xl tracking-tight">Récord de Notas</h2>
            <p class="text-xl md:text-2xl text-indigo-200/90 mt-2 font-medium tracking-wide">Tus asignaturas aprobadas y calificaciones históricas</p>
        </div>
        <button wire:click="goBack" class="bg-white/[0.03] hover:bg-white/10 active:bg-white/20 backdrop-blur-2xl text-white border border-white/20 hover:border-white/40 font-black tracking-widest py-4 px-8 rounded-[1.5rem] text-2xl shadow-[0_8px_32px_rgba(0,0,0,0.3)] transition-all duration-300 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 flex items-center gap-3 drop-shadow-sm touch-manipulation">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            VOLVER
        </button>
    </div>

    <!-- Lista de Notas (Scrollable) -->
    <div class="flex-1 overflow-y-auto pr-4 space-y-6 pb-8 custom-kiosk-scrollbar">
        @forelse($approvedSubjects as $index => $subject)
            <div class="bg-white/[0.05] backdrop-blur-2xl rounded-[2.5rem] p-8 shadow-[0_8px_32px_rgba(0,0,0,0.3)] flex items-center justify-between gap-6 border border-white/10 relative overflow-hidden group hover:bg-white/[0.08] transition-colors duration-300 animate-[slideUp_0.6s_ease-out_{{ $index * 0.1 }}s_forwards] opacity-0">
                
                <!-- Highlight lateral basado en la nota -->
                @php
                    $colorClass = match($subject['literal']) {
                        'A' => 'from-emerald-400 to-teal-500 shadow-[0_0_15px_rgba(16,185,129,0.5)] border-emerald-400/50',
                        'B' => 'from-blue-400 to-indigo-500 shadow-[0_0_15px_rgba(59,130,246,0.5)] border-blue-400/50',
                        'C' => 'from-amber-400 to-orange-500 shadow-[0_0_15px_rgba(245,158,11,0.5)] border-amber-400/50',
                        default => 'from-red-400 to-rose-500 shadow-[0_0_15px_rgba(239,68,68,0.5)] border-red-400/50',
                    };
                    $textClass = match($subject['literal']) {
                        'A' => 'text-emerald-300',
                        'B' => 'text-blue-300',
                        'C' => 'text-amber-300',
                        default => 'text-red-300',
                    };
                @endphp
                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b {{ $colorClass }} rounded-l-[2rem] group-hover:w-3 border-r transition-all duration-300"></div>

                <!-- Info Materia -->
                <div class="flex-1 w-full pl-4 z-10 flex flex-col justify-center">
                    <p class="text-indigo-200/80 font-black text-sm mb-1 uppercase tracking-widest drop-shadow-sm">{{ $subject['period'] }} • {{ $subject['course_name'] }}</p>
                    <h3 class="text-3xl md:text-4xl font-black text-white drop-shadow-lg tracking-tight">{{ $subject['module_name'] }}</h3>
                </div>

                <!-- Badge Calificación -->
                <div class="flex items-center gap-6 shrink-0 z-10 bg-black/20 rounded-[2rem] p-4 pr-10 border border-white/5">
                    <div class="flex flex-col items-end">
                        <span class="text-indigo-200/60 font-medium text-sm uppercase tracking-widest">Nota Final</span>
                        <span class="text-3xl font-black text-white font-mono">{{ $subject['grade'] }}</span>
                    </div>
                    
                    <!-- Literal Gigante -->
                    <div class="text-6xl md:text-7xl font-black {{ $textClass }} drop-shadow-[0_0_20px_currentColor] ml-4 leading-none w-16 text-center animate-pulse-glow" style="animation-delay: {{ $index * 0.2 }}s">
                        {{ $subject['literal'] }}
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white/[0.03] backdrop-blur-2xl rounded-[2.5rem] p-16 flex flex-col items-center justify-center text-center border border-white/10 h-64 animate-[slideUp_0.6s_ease-out_forwards]">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-32 h-32 text-indigo-300/30 mb-6 drop-shadow-md animate-floating" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="text-3xl font-black text-white/70 tracking-wide">Aún no tienes calificaciones registradas.</h3>
                <p class="text-indigo-200/50 text-xl font-medium mt-3">Al completar tus primeros módulos, aparecerán aquí.</p>
            </div>
        @endforelse
    </div>

    <!-- Botón Flotante Enviar por Correo -->
    @if(count($approvedSubjects) > 0)
    <div class="mt-4 shrink-0 animate-[slideUp_0.8s_ease-out_0.5s_forwards] opacity-0">
        <button wire:click="sendReportToEmail" 
                wire:loading.attr="disabled"
                class="w-full relative overflow-hidden bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 active:from-indigo-600 active:to-purple-700 border border-indigo-400/50 text-white font-black tracking-[0.15em] py-6 px-12 rounded-[1.5rem] text-2xl shadow-[0_8px_32px_rgba(99,102,241,0.5)] transition-all duration-300 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 flex items-center justify-center gap-4 disabled:opacity-50 disabled:cursor-not-allowed group/btn touch-manipulation">
            
            <div class="absolute inset-0 -translate-x-full group-hover/btn:animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none"></div>

            <span wire:loading.remove wire:target="sendReportToEmail" class="flex items-center gap-3 drop-shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                ENVIAR RÉCORD A MI CORREO ELECTRÓNICO
            </span>
            <span wire:loading wire:target="sendReportToEmail" class="flex items-center gap-3 drop-shadow-md">
                <svg class="animate-spin -ml-1 h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                GENERANDO Y ENVIANDO...
            </span>
        </button>
    </div>
    @endif

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
            
            <!-- Icono Success -->
            <svg x-show="type === 'success'" class="w-10 h-10 text-emerald-100 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            
            <!-- Icono Error -->
            <svg x-show="type === 'error'" class="w-10 h-10 text-red-100 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            
            <span class="text-2xl font-black tracking-wide drop-shadow-md" x-text="message"></span>
        </div>
    </div>
</div>
