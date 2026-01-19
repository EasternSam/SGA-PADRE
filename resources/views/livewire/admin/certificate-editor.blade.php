<div class="flex flex-col h-[calc(100vh-65px)] bg-gray-900 overflow-hidden font-sans select-none" 
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
        /* Fuentes disponibles en el editor */
        .font-cinzel { font-family: 'Cinzel Decorative', cursive; }
        .font-garamond { font-family: 'EB Garamond', serif; }
        .font-pinyon { font-family: 'Pinyon Script', cursive; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        .font-playfair { font-family: 'Playfair Display', serif; }
        
        [x-cloak] { display: none !important; }
        
        /* Fondo del área de trabajo (Gris neutro profesional) */
        .workspace-bg {
            background-color: #e5e5e5;
            background-image: 
                linear-gradient(45deg, #d4d4d4 25%, transparent 25%), 
                linear-gradient(-45deg, #d4d4d4 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, #d4d4d4 75%), 
                linear-gradient(-45deg, transparent 75%, #d4d4d4 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }

        /* Scrollbars finas y oscuras */
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 2px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6b7280; }

        /* Cursor de rotación */
        .cursor-rotate { cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>') 10 10, auto; }

        /* Inputs numéricos sin flechas */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none; 
            margin: 0; 
        }
    </style>

    <!-- 1. BARRA SUPERIOR (HEADER) -->
    <header class="h-14 bg-white border-b border-gray-200 flex items-center justify-between px-4 z-50 shadow-sm shrink-0 relative">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-gray-800">
                <div class="bg-gray-900 text-white p-1.5 rounded-lg">
                    <i class="ph-bold ph-certificate"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider leading-none">Editor</span>
                    <input type="text" wire:model.live.debounce.500ms="name" class="font-bold text-gray-800 border-none p-0 focus:ring-0 text-sm bg-transparent placeholder-gray-400 w-48 leading-tight" placeholder="Nombre del Diseño">
                </div>
            </div>

            <div class="h-6 w-px bg-gray-200"></div>

            <!-- Historial -->
            <div class="flex items-center gap-1">
                <button @click="undo" :disabled="historyStep <= 0" class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded disabled:opacity-30 disabled:hover:bg-transparent transition" title="Deshacer (Ctrl+Z)">
                    <i class="ph-bold ph-arrow-u-up-left"></i>
                </button>
                <button @click="redo" :disabled="historyStep >= history.length - 1" class="p-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded disabled:opacity-30 disabled:hover:bg-transparent transition" title="Rehacer (Ctrl+Y)">
                    <i class="ph-bold ph-arrow-u-up-right"></i>
                </button>
            </div>
        </div>

        <!-- Centro: Zoom -->
        <div class="absolute left-1/2 transform -translate-x-1/2 flex items-center bg-gray-100 rounded-lg p-0.5 border border-gray-200">
            <button @click="zoomOut" class="p-1.5 px-2 hover:bg-white hover:shadow-sm rounded-md transition text-gray-600 text-xs">
                <i class="ph-bold ph-minus"></i>
            </button>
            <span class="text-xs font-mono w-12 text-center font-semibold text-gray-600 select-none" x-text="Math.round(zoom * 100) + '%'"></span>
            <button @click="zoomIn" class="p-1.5 px-2 hover:bg-white hover:shadow-sm rounded-md transition text-gray-600 text-xs">
                <i class="ph-bold ph-plus"></i>
            </button>
        </div>

        <!-- Derecha: Acciones -->
        <div class="flex items-center gap-3">
            <button @click="snapToGrid = !snapToGrid" 
                    class="flex items-center gap-1.5 px-2 py-1.5 rounded text-xs font-medium transition border"
                    :class="snapToGrid ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'text-gray-500 border-transparent hover:bg-gray-100'">
                <i class="ph-bold ph-magnet"></i>
                <span>Snap</span>
            </button>

            <button @click="togglePreview" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-bold border transition duration-200" 
                :class="previewMode ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'">
                <i class="ph-bold" :class="previewMode ? 'ph-pencil-simple' : 'ph-eye'"></i>
                <span x-text="previewMode ? 'Editar' : 'Preview'"></span>
            </button>

            <button wire:click="save" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-1.5 rounded-lg text-xs font-bold shadow transition flex items-center gap-2 relative overflow-hidden">
                <div wire:loading wire:target="save" class="absolute inset-0 flex items-center justify-center bg-gray-900">
                    <i class="ph-bold ph-spinner animate-spin"></i>
                </div>
                <i class="ph-bold ph-floppy-disk"></i>
                <span>Guardar</span>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden relative z-0">
        
        <!-- 2. BARRA LATERAL IZQUIERDA (Menú) -->
        <aside class="w-16 bg-white border-r border-gray-200 flex flex-col items-center py-4 z-30 shrink-0 gap-2" x-show="!previewMode">
            <button @click="activeTab = 'elements'" class="w-10 h-10 flex items-center justify-center rounded-xl transition text-2xl" :class="activeTab === 'elements' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600'" title="Elementos">
                <i class="ph-bold ph-shapes"></i>
            </button>
            <button @click="activeTab = 'layers'" class="w-10 h-10 flex items-center justify-center rounded-xl transition text-2xl" :class="activeTab === 'layers' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600'" title="Capas">
                <i class="ph-bold ph-stack"></i>
            </button>
            <button @click="activeTab = 'settings'" class="w-10 h-10 flex items-center justify-center rounded-xl transition text-2xl" :class="activeTab === 'settings' ? 'bg-gray-900 text-white shadow-md' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600'" title="Configuración">
                <i class="ph-bold ph-gear"></i>
            </button>
        </aside>

        <!-- 2.1 PANEL EXTENDIDO IZQUIERDO (Contextual) -->
        <div class="w-64 bg-white border-r border-gray-200 flex flex-col z-20 shrink-0 transition-all duration-300 relative shadow-sm" x-show="activeTab && !previewMode">
            <div class="h-10 px-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider" x-text="activeTab === 'elements' ? 'Insertar' : (activeTab === 'layers' ? 'Capas' : 'Lienzo')"></span>
                <button @click="activeTab = null" class="text-gray-400 hover:text-gray-600"><i class="ph-bold ph-x"></i></button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                
                <!-- TAB: ELEMENTOS -->
                <div x-show="activeTab === 'elements'" class="space-y-6">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Básicos</span>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="addElement('text')" class="flex flex-col items-center justify-center p-3 border border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition bg-white group">
                                <i class="ph-bold ph-text-t text-xl text-gray-600 group-hover:text-indigo-600 mb-1"></i>
                                <span class="text-[10px] font-medium text-gray-500 group-hover:text-indigo-600">Texto</span>
                            </button>
                            <button @click="addElement('variable')" class="flex flex-col items-center justify-center p-3 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition bg-white group">
                                <i class="ph-bold ph-brackets-curly text-xl text-gray-600 group-hover:text-blue-600 mb-1"></i>
                                <span class="text-[10px] font-medium text-gray-500 group-hover:text-blue-600">Variable</span>
                            </button>
                        </div>
                        <button @click="addElement('qr')" class="w-full mt-2 flex items-center justify-center gap-2 p-2 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition bg-white group">
                            <i class="ph-bold ph-qr-code text-lg text-gray-600 group-hover:text-purple-600"></i>
                            <span class="text-xs font-medium text-gray-500 group-hover:text-purple-600">Código QR</span>
                        </button>
                    </div>

                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-2">Formas</span>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="addElement('shape', {width: 150, height: 150, borderRadius: 0, borderColor: '#1f2937', borderWidth: 2, fill: 'transparent'})" class="h-16 border border-gray-200 rounded hover:bg-gray-50 flex items-center justify-center">
                                <div class="w-8 h-8 border-2 border-gray-600"></div>
                            </button>
                            <button @click="addElement('shape', {width: 150, height: 150, borderRadius: 100, borderColor: '#b49b5a', borderWidth: 2, fill: 'transparent'})" class="h-16 border border-gray-200 rounded hover:bg-gray-50 flex items-center justify-center">
                                <div class="w-8 h-8 border-2 border-yellow-600 rounded-full"></div>
                            </button>
                            <button @click="addElement('shape', {width: 200, height: 2, fill: '#1f2937', borderWidth: 0})" class="h-16 border border-gray-200 rounded hover:bg-gray-50 flex items-center justify-center col-span-2">
                                <div class="w-12 h-0.5 bg-gray-800"></div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: CAPAS -->
                <div x-show="activeTab === 'layers'" class="space-y-1">
                    <template x-for="(element, index) in [...elements].reverse()" :key="element.id || index">
                        <div class="flex items-center gap-2 p-1.5 rounded-md cursor-pointer group border border-transparent transition"
                             :class="selectedIds.includes(getElementRealIndex(element)) ? 'bg-indigo-50 border-indigo-100' : 'hover:bg-gray-50'"
                             @click="selectElement(getElementRealIndex(element), $event.ctrlKey)">
                            
                            <!-- Icono Tipo -->
                            <div class="text-gray-400 w-5 flex justify-center">
                                <i class="ph-bold" :class="getIconForType(element.type)"></i>
                            </div>

                            <!-- Nombre Capa -->
                            <span class="text-xs font-medium text-gray-700 flex-1 truncate select-none" 
                                  x-text="element.content || (element.type === 'shape' ? 'Forma' : element.type)"></span>
                            
                            <!-- Acciones -->
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="toggleLock(getElementRealIndex(element))" class="text-gray-400 hover:text-gray-800 transition p-0.5">
                                    <i class="ph-bold" :class="element.locked ? 'ph-lock-key text-red-500 opacity-100' : 'ph-lock-key-open'"></i>
                                </button>
                                <button @click.stop="toggleVisibility(getElementRealIndex(element))" class="text-gray-400 hover:text-gray-800 transition p-0.5">
                                    <i class="ph-bold" :class="element.hidden ? 'ph-eye-slash text-gray-400 opacity-50' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- TAB: CONFIGURACIÓN -->
                <div x-show="activeTab === 'settings'" class="space-y-6">
                    <div class="space-y-3">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Orientación</span>
                        <div class="flex bg-gray-100 p-1 rounded-lg">
                            <button @click="canvasConfig.orientation = 'portrait'; updateCanvasSize()" class="flex-1 py-1.5 rounded-md text-xs transition flex items-center justify-center gap-1 font-medium" :class="canvasConfig.orientation === 'portrait' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'">
                                <i class="ph-bold ph-rectangle-vertical"></i> Vertical
                            </button>
                            <button @click="canvasConfig.orientation = 'landscape'; updateCanvasSize()" class="flex-1 py-1.5 rounded-md text-xs transition flex items-center justify-center gap-1 font-medium" :class="canvasConfig.orientation === 'landscape' ? 'bg-white shadow text-gray-900' : 'text-gray-500 hover:text-gray-700'">
                                <i class="ph-bold ph-rectangle"></i> Horizontal
                            </button>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Fondo</span>
                        <div class="relative group">
                            <label class="flex flex-col items-center justify-center w-full h-32 border border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-white hover:border-gray-400 transition overflow-hidden">
                                
                                <!-- Estado Vacío -->
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-gray-400 group-hover:text-gray-600 z-10" x-show="!$wire.currentBg && !$wire.bgImage">
                                    <i class="ph-bold ph-image text-2xl mb-1"></i>
                                    <p class="text-[10px] font-medium">Subir Imagen</p>
                                </div>
                                
                                <!-- Preview Livewire -->
                                @if($currentBg && !$bgImage)
                                    <img src="{{ asset('storage/'.$currentBg) }}" class="absolute inset-0 w-full h-full object-cover" />
                                @endif

                                @if($bgImage)
                                    <img src="{{ $bgImage->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover" />
                                @endif

                                <!-- Overlay Hover -->
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-20">
                                    <span class="text-white text-xs font-bold flex items-center gap-1"><i class="ph-bold ph-pencil-simple"></i> Cambiar</span>
                                </div>

                                <input type="file" wire:model="bgImage" class="hidden" accept="image/*">
                            </label>
                        </div>
                        
                        @if($currentBg || $bgImage)
                            <button @click="$wire.set('bgImage', null); $wire.set('currentBg', null)" class="w-full py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg flex items-center justify-center gap-1 transition">
                                <i class="ph-bold ph-trash"></i> Quitar fondo
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. LIENZO (CANVAS) -->
        <main class="flex-1 relative overflow-hidden flex flex-col bg-gray-200 workspace-bg z-10">
            
            <!-- Contenedor SCROLLABLE -->
            <div class="flex-1 overflow-auto relative w-full h-full custom-scrollbar" 
                 id="scroll-container"
                 @mousedown="if($event.target === $el || $event.target.id === 'center-wrapper') deselectAll()"
                 @wheel.ctrl.prevent="handleWheelZoom">
                
                <!-- Wrapper de Centrado -->
                <!-- min-h-full y min-w-full aseguran que el canvas se centre si es pequeño, pero permita scroll si es grande -->
                <div id="center-wrapper" class="min-w-full min-h-full flex items-center justify-center p-20">
                    
                    <!-- EL LIENZO -->
                    <div id="canvas" 
                         class="bg-white shadow-[0_4px_30px_rgba(0,0,0,0.1)] relative transition-all duration-100 ease-out origin-center shrink-0 select-none ring-1 ring-black/5"
                         :class="previewMode ? 'pointer-events-none' : ''"
                         :style="`
                            width: ${canvasConfig.width}px; 
                            height: ${canvasConfig.height}px; 
                            transform: scale(${zoom});
                            background-image: url('${ $wire.bgImage ? '{{ $bgImage ? $bgImage->temporaryUrl() : '' }}' : ($wire.currentBg ? '{{ asset('storage') }}/' + $wire.currentBg : '') }');
                            background-size: cover;
                            background-position: center;
                         `">
                        
                        <!-- Guías de Seguridad (Área segura de impresión) -->
                        <div x-show="!previewMode" class="absolute top-[10mm] bottom-[10mm] left-[10mm] right-[10mm] border border-cyan-500/20 pointer-events-none z-0 border-dashed"></div>

                        <!-- Elementos Renderizados -->
                        <template x-for="(element, index) in elements" :key="element.id || index">
                            <div x-show="!element.hidden"
                                 class="absolute group box-border select-none flex items-center justify-center"
                                 :class="{
                                    'cursor-move': !element.locked && !previewMode, 
                                    'ring-1 ring-indigo-500 z-50': isSelected(index) && !previewMode,
                                    'hover:ring-1 hover:ring-indigo-300 z-40': !isSelected(index) && !element.locked && !previewMode
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

                                <!-- Controles de Transformación (Handles) -->
                                <template x-if="isSelected(index) && !element.locked && !previewMode">
                                    <div class="absolute inset-0 z-50 pointer-events-none">
                                        <!-- Puntos esquinas -->
                                        <div class="absolute -top-1.5 -left-1.5 w-2.5 h-2.5 bg-white border border-indigo-600 shadow-sm pointer-events-auto cursor-nw-resize rounded-full" @mousedown.stop="startResize($event, index, 'nw')"></div>
                                        <div class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 bg-white border border-indigo-600 shadow-sm pointer-events-auto cursor-ne-resize rounded-full" @mousedown.stop="startResize($event, index, 'ne')"></div>
                                        <div class="absolute -bottom-1.5 -left-1.5 w-2.5 h-2.5 bg-white border border-indigo-600 shadow-sm pointer-events-auto cursor-sw-resize rounded-full" @mousedown.stop="startResize($event, index, 'sw')"></div>
                                        <div class="absolute -bottom-1.5 -right-1.5 w-2.5 h-2.5 bg-white border border-indigo-600 shadow-sm pointer-events-auto cursor-se-resize rounded-full" @mousedown.stop="startResize($event, index, 'se')"></div>
                                        
                                        <!-- Rotación -->
                                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 w-6 h-6 bg-white rounded-full shadow-sm border border-gray-200 flex items-center justify-center cursor-rotate pointer-events-auto hover:text-indigo-600 text-gray-500 hover:border-indigo-300 transition" @mousedown.stop="startRotate($event, index)">
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

        <!-- 4. PANEL DERECHO (Propiedades) -->
        <aside class="w-72 bg-white border-l border-gray-200 flex flex-col z-30 shrink-0 shadow-lg" x-show="!previewMode">
            <template x-if="selectedIds.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-center p-8 space-y-4 opacity-50">
                    <i class="ph-duotone ph-cursor-click text-4xl text-gray-300"></i>
                    <p class="text-sm font-medium text-gray-400">Selecciona un elemento para editar</p>
                </div>
            </template>

            <template x-if="selectedIds.length > 0">
                <div class="flex flex-col h-full">
                    <div class="h-10 px-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Propiedades</span>
                        <div class="flex gap-2 text-gray-500">
                            <button @click="duplicateElement()" class="hover:text-indigo-600 transition" title="Duplicar"><i class="ph-bold ph-copy"></i></button>
                            <button @click="removeElement()" class="hover:text-red-500 transition" title="Eliminar"><i class="ph-bold ph-trash"></i></button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-5 space-y-6 custom-scrollbar">
                        
                        <!-- Contenido -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Contenido</label>
                            <template x-if="activeElement.type === 'text'">
                                <textarea x-model="activeElement.content" @input="queueHistory" rows="3" class="w-full rounded-md border-gray-200 text-sm focus:border-indigo-500 focus:ring-0 bg-gray-50 hover:bg-white transition resize-none py-2 px-3"></textarea>
                            </template>
                            <template x-if="activeElement.type === 'variable'">
                                <select x-model="activeElement.content" @change="queueHistory" class="w-full rounded-md border-gray-200 text-sm focus:border-indigo-500 focus:ring-0 bg-blue-50/30 text-blue-800 font-medium py-2">
                                    <template x-for="(label, key) in variables" :key="key">
                                        <option :value="key" x-text="label"></option>
                                    </template>
                                </select>
                            </template>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Posición y Tamaño -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Transformación</label>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="relative">
                                    <span class="absolute left-2 top-2 text-[10px] text-gray-400 font-bold">X</span>
                                    <input type="number" x-model.number="activeElement.x" @change="queueHistory" class="w-full pl-6 py-1 rounded-md border-gray-200 text-xs focus:ring-0 bg-gray-50 focus:bg-white text-right font-mono">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-2 top-2 text-[10px] text-gray-400 font-bold">Y</span>
                                    <input type="number" x-model.number="activeElement.y" @change="queueHistory" class="w-full pl-6 py-1 rounded-md border-gray-200 text-xs focus:ring-0 bg-gray-50 focus:bg-white text-right font-mono">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-2 top-2 text-[10px] text-gray-400 font-bold">W</span>
                                    <input type="number" x-model.number="activeElement.width" @change="queueHistory" class="w-full pl-6 py-1 rounded-md border-gray-200 text-xs focus:ring-0 bg-gray-50 focus:bg-white text-right font-mono">
                                </div>
                                <template x-if="activeElement.height !== null">
                                    <div class="relative">
                                        <span class="absolute left-2 top-2 text-[10px] text-gray-400 font-bold">H</span>
                                        <input type="number" x-model.number="activeElement.height" @change="queueHistory" class="w-full pl-6 py-1 rounded-md border-gray-200 text-xs focus:ring-0 bg-gray-50 focus:bg-white text-right font-mono">
                                    </div>
                                </template>
                                <div class="relative">
                                    <span class="absolute left-2 top-2 text-[10px] text-gray-400 font-bold">R</span>
                                    <input type="number" x-model.number="activeElement.rotation" @change="queueHistory" class="w-full pl-6 py-1 rounded-md border-gray-200 text-xs focus:ring-0 bg-gray-50 focus:bg-white text-right font-mono">
                                </div>
                            </div>
                        </div>

                        <!-- Estilos de Texto -->
                        <template x-if="['text', 'variable'].includes(activeElement.type)">
                            <div class="space-y-4">
                                <hr class="border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Tipografía</label>
                                
                                <select x-model="activeElement.fontFamily" @change="queueHistory" class="w-full rounded-md border-gray-200 text-xs focus:ring-0 bg-gray-50 py-1.5">
                                    <option value="Inter">Inter (Sans)</option>
                                    <option value="EB Garamond">Garamond (Serif)</option>
                                    <option value="Cinzel Decorative">Cinzel (Decor)</option>
                                    <option value="Pinyon Script">Pinyon (Script)</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Playfair Display">Playfair</option>
                                </select>
                                
                                <div class="flex gap-2 items-center">
                                    <div class="relative flex-1">
                                        <input type="number" x-model="activeElement.fontSize" @change="queueHistory" class="w-full rounded-md border-gray-200 text-xs pl-2 py-1.5 bg-gray-50 focus:bg-white font-mono">
                                        <span class="absolute right-2 top-1.5 text-[10px] text-gray-400">px</span>
                                    </div>
                                    <div class="relative w-8 h-8 rounded-md border border-gray-200 shadow-sm cursor-pointer hover:border-indigo-300 overflow-hidden bg-white">
                                        <input type="color" x-model="activeElement.color" @change="queueHistory" class="absolute -top-4 -left-4 w-16 h-16 cursor-pointer border-0">
                                    </div>
                                    <div class="w-10">
                                        <select x-model="activeElement.fontWeight" class="w-full rounded-md border-gray-200 text-xs py-1.5 bg-gray-50">
                                            <option value="400">R</option>
                                            <option value="700">B</option>
                                            <option value="300">L</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex bg-gray-100 p-1 rounded-md">
                                    <button @click="activeElement.textAlign = 'left'; queueHistory()" class="flex-1 py-1 rounded-sm transition text-gray-500 hover:text-gray-800" :class="activeElement.textAlign === 'left' ? 'bg-white shadow-sm text-indigo-600' : ''"><i class="ph-bold ph-text-align-left"></i></button>
                                    <button @click="activeElement.textAlign = 'center'; queueHistory()" class="flex-1 py-1 rounded-sm transition text-gray-500 hover:text-gray-800" :class="activeElement.textAlign === 'center' ? 'bg-white shadow-sm text-indigo-600' : ''"><i class="ph-bold ph-text-align-center"></i></button>
                                    <button @click="activeElement.textAlign = 'right'; queueHistory()" class="flex-1 py-1 rounded-sm transition text-gray-500 hover:text-gray-800" :class="activeElement.textAlign === 'right' ? 'bg-white shadow-sm text-indigo-600' : ''"><i class="ph-bold ph-text-align-right"></i></button>
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