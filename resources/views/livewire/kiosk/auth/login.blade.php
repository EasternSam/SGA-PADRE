<div class="h-full flex items-center justify-center p-4 sm:p-8 relative z-10"
     x-data="barcodeScanner()" 
     @keydown.window="handleScannerInput($event)"
     wire:poll.2s="checkQrAuthorization"> <!-- Auto-Listen for Mobile Auth -->

    <div class="max-w-screen-xl w-full bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] shadow-[0_8px_32px_rgba(0,0,0,0.5)] overflow-hidden border border-white/10 relative animate-[slideUp_0.6s_ease-out_forwards]" style="display: flex; flex-direction: row; width: 100%;">
        
        <!-- Elemento decorativo del Glass UI interior -->
        <div class="absolute -top-32 -left-32 w-[30rem] h-[30rem] bg-indigo-500/20 rounded-full blur-[80px] pointer-events-none animate-blob"></div>

        <!-- Columna 1: Instrucciones / Branding (25%) -->
        <div class="bg-slate-900/30 p-8 flex flex-col justify-center border-r border-white/10 relative z-10 hidden lg:flex" style="width: 30%;">
            <h2 class="text-4xl sm:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-slate-400 mb-6 leading-tight drop-shadow-2xl tracking-tight">
                Acceso<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 to-indigo-400 drop-shadow-[0_0_15px_rgba(34,211,238,0.5)]">Kiosco</span>
            </h2>
            
            <p class="text-indigo-200/90 text-lg mb-8 leading-relaxed font-medium tracking-wide">
                Ingresa usando tu Cédula/PIN o escanea el QR con el portal en tu celular.
            </p>

            <div class="bg-white/[0.05] backdrop-blur-xl rounded-2xl p-6 border border-white/10 shadow-[0_4px_16px_rgba(0,0,0,0.2)] hover:bg-white/[0.08] transition-colors duration-300 mt-auto">
                <div class="flex items-start gap-4">
                    <div class="bg-indigo-500/30 p-3 rounded-xl text-indigo-200 shrink-0 border border-indigo-500/30 shadow-[0_0_15px_rgba(99,102,241,0.2)]">
                        <svg class="w-6 h-6 drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <p class="text-sm text-indigo-100/80 leading-snug font-medium">Protege tu PIN. El Kiosco se cerrará solo si te alejas.</p>
                </div>
            </div>
        </div>

        <!-- Area Central (70%) que contiene Formulario y QR -->
        <div class="relative z-10" style="display: flex; flex-direction: row; width: 70%; flex-wrap: nowrap;">
            
            <!-- Columna Izquierda: Teclado Tradicional -->
            <div class="p-6 md:p-8 flex flex-col items-center justify-center relative z-10 border-r border-white/5 bg-slate-900/20" style="width: 55%; flex-shrink: 0;">
            
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
                <!-- Input Cédula con Feedback de Escáner -->
                <div 
                    wire:click="setFocus('document_id')" 
                    class="relative border rounded-2xl p-5 transition-all duration-300 cursor-pointer backdrop-blur-xl group overflow-hidden"
                    :class="{ 
                        'border-cyan-400/80 bg-white/10 shadow-[0_0_30px_rgba(34,211,238,0.25)] scale-[1.02]': '{{ $focusedInput }}' === 'document_id' && !scannedPulse,
                        'border-white/10 bg-white/[0.03] hover:bg-white/[0.08]': '{{ $focusedInput }}' !== 'document_id' && !scannedPulse,
                        'border-emerald-400 bg-emerald-500/20 shadow-[0_0_40px_rgba(16,185,129,0.5)] scale-105 transition-all duration-150': scannedPulse 
                    }"
                >
                    <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/0 via-cyan-500/10 to-transparent translate-x-[-100%] {{ $focusedInput === 'document_id' ? 'animate-[shimmer_2s_infinite]' : '' }} pointer-events-none"></div>
                    
                    <!-- Flash Verde de Escaneo Exitoso -->
                    <div class="absolute inset-0 bg-emerald-400/20 opacity-0 transition-opacity duration-300 pointer-events-none" :class="{ 'opacity-100': scannedPulse }"></div>

                    <label class="block text-sm font-black text-indigo-300/80 uppercase tracking-widest mb-2 drop-shadow-md">Cédula / Matrícula</label>
                    <div class="text-4xl font-mono text-white h-12 flex items-center drop-shadow-lg tracking-wider relative z-10">
                        {{ $document_id ?: '___-_______-_' }}
                        @if($focusedInput === 'document_id')
                            <span class="animate-pulse ml-2 inline-block w-4 h-10 bg-cyan-400 rounded-sm shadow-[0_0_15px_rgba(34,211,238,0.8)]" x-show="!scannedPulse"></span>
                        @endif
                        
                        <!-- Ícono de Check que aparece al escanear -->
                        <svg x-show="scannedPulse" x-transition.duration.300ms xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-emerald-300 ml-auto drop-shadow-[0_0_10px_rgba(16,185,129,0.8)]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <!-- Input PIN -->
                <div 
                    wire:click="setFocus('pin')" 
                    class="relative border rounded-2xl p-5 transition-all duration-300 cursor-pointer backdrop-blur-xl group overflow-hidden {{ $focusedInput === 'pin' ? 'border-indigo-400/80 bg-white/10 shadow-[0_0_30px_rgba(129,140,248,0.25)] scale-[1.02]' : 'border-white/10 bg-white/[0.03] hover:bg-white/[0.08]' }}"
                >
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/0 via-indigo-500/10 to-transparent translate-x-[-100%] {{ $focusedInput === 'pin' ? 'animate-[shimmer_2s_infinite]' : '' }} pointer-events-none"></div>
                    <label class="block text-sm font-black text-indigo-300/80 uppercase tracking-widest mb-2 drop-shadow-md">PIN de 4 Dígitos</label>
                    <div class="text-5xl font-mono tracking-[0.5em] text-white h-12 flex items-center drop-shadow-lg leading-none pt-2">
                        {{ str_repeat('•', strlen($pin)) }}{{ str_repeat('_', 4 - strlen($pin)) }}
                        @if($focusedInput === 'pin')
                            <span class="animate-pulse ml-2 inline-block w-4 h-10 bg-indigo-400 rounded-sm shadow-[0_0_15px_rgba(129,140,248,0.8)]"></span>
                        @endif
                    </div>
                </div>

                <!-- Teclado Numérico Gigante Glass -->
                <div class="grid grid-cols-3 gap-3 md:gap-4 mt-10">
                    @foreach([1, 2, 3, 4, 5, 6, 7, 8, 9] as $digit)
                        <button 
                            wire:click="appendDigit('{{ $digit }}')" 
                            class="bg-white/[0.03] hover:bg-white/10 active:bg-white/20 border border-white/10 hover:border-white/30 text-white text-4xl font-bold py-7 rounded-[1.5rem] shadow-[0_4px_16px_rgba(0,0,0,0.2)] backdrop-blur-xl transition-all duration-200 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 touch-manipulation group"
                        >
                            <span class="drop-shadow-md group-hover:drop-shadow-[0_0_10px_rgba(255,255,255,0.8)] transition-all">{{ $digit }}</span>
                        </button>
                    @endforeach
                    
                    <button 
                        wire:click="clearInput" 
                        class="bg-red-500/10 hover:bg-red-500/20 active:bg-red-500/30 text-red-300 hover:text-red-200 text-xl font-black py-7 rounded-[1.5rem] shadow-[0_4px_16px_rgba(0,0,0,0.2)] backdrop-blur-xl transition-all duration-200 border border-red-500/30 hover:border-red-500/50 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 tracking-widest drop-shadow-md touch-manipulation"
                    >
                        BORRAR
                    </button>
                    
                    <button 
                        wire:click="appendDigit('0')" 
                        class="bg-white/[0.03] hover:bg-white/10 active:bg-white/20 border border-white/10 hover:border-white/30 text-white text-4xl font-bold py-7 rounded-[1.5rem] shadow-[0_4px_16px_rgba(0,0,0,0.2)] backdrop-blur-xl transition-all duration-200 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 touch-manipulation group"
                    >
                        <span class="drop-shadow-md group-hover:drop-shadow-[0_0_10px_rgba(255,255,255,0.8)] transition-all">0</span>
                    </button>
                    
                    <button 
                        wire:click="deleteDigit" 
                        class="bg-white/[0.03] hover:bg-white/10 active:bg-white/20 text-indigo-300 flex items-center justify-center py-7 rounded-[1.5rem] shadow-[0_4px_16px_rgba(0,0,0,0.2)] backdrop-blur-xl transition-all duration-200 border border-white/10 hover:border-indigo-400/50 transform hover:-translate-y-1 active:translate-y-1 active:scale-95 hover:text-indigo-200 touch-manipulation group"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 drop-shadow-md group-hover:drop-shadow-[0_0_10px_rgba(129,140,248,0.8)] transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" /></svg>
                    </button>
                </div>

                <!-- Entrar Button -->
                <button 
                    wire:click="login" 
                    wire:loading.attr="disabled"
                    class="w-full bg-gradient-to-r from-indigo-500 to-cyan-500 hover:from-indigo-400 hover:to-cyan-400 active:from-indigo-600 active:to-cyan-600 border border-indigo-400/50 text-white font-black tracking-[0.2em] text-2xl py-6 rounded-[1.5rem] shadow-[0_0_40px_rgba(99,102,241,0.5)] backdrop-blur-xl transition-all duration-300 transform hover:-translate-y-2 active:translate-y-1 active:scale-95 mt-8 overflow-hidden relative group disabled:opacity-50 disabled:cursor-not-allowed touch-manipulation flex items-center justify-center gap-4"
                >
                    <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:animate-[shimmer_1.5s_infinite] pointer-events-none"></div>
                    <span wire:loading.remove wire:target="login" class="drop-shadow-lg">INGRESAR</span>
                    <span wire:loading wire:target="login" class="drop-shadow-lg flex items-center gap-3">
                        <svg class="animate-spin -ml-1 mr-3 h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        PROCESANDO
                    </span>
                </button>
            </div>
            </div> <!-- CIERRA LA COLUMNA IZQUIERDA -->
            
            <!-- Columna Derecha: Código QR Inteligente -->
            <div class="p-8 flex flex-col items-center justify-center bg-black/20 backdrop-blur-xl relative" style="width: 45%; flex-shrink: 0; display: flex;">
                
                <h3 class="text-3xl font-black text-white mb-4 tracking-wide text-center drop-shadow-md">Escaneo Rápido</h3>
                <p class="text-indigo-200/80 text-center text-sm font-medium mb-8 max-w-[250px]">
                    Si ya iniciaste sesión en tu celular, escanea este QR para entrar directo.
                </p>

                <div class="relative bg-white p-4 rounded-[2rem] shadow-[0_0_40px_rgba(16,185,129,0.3)] group transition-all duration-500 hover:shadow-[0_0_60px_rgba(16,185,129,0.5)] hover:scale-105">
                    
                    <!-- Marco decorativo del escáner -->
                    <div class="absolute -top-2 -left-2 w-8 h-8 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl transition-all duration-300 group-hover:w-10 group-hover:h-10 group-hover:border-emerald-300"></div>
                    <div class="absolute -top-2 -right-2 w-8 h-8 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl transition-all duration-300 group-hover:w-10 group-hover:h-10 group-hover:border-emerald-300"></div>
                    <div class="absolute -bottom-2 -left-2 w-8 h-8 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl transition-all duration-300 group-hover:w-10 group-hover:h-10 group-hover:border-emerald-300"></div>
                    <div class="absolute -bottom-2 -right-2 w-8 h-8 border-b-4 border-r-4 border-emerald-400 rounded-br-xl transition-all duration-300 group-hover:w-10 group-hover:h-10 group-hover:border-emerald-300"></div>
                    
                    <!-- Láser animado del escáner -->
                    <div class="absolute top-0 left-0 w-full h-1 bg-emerald-400/80 shadow-[0_0_15px_rgba(16,185,129,1)] z-20 opacity-0 group-hover:opacity-100 animate-[scan_2s_ease-in-out_infinite] pointer-events-none rounded-full"></div>

                    <!-- El QR Generado puro en SVG -->
                    <div class="w-48 h-48 md:w-64 md:h-64 [&>svg]:w-full [&>svg]:h-full object-contain filter grayscale contrast-125">
                        {!! $qrSvg !!}
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-center gap-2 text-emerald-400/80 font-mono text-sm tracking-widest animate-pulse">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>CÓDIGO DINÁMICO ACTIVO</span>
                </div>

                <!-- Botón de Registro para Nuevos Estudiantes -->
                <div class="mt-12 w-full pt-8 border-t border-white/10 flex flex-col items-center">
                    <p class="text-indigo-200/60 text-sm font-semibold uppercase tracking-widest mb-4">¿No eres estudiante aún?</p>
                    <a href="{{ route('kiosk.signup') }}" class="w-full max-w-[80%] bg-gradient-to-r from-indigo-500/20 to-cyan-500/20 hover:from-indigo-500/40 hover:to-cyan-500/40 border border-indigo-400/30 text-white font-bold tracking-widest text-lg py-4 rounded-2xl shadow-[0_0_20px_rgba(99,102,241,0.1)] transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3 group">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-400 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        INSCRÍBETE AQUÍ
                    </a>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Script AlpineJS para Escaneo Mágico -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('barcodeScanner', () => ({
                barcodeString: '',
                lastKeyTime: 0,
                barcodeTimeout: null,
                scannedPulse: false,
                
                // Configurable tolerance for physical scanners (usually very fast, < 50ms per keystroke)
                keystrokeDelayThreshold: 50, 
                
                handleScannerInput(event) {
                    // Ignorar eventos que provengan deliberadamente de inputs (si lo hubiere)
                    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return;

                    const currentTime = new Date().getTime();
                    
                    // Si ha pasado demasiado tiempo desde la última tecla, asume que es escritura humana y resetea el string
                    if (this.lastKeyTime > 0 && (currentTime - this.lastKeyTime) > this.keystrokeDelayThreshold) {
                        this.barcodeString = '';
                    }

                    // Ignorar Shift, y teclas especiales que no sean Enter
                    if (event.key === 'Shift' || event.ctrlKey || event.altKey) return;
                    
                    // Si presiona "Enter" y tenemos un string razonable (ej. más de 6 números)
                    if (event.key === 'Enter') {
                        if (this.barcodeString.length >= 6) {
                            // ¡Es un escaneo válido! Evitar el submit default por si acaso.
                            event.preventDefault();
                            this.processScannedBarcode(this.barcodeString);
                        }
                        this.barcodeString = ''; // Resetear
                    } else if (event.key.length === 1) { // Solo añadir caracteres normales
                        // Algunos escáneres añaden el sufijo - , permitimos números y guiones
                        if(/^[0-9-a-zA-Z]$/.test(event.key)){
                             this.barcodeString += event.key;
                        }
                    }

                    this.lastKeyTime = currentTime;
                },

                processScannedBarcode(scannedData) {
                    console.log("Magic Scan Detected:", scannedData);
                    
                    // 1. Enviar el string limpio al componente Livewire (Cédula o Matrícula)
                    // Eliminamos guiones si el escáner los incluye, o los dejamos, el backend de login debe limpiar
                    @this.call('magicScanInput', scannedData);

                    // 2. Disparar Retroalimentación Visual de Éxito Inmediato ("Nivel Dios")
                    this.scannedPulse = true;
                    
                    // 3. Audio opcional (descomentar si se desea un Pip)
                    // new Audio('/sounds/beep.mp3').play().catch(e => {});

                    setTimeout(() => {
                        this.scannedPulse = false;
                    }, 1000); // El pulso verde interactivo dura 1 segundo
                }
            }))
        })
    </script>
</div>
