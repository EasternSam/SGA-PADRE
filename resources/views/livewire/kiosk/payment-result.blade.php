<div class="h-full flex flex-col items-center justify-center p-8 max-w-5xl mx-auto w-full relative z-10"
     x-data="{ countdown: 8 }"
     x-init="
        if ('{{ $status }}' === 'success' || '{{ $status }}' === 'error') {
            setInterval(() => {
                if (countdown > 0) countdown--;
                if (countdown === 0) @this.call('goDashboard');
            }, 1000);
        }
     ">
    
    @if($status === 'processing')
        <!-- Estado: Procesando / Esperando -->
        <div class="bg-white/[0.05] backdrop-blur-2xl rounded-[3rem] p-16 shadow-[0_10px_40px_rgba(0,0,0,0.5)] flex flex-col items-center justify-center text-center border border-white/10 relative overflow-hidden animate-pulse-glow">
            <div class="relative w-48 h-48 flex items-center justify-center mb-8">
                <!-- Anillos de carga -->
                <div class="absolute inset-0 border-8 border-cyan-500/20 rounded-full"></div>
                <div class="absolute inset-0 border-8 border-t-cyan-400 border-r-transparent border-b-transparent border-l-transparent rounded-full animate-spin"></div>
                <div class="absolute inset-4 border-8 border-indigo-500/20 rounded-full"></div>
                <div class="absolute inset-4 border-8 border-t-transparent border-r-indigo-400 border-b-transparent border-l-transparent rounded-full animate-spin" style="animation-direction: reverse; animation-duration: 1.5s;"></div>
                <!-- Icono Central -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-cyan-300 drop-shadow-[0_0_15px_rgba(34,211,238,0.8)] animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
            </div>
            <h2 class="text-4xl md:text-5xl font-black text-white drop-shadow-md tracking-tight mb-4">{{ $message }}</h2>
            <p class="text-xl text-indigo-200/80 font-medium">Por favor, no retires tu tarjeta ni toques la pantalla.</p>
        </div>

    @elseif($status === 'success')
        <!-- Estado: Éxito -->
        <div class="bg-emerald-900/40 backdrop-blur-3xl rounded-[3rem] p-16 shadow-[0_20px_60px_rgba(16,185,129,0.3)] flex flex-col items-center justify-center text-center border border-emerald-400/50 relative overflow-hidden animate-[slideUp_0.6s_ease-out_forwards]">
            
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-gradient-to-br from-emerald-400/20 via-transparent to-teal-600/20 mix-blend-overlay"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-400/30 rounded-full blur-[100px] pointer-events-none animate-pulse"></div>

            <div class="bg-emerald-500/20 rounded-full p-8 mb-8 border border-emerald-400/40 shadow-[0_0_40px_rgba(16,185,129,0.5)] relative z-10 animate-[bounce_1s_ease-in-out_1]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 text-emerald-300 drop-shadow-[0_0_15px_rgba(52,211,153,0.8)]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
            </div>

            <h2 class="text-5xl md:text-6xl font-black text-white drop-shadow-lg tracking-tight mb-4 relative z-10">{{ $message }}</h2>
            
            <div class="bg-black/20 rounded-2xl p-6 w-full max-w-lg mb-8 relative z-10 border border-white/10 backdrop-blur-md">
                <div class="flex justify-between items-center border-b border-white/10 pb-4 mb-4">
                    <span class="text-emerald-100/70 font-medium text-lg">Número de Autorización</span>
                    <span class="text-white font-black text-2xl tracking-widest">{{ $authCode }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-emerald-100/70 font-medium text-lg">Monto Pagado</span>
                    <span class="text-white font-black text-3xl">RD$ {{ isset($paymentDetails) ? number_format($paymentDetails->amount, 2) : '0.00' }}</span>
                </div>
            </div>

            <p class="text-2xl text-emerald-100/90 font-medium relative z-10">Tu estado de cuenta ha sido actualizado instantáneamente.</p>
            
            <div class="mt-10 relative z-10 flex flex-col items-center">
                <div class="w-full bg-black/30 rounded-full h-2 mb-2">
                    <div class="bg-emerald-400 h-2 rounded-full transition-all duration-1000 linear" :style="`width: ${(countdown / 8) * 100}%`"></div>
                </div>
                <p class="text-emerald-200/60 font-medium">Volviendo al inicio en <span x-text="countdown"></span> s...</p>
                <button wire:click="goDashboard" class="mt-6 px-10 py-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-full text-white font-bold tracking-widest uppercase transition-colors">Volver Ahora</button>
            </div>
        </div>

    @elseif($status === 'error')
        <!-- Estado: Error / Declinado -->
        <div class="bg-red-900/40 backdrop-blur-3xl rounded-[3rem] p-16 shadow-[0_20px_60px_rgba(220,38,38,0.3)] flex flex-col items-center justify-center text-center border border-red-500/50 relative overflow-hidden animate-[slideUp_0.6s_ease-out_forwards]">
            
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-gradient-to-br from-red-500/20 via-transparent to-rose-700/20 mix-blend-overlay"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-red-500/30 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="bg-red-500/20 rounded-full p-8 mb-8 border border-red-400/40 shadow-[0_0_40px_rgba(239,68,68,0.4)] relative z-10 animate-[shake_0.5s_ease-in-out_1]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 text-red-300 drop-shadow-[0_0_15px_rgba(248,113,113,0.8)]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>

            <h2 class="text-5xl md:text-6xl font-black text-white drop-shadow-lg tracking-tight mb-4 relative z-10">Transacción Declinada</h2>
            
            <div class="bg-black/20 rounded-2xl p-6 w-full max-w-lg mb-8 relative z-10 border border-white/10 backdrop-blur-md">
                <p class="text-red-100 font-bold text-xl">{{ $message }}</p>
            </div>

            <p class="text-xl text-red-200/80 font-medium relative z-10">Revisa tu tarjeta o comunícate con tu banco e intenta nuevamente.</p>
            
            <div class="mt-10 relative z-10 flex flex-col items-center">
                <div class="w-full bg-black/30 rounded-full h-2 mb-2">
                    <div class="bg-red-400 h-2 rounded-full transition-all duration-1000 linear" :style="`width: ${(countdown / 8) * 100}%`"></div>
                </div>
                <p class="text-red-200/60 font-medium">Volviendo al inicio en <span x-text="countdown"></span> s...</p>
                <button wire:click="goDashboard" class="mt-6 px-10 py-4 bg-white/10 hover:bg-white/20 border border-white/20 rounded-full text-white font-bold tracking-widest uppercase transition-colors">Volver Ahora</button>
            </div>
        </div>
    @endif
</div>
