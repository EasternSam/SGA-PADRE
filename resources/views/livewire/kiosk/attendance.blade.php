<div class="h-full flex flex-col p-6 md:p-10 relative z-10">

    <!-- Header -->
    <div class="flex items-center justify-between mb-10">
        <button wire:click="goBack" class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white font-bold py-4 px-8 rounded-2xl text-xl transition-all active:scale-95 border border-white/20 flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Volver
        </button>
        <h2 class="text-3xl md:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-200 to-emerald-400 tracking-tight">Mi Asistencia</h2>
        <div class="w-32"></div>
    </div>

    <!-- Resumen de Asistencia -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-10">
        <!-- Tasa de Asistencia -->
        <div class="col-span-2 md:col-span-1 bg-white/[0.05] backdrop-blur-xl rounded-3xl p-6 border border-white/10 flex flex-col items-center justify-center">
            <div class="text-5xl font-black {{ $attendanceRate >= 80 ? 'text-emerald-400' : 'text-red-400' }}">{{ $attendanceRate }}%</div>
            <div class="text-sm text-white/60 uppercase tracking-widest mt-2 font-bold">Asistencia</div>
        </div>

        <div class="bg-white/[0.05] backdrop-blur-xl rounded-3xl p-6 border border-white/10 flex flex-col items-center justify-center">
            <div class="text-4xl font-black text-emerald-400">{{ $totalPresent }}</div>
            <div class="text-xs text-white/60 uppercase tracking-widest mt-2 font-bold">Presentes</div>
        </div>

        <div class="bg-white/[0.05] backdrop-blur-xl rounded-3xl p-6 border border-white/10 flex flex-col items-center justify-center">
            <div class="text-4xl font-black text-red-400">{{ $totalAbsent }}</div>
            <div class="text-xs text-white/60 uppercase tracking-widest mt-2 font-bold">Ausencias</div>
        </div>

        <div class="bg-white/[0.05] backdrop-blur-xl rounded-3xl p-6 border border-white/10 flex flex-col items-center justify-center">
            <div class="text-4xl font-black text-amber-400">{{ $totalLate }}</div>
            <div class="text-xs text-white/60 uppercase tracking-widest mt-2 font-bold">Tardanzas</div>
        </div>

        <div class="bg-white/[0.05] backdrop-blur-xl rounded-3xl p-6 border border-white/10 flex flex-col items-center justify-center">
            <div class="text-4xl font-black text-blue-400">{{ $totalExcused }}</div>
            <div class="text-xs text-white/60 uppercase tracking-widest mt-2 font-bold">Excusadas</div>
        </div>
    </div>

    <!-- Últimas Faltas -->
    <div class="bg-white/[0.03] backdrop-blur-xl rounded-3xl border border-white/10 overflow-hidden flex-1">
        <div class="px-8 py-5 border-b border-white/10">
            <h3 class="text-2xl font-bold text-white tracking-wide">Últimas Ausencias y Tardanzas</h3>
        </div>

        @if(count($recentAbsences) > 0)
            <div class="divide-y divide-white/5">
                @foreach($recentAbsences as $record)
                    <div class="px-8 py-5 flex items-center justify-between">
                        <div class="flex items-center gap-6">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl font-black
                                {{ $record['status'] === 'absent' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : '' }}
                                {{ $record['status'] === 'late' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : '' }}
                                {{ $record['status'] === 'excused' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : '' }}
                            ">
                                @if($record['status'] === 'absent') ✗ @elseif($record['status'] === 'late') ⏰ @else ✓ @endif
                            </div>
                            <div>
                                <div class="text-xl font-bold text-white">{{ $record['date'] }}</div>
                                <div class="text-white/50 text-lg capitalize">{{ $record['day'] }}</div>
                            </div>
                        </div>
                        <span class="text-lg font-bold px-5 py-2 rounded-xl
                            {{ $record['status'] === 'absent' ? 'bg-red-500/20 text-red-300' : '' }}
                            {{ $record['status'] === 'late' ? 'bg-amber-500/20 text-amber-300' : '' }}
                            {{ $record['status'] === 'excused' ? 'bg-blue-500/20 text-blue-300' : '' }}
                        ">{{ $record['status_label'] }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-16 text-center">
                <div class="text-6xl mb-4">🎉</div>
                <p class="text-2xl text-emerald-300 font-bold">¡Excelente! No tienes ausencias registradas.</p>
                <p class="text-white/50 text-lg mt-2">Sigue así, tu asistencia es perfecta.</p>
            </div>
        @endif
    </div>
</div>
