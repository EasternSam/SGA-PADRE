<div class="flex flex-col h-[calc(100vh-65px)] bg-gray-50 overflow-hidden font-sans select-none" 
     x-data="certificateEditor(@entangle('elements').live)"
     @keydown.window.ctrl.z.prevent="undo()"
     @keydown.window.ctrl.y.prevent="redo()"
     @keydown.window.ctrl.d.prevent="duplicateElement()"
     @keydown.window.delete="removeElement()"
     @keydown.window.escape="deselectAll()">

    <!-- Fuentes Web -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=EB+Garamond:ital,wght@0,400;0,600;1,400&family=Pinyon+Script&family=Inter:wght@300;400;500;600&family=Montserrat:wght@400;700&family=Playfair+Display:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        .font-cinzel { font-family: 'Cinzel Decorative', cursive; }
        .font-garamond { font-family: 'EB Garamond', serif; }
        .font-pinyon { font-family: 'Pinyon Script', cursive; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .font-playfair { font-family: 'Playfair Display', serif; }
        
        [x-cloak] { display: none !important; }
        
        .canvas-bg {
            background-color: #f3f4f6;
            background-image: radial-gradient(#e5e7eb 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .cursor-rotate { cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>') 10 10, auto; }

        /* Reglas CSS */
        .ruler-h {
            background-image: linear-gradient(90deg, transparent 49px, #94a3b8 49px, transparent 50px),
                              linear-gradient(90deg, transparent 9px, #cbd5e1 9px, transparent 10px);
            background-size: 50px 100%, 10px 30%;
            background-repeat: repeat-x;
            background-position: 0 bottom;
        }
        .ruler-v {
            background-image: linear-gradient(transparent 49px, #94a3b8 49px, transparent 50px),
                              linear-gradient(transparent 9px, #cbd5e1 9px, transparent 10px);
            background-size: 100% 50px, 30% 10px;
            background-repeat: repeat-y;
            background-position: right 0;
        }
    </style>

    <!-- 1. Barra Superior (Header) -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 z-40 shadow-sm shrink-0">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                <i class="ph ph-certificate text-2xl"></i>
            </div>
            
            <div class="flex flex-col">
                <!-- Restaurado wire:model -->
                <input type="text" wire:model="name" class="font-bold text-gray-800 border-none p-0 focus:ring-0 text-sm bg-transparent placeholder-gray-400" placeholder="Nombre del Certificado">
                <span class="text-[10px] text-gray-400 font-medium">
                    <span wire:loading.remove wire:target="save">Guardado</span>
                    <span wire:loading wire:target="save" class="text-indigo-500">Guardando...</span>
                </span>
            </div>

            <div class="h-6 w-px bg-gray-200 mx-2"></div>

            <div class="flex items-center gap-2">
                <button @click="undo" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition" title="Deshacer (Ctrl+Z)">
                    <i class="ph ph-arrow-u-up-left text-lg"></i>
                </button>
                <button @click="redo" class="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition" title="Rehacer (Ctrl+Y)">
                    <i class="ph ph-arrow-u-up-right text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Centro: Zoom y Configuración -->
        <div class="absolute left-1/2 transform -translate-x-1/2 flex items-center gap-3 bg-gray-100/80 backdrop-blur-sm p-1.5 rounded-xl border border-gray-200 shadow-inner">
            <button @click="zoomOut" class="p-1.5 hover:bg-white rounded-lg shadow-sm transition text-gray-600">
                <i class="ph ph-minus"></i>
            </button>
            <span class="text-xs font-mono w-12 text-center font-medium select-none" x-text="Math.round(zoom * 100) + '%'"></span>
            <button @click="zoomIn" class="p-1.5 hover:bg-white rounded-lg shadow-sm transition text-gray-600">
                <i class="ph ph-plus"></i>
            </button>
            <div class="w-px h-4 bg-gray-300 mx-1"></div>
            <!-- Restaurado x-model para la config local del canvas -->
            <button @click="canvasConfig.orientation = canvasConfig.orientation === 'landscape' ? 'portrait' : 'landscape'; updateCanvasSize()" 
                    class="text-xs px-2 py-1 rounded-md transition flex items-center gap-1 font-medium"
                    :class="canvasConfig.orientation === 'landscape' ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-200 text-gray-600'">
                <i class="ph" :class="canvasConfig.orientation === 'landscape' ? 'ph-rectangle' : 'ph-rectangle text-rotate-90'"></i>
                <span x-text="canvasConfig.orientation === 'landscape' ? 'Horizontal' : 'Vertical'"></span>
            </button>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 mr-2">
                <button @click="snapToGrid = !snapToGrid" 
                        class="p-2 rounded-lg transition relative group"
                        :class="snapToGrid ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-100'">
                    <i class="ph ph-magnet text-xl"></i>
                    <span class="absolute top-full right-0 mt-1 text-[10px] bg-gray-800 text-white px-2 py-0.5 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap">Imán</span>
                </button>
            </div>

            <button @click="togglePreview" class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold border transition duration-200" 
                :class="previewMode ? 'bg-indigo-50 text-indigo-600 border-indigo-200' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                <i class="ph" :class="previewMode ? 'ph-pencil-simple' : 'ph-eye'"></i>
                <span x-text="previewMode ? 'Editar' : 'Vista Previa'"></span>
            </button>

            <!-- Restaurado wire:click -->
            <button wire:click="save" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-lg shadow-indigo-200 transition flex items-center gap-2 relative">
                <div wire:loading wire:target="save" class="absolute inset-0 flex items-center justify-center bg-indigo-600 rounded-lg">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </div>
                <i class="ph ph-floppy-disk"></i>
                <span>Guardar</span>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        
        <!-- 2. Barra Lateral Izquierda (Herramientas) -->
        <aside class="w-20 bg-white border-r border-gray-200 flex flex-col items-center py-4 z-20 shrink-0 gap-6" x-show="!previewMode">
            <div class="flex flex-col gap-4 w-full px-2">
                <button @click="activeTab = 'elements'" class="flex flex-col items-center gap-1 p-2 rounded-xl transition w-full group" :class="activeTab === 'elements' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'">
                    <i class="ph ph-shapes text-2xl mb-1"></i>
                    <span class="text-[10px] font-medium">Elementos</span>
                </button>
                <button @click="activeTab = 'layers'" class="flex flex-col items-center gap-1 p-2 rounded-xl transition w-full group" :class="activeTab === 'layers' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'">
                    <i class="ph ph-stack text-2xl mb-1"></i>
                    <span class="text-[10px] font-medium">Capas</span>
                </button>
                <button @click="activeTab = 'settings'" class="flex flex-col items-center gap-1 p-2 rounded-xl transition w-full group" :class="activeTab === 'settings' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'">
                    <i class="ph ph-gear text-2xl mb-1"></i>
                    <span class="text-[10px] font-medium">Ajustes</span>
                </button>
            </div>
        </aside>

        <!-- 2.1 Panel Extendido Izquierdo (Contextual) -->
        <div class="w-64 bg-white border-r border-gray-200 flex flex-col z-10 shrink-0 transition-all duration-300" x-show="activeTab && !previewMode">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-700" x-text="activeTab === 'elements' ? 'Insertar' : (activeTab === 'layers' ? 'Capas' : 'Configuración')"></h3>
                <button @click="activeTab = null" class="text-gray-400 hover:text-gray-600"><i class="ph ph-x"></i></button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                
                <!-- TAB: ELEMENTOS -->
                <div x-show="activeTab === 'elements'" class="space-y-6">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-3">Texto</span>
                        <div class="grid gap-2">
                            <button @click="addElement('text', {fontSize: 48, fontWeight: 700, content: 'Título Principal'})" class="text-left px-4 py-3 border rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition group bg-gray-50">
                                <span class="font-bold text-2xl text-gray-800 font-garamond group-hover:text-indigo-700">Añadir Título</span>
                            </button>
                            <button @click="addElement('text', {fontSize: 24, content: 'Subtítulo del certificado'})" class="text-left px-4 py-2 border rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition group bg-gray-50">
                                <span class="font-medium text-sm text-gray-600 font-inter group-hover:text-indigo-600">Añadir Subtítulo</span>
                            </button>
                            <button @click="addElement('variable')" class="flex items-center gap-3 px-4 py-2 border rounded-lg hover:border-blue-500 hover:bg-blue-50 transition bg-white border-dashed border-blue-200">
                                <span class="bg-blue-100 text-blue-600 p-1 rounded font-mono text-xs">{ }</span>
                                <span class="text-sm text-gray-600">Variable Dinámica</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-3">Gráficos</span>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="addElement('qr')" class="flex flex-col items-center justify-center h-20 border rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition bg-white">
                                <i class="ph ph-qr-code text-2xl text-gray-400 mb-1"></i>
                                <span class="text-[10px] font-medium text-gray-500">QR Code</span>
                            </button>
                            <button @click="addElement('shape', {width: 100, height: 100, borderRadius: 50, borderColor: '#e0e7ff', borderWidth: 2, fill: 'transparent'})" class="flex flex-col items-center justify-center h-20 border rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition bg-white">
                                <div class="w-6 h-6 rounded-full bg-transparent border-2 border-indigo-300"></div>
                                <span class="text-[10px] font-medium text-gray-500 mt-2">Círculo</span>
                            </button>
                            <button @click="addElement('shape', {width: 200, height: 4, fill: '#1f2937', borderWidth: 0})" class="flex flex-col items-center justify-center h-20 border rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition bg-white col-span-2">
                                <div class="w-12 h-0.5 bg-gray-800"></div>
                                <span class="text-[10px] font-medium text-gray-500 mt-2">Línea Divisoria</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: CAPAS -->
                <div x-show="activeTab === 'layers'" class="space-y-1">
                    <template x-for="(element, index) in [...elements].reverse()" :key="element.id || index">
                        <div class="flex items-center gap-2 p-2 rounded-md cursor-pointer group border border-transparent"
                             :class="selectedIds.includes(getElementRealIndex(element)) ? 'bg-indigo-50 border-indigo-200' : 'hover:bg-gray-50'"
                             @click="selectElement(getElementRealIndex(element), $event.ctrlKey)">
                            
                            <i class="ph text-gray-400" :class="getIconForType(element.type)"></i>
                            <span class="text-xs font-medium text-gray-600 flex-1 truncate select-none" x-text="element.content || element.type"></span>
                            
                            <button @click.stop="toggleLock(getElementRealIndex(element))" class="text-gray-300 hover:text-gray-600 opacity-0 group-hover:opacity-100 transition">
                                <i class="ph" :class="element.locked ? 'ph-lock-key text-red-400 opacity-100' : 'ph-lock-key-open'"></i>
                            </button>
                            <button @click.stop="toggleVisibility(getElementRealIndex(element))" class="text-gray-300 hover:text-gray-600 opacity-0 group-hover:opacity-100 transition">
                                <i class="ph" :class="element.hidden ? 'ph-eye-slash text-gray-400 opacity-100' : 'ph-eye'"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- TAB: CONFIGURACIÓN (Restaurado) -->
                <div x-show="activeTab === 'settings'" class="space-y-6">
                    <div class="space-y-3">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Imagen de Fondo</label>
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-200 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-white hover:border-indigo-400 transition relative overflow-hidden group">
                            
                            <!-- Estado Vacío -->
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-gray-400 group-hover:text-indigo-500">
                                <i class="ph ph-upload-simple text-2xl mb-2"></i>
                                <p class="text-xs font-medium">Subir imagen</p>
                            </div>
                            
                            <!-- Preview Livewire -->
                            @if($currentBg && !$bgImage)
                                <img src="{{ asset('storage/'.$currentBg) }}" class="absolute inset-0 w-full h-full object-cover opacity-80" />
                            @endif

                            @if($bgImage)
                                <img src="{{ $bgImage->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80" />
                            @endif

                            <input type="file" wire:model="bgImage" class="hidden" accept="image/*">
                        </label>
                        @if($currentBg || $bgImage)
                            <button @click="$wire.set('bgImage', null); $wire.set('currentBg', null)" class="text-xs text-red-500 hover:text-red-700 w-full text-center flex items-center justify-center gap-1">
                                <i class="ph ph-trash"></i> Eliminar fondo
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. LIENZO (CANVAS) -->
        <main class="flex-1 relative overflow-hidden flex flex-col">
            <!-- Reglas -->
            <div class="h-6 bg-white border-b border-gray-200 flex shrink-0 relative z-10">
                <div class="w-6 border-r border-gray-200 bg-gray-50 shrink-0"></div> <!-- Esquina -->
                <div class="flex-1 overflow-hidden relative" id="ruler-x">
                    <div class="h-full ruler-h opacity-50" :style="`background-size: ${50*zoom}px 100%, ${10*zoom}px 30%`"></div>
                </div>
            </div>
            
            <div class="flex flex-1 overflow-hidden relative">
                <div class="w-6 bg-white border-r border-gray-200 shrink-0 relative z-10" id="ruler-y">
                     <div class="w-full h-full ruler-v opacity-50" :style="`background-size: 100% ${50*zoom}px, 30% ${10*zoom}px`"></div>
                </div>

                <!-- Contenedor GRID para centrado perfecto -->
                <div class="flex-1 bg-gray-100 canvas-bg overflow-auto grid place-items-center w-full h-full p-20" 
                     @mousedown="if($event.target === $el) deselectAll()"
                     @wheel.ctrl.prevent="handleWheelZoom">
                    
                    <!-- Wrapper del Lienzo con lógica Livewire para background -->
                    <div id="canvas" 
                         class="bg-white shadow-[0_20px_50px_-12px_rgba(0,0,0,0.25)] relative transition-all duration-150 ease-out origin-center shrink-0 select-none ring-1 ring-black/5"
                         :class="previewMode ? 'pointer-events-none' : ''"
                         :style="`
                            width: ${canvasConfig.width}px; 
                            height: ${canvasConfig.height}px; 
                            transform: scale(${zoom});
                            background-image: url('${ $wire.bgImage ? '{{ $bgImage ? $bgImage->temporaryUrl() : '' }}' : ($wire.currentBg ? '{{ asset('storage') }}/' + $wire.currentBg : '') }');
                            background-size: cover;
                            background-position: center;
                         `">
                        
                        <!-- Guías de Seguridad -->
                        <div x-show="!previewMode" class="absolute top-[10mm] bottom-[10mm] left-[10mm] right-[10mm] border border-cyan-400/30 pointer-events-none z-0 border-dashed"></div>

                        <!-- Renderizado de Elementos -->
                        <template x-for="(element, index) in elements" :key="element.id || index">
                            <div x-show="!element.hidden"
                                 class="absolute group box-border select-none flex items-center justify-center"
                                 :class="{
                                    'cursor-move': !element.locked && !previewMode, 
                                    'ring-1 ring-indigo-500 z-50': isSelected(index) && !previewMode,
                                    'hover:ring-1 hover:ring-indigo-300': !isSelected(index) && !element.locked && !previewMode
                                 }"
                                 :style="getElementStyle(element)"
                                 @mousedown.stop="startDrag($event, index)"
                                 @click.stop="selectElement(index)">
                                
                                <!-- Contenido Interno -->
                                <div class="w-full h-full overflow-hidden pointer-events-none relative">
                                    <!-- Texto -->
                                    <template x-if="element.type === 'text'">
                                        <div x-text="element.content" class="w-full h-full whitespace-pre-wrap break-words leading-tight" style="outline: none;"></div>
                                    </template>
                                    
                                    <!-- Variable -->
                                    <template x-if="element.type === 'variable'">
                                        <div class="w-full h-full flex items-center justify-center px-1 leading-tight transition-colors duration-200"
                                             :class="previewMode ? '' : 'bg-blue-50/50 text-blue-600/80 border border-blue-300/50 border-dashed'">
                                            <span x-text="getPreviewValue(element.content)"></span>
                                        </div>
                                    </template>

                                    <!-- QR -->
                                    <template x-if="element.type === 'qr'">
                                        <div class="w-full h-full bg-white flex items-center justify-center">
                                             <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Example" class="w-full h-full object-cover mix-blend-multiply opacity-90">
                                        </div>
                                    </template>

                                    <!-- Forma -->
                                    <template x-if="element.type === 'shape'">
                                        <div class="w-full h-full" :style="`background-color: ${element.fill || 'transparent'}; border-radius: ${element.borderRadius || 0}px; border: ${element.borderWidth}px solid ${element.borderColor}`"></div>
                                    </template>
                                </div>

                                <!-- Controles de Transformación -->
                                <template x-if="isSelected(index) && !element.locked && !previewMode">
                                    <div class="absolute inset-0 pointer-events-none">
                                        <div class="absolute -top-1.5 -left-1.5 w-2.5 h-2.5 bg-white border border-indigo-600 shadow-sm pointer-events-auto cursor-nw-resize" @mousedown.stop="startResize($event, index, 'nw')"></div>
                                        <div class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 bg-white border border-indigo-600 shadow-sm pointer-events-auto cursor-ne-resize" @mousedown.stop="startResize($event, index, 'ne')"></div>
                                        <div class="absolute -bottom-1.5 -left-1.5 w-2.5 h-2.5 bg-white border border-indigo-600 shadow-sm pointer-events-auto cursor-sw-resize" @mousedown.stop="startResize($event, index, 'sw')"></div>
                                        <div class="absolute -bottom-1.5 -right-1.5 w-2.5 h-2.5 bg-white border border-indigo-600 shadow-sm pointer-events-auto cursor-se-resize" @mousedown.stop="startResize($event, index, 'se')"></div>
                                        
                                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 w-6 h-6 bg-white rounded-full shadow-md border border-gray-200 flex items-center justify-center cursor-rotate pointer-events-auto hover:text-indigo-600 text-gray-500" @mousedown.stop="startRotate($event, index)">
                                            <i class="ph-bold ph-arrow-clockwise text-xs"></i>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>

        <!-- 4. Panel Derecho (Propiedades) -->
        <aside class="w-72 bg-white border-l border-gray-200 flex flex-col z-20 shrink-0 shadow-lg" x-show="!previewMode">
            <template x-if="selectedIds.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-center p-8 space-y-4">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center">
                        <i class="ph ph-cursor-click text-3xl text-gray-300"></i>
                    </div>
                    <p class="text-sm font-medium text-gray-400">Selecciona un elemento para editar sus propiedades</p>
                </div>
            </template>

            <template x-if="selectedIds.length > 0">
                <div class="flex flex-col h-full">
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Propiedades</span>
                        <div class="flex gap-1">
                            <button @click="duplicateElement()" class="p-1.5 rounded hover:bg-white hover:text-indigo-600 hover:shadow-sm text-gray-400 transition" title="Duplicar">
                                <i class="ph ph-copy"></i>
                            </button>
                            <button @click="removeElement()" class="p-1.5 rounded hover:bg-red-50 hover:text-red-500 text-gray-400 transition" title="Eliminar">
                                <i class="ph ph-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-5 space-y-6 custom-scrollbar">
                        
                        <!-- Alineación Rápida -->
                        <div class="grid grid-cols-4 gap-1 p-1 bg-gray-100 rounded-lg">
                            <button @click="alignSelected('left')" class="p-1.5 rounded hover:bg-white hover:shadow-sm text-gray-500 transition"><i class="ph ph-align-left"></i></button>
                            <button @click="alignSelected('center')" class="p-1.5 rounded hover:bg-white hover:shadow-sm text-gray-500 transition"><i class="ph ph-align-center-horizontal"></i></button>
                            <button @click="alignSelected('right')" class="p-1.5 rounded hover:bg-white hover:shadow-sm text-gray-500 transition"><i class="ph ph-align-right"></i></button>
                            <button @click="alignSelected('middle')" class="p-1.5 rounded hover:bg-white hover:shadow-sm text-gray-500 transition"><i class="ph ph-align-center-vertical"></i></button>
                        </div>

                        <!-- Contenido -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Contenido</label>
                            <template x-if="activeElement.type === 'text'">
                                <textarea x-model="activeElement.content" @input="queueHistory" rows="3" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white shadow-sm resize-none"></textarea>
                            </template>
                            <template x-if="activeElement.type === 'variable'">
                                <select x-model="activeElement.content" @change="queueHistory" class="w-full rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-blue-50/30">
                                    <template x-for="(label, key) in variables" :key="key">
                                        <option :value="key" x-text="label"></option>
                                    </template>
                                </select>
                            </template>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Posición y Tamaño -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Dimensiones</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="relative">
                                    <span class="absolute left-2.5 top-2 text-[10px] text-gray-400 font-bold">X</span>
                                    <input type="number" x-model.number="activeElement.x" @change="queueHistory" class="w-full pl-6 py-1.5 rounded-md border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-2 text-[10px] text-gray-400 font-bold">Y</span>
                                    <input type="number" x-model.number="activeElement.y" @change="queueHistory" class="w-full pl-6 py-1.5 rounded-md border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-2.5 top-2 text-[10px] text-gray-400 font-bold">W</span>
                                    <input type="number" x-model.number="activeElement.width" @change="queueHistory" class="w-full pl-6 py-1.5 rounded-md border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <template x-if="activeElement.height !== null">
                                    <div class="relative">
                                        <span class="absolute left-2.5 top-2 text-[10px] text-gray-400 font-bold">H</span>
                                        <input type="number" x-model.number="activeElement.height" @change="queueHistory" class="w-full pl-6 py-1.5 rounded-md border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Estilos Específicos -->
                        <template x-if="['text', 'variable'].includes(activeElement.type)">
                            <div class="space-y-4">
                                <hr class="border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Tipografía</label>
                                
                                <select x-model="activeElement.fontFamily" @change="queueHistory" class="w-full rounded-lg border-gray-200 text-sm mb-2">
                                    <option value="Inter">Inter (Sans)</option>
                                    <option value="EB Garamond">Garamond (Serif)</option>
                                    <option value="Cinzel Decorative">Cinzel (Decor)</option>
                                    <option value="Pinyon Script">Pinyon (Script)</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Playfair Display">Playfair</option>
                                </select>
                                
                                <div class="flex gap-2 items-center">
                                    <div class="relative flex-1">
                                        <input type="number" x-model="activeElement.fontSize" @change="queueHistory" class="w-full rounded-lg border-gray-200 text-sm pl-2">
                                    </div>
                                    <div class="relative w-10 h-9 overflow-hidden rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:border-indigo-300">
                                        <input type="color" x-model="activeElement.color" @change="queueHistory" class="absolute -top-2 -left-2 w-16 h-16 cursor-pointer p-0 border-0">
                                    </div>
                                </div>

                                <div class="flex bg-gray-100 p-1 rounded-lg">
                                    <button @click="activeElement.textAlign = 'left'; queueHistory()" class="flex-1 py-1 rounded transition" :class="activeElement.textAlign === 'left' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400 hover:text-gray-600'"><i class="ph ph-text-align-left"></i></button>
                                    <button @click="activeElement.textAlign = 'center'; queueHistory()" class="flex-1 py-1 rounded transition" :class="activeElement.textAlign === 'center' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400 hover:text-gray-600'"><i class="ph ph-text-align-center"></i></button>
                                    <button @click="activeElement.textAlign = 'right'; queueHistory()" class="flex-1 py-1 rounded transition" :class="activeElement.textAlign === 'right' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400 hover:text-gray-600'"><i class="ph ph-text-align-right"></i></button>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>
            </template>
        </aside>
    </div>

    <!-- Script de Lógica Alpine -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('certificateEditor', (wireElements) => ({
                elements: wireElements || [],
                selectedIds: [],
                zoom: 0.7,
                activeTab: 'elements',
                previewMode: false,
                snapToGrid: true,
                canvasConfig: { format: 'A4', orientation: 'landscape', width: 0, height: 0 },
                
                // History
                history: [],
                historyStep: -1,
                _historyTimeout: null,

                // Interaction
                isDragging: false,
                interactionType: null,
                dragStart: { x: 0, y: 0 },
                elementStart: {},
                resizeHandle: null,
                rotationCenter: { x: 0, y: 0 },
                startAngle: 0,

                variables: {
                    '{student_name}': 'Nombre Estudiante',
                    '{course_name}': 'Nombre del Curso',
                    '{date}': 'Fecha',
                    '{folio}': 'Folio',
                    '{instructor}': 'Instructor'
                },

                get activeElement() {
                    return this.selectedIds.length > 0 ? this.elements[this.selectedIds[0]] : {};
                },

                init() {
                    this.bindEvents();
                    this.updateCanvasSize();
                    this.saveHistory();
                },

                bindEvents() {
                    this._move = this.handleMove.bind(this);
                    this._up = this.stopInteraction.bind(this);
                    window.addEventListener('mousemove', this._move);
                    window.addEventListener('mouseup', this._up);
                },

                updateCanvasSize() {
                    // 96 DPI standard: 1mm = 3.7795px
                    const mmToPx = (mm) => Math.round(mm * 3.7795);
                    let w_mm = 210, h_mm = 297; // A4 Default

                    if (this.canvasConfig.orientation === 'landscape') {
                        this.canvasConfig.width = mmToPx(h_mm);
                        this.canvasConfig.height = mmToPx(w_mm);
                    } else {
                        this.canvasConfig.width = mmToPx(w_mm);
                        this.canvasConfig.height = mmToPx(h_mm);
                    }
                },

                addElement(type, props = {}) {
                    // Centrar elemento nuevo en el canvas visible
                    const centerX = Math.round(this.canvasConfig.width / 2);
                    const centerY = Math.round(this.canvasConfig.height / 2);

                    const baseElement = {
                        id: Date.now() + Math.random(),
                        type: type,
                        x: props.x || centerX - 150, 
                        y: props.y || centerY - 40,
                        width: props.width || 300, 
                        height: props.height || (type === 'text' ? null : 150),
                        content: type === 'text' ? 'Texto Nuevo' : (type === 'variable' ? '{student_name}' : ''),
                        fontFamily: 'Inter', fontSize: 24, fontWeight: '400', color: '#1e293b',
                        textAlign: 'left', rotation: 0, locked: false, hidden: false,
                        zIndex: this.elements.length + 1,
                        ...props
                    };

                    this.elements.push(baseElement);
                    this.selectElement(this.elements.length - 1);
                    this.saveHistory();
                },

                removeElement() {
                    if (this.selectedIds.length === 0) return;
                    this.elements = this.elements.filter((_, i) => i !== this.selectedIds[0]);
                    this.selectedIds = [];
                    this.saveHistory();
                },

                duplicateElement() {
                    if (this.selectedIds.length === 0) return;
                    const original = this.elements[this.selectedIds[0]];
                    const copy = JSON.parse(JSON.stringify(original));
                    copy.id = Date.now() + Math.random();
                    copy.x += 20; copy.y += 20;
                    this.elements.push(copy);
                    this.selectElement(this.elements.length - 1);
                    this.saveHistory();
                },

                selectElement(index, multi = false) {
                    this.selectedIds = [index];
                },

                deselectAll() { this.selectedIds = []; },
                
                getElementRealIndex(el) { return this.elements.indexOf(el); },
                isSelected(index) { return this.selectedIds.includes(index); },

                toggleLock(index) { this.elements[index].locked = !this.elements[index].locked; },
                toggleVisibility(index) { this.elements[index].hidden = !this.elements[index].hidden; },

                // Herramientas de alineación
                alignSelected(mode) {
                    if (this.selectedIds.length === 0) return;
                    const idx = this.selectedIds[0];
                    const el = this.elements[idx];
                    
                    if (mode === 'center') el.x = (this.canvasConfig.width - el.width) / 2;
                    if (mode === 'middle') el.y = (this.canvasConfig.height - (el.height || 50)) / 2;
                    if (mode === 'left') el.x = 40; // Margen seguro
                    if (mode === 'right') el.x = this.canvasConfig.width - el.width - 40;
                    
                    this.saveHistory();
                },

                // Lógica de Movimiento y Redimensión (DRAG & DROP)
                startDrag(e, index) {
                    if (e.button !== 0 || this.elements[index].locked || this.previewMode) return;
                    this.selectElement(index);
                    this.isDragging = true;
                    this.interactionType = 'move';
                    this.dragStart = { x: e.clientX, y: e.clientY };
                    this.elementStart = { ...this.elements[index] };
                },

                startResize(e, index, handle) {
                    e.stopPropagation();
                    if(this.previewMode) return;
                    this.isDragging = true;
                    this.interactionType = 'resize';
                    this.resizeHandle = handle;
                    this.dragStart = { x: e.clientX, y: e.clientY };
                    this.elementStart = { ...this.elements[index] };
                },

                startRotate(e, index) {
                    e.stopPropagation();
                    if(this.previewMode) return;
                    this.isDragging = true;
                    this.interactionType = 'rotate';
                    
                    // Calcular centro visual
                    const el = e.target.closest('.group');
                    const rect = el.getBoundingClientRect();
                    this.rotationCenter = { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
                    this.startAngle = Math.atan2(e.clientY - this.rotationCenter.y, e.clientX - this.rotationCenter.x);
                    this.elementStart = { ...this.elements[index] };
                },

                handleMove(e) {
                    if (!this.isDragging) return;
                    const idx = this.selectedIds[0];
                    const el = this.elements[idx];
                    
                    // Delta ajustado al zoom
                    const dx = (e.clientX - this.dragStart.x) / this.zoom;
                    const dy = (e.clientY - this.dragStart.y) / this.zoom;

                    if (this.interactionType === 'move') {
                        let newX = this.elementStart.x + dx;
                        let newY = this.elementStart.y + dy;
                        
                        // Smart Snap simplificado
                        if (this.snapToGrid && !e.shiftKey) {
                            if (Math.abs(newX - (this.canvasConfig.width/2 - el.width/2)) < 10) newX = (this.canvasConfig.width/2 - el.width/2);
                        }

                        el.x = Math.round(newX);
                        el.y = Math.round(newY);
                    } 
                    else if (this.interactionType === 'resize') {
                        let w = this.elementStart.width;
                        let h = this.elementStart.height || 100;
                        let x = this.elementStart.x;
                        let y = this.elementStart.y;

                        if (this.resizeHandle.includes('e')) w += dx;
                        if (this.resizeHandle.includes('w')) { w -= dx; x += dx; }
                        if (this.resizeHandle.includes('s')) h += dy;
                        if (this.resizeHandle.includes('n')) { h -= dy; y += dy; }

                        el.width = Math.max(20, w);
                        if(el.height !== null) el.height = Math.max(20, h);
                        el.x = x; el.y = y;
                    }
                    else if (this.interactionType === 'rotate') {
                        const currentAngle = Math.atan2(e.clientY - this.rotationCenter.y, e.clientX - this.rotationCenter.x);
                        let deg = (currentAngle - this.startAngle) * (180 / Math.PI) + this.elementStart.rotation;
                        if (e.shiftKey) deg = Math.round(deg / 15) * 15;
                        el.rotation = Math.round(deg);
                    }
                },

                stopInteraction() {
                    if (this.isDragging) {
                        this.isDragging = false;
                        this.queueHistory();
                    }
                },

                getElementStyle(el) {
                    return `
                        left: ${el.x}px; top: ${el.y}px; width: ${el.width}px; 
                        ${el.height ? `height: ${el.height}px;` : ''}
                        transform: rotate(${el.rotation || 0}deg);
                        font-family: '${el.fontFamily}'; font-size: ${el.fontSize}px;
                        font-weight: ${el.fontWeight}; color: ${el.color};
                        text-align: ${el.textAlign};
                        z-index: ${el.zIndex};
                    `;
                },

                getPreviewValue(content) {
                    if (!this.previewMode) return content;
                    let text = content;
                    Object.entries(this.variables).forEach(([k, v]) => text = text.replace(new RegExp(k, 'g'), v));
                    return text;
                },
                
                getIconForType(type) {
                    return {
                        'text': 'ph-text-t',
                        'variable': 'ph-brackets-curly',
                        'qr': 'ph-qr-code',
                        'shape': 'ph-square'
                    }[type] || 'ph-circle';
                },

                togglePreview() {
                    this.previewMode = !this.previewMode;
                    this.deselectAll();
                },

                zoomIn() { if(this.zoom < 2) this.zoom += 0.1; },
                zoomOut() { if(this.zoom > 0.3) this.zoom -= 0.1; },
                handleWheelZoom(e) { e.deltaY < 0 ? this.zoomIn() : this.zoomOut(); },

                // Historial (Undo/Redo)
                queueHistory() {
                    clearTimeout(this._historyTimeout);
                    this._historyTimeout = setTimeout(() => { this.saveHistory(); }, 300);
                },

                saveHistory() {
                    const current = JSON.stringify(this.elements);
                    if (this.historyStep >= 0 && JSON.stringify(this.history[this.historyStep]) === current) return;
                    if (this.historyStep < this.history.length - 1) this.history = this.history.slice(0, this.historyStep + 1);
                    this.history.push(JSON.parse(current));
                    this.historyStep++;
                },
                
                undo() {
                    if (this.historyStep > 0) {
                        this.historyStep--;
                        this.elements = JSON.parse(JSON.stringify(this.history[this.historyStep]));
                        this.deselectAll();
                    }
                },
                
                redo() {
                    if (this.historyStep < this.history.length - 1) {
                        this.historyStep++;
                        this.elements = JSON.parse(JSON.stringify(this.history[this.historyStep]));
                        this.deselectAll();
                    }
                }
            }));
        });
    </script>
</div>