<div class="h-full flex flex-col p-6 md:p-10 relative z-10">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <button wire:click="goBack" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-bold py-4 px-8 rounded-2xl text-xl transition-all active:scale-95 border border-white/20 flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Volver
        </button>
        <div class="text-center">
            <h2 class="text-3xl md:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-amber-400 tracking-tight">Mis Calificaciones</h2>
            @if($sectionName)
                <p class="text-white/50 text-lg mt-1">{{ $sectionName }}</p>
            @endif
        </div>
        <div class="w-32"></div>
    </div>

    <!-- Selector de Período -->
    <div class="flex items-center gap-4 mb-8 justify-center">
        @foreach($periods as $period)
            <button wire:click="$set('selectedPeriod', {{ $period['id'] }})"
                class="px-8 py-4 rounded-2xl text-xl font-bold transition-all active:scale-95 border
                    {{ $selectedPeriod == $period['id'] 
                        ? 'bg-amber-500/30 border-amber-400/60 text-amber-200 shadow-[0_0_30px_rgba(245,158,11,0.2)]' 
                        : 'bg-white/5 border-white/10 text-white/60 hover:bg-white/10' }}">
                {{ $period['name'] }}
            </button>
        @endforeach
    </div>

    <!-- Promedio General -->
    @if($generalAverage !== null)
        <div class="flex justify-center mb-8">
            <div class="bg-white/[0.05] backdrop-blur-xl rounded-3xl px-12 py-6 border border-white/10 flex items-center gap-6">
                <span class="text-white/60 text-xl font-bold uppercase tracking-widest">Promedio General</span>
                <span class="text-5xl font-black {{ $generalAverage >= 70 ? 'text-emerald-400' : 'text-red-400' }}">{{ $generalAverage }}</span>
            </div>
        </div>
    @endif

    <!-- Tabla de Notas -->
    <div class="bg-white/[0.03] backdrop-blur-xl rounded-3xl border border-white/10 overflow-hidden flex-1">
        @if(count($grades) > 0)
            <div class="divide-y divide-white/5">
                <!-- Header -->
                <div class="px-8 py-4 flex items-center text-white/40 text-sm uppercase tracking-widest font-bold">
                    <div class="flex-1">Asignatura</div>
                    <div class="w-28 text-center">Nota</div>
                    <div class="w-24 text-center">Literal</div>
                    <div class="w-28 text-center">Estado</div>
                </div>

                @foreach($grades as $grade)
                    <div class="px-8 py-5 flex items-center hover:bg-white/[0.02] transition-colors">
                        <div class="flex-1">
                            <span class="text-xl font-bold text-white">{{ $grade['subject'] }}</span>
                        </div>
                        <div class="w-28 text-center">
                            <span class="text-3xl font-black {{ $grade['passed'] ? 'text-white' : 'text-red-400' }}">{{ $grade['score'] }}</span>
                        </div>
                        <div class="w-24 text-center">
                            <span class="text-2xl font-black {{ $grade['literal'] === 'A' ? 'text-emerald-400' : ($grade['literal'] === 'B' ? 'text-blue-400' : ($grade['literal'] === 'F' ? 'text-red-400' : 'text-amber-400')) }}">
                                {{ $grade['literal'] }}
                            </span>
                        </div>
                        <div class="w-28 text-center">
                            @if($grade['passed'])
                                <span class="bg-emerald-500/20 text-emerald-300 px-4 py-1.5 rounded-xl text-sm font-bold border border-emerald-500/30">Aprobada</span>
                            @else
                                <span class="bg-red-500/20 text-red-300 px-4 py-1.5 rounded-xl text-sm font-bold border border-red-500/30">Reprobada</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-16 text-center">
                <div class="text-6xl mb-4">📋</div>
                <p class="text-2xl text-white/60 font-bold">No hay calificaciones registradas para este período.</p>
            </div>
        @endif
    </div>
</div>
