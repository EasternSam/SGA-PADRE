<div class="flex flex-col h-[calc(100vh-65px)] bg-gray-100 overflow-hidden font-inter" 
     x-data="certificateEditor(@entangle('elements').live)"
     @keydown.window.ctrl.z.prevent="undo()"
     @keydown.window.ctrl.y.prevent="redo()"
     @keydown.window.ctrl.d.prevent="duplicateElement()"
     @keydown.window.delete="removeElement()"
     @keydown.window.escape="deselectAll()">
    
    <!-- Fuentes Web -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=EB+Garamond:ital,wght@0,400;0,600;1,400&family=Pinyon+Script&family=Inter:wght@300;400;500;600&family=Montserrat:wght@400;700&family=Playfair+Display:ital,wght@0,400;1,400&display=swap" rel="stylesheet">
    
    <style>
        .font-cinzel { font-family: 'Cinzel Decorative', cursive; }
        .font-garamond { font-family: 'EB Garamond', serif; }
        .font-pinyon { font-family: 'Pinyon Script', cursive; }
        
        /* UI Tweaks */
        [x-cloak] { display: none !important; }
        .checkerboard {
            background-image: linear-gradient(45deg, #e5e7eb 25%, transparent 25%), linear-gradient(-45deg, #e5e7eb 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #e5e7eb 75%), linear-gradient(-45deg, transparent 75%, #e5e7eb 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
        input[type=number]::-webkit-inner-spin-button { opacity: 1; }
        
        /* Cursores personalizados */
        .cursor-rotate { cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>') 10 10, auto; }
    </style>

    <!-- Barra Superior: Herramientas Globales -->
    <header class="bg-white border-b border-gray-200 h-14 flex items-center justify-between px-4 shadow-sm z-40 relative">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-gray-700">
                <div class="bg-indigo-600 p-1.5 rounded text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                </div>
                <input type="text" wire:model="name" class="border-none focus:ring-0 p-0 text-sm font-bold w-48 bg-transparent text-gray-800" placeholder="Nombre de la Plantilla">
            </div>
            
            <div class="h-6 w-px bg-gray-300 mx-2"></div>

            <!-- Información del Lienzo -->
            <div class="flex items-center gap-3 text-xs text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200">
                <span class="font-medium text-gray-700" x-text="canvasConfig.format"></span>
                <span class="text-gray-300">|</span>
                <span x-text="Math.round(canvasConfig.width) + ' x ' + Math.round(canvasConfig.height) + ' px'"></span>
            </div>

            <!-- Historial -->
            <div class="flex items-center gap-1 ml-2">
                <button @click="undo()" :disabled="historyStep <= 0" class="p-1.5 rounded hover:bg-gray-100 disabled:opacity-30 transition text-gray-600" title="Deshacer (Ctrl+Z)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </button>
                <button @click="redo()" :disabled="historyStep >= history.length - 1" class="p-1.5 rounded hover:bg-gray-100 disabled:opacity-30 transition text-gray-600" title="Rehacer (Ctrl+Y)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path></svg>
                </button>
            </div>

            <!-- Zoom -->
            <div class="flex items-center gap-2 bg-gray-100 rounded-md px-2 py-1 ml-2 border border-gray-200">
                <button @click="zoomOut" class="text-gray-500 hover:text-gray-800"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></button>
                <span class="text-xs font-mono w-10 text-center select-none" x-text="Math.round(zoom * 100) + '%'"></span>
                <button @click="zoomIn" class="text-gray-500 hover:text-gray-800"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></button>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 mr-2">
                <input type="checkbox" id="snapGrid" x-model="snapToGrid" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-4 w-4">
                <label for="snapGrid" class="text-xs text-gray-600 cursor-pointer select-none">Ajustar a cuadrícula</label>
            </div>

            <!-- Modo Preview -->
            <button @click="togglePreview" class="flex items-center gap-2 px-3 py-1.5 rounded text-xs font-medium border transition" :class="previewMode ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                <span x-text="previewMode ? 'Editar' : 'Vista Previa'"></span>
            </button>

            <button wire:click="save" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded text-sm font-medium shadow transition flex items-center gap-2">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        
        <!-- Panel Izquierdo: Herramientas y Capas -->
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col z-20 flex-shrink-0 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200">
                <button @click="activeTab = 'add'" :class="{'border-b-2 border-indigo-500 text-indigo-600 font-semibold': activeTab === 'add'}" class="flex-1 py-3 text-xs text-center text-gray-500 hover:text-gray-800 transition">Insertar</button>
                <button @click="activeTab = 'settings'" :class="{'border-b-2 border-indigo-500 text-indigo-600 font-semibold': activeTab === 'settings'}" class="flex-1 py-3 text-xs text-center text-gray-500 hover:text-gray-800 transition">Configuración</button>
                <button @click="activeTab = 'layers'" :class="{'border-b-2 border-indigo-500 text-indigo-600 font-semibold': activeTab === 'layers'}" class="flex-1 py-3 text-xs text-center text-gray-500 hover:text-gray-800 transition">Capas</button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 custom-scrollbar">
                
                <!-- Tab: Agregar -->
                <div x-show="activeTab === 'add'" class="space-y-6">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Elementos Básicos</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="addElement('text')" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition bg-white shadow-sm group">
                                <span class="text-2xl font-serif text-gray-600 group-hover:text-indigo-600 mb-1">T</span>
                                <span class="text-[10px] font-medium text-gray-500 group-hover:text-indigo-600">Texto Libre</span>
                            </button>
                            <button @click="addElement('variable')" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition bg-white shadow-sm group">
                                <span class="text-xl font-mono text-gray-600 group-hover:text-blue-600 mb-1">{ }</span>
                                <span class="text-[10px] font-medium text-gray-500 group-hover:text-blue-600">Dato Dinámico</span>
                            </button>
                            <button @click="addElement('qr')" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition bg-white shadow-sm col-span-2 group">
                                <svg class="w-6 h-6 text-gray-500 group-hover:text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                <span class="text-[10px] font-medium text-gray-500 group-hover:text-purple-600">Código QR de Validación</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Formas y Marcos</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="addElement('shape', {borderRadius: 0, borderWidth: 4, borderColor: '#b49b5a', fill: 'transparent'})" class="h-20 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center justify-center relative overflow-hidden group">
                                <div class="absolute inset-2 border-2 border-double border-yellow-600 opacity-60 group-hover:opacity-100"></div>
                                <span class="text-[9px] text-gray-400 mt-8">Marco Doble</span>
                            </button>
                            <button @click="addElement('shape', {borderRadius: 8, borderWidth: 2, borderColor: '#1f2937', fill: 'transparent'})" class="h-20 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center justify-center relative overflow-hidden group">
                                <div class="absolute inset-3 border-2 border-gray-700 rounded opacity-60 group-hover:opacity-100"></div>
                                <span class="text-[9px] text-gray-400 mt-8">Borde Simple</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tab: Configuración (Fondo y Tamaño) -->
                <div x-show="activeTab === 'settings'" class="space-y-6">
                    
                    <!-- Configuración del Lienzo -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tamaño del Documento</h3>
                        
                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Formato</label>
                            <select x-model="canvasConfig.format" @change="updateCanvasSize()" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="A4">A4 (210 x 297 mm)</option>
                                <option value="Letter">Carta (216 x 279 mm)</option>
                                <option value="Legal">Oficio (216 x 356 mm)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] text-gray-500 block mb-1">Orientación</label>
                            <div class="flex bg-gray-100 p-1 rounded-lg">
                                <button @click="canvasConfig.orientation = 'portrait'; updateCanvasSize()" class="flex-1 py-1.5 rounded-md text-xs transition flex items-center justify-center gap-1" :class="canvasConfig.orientation === 'portrait' ? 'bg-white shadow text-indigo-600 font-medium' : 'text-gray-500 hover:text-gray-700'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Vertical
                                </button>
                                <button @click="canvasConfig.orientation = 'landscape'; updateCanvasSize()" class="flex-1 py-1.5 rounded-md text-xs transition flex items-center justify-center gap-1" :class="canvasConfig.orientation === 'landscape' ? 'bg-white shadow text-indigo-600 font-medium' : 'text-gray-500 hover:text-gray-700'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.293.707V19a2 2 0 01-2 2z" transform="rotate(-90 12 12)"></path></svg>
                                    Horizontal
                                </button>
                            </div>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                            <div class="flex justify-between text-xs text-blue-800 mb-1">
                                <span>Ancho:</span>
                                <span class="font-mono" x-text="Math.round(canvasConfig.width) + ' px'"></span>
                            </div>
                            <div class="flex justify-between text-xs text-blue-800">
                                <span>Alto:</span>
                                <span class="font-mono" x-text="Math.round(canvasConfig.height) + ' px'"></span>
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100">

                    <!-- Fondo -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Imagen de Fondo</label>
                        <div class="relative group">
                            <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-white hover:border-indigo-400 transition overflow-hidden">
                                <template x-if="!$wire.currentBg && !$wire.bgImage">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-gray-400 group-hover:text-indigo-500">
                                        <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <p class="text-xs font-medium">Click para subir imagen</p>
                                    </div>
                                </template>
                                <!-- Preview si ya existe -->
                                @if($currentBg)
                                    <img src="{{ asset('storage/'.$currentBg) }}" class="absolute inset-0 w-full h-full object-cover opacity-80" />
                                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                                        <span class="text-white text-xs font-medium">Cambiar imagen</span>
                                    </div>
                                @endif
                                <input type="file" wire:model="bgImage" class="hidden" accept="image/*">
                            </label>
                        </div>
                        @if($currentBg)
                            <button @click="$wire.set('bgImage', null); $wire.set('currentBg', null)" class="text-xs text-red-500 hover:text-red-700 flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Eliminar fondo actual
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Tab: Capas -->
                <div x-show="activeTab === 'layers'" class="space-y-2">
                    <template x-for="(element, index) in [...elements].reverse()" :key="element.id || index">
                        <div class="flex items-center gap-2 p-2.5 rounded-lg border cursor-pointer transition text-sm group"
                             :class="selectedIds.includes(getElementRealIndex(element)) ? 'bg-indigo-50 border-indigo-200 text-indigo-700' : 'bg-white border-transparent hover:border-gray-200 hover:bg-gray-50 text-gray-600'"
                             @click="selectElement(getElementRealIndex(element), $event.ctrlKey)">
                            
                            <!-- Icono Tipo -->
                            <span class="text-gray-400">
                                <template x-if="element.type === 'text'"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg></template>
                                <template x-if="element.type === 'variable'"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></template>
                                <template x-if="element.type === 'qr'"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><path d="M3 14h7v7H3z"/></svg></template>
                                <template x-if="element.type === 'shape'"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg></template>
                            </span>

                            <span class="truncate flex-1 select-none font-medium" x-text="element.content || (element.type === 'shape' ? 'Forma' : element.type)"></span>

                            <!-- Acciones Capa -->
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="toggleLock(getElementRealIndex(element))" class="p-1 hover:bg-gray-200 rounded" :class="element.locked ? 'text-red-500 opacity-100' : 'text-gray-400'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="element.locked ? 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' : 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'"></path></svg>
                                </button>
                                <button @click.stop="toggleVisibility(getElementRealIndex(element))" class="p-1 hover:bg-gray-200 rounded" :class="element.hidden ? 'text-gray-300' : 'text-gray-400'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="element.hidden ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </aside>

        <!-- CANVAS AREA -->
        <main class="flex-1 bg-gray-200 overflow-hidden relative flex flex-col checkerboard" 
              @mousedown="if($event.target === $el) deselectAll()"
              @wheel.ctrl.prevent="handleWheelZoom">
            
            <div class="absolute top-4 right-4 z-50 pointer-events-none opacity-50 hover:opacity-100 transition-opacity">
                <div class="bg-black/75 text-white text-[10px] px-2 py-1 rounded backdrop-blur-sm">
                    Arrastra para mover • Shift+Arrastra para ajustar
                </div>
            </div>

            <!-- Contenedor Transformable (Viewport) -->
            <div class="w-full h-full flex items-center justify-center overflow-auto p-20" id="viewport">
                
                <!-- Lienzo Dinámico -->
                <div id="canvas" 
                     class="bg-white shadow-[0_0_50px_rgba(0,0,0,0.15)] relative transition-transform duration-75 ease-linear origin-center print:shadow-none"
                     :style="`
                        width: ${canvasConfig.width}px; 
                        height: ${canvasConfig.height}px; 
                        min-width: ${canvasConfig.width}px; 
                        min-height: ${canvasConfig.height}px; 
                        transform: scale(${zoom});
                        background-image: url('${ $wire.currentBg ? '{{ asset('storage') }}/' + $wire.currentBg : '' }');
                        background-size: cover;
                        background-position: center;
                     `">
                    
                    <!-- Guías de Seguridad (Toggleable) -->
                    <div x-show="showGuides" class="absolute top-[40px] bottom-[40px] left-[40px] right-[40px] border border-dashed border-red-300 pointer-events-none z-0 opacity-40"></div>

                    <!-- Elementos -->
                    <template x-for="(element, index) in elements" :key="element.id || index">
                        <div x-show="!element.hidden"
                             class="absolute group box-border select-none"
                             :class="{
                                'cursor-move': !element.locked, 
                                'cursor-not-allowed': element.locked,
                                'ring-1 ring-indigo-500 ring-offset-1': isSelected(index),
                                'hover:ring-1 hover:ring-indigo-300 hover:ring-offset-1': !isSelected(index) && !element.locked
                             }"
                             :style="getElementStyle(element)"
                             @mousedown.stop="startDrag($event, index)"
                             @click.stop="$wire.selectElement(index); selectedIndex = index;">
                            
                            <!-- Contenido del Elemento -->
                            <div class="w-full h-full overflow-hidden" style="pointer-events: none;">
                                <template x-if="element.type === 'text'">
                                    <div x-text="element.content" class="w-full h-full whitespace-pre-wrap break-words leading-tight"></div>
                                </template>
                                <template x-if="element.type === 'variable'">
                                    <div x-text="getPreviewValue(element.content)" class="w-full h-full flex items-center justify-center bg-blue-50/40 text-blue-800/80 border border-blue-300/30 border-dashed px-1 leading-tight"></div>
                                </template>
                                <template x-if="element.type === 'qr'">
                                    <div class="w-full h-full bg-white border border-gray-300 flex items-center justify-center">
                                        <svg class="w-full h-full p-1 text-gray-800" fill="currentColor" viewBox="0 0 24 24"><path d="M3 3h6v6H3V3zm2 2v2h2V5H5zm8-2h6v6h-6V3zm2 2v2h2V5h-2zM3 13h6v6H3v-6zm2 2v2h2v-2H5zm8 4h2v2h-2v-2zm-2 2h2v2h-2v-2zm4 0h2v2h-2v-2zm2-2h2v2h-2v-2z"/></svg>
                                    </div>
                                </template>
                                <template x-if="element.type === 'shape'">
                                    <div class="w-full h-full" :style="`border: ${element.borderWidth}px solid ${element.borderColor}; background-color: ${element.fill}; border-radius: ${element.borderRadius}px;`"></div>
                                </template>
                            </div>

                            <!-- Controles de Edición (Solo si seleccionado y no bloqueado) -->
                            <template x-if="isSelected(index) && !element.locked">
                                <div class="absolute inset-0 z-50 pointer-events-none">
                                    <!-- Resize Handles -->
                                    <div class="absolute -top-1.5 -left-1.5 w-3 h-3 bg-white border border-indigo-600 rounded-full pointer-events-auto cursor-nw-resize shadow-sm" @mousedown.stop="startResize($event, index, 'nw')"></div>
                                    <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-white border border-indigo-600 rounded-full pointer-events-auto cursor-ne-resize shadow-sm" @mousedown.stop="startResize($event, index, 'ne')"></div>
                                    <div class="absolute -bottom-1.5 -left-1.5 w-3 h-3 bg-white border border-indigo-600 rounded-full pointer-events-auto cursor-sw-resize shadow-sm" @mousedown.stop="startResize($event, index, 'sw')"></div>
                                    <div class="absolute -bottom-1.5 -right-1.5 w-3 h-3 bg-white border border-indigo-600 rounded-full pointer-events-auto cursor-se-resize shadow-sm" @mousedown.stop="startResize($event, index, 'se')"></div>
                                    
                                    <!-- Rotación -->
                                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 w-6 h-6 bg-white border border-indigo-600 rounded-full flex items-center justify-center pointer-events-auto cursor-rotate shadow-sm hover:bg-gray-50 transition" @mousedown.stop="startRotate($event, index)">
                                        <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </main>

        <!-- Panel Derecho: Propiedades -->
        <aside class="w-72 bg-white border-l border-gray-200 flex flex-col z-20 shadow-xl flex-shrink-0" x-cloak>
            <template x-if="selectedIds.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-gray-400 p-6 text-center">
                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                    <p class="text-sm font-medium text-gray-500">Selecciona un elemento</p>
                    <p class="text-xs mt-2 max-w-[150px]">Haz clic en cualquier elemento del lienzo para editar sus propiedades.</p>
                </div>
            </template>

            <template x-if="selectedIds.length > 0">
                <div class="flex flex-col h-full">
                    <!-- Header Propiedades -->
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Propiedades</span>
                        <div class="flex gap-1">
                            <button @click="toggleLock(selectedIds[0])" class="p-1 rounded hover:bg-gray-200" :title="activeElement.locked ? 'Desbloquear' : 'Bloquear'">
                                <svg class="w-4 h-4" :class="activeElement.locked ? 'text-red-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="activeElement.locked ? 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' : 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'"></path></svg>
                            </button>
                            <button @click="duplicateElement()" class="p-1 rounded hover:bg-gray-200 text-gray-400 hover:text-indigo-600" title="Duplicar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                            <button @click="removeElement()" class="p-1 rounded hover:bg-red-50 text-red-400 hover:text-red-600" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-5 space-y-6 custom-scrollbar">
                        
                        <!-- Sección de Contenido -->
                        <div class="space-y-3">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Contenido</label>
                            <template x-if="activeElement.type === 'text'">
                                <textarea x-model="activeElement.content" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50" placeholder="Escribe aquí..."></textarea>
                            </template>
                            <template x-if="activeElement.type === 'variable'">
                                <select x-model="activeElement.content" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 bg-blue-50">
                                    <template x-for="(label, key) in variables" :key="key">
                                        <option :value="key" x-text="label"></option>
                                    </template>
                                </select>
                            </template>
                            <template x-if="activeElement.type === 'shape'">
                                <div class="text-xs text-gray-500 italic">Elemento decorativo sin texto.</div>
                            </template>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Geometría -->
                        <div class="space-y-4">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block flex justify-between">
                                <span>Geometría</span>
                                <span class="text-[9px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-600" x-text="(activeElement.rotation || 0) + '°'"></span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-mono">X</span>
                                    <input type="number" x-model.number="activeElement.x" class="w-full pl-8 rounded-lg border-gray-300 text-xs bg-gray-50 focus:bg-white transition">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-mono">Y</span>
                                    <input type="number" x-model.number="activeElement.y" class="w-full pl-8 rounded-lg border-gray-300 text-xs bg-gray-50 focus:bg-white transition">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="relative">
                                    <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-mono">W</span>
                                    <input type="number" x-model.number="activeElement.width" class="w-full pl-8 rounded-lg border-gray-300 text-xs">
                                </div>
                                <template x-if="activeElement.height !== null">
                                    <div class="relative">
                                        <span class="absolute left-3 top-2.5 text-xs text-gray-400 font-mono">H</span>
                                        <input type="number" x-model.number="activeElement.height" class="w-full pl-8 rounded-lg border-gray-300 text-xs">
                                    </div>
                                </template>
                            </div>
                            
                            <div class="pt-2">
                                <input type="range" min="-180" max="180" x-model.number="activeElement.rotation" class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                            </div>
                        </div>

                        <!-- Estilo (Solo texto/variable) -->
                        <template x-if="['text', 'variable'].includes(activeElement.type)">
                            <div class="space-y-4 pt-2 border-t border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Tipografía</label>
                                
                                <div>
                                    <select x-model="activeElement.fontFamily" class="w-full rounded-lg border-gray-300 text-xs mb-2">
                                        <option value="EB Garamond">Garamond (Clásico)</option>
                                        <option value="Cinzel Decorative">Cinzel (Título)</option>
                                        <option value="Pinyon Script">Script (Manuscrito)</option>
                                        <option value="Montserrat">Montserrat (Moderno)</option>
                                        <option value="Playfair Display">Playfair (Elegante)</option>
                                        <option value="Inter">Inter (Limpio)</option>
                                    </select>
                                    
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <input type="number" x-model="activeElement.fontSize" class="w-full rounded-lg border-gray-300 text-xs pl-8">
                                            <span class="absolute left-2 top-2 text-xs text-gray-400">Pt</span>
                                        </div>
                                        <div class="relative w-10">
                                            <input type="color" x-model="activeElement.color" class="w-full h-[34px] p-0 border-gray-300 rounded cursor-pointer overflow-hidden">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <select x-model="activeElement.fontWeight" class="w-full text-xs border-gray-300 rounded-lg">
                                        <option value="300">Light</option>
                                        <option value="400">Regular</option>
                                        <option value="600">Semi-Bold</option>
                                        <option value="700">Bold</option>
                                        <option value="900">Black</option>
                                    </select>
                                    <select x-model="activeElement.textTransform" class="w-full text-xs border-gray-300 rounded-lg">
                                        <option value="none">Normal</option>
                                        <option value="uppercase">ABC</option>
                                        <option value="capitalize">Abc</option>
                                    </select>
                                </div>

                                <div>
                                    <div class="flex bg-gray-100 rounded-lg p-1">
                                        <button @click="activeElement.textAlign = 'left'" class="flex-1 py-1 rounded hover:bg-white hover:shadow-sm transition" :class="activeElement.textAlign === 'left' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400'">
                                            <svg class="w-3.5 h-3.5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h7"></path></svg>
                                        </button>
                                        <button @click="activeElement.textAlign = 'center'" class="flex-1 py-1 rounded hover:bg-white hover:shadow-sm transition" :class="activeElement.textAlign === 'center' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400'">
                                            <svg class="w-3.5 h-3.5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M7 18h10"></path></svg>
                                        </button>
                                        <button @click="activeElement.textAlign = 'right'" class="flex-1 py-1 rounded hover:bg-white hover:shadow-sm transition" :class="activeElement.textAlign === 'right' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400'">
                                            <svg class="w-3.5 h-3.5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M13 18h7"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Estilo (Solo Formas) -->
                        <template x-if="activeElement.type === 'shape'">
                            <div class="space-y-4 pt-2 border-t border-gray-100">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Estilo de Borde</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <span class="text-xs text-gray-400 mb-1 block">Grosor</span>
                                        <input type="number" x-model.number="activeElement.borderWidth" class="w-full text-xs border-gray-300 rounded-lg">
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-400 mb-1 block">Radio</span>
                                        <input type="number" x-model.number="activeElement.borderRadius" class="w-full text-xs border-gray-300 rounded-lg">
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 mb-1 block">Color Borde</span>
                                    <div class="flex items-center gap-2 border border-gray-300 rounded-lg p-1 bg-white">
                                        <input type="color" x-model="activeElement.borderColor" class="w-6 h-6 rounded cursor-pointer border-0 p-0 overflow-hidden">
                                        <span class="text-xs text-gray-500 uppercase flex-1" x-text="activeElement.borderColor"></span>
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>
                </div>
            </template>
        </aside>
    </div>

    <!-- JavaScript para Drag & Drop (Optimizado) -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('certificateEditor', (wireElements) => ({
                elements: wireElements || [],
                selectedIds: [],
                zoom: 1,
                activeTab: 'add',
                showGuides: true,
                previewMode: false,
                isDragging: false,
                snapToGrid: false, // Default false por "movimiento libre"
                
                // Configuración de Lienzo
                canvasConfig: {
                    format: 'A4',
                    orientation: 'landscape',
                    width: 1123, // 96dpi approx
                    height: 794
                },
                
                // Historial
                history: [],
                historyStep: -1,
                
                // Estado Temporal de Interacción
                dragStart: { x: 0, y: 0 },
                elementStart: { x: 0, y: 0, w: 0, h: 0, r: 0 },
                interactionType: null,
                resizeHandle: null,

                variables: {
                    '{student_name}': 'Juan Pérez García',
                    '{course_name}': 'Desarrollo Web Avanzado',
                    '{date}': '18/01/2026',
                    '{folio}': 'CERT-2026-001',
                    '{director_name}': 'Dr. Roberto Gómez',
                    '{institution_name}': 'SGA Academy'
                },

                get activeElement() {
                    if (this.selectedIds.length === 0) return {};
                    return this.elements[this.selectedIds[0]] || {};
                },

                init() {
                    this.saveHistory();
                    this.updateCanvasSize(); // Inicializar tamaño
                    this.$watch('elements', () => {}); // Trigger básico
                },

                // --- Canvas Management ---
                
                updateCanvasSize() {
                    const dpi = 96;
                    const mmToPx = (mm) => Math.round(mm * (dpi / 25.4));
                    
                    let w_mm, h_mm;
                    
                    if (this.canvasConfig.format === 'A4') { w_mm = 210; h_mm = 297; }
                    else if (this.canvasConfig.format === 'Letter') { w_mm = 215.9; h_mm = 279.4; }
                    else if (this.canvasConfig.format === 'Legal') { w_mm = 215.9; h_mm = 355.6; }
                    
                    if (this.canvasConfig.orientation === 'landscape') {
                        this.canvasConfig.width = mmToPx(h_mm);
                        this.canvasConfig.height = mmToPx(w_mm);
                    } else {
                        this.canvasConfig.width = mmToPx(w_mm);
                        this.canvasConfig.height = mmToPx(h_mm);
                    }
                },

                // --- Gestión de Elementos ---

                addElement(type, props = {}) {
                    // Centrar nuevo elemento en el viewport visible si es posible
                    const startX = Math.round(this.canvasConfig.width / 2) - 150;
                    const startY = Math.round(this.canvasConfig.height / 2) - 25;

                    const defaults = {
                        id: Date.now() + Math.random(),
                        type: type,
                        x: startX, y: startY, width: 300, height: type === 'text' ? null : 150,
                        content: type === 'text' ? 'Texto Nuevo' : (type === 'variable' ? '{student_name}' : ''),
                        fontFamily: 'Inter', fontSize: 24, fontWeight: '400', color: '#000000',
                        textAlign: 'left', rotation: 0, locked: false, hidden: false,
                        zIndex: this.elements.length + 1,
                        textTransform: 'none', letterSpacing: 0, lineHeight: 1.2,
                        ...props
                    };
                    
                    this.elements.push(defaults);
                    this.selectElement(this.elements.length - 1);
                    this.saveHistory();
                },

                removeElement(index = null) {
                    const idx = index !== null ? index : (this.selectedIds.length ? this.selectedIds[0] : null);
                    if (idx === null) return;
                    
                    this.elements.splice(idx, 1);
                    this.selectedIds = [];
                    this.saveHistory();
                },

                duplicateElement() {
                    if (this.selectedIds.length === 0) return;
                    const original = this.elements[this.selectedIds[0]];
                    const copy = JSON.parse(JSON.stringify(original));
                    copy.id = Date.now() + Math.random();
                    copy.x += 20;
                    copy.y += 20;
                    this.elements.push(copy);
                    this.selectElement(this.elements.length - 1);
                    this.saveHistory();
                },

                selectElement(index, multi = false) {
                    this.selectedIds = [index];
                },

                deselectAll() {
                    this.selectedIds = [];
                },

                // --- Capas ---

                moveLayer(index, direction) {
                    if (direction === 'up' && index < this.elements.length - 1) {
                        const temp = this.elements[index];
                        this.elements[index] = this.elements[index + 1];
                        this.elements[index + 1] = temp;
                        this.selectElement(index + 1);
                    } else if (direction === 'down' && index > 0) {
                        const temp = this.elements[index];
                        this.elements[index] = this.elements[index - 1];
                        this.elements[index - 1] = temp;
                        this.selectElement(index - 1);
                    }
                },

                toggleLock(index) {
                    this.elements[index].locked = !this.elements[index].locked;
                },

                toggleVisibility(index) {
                    this.elements[index].hidden = !this.elements[index].hidden;
                },

                // --- Interacciones (Drag, Resize, Rotate) ---

                startDrag(e, index) {
                    if (e.button !== 0 || this.elements[index].locked) return;
                    
                    this.selectElement(index);
                    this.isDragging = true;
                    this.interactionType = 'move';
                    
                    this.dragStart = { x: e.clientX, y: e.clientY };
                    this.elementStart = { ...this.elements[index] };

                    window.addEventListener('mousemove', this.handleInteraction);
                    window.addEventListener('mouseup', this.stopInteraction);
                },

                startResize(e, index, handle) {
                    e.stopPropagation();
                    this.isDragging = true;
                    this.interactionType = 'resize';
                    this.resizeHandle = handle;
                    
                    this.dragStart = { x: e.clientX, y: e.clientY };
                    this.elementStart = { ...this.elements[index] };

                    window.addEventListener('mousemove', this.handleInteraction);
                    window.addEventListener('mouseup', this.stopInteraction);
                },

                startRotate(e, index) {
                    e.stopPropagation();
                    this.isDragging = true;
                    this.interactionType = 'rotate';
                    
                    const el = this.elements[index];
                    this.elementStart = { ...el };
                    
                    // Guardamos la posición X inicial del ratón para calcular delta
                    this.dragStart = { x: e.clientX, y: e.clientY };
                    
                    window.addEventListener('mousemove', this.handleInteraction);
                    window.addEventListener('mouseup', this.stopInteraction);
                },

                handleMove(e) {
                    if (!this.isDragging) return;
                    const idx = this.selectedIds[0];
                    const el = this.elements[idx];
                    
                    // Delta ajustado por zoom
                    const deltaX = (e.clientX - this.dragStart.x) / this.zoom;
                    const deltaY = (e.clientY - this.dragStart.y) / this.zoom;

                    if (this.interactionType === 'move') {
                        let newX = this.elementStart.x + deltaX;
                        let newY = this.elementStart.y + deltaY;
                        
                        // Grid snapping condicional
                        if (this.snapToGrid || e.shiftKey) {
                            newX = Math.round(newX / 10) * 10;
                            newY = Math.round(newY / 10) * 10;
                        }
                        
                        el.x = Math.round(newX);
                        el.y = Math.round(newY);
                    } 
                    else if (this.interactionType === 'resize') {
                        const handle = this.resizeHandle;
                        
                        if (handle.includes('e')) el.width = Math.max(20, this.elementStart.width + deltaX);
                        if (handle.includes('s')) el.height = Math.max(20, this.elementStart.height + deltaY);
                        if (handle.includes('w')) {
                            const newW = Math.max(20, this.elementStart.width - deltaX);
                            el.x = this.elementStart.x + (this.elementStart.width - newW);
                            el.width = newW;
                        }
                        if (handle.includes('n')) {
                            const newH = Math.max(20, this.elementStart.height - deltaY);
                            el.y = this.elementStart.y + (this.elementStart.height - newH);
                            el.height = newH;
                        }
                    }
                    else if (this.interactionType === 'rotate') {
                        // Rotación intuitiva basada en movimiento X
                        const sensitivity = 0.5;
                        let newRot = this.elementStart.rotation + (deltaX * sensitivity);
                        
                        // Snap a ángulos comunes si Shift presionado
                        if (e.shiftKey) newRot = Math.round(newRot / 15) * 15;
                        
                        el.rotation = Math.round(newRot % 360);
                    }
                },

                stopInteraction() {
                    this.isDragging = false;
                    this.interactionType = null;
                    window.removeEventListener('mousemove', this.handleInteraction);
                    window.removeEventListener('mouseup', this.stopInteraction);
                    this.saveHistory();
                },

                // --- Utilidades ---

                getElementStyle(el) {
                    return `
                        left: ${el.x}px; 
                        top: ${el.y}px; 
                        width: ${el.width}px; 
                        ${el.height ? `height: ${el.height}px;` : ''}
                        transform: rotate(${el.rotation || 0}deg);
                        font-family: '${el.fontFamily}';
                        font-size: ${el.fontSize}px;
                        font-weight: ${el.fontWeight};
                        color: ${el.color};
                        text-align: ${el.textAlign};
                        text-transform: ${el.textTransform || 'none'};
                        letter-spacing: ${el.letterSpacing || 0}px;
                        line-height: ${el.lineHeight || 1.2};
                        z-index: ${el.zIndex};
                    `;
                },

                getElementRealIndex(elementObj) {
                    return this.elements.indexOf(elementObj);
                },

                togglePreview() {
                    this.previewMode = !this.previewMode;
                    this.deselectAll();
                },

                getPreviewValue(content) {
                    if (!this.previewMode) return content;
                    let text = content;
                    Object.entries(this.variables).forEach(([key, val]) => {
                        text = text.replace(new RegExp(key, 'g'), val);
                    });
                    return text;
                },

                // --- Zoom ---
                zoomIn() { if(this.zoom < 2) this.zoom += 0.1; },
                zoomOut() { if(this.zoom > 0.3) this.zoom -= 0.1; },
                handleWheelZoom(e) {
                    if(e.deltaY < 0) this.zoomIn(); else this.zoomOut();
                },

                // --- Historial ---
                saveHistory() {
                    if (this.historyStep < this.history.length - 1) {
                        this.history = this.history.slice(0, this.historyStep + 1);
                    }
                    this.history.push(JSON.parse(JSON.stringify(this.elements)));
                    this.historyStep++;
                    if (this.history.length > 20) {
                        this.history.shift();
                        this.historyStep--;
                    }
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

        document.addEventListener('alpine:initialized', () => {
            const component = document.querySelector('[x-data]')._x_dataStack[0];
            component.handleInteraction = component.handleMove.bind(component);
            component.stopInteraction = component.stopInteraction.bind(component);
        });
    </script>
</div>