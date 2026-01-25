<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            
            {{-- Encabezado --}}
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Central de Comunicaciones</h2>
                    <p class="text-sm text-gray-500">Envío de avisos masivos, recordatorios de pago y pruebas.</p>
                </div>
                <div class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold">
                    SMTP Configurado
                </div>
            </div>

            <div class="p-6 text-gray-900">
                
                <!-- Mensajes Flash -->
                @if (session()->has('success'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded shadow-sm">
                        <div class="flex">
                            <div class="py-1"><svg class="fill-current h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.59l4.3-4.3 1.4 1.42L9 14.41l-3.7-3.7 1.4-1.42z"/></svg></div>
                            <div>
                                <p class="font-bold">Envío Exitoso</p>
                                <p class="text-sm">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded shadow-sm">
                        <p class="font-bold">Error en el envío</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Columna Izquierda: Configuración del Envío -->
                    <div class="lg:col-span-2 space-y-6">
                        <form wire:submit.prevent="sendEmail" class="space-y-5">
                            
                            <!-- Selección de Audiencia -->
                            <div class="bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                                <x-input-label for="audience" :value="__('Destinatario / Audiencia')" class="text-indigo-800 font-bold" />
                                <select wire:model.live="audience" id="audience" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="individual">👤 Prueba Individual (Un solo correo)</option>
                                    <option value="section">📚 Sección/Grupo Específico</option>
                                    <option value="debt">💰 Estudiantes con Pagos Pendientes (Deudores)</option>
                                    <option value="all">📢 Todos los Estudiantes Activos (Masivo)</option>
                                </select>
                                <p class="text-xs text-indigo-600 mt-2">
                                    Se enviará a: <strong>{{ $recipientCount }} personas</strong> estimadas.
                                </p>
                            </div>

                            <!-- Campos Dinámicos según Audiencia -->
                            
                            <!-- Caso: Individual -->
                            @if($audience === 'individual')
                                <div class="animate-in fade-in slide-in-from-top-2">
                                    <x-input-label for="emailTo" :value="__('Correo Electrónico Destino')" />
                                    <x-text-input wire:model.live.debounce.500ms="emailTo" id="emailTo" class="block mt-1 w-full" type="email" placeholder="ejemplo@correo.com" />
                                    <x-input-error :messages="$errors->get('emailTo')" class="mt-2" />
                                </div>
                            @endif

                            <!-- Caso: Sección -->
                            @if($audience === 'section')
                                <div class="animate-in fade-in slide-in-from-top-2">
                                    <x-input-label for="sectionId" :value="__('Seleccionar Sección Académica')" />
                                    <select wire:model.live="sectionId" id="sectionId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Seleccione una sección --</option>
                                        @foreach($availableSections as $section)
                                            <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('sectionId')" class="mt-2" />
                                </div>
                            @endif

                            <hr class="border-gray-200">

                            <!-- Contenido del Correo -->
                            <div>
                                <x-input-label for="subject" :value="__('Asunto del Correo')" />
                                <x-text-input wire:model="subject" id="subject" class="block mt-1 w-full font-bold" type="text" placeholder="Ej: Aviso Importante sobre..." />
                                <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="messageBody" :value="__('Cuerpo del Mensaje')" />
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <textarea wire:model="messageBody" id="messageBody" rows="8" 
                                        class="block w-full border-gray-300 rounded-md focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-4" 
                                        placeholder="Escriba su comunicado aquí..."></textarea>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">El sistema añadirá automáticamente la firma y el logo institucional.</p>
                                <x-input-error :messages="$errors->get('messageBody')" class="mt-2" />
                            </div>

                            <!-- Botón de Envío -->
                            <div class="pt-4">
                                <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-all">
                                    <span wire:loading.remove wire:target="sendEmail" class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        Enviar Comunicado ({{ $recipientCount }})
                                    </span>
                                    <span wire:loading wire:target="sendEmail" class="flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Procesando envíos... por favor espere
                                    </span>
                                </button>
                                <p class="text-center text-xs text-gray-400 mt-2">
                                    * El tiempo de envío dependerá de la cantidad de destinatarios. No cierre esta pestaña.
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Columna Derecha: Consola de Salida -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-900 text-green-400 p-4 rounded-xl font-mono text-xs h-[600px] overflow-auto shadow-inner border border-gray-700 flex flex-col">
                            <div class="border-b border-gray-700 pb-2 mb-2 flex justify-between items-center">
                                <h3 class="text-white font-bold uppercase tracking-wider">Monitor de Actividad</h3>
                                <div class="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
                            </div>
                            
                            <div class="flex-1 overflow-y-auto space-y-1 font-mono">
                                @if(empty($debugLog))
                                    <p class="opacity-30 italic text-center mt-10">Esperando inicio de operación...</p>
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