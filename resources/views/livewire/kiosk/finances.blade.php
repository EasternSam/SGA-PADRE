<div class="h-full flex flex-col p-8 max-w-7xl mx-auto w-full relative z-10">
    
    <!-- Alertas Flash Personalizadas para Kiosco -->
    <div x-data="{ show: false, message: '', type: 'info' }" 
         @notify.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 5000)"
         x-show="show" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-[-20px]"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-[-20px]"
         class="fixed top-8 left-1/2 transform -translate-x-1/2 z-50 text-2xl font-bold px-10 py-6 rounded-[2rem] shadow-[0_8px_32px_rgba(0,0,0,0.5)] backdrop-blur-xl border flex items-center gap-4"
         :class="{
            'bg-emerald-500/80 border-emerald-400/50 text-white': type === 'success',
            'bg-red-600/80 border-red-400/50 text-white': type === 'error',
            'bg-amber-500/80 border-amber-400/50 text-white': type === 'warning',
            'bg-indigo-600/80 border-indigo-400/50 text-white': type === 'info'
         }"
         style="display: none;">
        <svg x-show="type === 'success'" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
        <svg x-show="type === 'error'" class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
        <span x-text="message" class="drop-shadow-md"></span>
    </div>

    <!-- Encabezado con Botón Volver -->
    <div class="flex items-center justify-between mb-10 shrink-0 animate-[slideUp_0.4s_ease-out_forwards]">
        <div>
            <h2 class="text-4xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-slate-400 drop-shadow-2xl tracking-tight">Estado de Cuenta</h2>
            <p class="text-xl md:text-2xl text-indigo-200/90 mt-2 font-medium tracking-wide">Revisa tus cuotas y realiza pagos al instante</p>
        </div>
        <button wire:click="goBack" class="bg-white/[0.03] hover:bg-white/10 active:bg-white/20 backdrop-blur-2xl text-white border border-white/20 hover:border-white/40 font-black tracking-widest py-4 px-8 rounded-[1.5rem] text-2xl shadow-[0_8px_32px_rgba(0,0,0,0.3)] transition-all duration-300 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 flex items-center gap-3 drop-shadow-sm touch-manipulation">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            VOLVER
        </button>
    </div>

    <!-- Resumen Total Glass -->
    <div class="relative bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] p-8 shadow-[0_8px_32px_rgba(0,0,0,0.4)] mb-8 shrink-0 flex items-center justify-between border border-red-500/30 overflow-hidden animate-[slideUp_0.5s_ease-out_forwards]">
        <!-- Glow Fuerte de Deuda -->
        <div class="absolute -top-32 -left-10 w-80 h-80 bg-red-600/30 rounded-full blur-[80px] pointer-events-none"></div>
        <div class="absolute -bottom-20 -right-10 w-64 h-64 bg-rose-600/20 rounded-full blur-[60px] pointer-events-none"></div>

        <div class="relative z-10">
            <h3 class="text-2xl font-black text-red-200/90 uppercase tracking-widest mb-2 drop-shadow-sm">Total Pendiente</h3>
            <p class="text-6xl font-black text-white drop-shadow-[0_4px_8px_rgba(220,38,38,0.5)]">
                RD$ {{ number_format($totalDebt, 2) }}
            </p>
        </div>
        <div class="relative z-10 bg-red-500/20 p-6 rounded-full border border-red-400/30 shadow-[0_0_20px_rgba(220,38,38,0.2)]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-red-200 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
        </div>
    </div>

    <!-- Lista de Cuotas (Scrollable) -->
    <div class="flex-1 overflow-y-auto pr-4 space-y-6 pb-20 custom-kiosk-scrollbar">
        @forelse($pendingPayments as $index => $payment)
            <div class="bg-white/[0.05] backdrop-blur-2xl rounded-[2.5rem] p-8 shadow-[0_8px_32px_rgba(0,0,0,0.3)] flex flex-col md:flex-row items-center justify-between gap-6 border border-white/10 relative overflow-hidden group hover:bg-white/[0.08] transition-colors duration-300 animate-[slideUp_0.6s_ease-out_{{ $index * 0.1 }}s_forwards] opacity-0">
                
                <!-- Highlight de la tarjeta -->
                <div class="absolute inset-y-0 left-0 w-2 bg-gradient-to-b from-red-500 to-rose-500 rounded-l-[2rem] group-hover:w-3 border-r border-red-400/50 shadow-[0_0_15px_rgba(239,68,68,0.5)] transition-all duration-300"></div>

                <!-- Info Cuota -->
                <div class="flex-1 w-full pl-4 z-10">
                    <div class="flex items-center gap-3 mb-2">
                        @if($payment->enrollment && $payment->enrollment->courseSchedule)
                            <span class="bg-indigo-500/20 text-indigo-100 text-lg font-bold px-4 py-1.5 rounded-full border border-indigo-500/30 backdrop-blur-md shadow-sm">
                                {{ $payment->enrollment->courseSchedule->module->name ?? 'Materia' }}
                            </span>
                        @else
                            <span class="bg-amber-500/20 text-amber-100 text-lg font-bold px-4 py-1.5 rounded-full border border-amber-500/30 backdrop-blur-md shadow-sm">
                                {{ $payment->category ?? 'Cargo Administrativo' }}
                            </span>
                        @endif
                    </div>
                    <h3 class="text-3xl font-black text-white mb-2 drop-shadow-md">
                        {{ $payment->paymentConcept->name ?? 'Cargo General' }}
                    </h3>
                    <p class="text-xl text-indigo-100/70 font-medium">
                        Vencimiento: <strong class="{{ $payment->due_date && $payment->due_date->isPast() ? 'text-red-400 drop-shadow-sm' : 'text-white' }}">{{ $payment->due_date ? $payment->due_date->format('d/m/Y') : 'N/A' }}</strong>
                    </p>
                </div>

                <!-- Monto y Pagar -->
                <div class="flex flex-col md:flex-row items-center gap-6 shrink-0 z-10">
                    <div class="text-right">
                        <p class="text-indigo-200/60 text-lg font-bold uppercase tracking-widest mb-1">Monto a pagar</p>
                        <p class="text-4xl font-black text-white drop-shadow-md">RD$ {{ number_format($payment->amount, 2) }}</p>
                    </div>

                    <button wire:click="initiatePayment({{ $payment->id }})" 
                            wire:loading.attr="disabled"
                            class="relative overflow-hidden bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 active:from-emerald-600 active:to-teal-600 border border-emerald-400/50 text-white font-black tracking-widest py-6 px-12 rounded-[1.5rem] text-3xl shadow-[0_8px_32px_rgba(16,185,129,0.5)] transition-all duration-300 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 flex items-center gap-4 disabled:opacity-50 disabled:cursor-not-allowed min-w-[260px] justify-center group/btn touch-manipulation">
                        
                        <!-- Shine effect -->
                        <div class="absolute inset-0 -translate-x-full group-hover/btn:animate-[shimmer_1.5s_infinite] bg-gradient-to-r from-transparent via-white/30 to-transparent pointer-events-none"></div>

                        <span wire:loading.remove wire:target="initiatePayment({{ $payment->id }})" class="flex items-center gap-2 drop-shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                            PAGAR AHORA
                        </span>
                        
                        <span wire:loading wire:target="initiatePayment({{ $payment->id }})" class="flex items-center gap-2 drop-shadow-md">
                            <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            PROCESANDO...
                        </span>
                    </button>
                </div>

            </div>
        @empty
            <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-[2rem] p-16 text-center shadow-[0_8px_32px_rgba(0,0,0,0.2)]">
                <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[80px] pointer-events-none"></div>
                
                <div class="bg-emerald-500/20 w-32 h-32 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-400/30 shadow-[0_0_30px_rgba(16,185,129,0.2)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-emerald-300 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                </div>
                <h3 class="text-4xl font-black text-white mb-4 drop-shadow-md tracking-wide">¡Todo al día!</h3>
                <p class="text-2xl text-emerald-100/80 font-medium">No tienes balances pendientes de pago.</p>
            </div>
        @endforelse
    </div>

    {{-- Formulario Oculto para Cardnet --}}
    <form id="kiosk-cardnet-form" method="POST" style="display:none;"></form>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('submit-cardnet-form', event => {
                // Compatible con Livewire 3 events payload
                const payload = Array.isArray(event) ? event[0] : event;
                const data = payload?.data || payload; 
                
                if(!data || !data.url) {
                    console.error('Datos Cardnet Inválidos', data);
                    window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Error de configuración en pasarela.', type: 'error' }}));
                    return;
                }

                // Generamos el form al vuelo y disparamos
                const form = document.getElementById('kiosk-cardnet-form');
                form.action = data.url;
                form.innerHTML = '';
                
                for (const [key, value] of Object.entries(data.fields)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = value;
                    form.appendChild(input);
                }
                
                // Submit al form (Redirigirá toda la ventana del kiosco a Cardnet)
                form.submit();
            });
        });
    </script>
</div>
