<div x-data="{
        focusedAction: @entangle('focusedInput'),
        handleScannerInput(event) {
            // Support for optional barcode scanners if they scan cedulas.
            if (this.focusedAction === 'cedula' && event.key && event.key.length === 1 && /[0-9A-Z-]/.test(event.key)) {
                @this.call('appendDigit', event.key);
            } else if (event.key === 'Backspace') {
                @this.call('deleteDigit');
            }
        }
    }" 
    @keydown.window="handleScannerInput($event)"
    class="max-w-screen-2xl mx-auto w-full h-full flex flex-col relative animate-[slideUp_0.6s_ease-out_forwards]">
    
    <!-- Background Decorators -->
    <div class="absolute -top-32 -right-32 w-[30rem] h-[30rem] bg-indigo-500/20 rounded-full blur-[80px] pointer-events-none animate-blob"></div>
    <div class="absolute -bottom-32 -left-32 w-[35rem] h-[35rem] bg-cyan-500/20 rounded-full blur-[100px] pointer-events-none animate-blob-slow transform rotate-45"></div>

    <!-- Header Actions -->
    <div class="flex justify-between items-center mb-6 z-10">
        <button 
            wire:click="previousStep" 
            @if($step == 1) onclick="window.location.href='{{ route('kiosk.login') }}'" @endif
            class="bg-white/5 hover:bg-white/10 text-white px-6 py-3 rounded-2xl backdrop-blur-md border border-white/10 flex items-center gap-3 transition-all hover:-translate-x-1"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            <span class="font-bold tracking-widest uppercase text-sm">Volver al Inicio</span>
        </button>

        <h2 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white to-cyan-300 drop-shadow-md">NUEVO INGRESO</h2>
        
        <!-- Progress Steps -->
        <div class="flex items-center gap-2 bg-black/40 p-2 rounded-2xl backdrop-blur-md border border-white/5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold {{ $step >= 1 ? 'bg-indigo-500 text-white shadow-[0_0_15px_rgba(99,102,241,0.5)]' : 'bg-white/10 text-white/50' }}">1</div>
            <div class="w-8 h-1 bg-white/10 rounded-full"><div class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: {{ $step >= 2 ? '100%' : '0%' }}"></div></div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold {{ $step >= 2 ? 'bg-indigo-500 text-white shadow-[0_0_15px_rgba(99,102,241,0.5)]' : 'bg-white/10 text-white/50' }}">2</div>
            <div class="w-8 h-1 bg-white/10 rounded-full"><div class="h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: {{ $step >= 3 ? '100%' : '0%' }}"></div></div>
            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold {{ $step >= 3 ? 'bg-indigo-500 text-white shadow-[0_0_15px_rgba(99,102,241,0.5)]' : 'bg-white/10 text-white/50' }}">3</div>
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="flex-1 bg-white/[0.03] backdrop-blur-2xl backdrop-saturate-200 rounded-[2.5rem] shadow-[0_8px_32px_rgba(0,0,0,0.5)] border border-white/10 overflow-hidden relative flex flex-col">
        
        <!-- Global Error Messages -->
        @if($errors->has('registration'))
            <div class="bg-red-500/20 backdrop-blur-md border tracking-wide border-red-500/50 p-4 rounded-xl shadow-[0_4px_16px_rgba(239,68,68,0.15)] animate-fade-in m-6 mb-0 text-red-100 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span class="font-semibold">{{ $errors->first('registration') }}</span>
            </div>
        @endif

        @if($step == 1)
        <!-- ================= PASO 1: SELECCIONAR CURSO ================= -->
        <div class="p-8 md:p-12 flex-1 overflow-hidden flex flex-col relative animate-fade-in">
            <div class="text-center mb-8 shrink-0">
                <h3 class="text-4xl font-black text-white mb-2 drop-shadow-md">Toca la clase a la que deseas inscribirte</h3>
                <p class="text-indigo-200/80 text-xl font-medium">Selecciona el módulo y el horario para iniciar de forma instantánea.</p>
            </div>

            <div class="max-w-2xl mx-auto mb-8 w-full relative shrink-0 z-20">
                <input type="text" wire:model="search" wire:click="setFocus('search')" readonly
                       class="w-full bg-black/40 border {{ $focusedInput === 'search' ? 'border-indigo-500 ring-4 ring-indigo-500/20' : 'border-white/10' }} text-white text-xl px-6 py-5 rounded-full transition-all outline-none pl-16 cursor-pointer" 
                       placeholder="Buscar por materia, módulo o profesor...">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 absolute left-6 top-[18px] text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                
                <!-- Clear Search -->
                <button type="button" x-show="$wire.search.length > 0" @click="$wire.set('search', ''); $wire.set('focusedInput', 'search')" class="absolute right-6 top-[18px] text-white/50 hover:text-white transition-colors bg-white/10 p-1.5 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 overflow-y-auto flex-1 pb-10 transition-opacity duration-300" :class="focusedAction === 'search' ? 'opacity-20 pointer-events-none' : ''">
                @forelse($this->filteredSchedules as $schedule)
                    <button 
                        wire:click="selectSchedule({{ $schedule['id'] }})"
                        class="group text-left p-6 rounded-[2rem] bg-gradient-to-br from-white/10 to-white/5 border border-white/10 backdrop-blur-sm hover:from-indigo-600/40 hover:to-cyan-600/40 hover:border-indigo-400/50 transition-all duration-300 transform hover:-translate-y-2 hover:shadow-[0_15px_30px_rgba(99,102,241,0.2)]"
                    >
                        <div class="flex justify-between items-start mb-4">
                            <span class="bg-indigo-500/20 text-indigo-300 text-xs font-bold px-3 py-1.5 rounded-lg border border-indigo-500/30 backdrop-blur-md tracking-widest uppercase truncate max-w-[70%]">
                                {{ $schedule['course_name'] }}
                            </span>
                            <span class="bg-emerald-500/20 text-emerald-300 text-lg font-black px-3 py-1 rounded-lg border border-emerald-500/30">
                                RD${{ number_format($schedule['cost'], 2) }}
                            </span>
                        </div>
                        
                        <h4 class="text-2xl font-black text-white mb-3 group-hover:text-indigo-100 transition-colors drop-shadow-sm leading-tight">
                            {{ $schedule['module_name'] }}
                        </h4>
                        
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 text-indigo-200/80 bg-black/20 p-3 rounded-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="text-sm font-semibold truncate">{{ $schedule['schedule_str'] }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-indigo-200/80">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span class="text-sm">{{ $schedule['teacher_name'] }}</span>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="col-span-full py-20 flex flex-col items-center justify-center text-indigo-200/50 bg-black/10 rounded-[2rem] border-2 border-dashed border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="text-2xl font-medium">No se encontraron clases disponibles bajo ese criterio.</p>
                    </div>
                @endforelse
            </div>

            <!-- Floating Keyboard Layer for Step 1 Search -->
            <div x-show="focusedAction === 'search'" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
                 class="absolute bottom-0 left-0 w-full bg-slate-900/95 backdrop-blur-3xl border-t border-white/10 p-6 md:p-8 z-50 rounded-b-[2.5rem] flex flex-col items-center mt-auto shadow-[0_-15px_40px_rgba(0,0,0,0.5)]">
                 
                 <div class="flex justify-between w-full max-w-4xl mb-4 items-center">
                     <span class="text-indigo-300 font-bold tracking-widest uppercase text-sm flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg> Teclado de Búsqueda</span>
                     <button type="button" @click="$wire.set('focusedInput', '')" class="bg-indigo-500/20 text-indigo-300 px-6 py-2.5 rounded-xl font-bold uppercase text-xs border border-indigo-500/30 hover:bg-indigo-500/40 transition-colors">Ver Resultados / Ocultar</button>
                 </div>

                 <!-- QWERTY KEYBOARD -->
                 <div class="flex flex-col gap-3 w-full max-w-4xl">
                     <div class="flex justify-center gap-2">
                         <template x-for="k in ['q','w','e','r','t','y','u','i','o','p']">
                             <button type="button" @click="$wire.appendDigit(k)" x-text="k.toUpperCase()" class="flex-1 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-2xl font-black py-5 rounded-2xl shadow-sm transition-all active:scale-95 touch-manipulation"></button>
                         </template>
                     </div>
                     <div class="flex justify-center gap-2 px-8">
                         <template x-for="k in ['a','s','d','f','g','h','j','k','l']">
                             <button type="button" @click="$wire.appendDigit(k)" x-text="k.toUpperCase()" class="flex-1 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-2xl font-black py-5 rounded-2xl shadow-sm transition-all active:scale-95 touch-manipulation"></button>
                         </template>
                     </div>
                     <div class="flex justify-center gap-2">
                         <template x-for="k in ['z','x','c','v','b','n','m']">
                             <button type="button" @click="$wire.appendDigit(k)" x-text="k.toUpperCase()" class="flex-1 bg-white/10 hover:bg-white/20 border border-white/20 text-white text-2xl font-black py-5 rounded-2xl shadow-sm transition-all active:scale-95 touch-manipulation"></button>
                         </template>
                         <button type="button" @click="$wire.deleteDigit()" class="flex-[1.5] bg-red-500/20 border border-red-500/40 text-red-400 py-5 rounded-2xl flex items-center justify-center active:scale-95">
                             <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" /></svg>
                         </button>
                     </div>
                     <div class="flex justify-center gap-2">
                         <button type="button" @click="$wire.appendDigit(' ')" class="flex-[3] bg-indigo-500/20 border border-indigo-500/30 hover:bg-indigo-500/30 text-indigo-200 text-xl font-black tracking-widest py-5 rounded-2xl">ESPACIO</button>
                     </div>
                 </div>
            </div>
        </div>
        
        @elseif($step == 2)
        <!-- ================= PASO 2: FORMULARIO PERSONAL ================= -->
        <div class="flex-1 flex flex-col lg:flex-row relative z-10 animate-fade-in overflow-hidden">
            <!-- Formulario Izquierdo -->
            <div class="w-full lg:w-3/5 p-8 md:p-12 overflow-y-auto border-r border-white/5 bg-slate-900/40">
                <div class="mb-10">
                    <h3 class="text-4xl font-black text-white mb-2 drop-shadow-md">Crea tu perfil ahora</h3>
                    <p class="text-indigo-200/80 text-lg font-medium">Toca los campos para usar el teclado virtual en pantalla o físico.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nombre -->
                    <div class="space-y-2">
                        <label class="text-indigo-200 font-bold uppercase tracking-widest text-xs px-2">Nombres</label>
                        <input type="text" wire:model="first_name" wire:click="setFocus('first_name')" 
                            class="w-full bg-black/40 border {{ $errors->has('first_name') ? 'border-red-500' : 'border-white/10 focus:border-indigo-500' }} text-white text-xl font-medium px-6 py-5 rounded-[1.5rem] focus:ring-4 focus:ring-indigo-500/20 transition-all outline-none" 
                            placeholder="Ej. Juan Andrés">
                        @error('first_name') <span class="text-red-400 text-sm font-semibold px-2">{{ $message }}</span> @enderror
                    </div>

                    <!-- Apellido -->
                    <div class="space-y-2">
                        <label class="text-indigo-200 font-bold uppercase tracking-widest text-xs px-2">Apellidos</label>
                        <input type="text" wire:model="last_name" wire:click="setFocus('last_name')" 
                            class="w-full bg-black/40 border {{ $errors->has('last_name') ? 'border-red-500' : 'border-white/10 focus:border-indigo-500' }} text-white text-xl font-medium px-6 py-5 rounded-[1.5rem] focus:ring-4 focus:ring-indigo-500/20 transition-all outline-none" 
                            placeholder="Ej. Pérez">
                        @error('last_name') <span class="text-red-400 text-sm font-semibold px-2">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div class="col-span-1 md:col-span-2 space-y-2">
                        <label class="text-indigo-200 font-bold uppercase tracking-widest text-xs px-2">Correo Electrónico (Tu acceso al portal Móvil)</label>
                        <input type="email" wire:model="email" wire:click="setFocus('email')" 
                            class="w-full bg-black/40 border {{ $errors->has('email') ? 'border-red-500' : 'border-white/10 focus:border-indigo-500' }} text-white text-xl font-medium px-6 py-5 rounded-[1.5rem] focus:ring-4 focus:ring-indigo-500/20 transition-all outline-none" 
                            placeholder="tucorreo@ejemplo.com">
                        @error('email') <span class="text-red-400 text-sm font-semibold px-2">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-indigo-200 font-bold uppercase tracking-widest text-xs px-2 flex justify-between items-center">
                            <span>Cédula o Pasaporte</span>
                            <span class="bg-indigo-500/20 text-indigo-300 text-[10px] px-2 py-0.5 rounded outline outline-1 outline-indigo-500/50">Usa el teclado Kiosco</span>
                        </label>
                        <input type="text" wire:model="cedula" wire:click="setFocus('cedula')" readonly
                            class="w-full bg-indigo-900/30 cursor-pointer border {{ $errors->has('cedula') ? 'border-red-500' : ($focusedInput === 'cedula' ? 'border-indigo-400 ring-4 ring-indigo-500/20' : 'border-white/10') }} text-white text-2xl font-mono tracking-widest px-6 py-5 rounded-[1.5rem] transition-all outline-none" 
                            placeholder="-----------">
                        @error('cedula') <span class="text-red-400 text-sm font-semibold px-2">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-indigo-200 font-bold uppercase tracking-widest text-xs px-2 flex justify-between items-center">
                            <span>Celular (WhatsApp)</span>
                            <span class="bg-indigo-500/20 text-indigo-300 text-[10px] px-2 py-0.5 rounded outline outline-1 outline-indigo-500/50">Usa el teclado Kiosco</span>
                        </label>
                        <input type="text" wire:model="phone" wire:click="setFocus('phone')" readonly
                            class="w-full bg-indigo-900/30 cursor-pointer border {{ $errors->has('phone') ? 'border-red-500' : ($focusedInput === 'phone' ? 'border-indigo-400 ring-4 ring-indigo-500/20' : 'border-white/10') }} text-white text-2xl font-mono tracking-widest px-6 py-5 rounded-[1.5rem] transition-all outline-none" 
                            placeholder="809-------">
                        @error('phone') <span class="text-red-400 text-sm font-semibold px-2">{{ $message }}</span> @enderror
                    </div>

                    <!-- PIN Maestro del Kiosco -->
                    <div class="col-span-1 md:col-span-2 space-y-2 mt-4 bg-indigo-900/20 p-6 border border-indigo-500/30 rounded-[2rem] relative overflow-hidden">
                        <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-500/20 rounded-full blur-2xl"></div>
                        <label class="text-indigo-200 font-bold uppercase tracking-widest text-sm px-2 flex items-center gap-2 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Clave de Kiosco (4 Dígitos)
                        </label>
                        <p class="text-indigo-200/60 text-sm px-2 mb-4">Escribe un PIN fácil de recordar. Lo usarás para acceder rápidamente a esta máquina de autoservicio.</p>
                        
                        <input type="password" wire:model="pin" wire:click="setFocus('pin')" readonly maxlength="4"
                            class="w-full text-center bg-black/60 cursor-pointer border {{ $errors->has('pin') ? 'border-red-500' : ($focusedInput === 'pin' ? 'border-emerald-400 shadow-[0_0_20px_rgba(52,211,153,0.3)]' : 'border-white/10') }} text-white text-4xl font-black tracking-[1em] px-6 py-6 rounded-[1.5rem] transition-all outline-none" 
                            placeholder="••••">
                        @error('pin') <span class="text-red-400 text-sm font-semibold px-2">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="mt-10 flex justify-end">
                    <button wire:click="nextStep" class="bg-gradient-to-r from-indigo-500 to-cyan-500 hover:from-indigo-400 hover:to-cyan-400 text-white font-black tracking-widest text-xl py-6 px-12 rounded-[1.5rem] shadow-[0_0_30px_rgba(99,102,241,0.4)] transition-all transform hover:-translate-y-1 flex items-center gap-3 w-full md:w-auto justify-center">
                        CONTINUAR
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </div>

            <!-- Virtual Numpad & Keyboard (Derecha) -->
            <div class="w-full lg:w-2/5 p-8 flex flex-col justify-center items-center bg-black/20 backdrop-blur-3xl relative">
                <div class="w-full transition-all duration-300" :class="['pin', 'phone', 'cedula'].includes(focusedAction) ? 'max-w-sm' : 'max-w-2xl'">
                    <div class="flex items-center justify-between mb-6 px-4">
                         <h4 class="text-white/60 font-bold uppercase tracking-widest text-sm flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            Teclado de Kiosco
                        </h4>
                        <span class="text-emerald-400 text-xs font-bold uppercase px-2 py-1 bg-emerald-500/10 rounded border border-emerald-500/20" x-text="focusedAction || 'INACTIVO'"></span>
                    </div>

                    <!-- NUMPAD -->
                    <div x-show="['pin', 'phone', 'cedula'].includes(focusedAction)" class="grid grid-cols-3 gap-3">
                        @for ($i = 1; $i <= 9; $i++)
                            <button wire:click="appendDigit('{{ $i }}')" class="bg-white/5 hover:bg-white/10 active:bg-white/20 border border-white/10 text-white text-3xl font-black py-6 rounded-[1.5rem] shadow-sm backdrop-blur-xl transition-all transform hover:-translate-y-1 active:scale-95 touch-manipulation">{{ $i }}</button>
                        @endfor
                        <button wire:click="appendDigit('-')" class="bg-white/5 hover:bg-white/10 active:bg-white/20 border border-white/10 text-white text-3xl font-black py-6 rounded-[1.5rem] shadow-sm backdrop-blur-xl transition-all transform hover:-translate-y-1 active:scale-95 touch-manipulation">-</button>
                        <button wire:click="appendDigit('0')" class="bg-white/5 hover:bg-white/10 active:bg-white/20 border border-white/10 text-white text-3xl font-black py-6 rounded-[1.5rem] shadow-sm backdrop-blur-xl transition-all transform hover:-translate-y-1 active:scale-95 touch-manipulation">0</button>
                        <button wire:click="deleteDigit" class="bg-red-500/10 hover:bg-red-500/20 active:bg-red-500/30 border border-red-500/30 text-red-400 flex items-center justify-center py-6 rounded-[1.5rem] shadow-sm backdrop-blur-xl transition-all transform hover:-translate-y-1 active:scale-95 touch-manipulation">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" /></svg>
                        </button>
                    </div>

                    <!-- QWERTY KEYBOARD -->
                    <div x-cloak x-show="['first_name', 'last_name', 'email'].includes(focusedAction)" class="flex flex-col gap-2 w-full animate-fade-in">
                        <div class="flex justify-center gap-1.5">
                            <template x-for="k in ['q','w','e','r','t','y','u','i','o','p']">
                                <button type="button" @click="$wire.appendDigit(k)" x-text="k.toUpperCase()" class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-white text-xl font-black py-4 rounded-xl shadow-sm transition-all active:scale-95 touch-manipulation"></button>
                            </template>
                        </div>
                        <div class="flex justify-center gap-1.5 px-4">
                            <template x-for="k in ['a','s','d','f','g','h','j','k','l']">
                                <button type="button" @click="$wire.appendDigit(k)" x-text="k.toUpperCase()" class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-white text-xl font-black py-4 rounded-xl shadow-sm transition-all active:scale-95 touch-manipulation"></button>
                            </template>
                        </div>
                        <div class="flex justify-center gap-1.5">
                            <template x-for="k in ['z','x','c','v','b','n','m']">
                                <button type="button" @click="$wire.appendDigit(k)" x-text="k.toUpperCase()" class="flex-1 bg-white/5 hover:bg-white/10 border border-white/10 text-white text-xl font-black py-4 rounded-xl shadow-sm transition-all active:scale-95 touch-manipulation"></button>
                            </template>
                            <button type="button" @click="$wire.deleteDigit()" class="flex-[1.5] bg-red-500/10 border border-red-500/30 text-red-400 py-4 rounded-xl flex items-center justify-center active:scale-95 shadow-sm transition-all">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" /></svg>
                            </button>
                        </div>
                        <div class="flex justify-center gap-1.5 mt-1">
                            <button type="button" @click="$wire.appendDigit('@')" class="flex-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-black text-xl py-4 rounded-xl hover:bg-indigo-500/30">@</button>
                            <button type="button" @click="$wire.appendDigit(' ')" class="flex-[3] bg-white/5 text-white/50 border border-white/10 font-bold tracking-widest text-sm py-4 rounded-xl hover:bg-white/10 hover:text-white">ESPACIO</button>
                            <button type="button" @click="$wire.appendDigit('.')" class="flex-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-black text-xl py-4 rounded-xl hover:bg-indigo-500/30">.</button>
                            <button type="button" x-show="focusedAction === 'email'" @click="$wire.appendDigit('.com')" class="flex-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-bold text-sm py-4 rounded-xl hover:bg-emerald-500/30">.com</button>
                        </div>
                    </div>

                    <!-- Placeholder text when no input is focused -->
                    <div x-show="!['pin', 'phone', 'cedula', 'first_name', 'last_name', 'email'].includes(focusedAction)" class="flex flex-col items-center justify-center text-center p-10 opacity-30 mt-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mb-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                        <p class="text-white font-medium text-lg">Toca un campo a la izquierda para activar este teclado.</p>
                    </div>

                </div>
            </div>
        </div>

        @elseif($step == 3)
        <!-- ================= PASO 3: CONFIRMACIÓN Y PRE-EMISIÓN ================= -->
        <div class="flex-1 flex flex-col items-center justify-center p-8 md:p-12 animate-fade-in text-center z-10">
            
            <div class="h-24 w-24 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full flex items-center justify-center mb-8 shadow-[0_0_40px_rgba(52,211,153,0.5)] border-4 border-emerald-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
            </div>

            <h3 class="text-4xl lg:text-5xl font-black text-white mb-4 drop-shadow-md">Confirmar Inscripción</h3>
            <p class="text-indigo-200/80 text-xl md:text-2xl max-w-2xl font-medium mb-10 leading-relaxed">
                ¡Perfecto <strong>{{ $first_name }}</strong>! Estamos a un toque de generar tu código de estudiante y auto-inscribirte en la clase.
            </p>

            <div class="bg-black/30 backdrop-blur-xl border border-white/10 rounded-[2.5rem] p-8 md:p-10 w-full max-w-3xl mb-10 text-left relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl transform translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
                
                <h4 class="text-white/50 uppercase tracking-[0.2em] font-bold text-xs mb-4">Resumen del Cargo Académico</h4>
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-white/10 pb-6 mb-6 gap-4">
                    <div>
                        <span class="bg-indigo-500/20 text-indigo-300 text-xs font-bold px-3 py-1 rounded-lg border border-indigo-500/30 uppercase tracking-widest inline-block mb-3">
                            {{ $selectedScheduleDetails['course_name'] }}
                        </span>
                        <h4 class="text-3xl font-black text-white leading-tight">
                            {{ $selectedScheduleDetails['module_name'] }}
                        </h4>
                    </div>
                    <div class="text-right">
                        <span class="text-emerald-400 font-black text-4xl whitespace-nowrap">RD$ {{ number_format($selectedScheduleDetails['cost'], 2) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-white/40 text-xs font-bold uppercase tracking-widest mb-1">Cédula</p>
                        <p class="text-white font-medium text-lg">{{ $cedula }}</p>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <p class="text-white/40 text-xs font-bold uppercase tracking-widest mb-1">Horario</p>
                        <p class="text-white font-medium text-lg truncate">{{ $selectedScheduleDetails['schedule_str'] }}</p>
                    </div>
                    <div>
                        <p class="text-white/40 text-xs font-bold uppercase tracking-widest mb-1">Profesor</p>
                        <p class="text-white font-medium text-lg truncate">{{ $selectedScheduleDetails['teacher_name'] }}</p>
                    </div>
                </div>
            </div>

            <button wire:click="register" wire:loading.attr="disabled" class="w-full max-w-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-black tracking-[0.2em] text-2xl py-7 rounded-[2rem] shadow-[0_0_50px_rgba(16,185,129,0.5)] transition-all duration-300 transform hover:-translate-y-2 active:scale-95 group relative overflow-hidden disabled:opacity-50">
                <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:animate-[shimmer_1.5s_infinite] pointer-events-none"></div>
                <span wire:loading.remove wire:target="register" class="flex items-center justify-center gap-4">
                    FINALIZAR E IR A PAGAR
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                </span>
                <span wire:loading wire:target="register" class="flex items-center justify-center gap-3">
                    <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    PROCESANDO MATRÍCULA...
                </span>
            </button>
            <p class="text-white/40 mt-6 text-sm">Al tocar, el Kiosco registrará tu perfil y encenderá el Verifone CardNet.</p>
        </div>
        @endif
    </div>

</div>
