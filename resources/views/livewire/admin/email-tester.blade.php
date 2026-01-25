<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            
            {{-- Encabezado --}}
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Central de Comunicaciones</h2>
                    <p class="text-sm text-gray-500">Envío de avisos masivos por lotes (sin bloquear el sistema).</p>
                </div>
                <div class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                    Sistema Batch Activo
                </div>
            </div>

            <div class="p-6 text-gray-900">
                
                {{-- BARRA DE PROGRESO (Solo visible procesando) --}}
                @if($isProcessing)
                    {{-- CAMBIO: Polling cada 2 segundos para no saturar SQLite --}}
                    <div class="mb-8 p-4 bg-indigo-50 border border-indigo-200 rounded-lg animate-pulse" wire:poll.2s="processBatch">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-indigo-700">Enviando correos... ({{ $sentCount }}/{{ $totalToSend }})</span>
                            <span class="text-sm font-medium text-indigo-700">{{ $progress }}%</span>
                        </div>
                        <div class="w-full bg-indigo-200 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="text-xs text-indigo-500 mt-2 text-center">Por favor no cierre esta ventana hasta que finalice.</p>
                    </div>
                @endif

                <!-- Mensajes Flash -->
                @if (session()->has('success'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                        <p class="font-bold">¡Listo!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Columna Izquierda: Configuración -->
                    <div class="lg:col-span-2 space-y-6">
                        <form wire:submit.prevent="startSending" class="space-y-5">
                            
                            <!-- Selección de Audiencia -->
                            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                                <x-input-label for="audience" :value="__('Destinatario / Audiencia')" class="text-gray-800 font-bold" />
                                <select wire:model.live="audience" id="audience" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isProcessing)>
                                    <option value="individual">👤 Prueba Individual</option>
                                    <option value="section">📚 Sección Académica</option>
                                    <option value="debt">💰 Deudores (Pagos Pendientes)</option>
                                    <option value="all">📢 Todos los Estudiantes</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-2">
                                    Alcance estimado: <strong>{{ $recipientCount }} destinatarios</strong>.
                                </p>
                            </div>

                            <!-- Campos Dinámicos -->
                            @if($audience === 'individual')
                                <div>
                                    <x-input-label for="emailTo" :value="__('Correo Destino')" />
                                    <x-text-input wire:model.live.debounce.500ms="emailTo" id="emailTo" class="block mt-1 w-full" type="email" placeholder="ejemplo@correo.com" @disabled($isProcessing) />
                                    <x-input-error :messages="$errors->get('emailTo')" class="mt-2" />
                                </div>
                            @endif

                            @if($audience === 'section')
                                <div>
                                    <x-input-label for="sectionId" :value="__('Seleccionar Sección')" />
                                    <select wire:model.live="sectionId" id="sectionId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @disabled($isProcessing)>
                                        <option value="">-- Seleccione una sección --</option>
                                        @foreach($availableSections as $section)
                                            <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('sectionId')" class="mt-2" />
                                </div>
                            @endif

                            <hr class="border-gray-100">

                            <!-- Contenido -->
                            <div>
                                <x-input-label for="subject" :value="__('Asunto')" />
                                <x-text-input wire:model="subject" id="subject" class="block mt-1 w-full font-semibold" type="text" placeholder="Ej: Aviso Importante" @disabled($isProcessing) />
                                <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="messageBody" :value="__('Mensaje')" />
                                <textarea wire:model="messageBody" id="messageBody" rows="6" 
                                    class="block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                    placeholder="Escriba su mensaje aquí..." @disabled($isProcessing)></textarea>
                                <x-input-error :messages="$errors->get('messageBody')" class="mt-2" />
                            </div>

                            <!-- Botón de Envío -->
                            <div class="pt-4">
                                @if($isProcessing)
                                    <button type="button" disabled class="w-full py-3 px-4 rounded-md shadow-sm text-sm font-medium text-white bg-gray-400 cursor-not-allowed">
                                        Envío en curso... Espere
                                    </button>
                                @else
                                    <button type="submit" 
                                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        Iniciar Envío Masivo
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Columna Derecha: Monitor -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-900 text-green-400 p-4 rounded-xl font-mono text-xs h-[500px] overflow-auto shadow-inner border border-gray-700 flex flex-col">
                            <div class="border-b border-gray-700 pb-2 mb-2 flex justify-between items-center">
                                <h3 class="text-white font-bold uppercase tracking-wider">Monitor</h3>
                                <div class="h-2 w-2 rounded-full {{ $isProcessing ? 'bg-green-500 animate-pulse' : 'bg-gray-500' }}"></div>
                            </div>
                            
                            <div class="flex-1 overflow-y-auto space-y-1 font-mono">
                                @if(empty($debugLog))
                                    <p class="opacity-30 italic text-center mt-10">Esperando orden...</p>
                                @else
                                    @foreach($debugLog as $log)
                                        <div class="break-words border-l-2 border-gray-700 pl-2 hover:bg-gray-800 transition-colors">
                                            {!! $log !!}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>