<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6 border-b pb-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Panel de Comunicación Avanzado</h2>
                        <p class="text-gray-600 text-sm mt-1">Envía comunicados masivos y notificaciones dirigidas usando plantillas dinámicas y procesamiento en segundo plano.</p>
                    </div>
                </div>

                <!-- Mensajes Flash -->
                @if (session()->has('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded relative shadow-sm">
                        <strong class="font-bold">✓ Operación Estelar:</strong>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded relative shadow-sm">
                        <strong class="font-bold">⚠ Error:</strong>
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Área Principal de Configuración (2/3) -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- 1. SELECCIÓN DE AUDIENCIA -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <h3 class="font-semibold text-gray-700 mb-3"><i class="fas fa-users mr-2 text-indigo-500"></i> 1. Seleccionar Audiencia</h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <label class="flex items-center p-3 border rounded-md cursor-pointer hover:bg-white transition-colors {{ $audienceType === 'individual' ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-300' }}">
                                    <input type="radio" wire:model.live="audienceType" value="individual" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm font-medium">1 Estudiante</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-md cursor-pointer hover:bg-white transition-colors {{ $audienceType === 'section' ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-300' }}">
                                    <input type="radio" wire:model.live="audienceType" value="section" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm font-medium">Toda una Sección</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-md cursor-pointer hover:bg-white transition-colors {{ $audienceType === 'course' ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500' : 'border-gray-300' }}">
                                    <input type="radio" wire:model.live="audienceType" value="course" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="ml-2 text-sm font-medium">Un Curso Entero</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-md cursor-pointer hover:bg-white transition-colors {{ $audienceType === 'debtors' ? 'border-red-500 bg-red-50 ring-1 ring-red-500' : 'border-gray-300' }}">
                                    <input type="radio" wire:model.live="audienceType" value="debtors" class="text-red-600 focus:ring-red-500">
                                    <span class="ml-2 text-sm font-medium text-red-700">Con Deudas</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-md cursor-pointer hover:bg-white transition-colors {{ $audienceType === 'all' ? 'border-gray-800 bg-gray-100 ring-1 ring-gray-800' : 'border-gray-300' }}">
                                    <input type="radio" wire:model.live="audienceType" value="all" class="text-gray-800 focus:ring-gray-800">
                                    <span class="ml-2 text-sm font-medium">Todos (Global)</span>
                                </label>
                            </div>
                            <x-input-error :messages="$errors->get('audienceType')" class="mt-2" />
                            
                            <!-- Búsqueda Individual -->
                            @if($audienceType === 'individual')
                                <div class="mt-4 pt-4 border-t border-gray-200 relative">
                                    <x-input-label value="Buscar Estudiante (Nombre, Cédula o Matrícula)" />
                                    <div class="flex mt-1">
                                        <input wire:model.live.debounce.300ms="individualSearch" wire:keyup="searchStudents" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej: Perez, 402-123..., MAT-001">
                                    </div>
                                    
                                    @if(count($individualResults) > 0)
                                        <div class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200 max-h-60 overflow-y-auto">
                                            <ul class="py-1">
                                                @foreach($individualResults as $result)
                                                    <li>
                                                        <button type="button" wire:click="selectStudent({{ $result['id'] }}, '{{ $result['email'] }}', '{{ addslashes($result['first_name'] . ' ' . $result['last_name']) }}')" class="w-full text-left px-4 py-2 hover:bg-indigo-50 focus:bg-indigo-50 focus:outline-none flex justify-between">
                                                            <span>
                                                                <div class="font-medium text-gray-900">{{ $result['first_name'] }} {{ $result['last_name'] }}</div>
                                                                <div class="text-xs text-gray-500">{{ $result['email'] ?? 'Sin correo' }}</div>
                                                            </span>
                                                            <span class="text-xs font-mono text-indigo-600 font-semibold">{{ $result['student_code'] }}</span>
                                                        </button>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if($selectedIndividualEmail)
                                        <div class="mt-2 text-sm text-green-600 bg-green-50 p-2 rounded inline-block border border-green-200">
                                            ✓ Seleccionado: <strong>{{ $selectedIndividualName }}</strong> ({{ $selectedIndividualEmail }})
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Selección de Sección -->
                            @if($audienceType === 'section')
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <x-input-label for="targetSection" value="Seleccione la Sección" />
                                    <select wire:model="targetId" id="targetSection" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">-- Elegir Sección Activa --</option>
                                        @foreach($availableSections as $section)
                                            <option value="{{ $section->id }}">{{ $section->alias ?? 'G-'.$section->id }} - {{ $section->schedule_days }} ({{ $section->module->course->name ?? 'Curso' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- Selección de Curso -->
                            @if($audienceType === 'course')
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <x-input-label for="targetCourse" value="Seleccione el Curso" />
                                    <select wire:model="targetId" id="targetCourse" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="">-- Elegir Curso Activo --</option>
                                        @foreach($availableCourses as $course)
                                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                             <!-- Aviso de Deudores -->
                            @if($audienceType === 'debtors')
                                <div class="mt-4 pt-4 border-t border-red-200 bg-red-50 p-3 rounded text-sm text-red-800 flex items-start">
                                    <i class="fas fa-exclamation-triangle mt-1 mr-2 text-red-500"></i>
                                    <p>Este envío buscará automáticamente a *todos* los estudiantes que tengan una cuenta por pagar (Mensualidad, Inscripción, etc) con fecha de vencimiento menor a hoy. Utilice la plantilla de Deudas abajo.</p>
                                </div>
                            @endif
                        </div>

                        <!-- 2. PLANTILLAS RÁPIDAS -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                             <h3 class="font-semibold text-gray-700 mb-3"><i class="fas fa-magic mr-2 text-indigo-500"></i> 2. Plantillas Predefinidas</h3>
                             <div class="flex flex-wrap gap-2">
                                 <button type="button" wire:click="loadTemplate('cambio_aula')" class="px-3 py-1.5 bg-white border border-gray-300 shadow-sm rounded-md text-sm font-medium text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                     🏫 Cambio de Aula/Horario
                                 </button>
                                 <button type="button" wire:click="loadTemplate('recordatorio_pago')" class="px-3 py-1.5 bg-white border border-red-300 shadow-sm rounded-md text-sm font-medium text-red-700 hover:bg-red-50 transition">
                                     💰 Recordatorio de Pago
                                 </button>
                                 <button type="button" wire:click="loadTemplate('aviso_general')" class="px-3 py-1.5 bg-white border border-gray-300 shadow-sm rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                                     📢 Aviso Global (Blanco)
                                 </button>
                             </div>
                        </div>

                        <!-- 3. REDACCIÓN Y ENVÍO -->
                        <div class="bg-white">
                            <form wire:submit.prevent="sendEmail" class="space-y-4">
                                
                                <div>
                                    <x-input-label for="subject" :value="__('Asunto del Correo')" />
                                    <x-text-input wire:model="subject" id="subject" class="block mt-1 w-full" type="text" placeholder="Ingresa el asunto..." required />
                                    <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                                </div>

                                <div>
                                    <div class="flex justify-between items-end">
                                        <x-input-label for="messageBody" :value="__('Cuerpo del Mensaje')" />
                                        <span class="text-xs text-indigo-600 font-mono bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100">Variables: [NOMBRE_ESTUDIANTE], [NOMBRE_CURSO], [BALANCE_PENDIENTE]</span>
                                    </div>
                                    <textarea wire:model="messageBody" id="messageBody" rows="10" 
                                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                                        placeholder="Redacta el correo o carga una plantilla. Usa las variables mágicas de arriba para personalizar cada envío." required></textarea>
                                    <x-input-error :messages="$errors->get('messageBody')" class="mt-2" />
                                </div>

                                <div class="pt-2">
                                    <button type="submit" wire:loading.attr="disabled" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg shadow transition flex items-center justify-center">
                                        <i class="fas fa-paper-plane mr-2" wire:loading.remove wire:target="sendEmail"></i>
                                        <i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="sendEmail"></i>
                                        <span wire:loading.remove wire:target="sendEmail">ENVIAR COMUNICADO MASIVO</span>
                                        <span wire:loading wire:target="sendEmail">Procesando y Encolando Envío...</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Columna Derecha: Consola de Debug (1/3) -->
                    <div class="bg-gray-900 rounded-xl font-mono text-sm h-[600px] shadow-2xl overflow-hidden flex flex-col border border-gray-800">
                        <div class="bg-gray-800 px-4 py-2 border-b border-gray-700 flex justify-between items-center">
                            <h3 class="text-gray-300 font-bold text-xs uppercase tracking-wider">Centro de Operaciones (Logs)</h3>
                            <div class="flex space-x-1">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                        </div>
                        <div class="p-4 overflow-y-auto flex-1 text-green-400">
                            @if(empty($debugLog))
                                <p class="opacity-50 italic">Esperando órdenes de comando...</p>
                                <p class="text-gray-500 mt-4 text-xs">
                                    > El sistema usará Laravel Queues para el envío masivo.<br>
                                    > Se generarán plantillas individuales por cada estudiante resolviendo las variables [MARCADAS].
                                </p>
                            @else
                                <ul class="list-none space-y-2">
                                    @foreach($debugLog as $log)
                                        <li class="break-words leading-tight">{!! htmlspecialchars($log) !!}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>