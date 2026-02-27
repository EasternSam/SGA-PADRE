<div class="min-h-screen bg-slate-900 flex items-center justify-center p-4 relative overflow-hidden font-sans">
    
    <!-- Esferas de Fondo Animadas (Mismo estilo Nivel Dios) -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 animate-blob"></div>
    <div class="absolute top-40 -right-40 w-96 h-96 bg-emerald-500 rounded-full mix-blend-multiply filter blur-[128px] opacity-40 animate-blob animation-delay-2000"></div>

    <div class="relative w-full max-w-sm bg-slate-800/60 backdrop-blur-2xl border border-slate-700/50 rounded-3xl shadow-2xl overflow-hidden">
        
        <div class="p-8 text-center">
            
            @if($status === 'pending')
                <div class="mb-6 flex justify-center">
                    <div class="w-24 h-24 bg-indigo-500/20 rounded-full flex items-center justify-center border border-indigo-500/30">
                        <svg class="w-12 h-12 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>

                <h1 class="text-2xl font-black text-white mb-2">Iniciar Sesión en el Kiosco</h1>
                <p class="text-slate-400 mb-8 font-medium">¿Deseas autorizar al Kiosco físico a iniciar sesión con tu cuenta: <span class="text-white">{{ Auth::user()->name }}</span>?</p>

                <div class="flex flex-col gap-4">
                    <button wire:click="authorizeLogin" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 px-6 rounded-2xl shadow-lg shadow-indigo-500/30 transition-all duration-300 transform active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Autorizar Login
                    </button>
                    
                    <button wire:click="denyLogin" class="w-full bg-slate-700/50 hover:bg-slate-700 text-slate-300 font-bold py-4 px-6 rounded-2xl border border-slate-600 transition-all duration-300 transform active:scale-95">
                        <span class="text-red-400">Desautorizar y Cerrar</span>
                    </button>
                </div>

            @elseif($status === 'authorized')
                <div class="mb-6 flex justify-center">
                    <div class="w-24 h-24 bg-emerald-500/20 rounded-full flex items-center justify-center border border-emerald-500/30 animate-pulse">
                        <svg class="w-12 h-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-black text-white mb-2">¡Completado!</h1>
                <p class="text-slate-400 font-medium pb-4">La pantalla física del Kiosco ha sido desbloqueada mágicamente. Ya puedes guardar tu teléfono.</p>

            @elseif($status === 'denied')
                <div class="mb-6 flex justify-center">
                    <div class="w-24 h-24 bg-red-500/20 rounded-full flex items-center justify-center border border-red-500/30">
                        <svg class="w-12 h-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-black text-white mb-2">Login Denegado</h1>
                <p class="text-slate-400 font-medium">Has bloqueado el intento de inicio de sesión.</p>

            @elseif($status === 'invalid')
                <div class="mb-6 flex justify-center">
                    <div class="w-24 h-24 bg-amber-500/20 rounded-full flex items-center justify-center border border-amber-500/30">
                        <svg class="w-12 h-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-black text-white mb-2">Código Expirado</h1>
                <p class="text-slate-400 font-medium pb-2">Este código QR ha expirado por seguridad o ya fue utilizado.</p>
                <button onclick="window.close()" class="w-full mt-4 bg-slate-700 text-white font-bold py-3 px-6 rounded-2xl">Cerrar Ventana</button>
            @endif

        </div>
    </div>
</div>
