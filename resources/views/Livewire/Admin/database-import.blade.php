<div class="p-6 bg-white rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Importador Masivo</h2>
        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs">Paso {{ $step }} de 3</span>
    </div>

    @if($errors->has('general'))
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ $errors->first('general') }}
        </div>
    @endif

    {{-- PASO 1: SUBIDA --}}
    @if($step === 1)
        <div class="space-y-6 animate-fade-in">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Importación</label>
                <select wire:model.live="entity" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($availableEntities as $key => $config)
                        <option value="{{ $key }}">{{ $config['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div 
                x-data="{ isUploading: false, progress: 0 }"
                x-on:livewire-upload-start="isUploading = true"
                x-on:livewire-upload-finish="isUploading = false"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
                class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:bg-gray-50 transition-colors relative"
            >
                <input type="file" wire:model="file" class="hidden" id="file-upload" accept=".csv, .txt">
                <label for="file-upload" class="cursor-pointer w-full block">
                    <div class="flex flex-col items-center justify-center" x-show="!isUploading">
                        <svg class="w-12 h-12 text-indigo-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <span class="text-indigo-600 font-medium">Clic para subir CSV</span>
                        <span class="text-gray-400 text-sm mt-1">(Soporta +100MB)</span>
                    </div>
                    <div x-show="isUploading" class="w-full">
                        <div class="mb-2 text-indigo-600 font-semibold">Subiendo...</div>
                        <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                            <div class="bg-indigo-600 h-4 transition-all duration-200" :style="'width: ' + progress + '%'"></div>
                        </div>
                        <div class="text-xs text-gray-500 mt-1" x-text="progress + '%'"></div>
                    </div>
                </label>
                @if($file && !$errors->has('file'))
                    <div class="mt-4 text-green-600 text-sm font-bold flex justify-center items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ $file->getClientOriginalName() }}
                    </div>
                @endif
                @error('file') <div class="mt-2 text-red-600 text-sm">{{ $message }}</div> @enderror
            </div>

            <div class="flex justify-end border-t pt-4">
                <button wire:click="analyzeFile" @if(!$file) disabled @endif class="px-6 py-2 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="analyzeFile">Analizar Archivo</span>
                    <span wire:loading wire:target="analyzeFile">Procesando...</span>
                </button>
            </div>
        </div>
    @endif

    {{-- PASO 2: MAPEO Y PROCESO --}}
    @if($step === 2)
        <div 
            x-data="{ 
                processing: @entangle('isProcessing'),
                progress: 0,
                initBatch() {
                    Livewire.on('start-batch-process', () => {
                        this.processing = true;
                        this.progress = 0;
                        this.$wire.importBatch();
                    });

                    Livewire.on('batch-processed', (event) => {
                        let p = event.progress || event[0]?.progress || 0;
                        this.progress = p;
                        this.$wire.importBatch();
                    });
                }
            }"
            x-init="initBatch()"
            class="space-y-6"
        >
            @if(!$isProcessing)
                <div class="bg-white border rounded-lg overflow-hidden mb-6">
                    <div class="p-4 bg-gray-50 border-b font-semibold text-gray-700">Confirmar Columnas</div>
                    <div class="divide-y">
                        @foreach($dbFields as $fieldKey => $label)
                            <div class="grid grid-cols-2 gap-4 p-3 items-center">
                                <div class="text-sm text-gray-600">{{ $label }}</div>
                                <select wire:model="columnMapping.{{ $fieldKey }}" class="text-sm border-gray-300 rounded-md">
                                    <option value="">-- Ignorar --</option>
                                    @foreach($csvHeaders as $header)
                                        <option value="{{ $header }}">{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between border-t pt-4">
                    <button wire:click="$set('step', 1)" class="px-4 py-2 text-gray-600 border rounded hover:bg-gray-50">Atrás</button>
                    <button wire:click="startImport" class="px-6 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700">
                        <span wire:loading.remove wire:target="startImport">Iniciar Importación Masiva</span>
                        <span wire:loading wire:target="startImport">Preparando...</span>
                    </button>
                </div>
            @else
                {{-- PANTALLA DE PROGRESO --}}
                <div class="text-center py-10">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Importando Datos...</h3>
                    <p class="text-gray-600 mb-6">No cierres esta ventana.</p>
                    
                    <div class="relative w-full bg-gray-200 rounded-full h-6 overflow-hidden mb-2">
                        <div class="bg-green-500 h-full transition-all duration-300 ease-out flex items-center justify-center text-xs text-white font-bold" 
                             :style="'width: ' + progress + '%'" x-text="progress + '%'">
                        </div>
                    </div>
                    
                    <div class="text-sm text-gray-500 flex justify-between">
                        <span>Lotes de {{ $chunkSize }}</span>
                        <span>Total: {{ number_format($totalRows) }}</span>
                    </div>
                    
                    <div class="mt-8 flex justify-center">
                        <svg class="animate-spin h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>

                    {{-- BOTÓN DE EMERGENCIA --}}
                    <div class="mt-8" x-show="progress == 0">
                        <p class="text-xs text-red-500 mb-2">¿No avanza?</p>
                        <button wire:click="importBatch" class="px-3 py-1 text-xs bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                            Forzar Inicio Manualmente
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- PASO 3: RESULTADOS --}}
    @if($step === 3)
        <div class="text-center py-12 animate-fade-in">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 mb-2">¡Proceso Finalizado!</h3>
            <p class="text-lg text-gray-600 mb-4">Se procesaron <strong>{{ number_format($processedRows) }}</strong> registros.</p>
            
            @if(count($importErrors) > 0)
                <div class="max-w-2xl mx-auto text-left bg-white shadow overflow-hidden rounded-md border border-red-200 mt-6">
                    <div class="px-4 py-3 bg-red-50 border-b border-red-200 font-bold text-red-800">
                        Hubo {{ count($importErrors) }} errores
                    </div>
                    <ul class="divide-y divide-gray-200 max-h-64 overflow-y-auto text-sm text-red-600 bg-gray-50">
                        @foreach(array_slice($importErrors, 0, 100) as $error)
                            <li class="px-4 py-2">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-10">
                <button wire:click="resetImport" class="px-8 py-3 bg-indigo-600 text-white rounded-md shadow hover:bg-indigo-700 font-medium">
                    Importar otro archivo
                </button>
            </div>
        </div>
    @endif
</div>