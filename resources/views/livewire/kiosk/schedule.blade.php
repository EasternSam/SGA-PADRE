<div class="h-full flex flex-col p-6 md:p-10 relative z-10">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <button wire:click="goBack" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-bold py-4 px-8 rounded-2xl text-xl transition-all active:scale-95 border border-white/20 flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Volver
        </button>
        <div class="text-center">
            <h2 class="text-3xl md:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-200 to-indigo-400 tracking-tight">Mi Horario</h2>
            @if($sectionName && $gradeLevelName)
                <p class="text-white/50 text-lg mt-1">{{ $gradeLevelName }} — {{ $sectionName }}</p>
            @endif
        </div>
        <div class="w-32"></div>
    </div>

    <!-- Horario por Día -->
    <div class="flex-1 overflow-y-auto space-y-6">
        @foreach($scheduleByDay as $day => $classes)
            <div class="bg-white/[0.03] backdrop-blur-xl rounded-3xl border border-white/10 overflow-hidden">
                <!-- Día Header -->
                <div class="px-8 py-4 border-b border-white/10 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <h3 class="text-2xl font-black text-white uppercase tracking-widest">{{ $day }}</h3>
                    <span class="text-white/30 text-lg font-bold ml-auto">{{ count($classes) }} {{ count($classes) === 1 ? 'clase' : 'clases' }}</span>
                </div>

                @if(count($classes) > 0)
                    <div class="divide-y divide-white/5">
                        @foreach($classes as $class)
                            <div class="px-8 py-5 flex items-center gap-8">
                                <!-- Hora -->
                                <div class="w-44 shrink-0">
                                    <div class="text-xl font-black text-indigo-300 font-mono">{{ $class['time_start'] }}</div>
                                    <div class="text-sm text-white/30">a {{ $class['time_end'] }}</div>
                                </div>
                                <!-- Asignatura -->
                                <div class="flex-1">
                                    <div class="text-xl font-bold text-white">{{ $class['subject'] }}</div>
                                    <div class="text-white/50 text-base">Prof. {{ $class['teacher'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="px-8 py-6 text-center text-white/30 text-lg">Sin clases programadas</div>
                @endif
            </div>
        @endforeach
    </div>
</div>
