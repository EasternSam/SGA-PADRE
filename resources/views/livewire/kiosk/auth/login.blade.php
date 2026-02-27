<div class="h-full flex items-center justify-center p-4 sm:p-8">
    <div class="max-w-4xl w-full bg-white/10 backdrop-blur-xl backdrop-saturate-150 rounded-[2rem] shadow-[0_8px_32px_rgba(0,0,0,0.3)] overflow-hidden border border-white/20 flex flex-col md:flex-row relative">
        
        <!-- Elemento decorativo del Glass UI interior -->
        <div class="absolute -top-32 -left-32 w-64 h-64 bg-indigo-500/30 rounded-full blur-[80px] pointer-events-none"></div>

        <!-- Sección de Instrucciones / Branding -->
        <div class="bg-slate-900/40 p-8 md:p-12 md:w-5/12 flex flex-col justify-center border-b md:border-b-0 md:border-r border-white/10 relative z-10">
            <h2 class="text-4xl sm:text-5xl font-extrabold text-white mb-6 leading-tight drop-shadow-md">
                Bienvenido al<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 to-cyan-300">Autoservicio</span>
            </h2>
            
            <p class="text-indigo-100/90 text-lg mb-8 leading-relaxed font-medium">
                Por favor, identifíquese ingresando su <strong class="text-white drop-shadow-sm">Número de Cédula (o Matrícula)</strong> y su <strong class="text-white drop-shadow-sm">PIN de 4 dígitos</strong> secreto.
            </p>

            <div class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10 shadow-[0_4px_16px_rgba(0,0,0,0.1)]">
                <div class="flex items-start gap-4">
                    <div class="bg-indigo-500/20 p-2 rounded-xl text-indigo-300 shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <p class="text-sm text-indigo-100/80 leading-snug">Si aún no tiene un PIN de Kiosco, por favor solicítelo al personal de caja o admisiones.</p>
                </div>
            </div>
        </div>

        <!-- Sección de Formularios y Teclado -->
        <div class="p-8 md:p-12 md:w-7/12 flex flex-col items-center justify-center bg-slate-900/20 relative z-10">
            
            @if($errorMessage)
                <div class="w-full bg-red-500/20 backdrop-blur-md border border-red-500/50 p-4 mb-8 rounded-2xl shadow-[0_4px_16px_rgba(239,68,68,0.15)] animate-fade-in">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-red-500/20 p-1.5 rounded-full">
                            <svg class="h-6 w-6 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm md:text-base text-red-100 font-bold tracking-wide">
                                {{ $errorMessage }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="w-full max-w-sm space-y-6">
                <!-- Input Cédula -->
                <div 
                    wire:click="setFocus('document_id')" 
                    class="relative border rounded-2xl p-4 transition-all duration-300 cursor-pointer backdrop-blur-md {{ $focusedInput === 'document_id' ? 'border-cyan-400/60 bg-white/15 shadow-[0_0_20px_rgba(34,211,238,0.2)]' : 'border-white/10 bg-white/5 hover:bg-white/10' }}"
                >
                    <label class="block text-xs font-black text-indigo-300/70 uppercase tracking-widest mb-1.5 drop-shadow-md">Cédula / Matrícula</label>
                    <div class="text-3xl font-mono text-white h-10 flex items-center drop-shadow-sm">
                        {{ $document_id ?: '___-_______-_' }}
                        @if($focusedInput === 'document_id')
                            <span class="animate-pulse ml-1 inline-block w-3.5 h-8 bg-cyan-400 rounded-sm shadow-[0_0_8px_rgba(34,211,238,0.8)]"></span>
                        @endif
                    </div>
                </div>

                <!-- Input PIN -->
                <div 
                    wire:click="setFocus('pin')" 
                    class="relative border rounded-2xl p-4 transition-all duration-300 cursor-pointer backdrop-blur-md {{ $focusedInput === 'pin' ? 'border-indigo-400/60 bg-white/15 shadow-[0_0_20px_rgba(129,140,248,0.2)]' : 'border-white/10 bg-white/5 hover:bg-white/10' }}"
                >
                    <label class="block text-xs font-black text-indigo-300/70 uppercase tracking-widest mb-1.5 drop-shadow-md">PIN de 4 Dígitos</label>
                    <div class="text-4xl font-mono tracking-[0.5em] text-white h-10 flex items-center drop-shadow-sm">
                        {{ str_repeat('•', strlen($pin)) }}{{ str_repeat('_', 4 - strlen($pin)) }}
                        @if($focusedInput === 'pin')
                            <span class="animate-pulse ml-2 inline-block w-3.5 h-8 bg-indigo-400 rounded-sm shadow-[0_0_8px_rgba(129,140,248,0.8)]"></span>
                        @endif
                    </div>
                </div>

                <!-- Teclado Numérico Gigante Glass -->
                <div class="grid grid-cols-3 gap-3 md:gap-4 mt-8">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $digit)
                        <button 
                            wire:click="appendDigit('{{ $digit }}')" 
                            class="bg-white/5 hover:bg-white/15 active:bg-white/25 border border-white/10 text-white text-3xl font-medium py-6 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.1)] backdrop-blur-md transition-all duration-200 transform active:scale-95"
                        >
                            {{ $digit }}
                        </button>
                    @endforeach
                    
                    <button 
                        wire:click="clearInput" 
                        class="bg-red-500/10 hover:bg-red-500/20 active:bg-red-500/30 text-red-300 hover:text-red-200 text-lg font-bold py-6 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.1)] backdrop-blur-md transition-all duration-200 border border-red-500/20 transform active:scale-95 tracking-wider"
                    >
                        BORRAR
                    </button>
                    
                    <button 
                        wire:click="appendDigit('0')" 
                        class="bg-white/5 hover:bg-white/15 active:bg-white/25 border border-white/10 text-white text-3xl font-medium py-6 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.1)] backdrop-blur-md transition-all duration-200 transform active:scale-95"
                    >
                        0
                    </button>
                    
                    <button 
                        wire:click="deleteDigit" 
                        class="bg-white/5 hover:bg-white/15 active:bg-white/25 text-white flex items-center justify-center py-6 rounded-2xl shadow-[0_4px_16px_rgba(0,0,0,0.1)] backdrop-blur-md transition-all duration-200 border border-white/10 transform active:scale-95 text-indigo-200 hover:text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" /></svg>
                    </button>
                </div>

                <!-- Entrar Button -->
                <button 
                    wire:click="login" 
                    class="w-full bg-gradient-to-r from-indigo-500/80 to-cyan-500/80 hover:from-indigo-500 hover:to-cyan-500 active:from-indigo-600 active:to-cyan-600 text-white font-black tracking-widest text-2xl py-6 rounded-2xl shadow-[0_0_30px_rgba(99,102,241,0.4)] backdrop-blur-md transition-all duration-300 transform active:scale-95 border border-white/20 mt-6 overflow-hidden relative group"
                >
                    <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700 pointer-events-none"></div>
                    INGRESAR
                </button>
            </div>
        </div>
    </div>
</div>
