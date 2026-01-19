<div class="flex flex-col h-screen bg-gray-50 overflow-hidden font-sans select-none text-slate-800" 
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
        
        /* Patrón de fondo estilo Dashboard */
        .workspace-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .cursor-rotate { cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>') 10 10, auto; }
        
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    </style>

    <!-- 1. BARRA SUPERIOR (HEADER) ESTILO DASHBOARD -->
    <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 z-50 shadow-sm shrink-0 relative">
        <!-- Izquierda: Título y Estado -->
        <div class="flex items-center gap-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 ring-1 ring-indigo-100 shadow-sm">
                    <i class="ph-bold ph-certificate text-xl"></i>
                </div>
                <div class="flex flex-col justify-center">
                    <input type="text" wire:model.live.debounce.500ms="name" 
                           class="font-bold text-gray-900 border-none p-0 focus:ring-0 text-sm bg-transparent placeholder-gray-400 w-48 leading-tight focus:border-b focus:border-indigo-500 transition-colors" 
                           placeholder="Nombre del Diploma">
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider">Editor Visual</span>
                        <!-- Indicador de guardado -->
                        <span class="flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-green-50 border border-green-100">
                            <span wire:loading.remove wire:target="save" class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            <span wire:loading wire:target="save" class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                            <span wire:loading.remove wire:target="save" class="text-[9px] font-bold text-green-700">Guardado</span>
                            <span wire:loading wire:target="save" class="text-[9px] font-bold text-indigo-700">Guardando...</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="h-8 w-px bg-gray-200 mx-2"></div>

            <!-- Historia -->
            <div class="flex items-center gap-1 bg-gray-50 p-1 rounded-lg border border-gray-200">
                <button @click="undo" :disabled="historyStep <= 0" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-white rounded-md disabled:opacity-30 disabled:hover:bg-transparent transition shadow-sm disabled:shadow-none" title="Deshacer (Ctrl+Z)">
                    <i class="ph-bold ph-arrow-u-up-left text-lg"></i>
                </button>
                <button @click="redo" :disabled="historyStep >= history.length - 1" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-white rounded-md disabled:opacity-30 disabled:hover:bg-transparent transition shadow-sm disabled:shadow-none" title="Rehacer (Ctrl+Y)">
                    <i class="ph-bold ph-arrow-u-up-right text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Centro: Controles de Vista (Posicionamiento Absoluto Fijo) -->
        <div class="absolute left-[648px] top-1/2 transform -translate-y-1/2 flex items-center gap-2 bg-white/90 backdrop-blur-md p-1.5 rounded-xl shadow-lg ring-1 ring-gray-900/5">
            <div class="flex items-center bg-gray-50 rounded-lg p-0.5 border border-gray-200">
                <button @click="zoomOut" class="w-7 h-7 flex items-center justify-center hover:bg-white rounded-md shadow-sm transition text-gray-600 hover:text-indigo-600">
                    <i class="ph-bold ph-minus"></i>
                </button>
                <span class="text-xs font-mono w-10 text-center font-bold text-gray-700" x-text="Math.round(zoom * 100) + '%'"></span>
                <button @click="zoomIn" class="w-7 h-7 flex items-center justify-center hover:bg-white rounded-md shadow-sm transition text-gray-600 hover:text-indigo-600">
                    <i class="ph-bold ph-plus"></i>
                </button>
            </div>
            
            <div class="w-px h-5 bg-gray-200 mx-1"></div>
            
            <button @click="canvasConfig.orientation = canvasConfig.orientation === 'landscape' ? 'portrait' : 'landscape'; updateCanvasSize()" 
                    class="text-[10px] px-3 py-1.5 rounded-lg transition flex items-center gap-2 font-bold uppercase tracking-wide border"
                    :class="canvasConfig.orientation === 'landscape' ? 'bg-indigo-50 border-indigo-100 text-indigo-700' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'">
                <i class="ph-bold text-lg" :class="canvasConfig.orientation === 'landscape' ? 'ph-rectangle' : 'ph-rectangle text-rotate-90'"></i>
                <span x-text="canvasConfig.orientation === 'landscape' ? 'Horizontal' : 'Vertical'"></span>
            </button>
        </div>

        <!-- Derecha: Acciones Principales -->
        <div class="flex items-center gap-3">
            <button @click="snapToGrid = !snapToGrid" 
                    class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold transition border"
                    :class="snapToGrid ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'text-gray-500 border-transparent hover:bg-gray-100'">
                <i class="ph-bold ph-magnet text-base"></i>
                <span class="hidden sm:inline">Guías</span>
            </button>

            <button @click="togglePreview" class="group relative flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-bold border transition-all duration-200" 
                :class="previewMode ? 'bg-gray-900 text-white border-gray-900 shadow-md' : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300 hover:shadow-sm'">
                <i class="ph-bold text-base" :class="previewMode ? 'ph-pencil-simple' : 'ph-eye'"></i>
                <span x-text="previewMode ? 'Editar Diseño' : 'Vista Previa'"></span>
            </button>

            <button wire:click="save" class="bg-indigo-600 hover:bg-indigo-500 text-white px-5 py-2 rounded-lg text-xs font-bold shadow-md shadow-indigo-200 transition flex items-center gap-2 relative overflow-hidden ring-1 ring-inset ring-indigo-700/10">
                <div wire:loading wire:target="save" class="absolute inset-0 flex items-center justify-center bg-indigo-600 z-10">
                    <i class="ph-bold ph-spinner animate-spin text-lg"></i>
                </div>
                <i class="ph-bold ph-floppy-disk text-base"></i>
                <span>Guardar</span>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden relative z-0">
        
        <!-- 2. BARRA LATERAL IZQUIERDA (Menú Estilo Tabs) -->
        <aside class="w-20 bg-white border-r border-gray-200 flex flex-col items-center py-6 z-30 shrink-0 gap-4 shadow-[4px_0_24px_-12px_rgba(0,0,0,0.1)] relative" x-show="!previewMode">
            <template x-for="tab in [
                { id: 'elements', icon: 'ph-shapes', label: 'Insertar' },
                { id: 'layers', icon: 'ph-stack', label: 'Capas' },
                { id: 'settings', icon: 'ph-sliders', label: 'Ajustes' }
            ]">
                <button @click="activeTab = activeTab === tab.id ? null : tab.id" 
                        class="w-12 h-12 flex flex-col items-center justify-center rounded-xl transition duration-200 relative group" 
                        :class="activeTab === tab.id ? 'bg-indigo-50 text-indigo-600 shadow-sm ring-1 ring-indigo-100' : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'">
                    <i class="ph-bold text-2xl mb-1" :class="tab.icon"></i>
                    <span class="text-[9px] font-bold" x-text="tab.label"></span>
                    
                    <!-- Indicador activo -->
                    <div x-show="activeTab === tab.id" class="absolute -right-[17px] top-1/2 -translate-y-1/2 w-1.5 h-8 bg-indigo-600 rounded-l-full"></div>
                </button>
            </template>
        </aside>

        <!-- 2.1 PANEL EXTENDIDO IZQUIERDO (FIXED / DOCKED) -->
        <!-- Cambio clave: Removido 'absolute' y 'left-20' para que sea parte del flujo Flex -->
        <div class="w-72 bg-white border-r border-gray-200 flex flex-col z-20 shrink-0 h-full transition-all duration-300" 
             x-show="activeTab && !previewMode"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-4">
            
            <div class="h-14 px-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800" x-text="activeTab === 'elements' ? 'Biblioteca' : (activeTab === 'layers' ? 'Gestor de Capas' : 'Configuración Global')"></h3>
                <button @click="activeTab = null" class="text-gray-400 hover:text-gray-600 hover:bg-gray-200/50 rounded-lg p-1 transition"><i class="ph-bold ph-x text-lg"></i></button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 custom-scrollbar bg-white">
                
                <!-- TAB: ELEMENTOS -->
                <div x-show="activeTab === 'elements'" class="space-y-6">
                    <div>
                        <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block mb-3 flex items-center gap-2">
                            <i class="ph-fill ph-text-t"></i> Tipografía
                        </span>
                        <div class="space-y-2">
                            <button @click="addElement('text', {fontSize: 48, fontWeight: 700, content: 'TÍTULO DIPLOMA', fontFamily: 'Cinzel Decorative'})" 
                                    class="w-full text-left p-3 border border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50/50 hover:shadow-md transition group bg-white relative overflow-hidden">
                                <span class="font-bold text-xl text-gray-800 font-cinzel relative z-10 group-hover:text-indigo-800">Título Principal</span>
                                <i class="ph-bold ph-plus-circle absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 group-hover:text-indigo-500 text-xl transition"></i>
                            </button>
                            
                            <button @click="addElement('text', {fontSize: 24, content: 'Subtítulo del documento', fontFamily: 'Montserrat'})" 
                                    class="w-full text-left p-3 border border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50/50 hover:shadow-md transition group bg-white relative">
                                <span class="font-medium text-sm text-gray-600 font-montserrat group-hover:text-indigo-700">Texto Secundario</span>
                                <i class="ph-bold ph-plus-circle absolute right-3 top-1/2 -translate-y-1/2 text-gray-300 group-hover:text-indigo-500 text-xl transition"></i>
                            </button>

                            <button @click="addElement('variable')" class="w-full flex items-center justify-between p-3 border border-dashed border-indigo-300 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition bg-indigo-50/30 group">
                                <div class="flex items-center gap-3">
                                    <span class="bg-indigo-100 text-indigo-600 w-8 h-8 rounded-lg flex items-center justify-center font-mono text-xs font-bold border border-indigo-200">{ }</span>
                                    <div class="flex flex-col text-left">
                                        <span class="text-xs font-bold text-gray-700">Dato Dinámico</span>
                                        <span class="text-[10px] text-gray-500">Nombre, Fecha, etc.</span>
                                    </div>
                                </div>
                                <i class="ph-bold ph-plus text-indigo-400 group-hover:text-indigo-600"></i>
                            </button>
                        </div>
                    </div>

                    <div class="h-px bg-gray-100 w-full"></div>

                    <div>
                        <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block mb-3 flex items-center gap-2">
                            <i class="ph-fill ph-shapes"></i> Gráficos
                        </span>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="addElement('qr')" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition bg-white shadow-sm hover:shadow-md group h-28">
                                <i class="ph ph-qr-code text-3xl text-gray-400 group-hover:text-indigo-600 mb-2 transition transform group-hover:scale-110"></i>
                                <span class="text-[10px] font-bold text-gray-500 group-hover:text-indigo-700">QR Code</span>
                            </button>
                            <button @click="addElement('shape', {width: 100, height: 100, borderRadius: 100, borderColor: '#b49b5a', borderWidth: 2, fill: 'transparent'})" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition bg-white shadow-sm hover:shadow-md group h-28">
                                <div class="w-8 h-8 rounded-full border-2 border-yellow-600 mb-2 group-hover:border-indigo-500 transition-colors"></div>
                                <span class="text-[10px] font-bold text-gray-500 group-hover:text-indigo-700">Círculo</span>
                            </button>
                            <button @click="addElement('shape', {width: 150, height: 150, borderRadius: 0, borderColor: '#1f2937', borderWidth: 4, fill: 'transparent'})" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition bg-white shadow-sm hover:shadow-md group col-span-2 h-20 flex-row gap-4">
                                <div class="w-12 h-8 border-2 border-gray-700 group-hover:border-indigo-500 transition-colors"></div>
                                <span class="text-[10px] font-bold text-gray-500 group-hover:text-indigo-700">Marco Rectangular</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- TAB: CAPAS -->
                <div x-show="activeTab === 'layers'" class="space-y-2">
                    <template x-for="(element, index) in [...elements].reverse()" :key="element.id || index">
                        <div class="flex items-center gap-3 p-2.5 rounded-xl cursor-pointer group border transition-all duration-200"
                             :class="selectedIds.includes(getElementRealIndex(element)) ? 'bg-indigo-50 border-indigo-200 shadow-sm ring-1 ring-indigo-200/50' : 'bg-white border-transparent hover:border-gray-200 hover:bg-gray-50'"
                             @click="selectElement(getElementRealIndex(element), $event.ctrlKey)">
                            
                            <!-- Icono Tipo -->
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg shrink-0 transition-colors"
                                 :class="selectedIds.includes(getElementRealIndex(element)) ? 'bg-indigo-200 text-indigo-700' : 'bg-gray-100 text-gray-500'">
                                <i class="ph-bold" :class="getIconForType(element.type)"></i>
                            </div>

                            <!-- Nombre Capa -->
                            <div class="flex-1 overflow-hidden">
                                <span class="text-xs font-bold text-gray-800 block truncate" x-text="element.type === 'variable' ? 'Variable Dinámica' : (element.type === 'text' ? 'Texto' : (element.type === 'qr' ? 'Código QR' : 'Forma Geométrica'))"></span>
                                <span class="text-[10px] text-gray-500 block truncate font-mono" x-text="element.content || 'Sin contenido'"></span>
                            </div>
                            
                            <!-- Acciones Rápidas -->
                            <div class="flex items-center gap-1" :class="selectedIds.includes(getElementRealIndex(element)) ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                <button @click.stop="toggleLock(getElementRealIndex(element))" class="w-6 h-6 flex items-center justify-center rounded hover:bg-white text-gray-400 hover:text-gray-700 transition" title="Bloquear">
                                    <i class="ph-bold text-sm" :class="element.locked ? 'ph-lock-key text-red-500' : 'ph-lock-key-open'"></i>
                                </button>
                                <button @click.stop="toggleVisibility(getElementRealIndex(element))" class="w-6 h-6 flex items-center justify-center rounded hover:bg-white text-gray-400 hover:text-gray-700 transition" title="Ocultar">
                                    <i class="ph-bold text-sm" :class="element.hidden ? 'ph-eye-slash text-gray-400' : 'ph-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="elements.length === 0" class="text-center py-10">
                        <i class="ph-duotone ph-stack text-4xl text-gray-200 mb-2"></i>
                        <p class="text-xs text-gray-400">No hay capas</p>
                    </div>
                </div>

                <!-- TAB: CONFIGURACIÓN -->
                <div x-show="activeTab === 'settings'" class="space-y-6">
                    <div class="space-y-4">
                        <span class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block">Fondo del Diploma</span>
                        
                        <div class="relative group">
                            <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-200 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:bg-white hover:border-indigo-400 hover:shadow-md transition-all duration-300 relative overflow-hidden group">
                                
                                <!-- Estado Vacío -->
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-gray-400 group-hover:text-gray-600 z-10" x-show="!$wire.currentBg && !$wire.bgImage">
                                    <div class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center mb-3 group-hover:scale-110 transition">
                                        <i class="ph-bold ph-image text-2xl text-indigo-400"></i>
                                    </div>
                                    <p class="text-xs font-bold uppercase tracking-wide">Subir Imagen</p>
                                    <p class="text-[10px] text-gray-400 mt-1">PNG, JPG hasta 5MB</p>
                                </div>
                                
                                <!-- Preview -->
                                @if($currentBg && !$bgImage)
                                    <img src="{{ asset('storage/'.$currentBg) }}" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-100 transition" />
                                @endif

                                @if($bgImage)
                                    <img src="{{ $bgImage->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover opacity-80 group-hover:opacity-100 transition" />
                                @endif

                                <!-- Overlay Hover -->
                                <div class="absolute inset-0 bg-gray-900/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition z-20 backdrop-blur-[2px]">
                                    <span class="text-white text-xs font-bold flex items-center gap-2 bg-white/20 px-4 py-2 rounded-full border border-white/30 backdrop-blur-md hover:bg-white/30 transition">
                                        <i class="ph-bold ph-arrows-clockwise"></i> Reemplazar
                                    </span>
                                </div>

                                <input type="file" wire:model="bgImage" class="hidden" accept="image/*">
                            </label>
                        </div>
                        
                        @if($currentBg || $bgImage)
                            <button @click="$wire.set('bgImage', null); $wire.set('currentBg', null)" class="w-full py-2.5 text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 rounded-xl flex items-center justify-center gap-2 transition">
                                <i class="ph-bold ph-trash"></i> Eliminar fondo actual
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. LIENZO (CANVAS) -->
        <main class="flex-1 relative overflow-hidden flex flex-col workspace-pattern z-0" 
              @mousedown="if($event.target === $el || $event.target.id === 'scroll-container') deselectAll()">
            
            <!-- Contenedor SCROLLABLE con GRID -->
            <div class="flex-1 overflow-auto relative w-full h-full custom-scrollbar grid place-items-center p-20" 
                 id="scroll-container"
                 @wheel.ctrl.prevent="handleWheelZoom">
                
                <!-- EL LIENZO -->
                <div id="canvas" 
                     class="bg-white shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] relative transition-all duration-150 ease-out origin-center shrink-0 select-none ring-1 ring-gray-900/5"
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
                    <div x-show="!previewMode && snapToGrid" class="absolute top-[10mm] bottom-[10mm] left-[10mm] right-[10mm] border border-indigo-300/30 pointer-events-none z-0 border-dashed">
                        <div class="absolute -top-4 left-0 text-indigo-300/50 text-[9px] font-mono">MARGEN SEGURO</div>
                    </div>

                    <!-- Elementos -->
                    <template x-for="(element, index) in elements" :key="element.id || index">
                        <div x-show="!element.hidden"
                             class="absolute group box-border select-none flex items-center justify-center"
                             :class="{
                                'cursor-move': !element.locked && !previewMode, 
                                'ring-2 ring-indigo-500 z-50 shadow-xl': isSelected(index) && !previewMode,
                                'hover:ring-1 hover:ring-indigo-300 z-40': !isSelected(index) && !element.locked && !previewMode
                             }"
                             :style="getElementStyle(element)"
                             @mousedown.stop="startDrag($event, index)"
                             @click.stop="selectElement(index)">
                            
                            <!-- Contenido -->
                            <div class="w-full h-full overflow-hidden pointer-events-none relative">
                                <!-- Texto -->
                                <template x-if="element.type === 'text'">
                                    <div x-text="element.content" class="w-full h-full whitespace-pre-wrap break-words leading-tight" style="outline: none;"></div>
                                </template>
                                
                                <!-- Variable -->
                                <template x-if="element.type === 'variable'">
                                    <div class="w-full h-full flex items-center justify-center px-2 leading-tight transition-colors duration-200"
                                         :class="previewMode ? '' : 'bg-indigo-50/80 text-indigo-700/80 border border-indigo-300/50 border-dashed rounded'">
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

                            <!-- Handles Transformación -->
                            <template x-if="isSelected(index) && !element.locked && !previewMode">
                                <div class="absolute inset-0 z-50 pointer-events-none">
                                    <!-- Esquinas -->
                                    <div class="absolute -top-1.5 -left-1.5 w-3 h-3 bg-white border-2 border-indigo-600 shadow-sm pointer-events-auto cursor-nw-resize rounded-full hover:scale-125 transition" @mousedown.stop="startResize($event, index, 'nw')"></div>
                                    <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-white border-2 border-indigo-600 shadow-sm pointer-events-auto cursor-ne-resize rounded-full hover:scale-125 transition" @mousedown.stop="startResize($event, index, 'ne')"></div>
                                    <div class="absolute -bottom-1.5 -left-1.5 w-3 h-3 bg-white border-2 border-indigo-600 shadow-sm pointer-events-auto cursor-sw-resize rounded-full hover:scale-125 transition" @mousedown.stop="startResize($event, index, 'sw')"></div>
                                    <div class="absolute -bottom-1.5 -right-1.5 w-3 h-3 bg-white border-2 border-indigo-600 shadow-sm pointer-events-auto cursor-se-resize rounded-full hover:scale-125 transition" @mousedown.stop="startResize($event, index, 'se')"></div>
                                    
                                    <!-- Rotación -->
                                    <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-8 h-8 bg-white rounded-full shadow-lg border border-gray-200 flex items-center justify-center cursor-rotate pointer-events-auto hover:text-indigo-600 text-gray-500 hover:border-indigo-300 transition z-50 group-rotate" @mousedown.stop="startRotate($event, index)">
                                        <i class="ph-bold ph-arrow-clockwise text-sm"></i>
                                    </div>
                                    <div class="absolute -top-4 left-1/2 h-4 w-px bg-indigo-500/50 -translate-x-1/2"></div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Floating Zoom/Info -->
            <div class="absolute bottom-6 right-6 flex items-center gap-2 pointer-events-none">
                <div class="bg-gray-800/80 backdrop-blur text-white px-3 py-1.5 rounded-lg text-xs font-mono shadow-lg pointer-events-auto">
                    <span x-text="canvasConfig.width + ' x ' + canvasConfig.height + ' px'"></span>
                </div>
            </div>
        </main>

        <!-- 4. PANEL DERECHO (Propiedades & Alineación) -->
        <aside class="w-80 bg-white border-l border-gray-200 flex flex-col z-30 shrink-0 shadow-[-4px_0_24px_-12px_rgba(0,0,0,0.05)]" x-show="!previewMode">
            <template x-if="selectedIds.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-center p-8 space-y-4 select-none">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-2">
                        <i class="ph-duotone ph-cursor-click text-4xl text-gray-300"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Sin Selección</h4>
                        <p class="text-xs text-gray-500 mt-1 max-w-[180px] mx-auto leading-relaxed">Haz clic en un elemento del lienzo para editar sus propiedades o alineación.</p>
                    </div>
                </div>
            </template>

            <template x-if="selectedIds.length > 0">
                <div class="flex flex-col h-full bg-gray-50/30">
                    <div class="h-14 px-6 border-b border-gray-200 flex justify-between items-center bg-white shrink-0">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider flex items-center gap-2">
                            <i class="ph-fill ph-sliders-horizontal"></i> Propiedades
                        </span>
                        <div class="flex gap-1 text-gray-500">
                            <button @click="duplicateElement()" class="hover:text-indigo-600 transition p-2 hover:bg-indigo-50 rounded-lg" title="Duplicar"><i class="ph-bold ph-copy text-lg"></i></button>
                            <button @click="removeElement()" class="hover:text-red-500 transition p-2 hover:bg-red-50 rounded-lg" title="Eliminar"><i class="ph-bold ph-trash text-lg"></i></button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 space-y-8 custom-scrollbar">
                        
                        <!-- Alineación y Distribución -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block">Alineación (Lienzo)</label>
                            <div class="bg-white p-2 rounded-xl border border-gray-200 shadow-sm">
                                <div class="grid grid-cols-6 gap-1">
                                    <button @click="alignElement('left')" class="p-1.5 rounded hover:bg-gray-100 text-gray-500 hover:text-indigo-600 transition" title="Izquierda"><i class="ph-bold ph-align-left text-lg"></i></button>
                                    <button @click="alignElement('center-h')" class="p-1.5 rounded hover:bg-gray-100 text-gray-500 hover:text-indigo-600 transition" title="Centro Horizontal"><i class="ph-bold ph-align-center-horizontal text-lg"></i></button>
                                    <button @click="alignElement('right')" class="p-1.5 rounded hover:bg-gray-100 text-gray-500 hover:text-indigo-600 transition" title="Derecha"><i class="ph-bold ph-align-right text-lg"></i></button>
                                    <button @click="alignElement('top')" class="p-1.5 rounded hover:bg-gray-100 text-gray-500 hover:text-indigo-600 transition" title="Arriba"><i class="ph-bold ph-align-top text-lg"></i></button>
                                    <button @click="alignElement('middle-v')" class="p-1.5 rounded hover:bg-gray-100 text-gray-500 hover:text-indigo-600 transition" title="Centro Vertical"><i class="ph-bold ph-align-center-vertical text-lg"></i></button>
                                    <button @click="alignElement('bottom')" class="p-1.5 rounded hover:bg-gray-100 text-gray-500 hover:text-indigo-600 transition" title="Abajo"><i class="ph-bold ph-align-bottom text-lg"></i></button>
                                </div>
                            </div>
                            
                            <!-- Orden (Z-Index) -->
                             <div class="grid grid-cols-2 gap-2">
                                <button @click="bringToFront()" class="flex items-center justify-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm">
                                    <i class="ph-bold ph-caret-double-up"></i> Traer al frente
                                </button>
                                <button @click="sendToBack()" class="flex items-center justify-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm">
                                    <i class="ph-bold ph-caret-double-down"></i> Enviar al fondo
                                </button>
                            </div>
                        </div>

                        <div class="h-px bg-gray-200"></div>

                        <!-- Contenido -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block">Contenido</label>
                            
                            <template x-if="activeElement.type === 'text'">
                                <textarea x-model="activeElement.content" @input="queueHistory" rows="3" 
                                    class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white shadow-sm resize-none py-3 px-3 placeholder-gray-400 font-medium transition"></textarea>
                            </template>
                            
                            <template x-if="activeElement.type === 'variable'">
                                <div class="relative">
                                    <select x-model="activeElement.content" @change="queueHistory" 
                                            class="w-full rounded-xl border-indigo-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-indigo-50/50 text-indigo-900 font-medium py-2.5 pl-3 pr-10 appearance-none">
                                        <template x-for="(label, key) in variables" :key="key">
                                            <option :value="key" x-text="label"></option>
                                        </template>
                                    </select>
                                    <i class="ph-bold ph-caret-down absolute right-3 top-3 text-indigo-400 pointer-events-none"></i>
                                </div>
                            </template>
                            
                            <template x-if="['shape', 'qr'].includes(activeElement.type)">
                                <div class="bg-blue-50 text-blue-700 px-4 py-3 rounded-xl text-xs flex items-start gap-2 border border-blue-100">
                                    <i class="ph-fill ph-info text-base shrink-0 mt-0.5"></i>
                                    <span>Este elemento es gráfico y su contenido no es texto editable directamente.</span>
                                </div>
                            </template>
                        </div>

                        <!-- Estilos de Texto -->
                        <template x-if="['text', 'variable'].includes(activeElement.type)">
                            <div class="space-y-4">
                                <div class="h-px bg-gray-200"></div>
                                <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block">Tipografía</label>
                                
                                <div class="space-y-3">
                                    <div class="relative">
                                        <select x-model="activeElement.fontFamily" @change="queueHistory" class="w-full rounded-xl border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white py-2.5 px-3 font-medium shadow-sm appearance-none">
                                            <option value="Inter">Inter (Moderna)</option>
                                            <option value="EB Garamond">Garamond (Clásica)</option>
                                            <option value="Cinzel Decorative">Cinzel (Título)</option>
                                            <option value="Pinyon Script">Pinyon (Manuscrita)</option>
                                            <option value="Montserrat">Montserrat (Geométrica)</option>
                                            <option value="Playfair Display">Playfair (Elegante)</option>
                                        </select>
                                        <i class="ph-bold ph-caret-down absolute right-3 top-3 text-gray-400 pointer-events-none"></i>
                                    </div>
                                    
                                    <div class="flex gap-2 items-center">
                                        <div class="relative flex-1 group">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-400 text-xs font-bold">Pt</span>
                                            </div>
                                            <input type="number" x-model="activeElement.fontSize" @change="queueHistory" 
                                                   class="w-full rounded-xl border-gray-200 text-sm pl-8 py-2.5 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-center font-bold">
                                        </div>
                                        
                                        <div class="w-20 shrink-0">
                                            <select x-model="activeElement.fontWeight" class="w-full rounded-xl border-gray-200 text-xs py-2.5 bg-white px-1 text-center font-bold shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="300">Light</option>
                                                <option value="400">Regular</option>
                                                <option value="700">Bold</option>
                                            </select>
                                        </div>

                                        <div class="relative w-10 h-10 rounded-xl border border-gray-200 shadow-sm cursor-pointer hover:border-indigo-300 overflow-hidden bg-white shrink-0 ring-2 ring-transparent hover:ring-indigo-100 transition">
                                            <input type="color" x-model="activeElement.color" @change="queueHistory" class="absolute -top-4 -left-4 w-20 h-20 cursor-pointer border-0 p-0">
                                        </div>
                                    </div>

                                    <div class="flex bg-gray-100 p-1 rounded-xl">
                                        <button @click="activeElement.textAlign = 'left'; queueHistory()" class="flex-1 py-1.5 rounded-lg transition text-gray-500 hover:text-gray-800" :class="activeElement.textAlign === 'left' ? 'bg-white shadow-sm text-indigo-600 ring-1 ring-black/5' : ''"><i class="ph-bold ph-text-align-left text-lg"></i></button>
                                        <button @click="activeElement.textAlign = 'center'; queueHistory()" class="flex-1 py-1.5 rounded-lg transition text-gray-500 hover:text-gray-800" :class="activeElement.textAlign === 'center' ? 'bg-white shadow-sm text-indigo-600 ring-1 ring-black/5' : ''"><i class="ph-bold ph-text-align-center text-lg"></i></button>
                                        <button @click="activeElement.textAlign = 'right'; queueHistory()" class="flex-1 py-1.5 rounded-lg transition text-gray-500 hover:text-gray-800" :class="activeElement.textAlign === 'right' ? 'bg-white shadow-sm text-indigo-600 ring-1 ring-black/5' : ''"><i class="ph-bold ph-text-align-right text-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Transformación -->
                        <div class="space-y-3">
                            <div class="h-px bg-gray-200"></div>
                            <label class="text-[10px] font-extrabold text-gray-400 uppercase tracking-widest block">Dimensiones & Posición</label>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-[10px] text-gray-400 font-bold">X</span>
                                    <input type="number" x-model.number="activeElement.x" @change="queueHistory" class="w-full pl-7 py-2 rounded-xl border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500 bg-white text-right font-mono transition shadow-sm">
                                </div>
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-[10px] text-gray-400 font-bold">Y</span>
                                    <input type="number" x-model.number="activeElement.y" @change="queueHistory" class="w-full pl-7 py-2 rounded-xl border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500 bg-white text-right font-mono transition shadow-sm">
                                </div>
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-[10px] text-gray-400 font-bold">W</span>
                                    <input type="number" x-model.number="activeElement.width" @change="queueHistory" class="w-full pl-7 py-2 rounded-xl border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500 bg-white text-right font-mono transition shadow-sm">
                                </div>
                                <template x-if="activeElement.height !== null">
                                    <div class="relative group">
                                        <span class="absolute left-3 top-2.5 text-[10px] text-gray-400 font-bold">H</span>
                                        <input type="number" x-model.number="activeElement.height" @change="queueHistory" class="w-full pl-7 py-2 rounded-xl border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500 bg-white text-right font-mono transition shadow-sm">
                                    </div>
                                </template>
                                <div class="relative group col-span-2">
                                    <span class="absolute left-3 top-2.5 text-[10px] text-gray-400 font-bold">R</span>
                                    <input type="number" x-model.number="activeElement.rotation" @change="queueHistory" class="w-full pl-7 py-2 rounded-xl border-gray-200 text-xs focus:ring-indigo-500 focus:border-indigo-500 bg-white text-right font-mono transition shadow-sm">
                                    <span class="absolute right-8 top-2.5 text-[10px] text-gray-400">°</span>
                                </div>
                            </div>
                        </div>

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
                zoom: 0.6,
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
                    '{date}': 'Fecha Actual',
                    '{folio}': 'Folio Único',
                    '{instructor}': 'Nombre Instructor'
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
                    const mmToPx = (mm) => Math.round(mm * 3.7795);
                    let w_mm = 210, h_mm = 297; // A4

                    if (this.canvasConfig.orientation === 'landscape') {
                        this.canvasConfig.width = mmToPx(h_mm);
                        this.canvasConfig.height = mmToPx(w_mm);
                    } else {
                        this.canvasConfig.width = mmToPx(w_mm);
                        this.canvasConfig.height = mmToPx(h_mm);
                    }
                },

                addElement(type, props = {}) {
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
                        fontFamily: 'Inter', fontSize: 24, fontWeight: '400', color: '#1f2937',
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
                    // Sort indices desc to remove without shifting issues
                    const indicesToRemove = [...this.selectedIds].sort((a, b) => b - a);
                    indicesToRemove.forEach(index => {
                         this.elements.splice(index, 1);
                    });
                    this.selectedIds = [];
                    this.saveHistory();
                },

                duplicateElement() {
                    if (this.selectedIds.length === 0) return;
                    const idx = this.selectedIds[0];
                    const original = this.elements[idx];
                    const copy = JSON.parse(JSON.stringify(original));
                    copy.id = Date.now() + Math.random();
                    copy.x += 20; copy.y += 20;
                    this.elements.push(copy);
                    this.selectElement(this.elements.length - 1);
                    this.saveHistory();
                },

                selectElement(index, multi = false) {
                    if (this.previewMode) return;
                    this.selectedIds = [index];
                },

                deselectAll() { this.selectedIds = []; },
                
                getElementRealIndex(el) { return this.elements.indexOf(el); },
                isSelected(index) { return this.selectedIds.includes(index); },

                toggleLock(index) { this.elements[index].locked = !this.elements[index].locked; },
                toggleVisibility(index) { this.elements[index].hidden = !this.elements[index].hidden; },

                // Nuevas Funciones de Alineación
                alignElement(position) {
                    if (this.selectedIds.length === 0) return;
                    const idx = this.selectedIds[0];
                    const el = this.elements[idx];
                    const canvasW = this.canvasConfig.width;
                    const canvasH = this.canvasConfig.height;

                    switch(position) {
                        case 'left': el.x = 0; break;
                        case 'center-h': el.x = (canvasW - el.width) / 2; break;
                        case 'right': el.x = canvasW - el.width; break;
                        case 'top': el.y = 0; break;
                        case 'middle-v': el.y = (canvasH - (el.height || 50)) / 2; break;
                        case 'bottom': el.y = canvasH - (el.height || 50); break;
                    }
                    this.queueHistory();
                },

                // Z-Index Management
                bringToFront() {
                      if (this.selectedIds.length === 0) return;
                      const idx = this.selectedIds[0];
                      const element = this.elements.splice(idx, 1)[0];
                      this.elements.push(element); // Move to end of array (top)
                      this.selectElement(this.elements.length - 1);
                      this.queueHistory();
                },

                sendToBack() {
                      if (this.selectedIds.length === 0) return;
                      const idx = this.selectedIds[0];
                      const element = this.elements.splice(idx, 1)[0];
                      this.elements.unshift(element); // Move to start of array (bottom)
                      this.selectElement(0);
                      this.queueHistory();
                },

                // Drag & Drop
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
                    const dx = (e.clientX - this.dragStart.x) / this.zoom;
                    const dy = (e.clientY - this.dragStart.y) / this.zoom;

                    if (this.interactionType === 'move') {
                        let newX = this.elementStart.x + dx;
                        let newY = this.elementStart.y + dy;
                        // Snap simple al centro
                        if (this.snapToGrid && !e.shiftKey) {
                            if (Math.abs(newX - (this.canvasConfig.width/2 - el.width/2)) < 15) newX = (this.canvasConfig.width/2 - el.width/2);
                            if (Math.abs(newY - (this.canvasConfig.height/2 - (el.height||0)/2)) < 15) newY = (this.canvasConfig.height/2 - (el.height||0)/2);
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
                        z-index: ${el.zIndex || 1};
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