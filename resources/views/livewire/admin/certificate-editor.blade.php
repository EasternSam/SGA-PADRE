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
        
        /* Cursores personalizados para rotación */
        .cursor-rotate { cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>') 10 10, auto; }
    </style>

    <!-- Barra Superior: Herramientas Globales -->
    <header class="bg-white border-b border-gray-200 h-14 flex items-center justify-between px-4 shadow-sm z-40 relative">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 text-gray-700">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                <input type="text" wire:model="name" class="border-none focus:ring-0 p-0 text-sm font-bold w-48 bg-transparent" placeholder="Nombre de la Plantilla">
            </div>
            
            <div class="h-6 w-px bg-gray-300 mx-2"></div>

            <!-- Historial -->
            <div class="flex items-center gap-1">
                <button @click="undo()" :disabled="historyStep <= 0" class="p-1.5 rounded hover:bg-gray-100 disabled:opacity-30 transition" title="Deshacer (Ctrl+Z)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                </button>
                <button @click="redo()" :disabled="historyStep >= history.length - 1" class="p-1.5 rounded hover:bg-gray-100 disabled:opacity-30 transition" title="Rehacer (Ctrl+Y)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"></path></svg>
                </button>
            </div>

            <!-- Zoom -->
            <div class="flex items-center gap-2 bg-gray-100 rounded-md px-2 py-1 ml-2">
                <button @click="zoomOut" class="text-gray-500 hover:text-gray-800"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg></button>
                <span class="text-xs font-mono w-10 text-center" x-text="Math.round(zoom * 100) + '%'"></span>
                <button @click="zoomIn" class="text-gray-500 hover:text-gray-800"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg></button>
            </div>
        </div>

        <div class="flex items-center gap-3">
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
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col z-20 flex-shrink-0">
            <!-- Tabs -->
            <div class="flex border-b border-gray-200">
                <button @click="activeTab = 'add'" :class="{'border-b-2 border-indigo-500 text-indigo-600': activeTab === 'add'}" class="flex-1 py-3 text-xs font-semibold text-center text-gray-500 hover:text-gray-800 transition">Agregar</button>
                <button @click="activeTab = 'layers'" :class="{'border-b-2 border-indigo-500 text-indigo-600': activeTab === 'layers'}" class="flex-1 py-3 text-xs font-semibold text-center text-gray-500 hover:text-gray-800 transition">Capas</button>
                <button @click="activeTab = 'settings'" :class="{'border-b-2 border-indigo-500 text-indigo-600': activeTab === 'settings'}" class="flex-1 py-3 text-xs font-semibold text-center text-gray-500 hover:text-gray-800 transition">Fondo</button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                
                <!-- Tab: Agregar -->
                <div x-show="activeTab === 'add'" class="space-y-6">
                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Básicos</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="addElement('text')" class="flex flex-col items-center justify-center p-3 border border-gray-200 rounded hover:border-indigo-500 hover:bg-indigo-50 transition bg-white">
                                <span class="text-xl font-serif">T</span>
                                <span class="text-[10px] mt-1">Texto</span>
                            </button>
                            <button @click="addElement('variable')" class="flex flex-col items-center justify-center p-3 border border-gray-200 rounded hover:border-blue-500 hover:bg-blue-50 transition bg-white">
                                <span class="text-xl font-mono">{}</span>
                                <span class="text-[10px] mt-1">Variable</span>
                            </button>
                            <button @click="addElement('qr')" class="flex flex-col items-center justify-center p-3 border border-gray-200 rounded hover:border-purple-500 hover:bg-purple-50 transition bg-white col-span-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                <span class="text-[10px] mt-1">Código QR</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Marcos Decorativos</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="addElement('shape', {borderRadius: 0, borderWidth: 5, borderColor: '#b49b5a', fill: 'transparent'})" class="h-16 border-2 border-double border-yellow-600 bg-white hover:bg-gray-50"></button>
                            <button @click="addElement('shape', {borderRadius: 10, borderWidth: 2, borderColor: '#1f2937', fill: 'transparent'})" class="h-16 border-2 border-gray-800 rounded-lg bg-white hover:bg-gray-50"></button>
                        </div>
                    </div>
                </div>

                <!-- Tab: Capas -->
                <div x-show="activeTab === 'layers'" class="space-y-2">
                    <template x-for="(element, index) in [...elements].reverse()" :key="element.id || index">
                        <div class="flex items-center gap-2 p-2 rounded text-sm group"
                             :class="selectedIds.includes(getElementRealIndex(element)) ? 'bg-indigo-50 text-indigo-700' : 'hover:bg-gray-50 text-gray-600'"
                             @click="selectElement(getElementRealIndex(element), $event.ctrlKey)">
                            
                            <!-- Icono Tipo -->
                            <span class="text-gray-400">
                                <template x-if="element.type === 'text'"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3M9 20h6M12 4v16"/></svg></template>
                                <template x-if="element.type === 'variable'"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></template>
                                <template x-if="element.type === 'qr'"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><path d="M3 14h7v7H3z"/></svg></template>
                            </span>

                            <span class="truncate flex-1 select-none" x-text="element.content || element.type"></span>

                            <!-- Acciones Capa -->
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="toggleLock(getElementRealIndex(element))" class="p-1 hover:text-black" :class="element.locked ? 'text-red-500 opacity-100' : 'text-gray-400'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="element.locked ? 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' : 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'"></path></svg>
                                </button>
                                <button @click.stop="toggleVisibility(getElementRealIndex(element))" class="p-1 hover:text-black" :class="element.hidden ? 'text-gray-300' : 'text-gray-400'">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="element.hidden ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'"></path></svg>
                                </button>
                                <button @click.stop="moveLayer(getElementRealIndex(element), 'up')" class="p-1 text-gray-400 hover:text-indigo-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                </button>
                                <button @click.stop="moveLayer(getElementRealIndex(element), 'down')" class="p-1 text-gray-400 hover:text-indigo-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Tab: Configuración -->
                <div x-show="activeTab === 'settings'" class="space-y-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fondo del Diploma</label>
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="text-xs text-gray-500">Subir imagen</p>
                        </div>
                        <input type="file" wire:model="bgImage" class="hidden" accept="image/*">
                    </label>
                    <button @click="$wire.set('bgImage', null); $wire.set('currentBg', null)" class="text-xs text-red-500 hover:underline">Eliminar fondo</button>
                </div>
            </div>
        </aside>

        <!-- CANVAS AREA -->
        <main class="flex-1 bg-gray-200 overflow-hidden relative flex flex-col checkerboard" 
              @mousedown="if($event.target === $el) deselectAll()"
              @wheel.ctrl.prevent="handleWheelZoom">
            
            <div class="absolute top-2 right-2 text-xs text-gray-400 bg-white/80 p-1 rounded z-50 pointer-events-none">
                Ctrl+Scroll: Zoom | Espacio+Drag: Pan (Próx)
            </div>

            <!-- Contenedor Transformable -->
            <div class="w-full h-full flex items-center justify-center overflow-auto p-20" id="viewport">
                <!-- Lienzo A4 (297mm x 210mm ~ 1123px x 794px @ 96dpi) -->
                <div id="canvas" 
                     class="bg-white shadow-2xl relative transition-transform duration-75 ease-linear origin-center print:shadow-none"
                     :style="`
                        width: 1123px; 
                        height: 794px; 
                        transform: scale(${zoom});
                        background-image: url('${ $wire.currentBg ? '{{ asset('storage') }}/' + $wire.currentBg : '' }');
                        background-size: cover;
                        background-position: center;
                     `">
                    
                    <!-- Guías de Seguridad (Toggleable) -->
                    <div x-show="showGuides" class="absolute top-[40px] bottom-[40px] left-[40px] right-[40px] border border-dashed border-red-300 pointer-events-none z-0 opacity-50"></div>

                    <!-- Elementos -->
                    <template x-for="(element, index) in elements" :key="element.id || index">
                        <div x-show="!element.hidden"
                             class="absolute group box-border select-none"
                             :class="{
                                'cursor-move': !element.locked, 
                                'cursor-not-allowed': element.locked,
                                'ring-1 ring-blue-500 ring-offset-1': isSelected(index),
                                'hover:ring-1 hover:ring-blue-300 hover:ring-offset-1': !isSelected(index) && !element.locked
                             }"
                             :style="getElementStyle(element)"
                             @mousedown.stop="startDrag($event, index)">
                            
                            <!-- Contenido del Elemento -->
                            <div class="w-full h-full overflow-hidden" style="pointer-events: none;">
                                <template x-if="element.type === 'text'">
                                    <div x-text="element.content" class="w-full h-full whitespace-pre-wrap break-words"></div>
                                </template>
                                <template x-if="element.type === 'variable'">
                                    <div x-text="getPreviewValue(element.content)" class="w-full h-full flex items-center justify-center bg-blue-50/30 text-blue-800/80 border border-blue-200/50 border-dashed px-1"></div>
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
                                    <div class="absolute -top-1.5 -left-1.5 w-3 h-3 bg-white border border-blue-600 rounded-full pointer-events-auto cursor-nw-resize" @mousedown.stop="startResize($event, index, 'nw')"></div>
                                    <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-white border border-blue-600 rounded-full pointer-events-auto cursor-ne-resize" @mousedown.stop="startResize($event, index, 'ne')"></div>
                                    <div class="absolute -bottom-1.5 -left-1.5 w-3 h-3 bg-white border border-blue-600 rounded-full pointer-events-auto cursor-sw-resize" @mousedown.stop="startResize($event, index, 'sw')"></div>
                                    <div class="absolute -bottom-1.5 -right-1.5 w-3 h-3 bg-white border border-blue-600 rounded-full pointer-events-auto cursor-se-resize" @mousedown.stop="startResize($event, index, 'se')"></div>
                                    
                                    <!-- Rotación -->
                                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 w-6 h-6 bg-white border border-blue-600 rounded-full flex items-center justify-center pointer-events-auto cursor-rotate shadow-sm" @mousedown.stop="startRotate($event, index)">
                                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
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
                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <p class="text-sm font-medium">Selecciona un elemento</p>
                    <div class="mt-6 w-full border-t pt-4 text-left">
                        <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                            <input type="checkbox" x-model="showGuides" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            Mostrar Guías de Seguridad
                        </label>
                    </div>
                </div>
            </template>

            <template x-if="selectedIds.length > 0">
                <div class="flex flex-col h-full">
                    <!-- Header Propiedades -->
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <span class="text-xs font-bold text-gray-500 uppercase">Propiedades</span>
                        <div class="flex gap-1">
                            <button @click="toggleLock(selectedIds[0])" class="p-1 rounded hover:bg-gray-200" :title="activeElement.locked ? 'Desbloquear' : 'Bloquear'">
                                <svg class="w-4 h-4" :class="activeElement.locked ? 'text-red-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="activeElement.locked ? 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' : 'M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z'"></path></svg>
                            </button>
                            <button @click="duplicateElement()" class="p-1 rounded hover:bg-gray-200 text-gray-400" title="Duplicar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                            <button @click="removeElement()" class="p-1 rounded hover:bg-red-50 text-red-400 hover:text-red-600" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-5 custom-scrollbar">
                        
                        <!-- Alineación -->
                        <div class="grid grid-cols-3 gap-1 p-1 bg-gray-100 rounded-lg">
                            <button @click="align('left')" class="p-1.5 rounded hover:bg-white hover:shadow text-gray-500" title="Izquierda"><svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h7"></path></svg></button>
                            <button @click="align('center')" class="p-1.5 rounded hover:bg-white hover:shadow text-gray-500" title="Centro"><svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M7 18h10"></path></svg></button>
                            <button @click="align('right')" class="p-1.5 rounded hover:bg-white hover:shadow text-gray-500" title="Derecha"><svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M13 18h7"></path></svg></button>
                        </div>

                        <!-- Contenido -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Texto / Valor</label>
                            <template x-if="activeElement.type === 'text'">
                                <textarea x-model="activeElement.content" rows="3" class="w-full text-sm border-gray-300 rounded focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </template>
                            <template x-if="activeElement.type === 'variable'">
                                <select x-model="activeElement.content" class="w-full text-sm border-gray-300 rounded focus:border-indigo-500 focus:ring-indigo-500">
                                    <template x-for="(label, key) in variables" :key="key">
                                        <option :value="key" x-text="label"></option>
                                    </template>
                                </select>
                            </template>
                        </div>

                        <!-- Estilo (Solo texto/variable) -->
                        <template x-if="['text', 'variable'].includes(activeElement.type)">
                            <div class="space-y-3">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Tipografía</label>
                                    <select x-model="activeElement.fontFamily" class="w-full text-sm border-gray-300 rounded mb-2">
                                        <option value="EB Garamond">Garamond (Clásico)</option>
                                        <option value="Cinzel Decorative">Cinzel (Ornamental)</option>
                                        <option value="Pinyon Script">Pinyon Script (Cursiva)</option>
                                        <option value="Montserrat">Montserrat (Moderno)</option>
                                        <option value="Playfair Display">Playfair (Elegante)</option>
                                        <option value="Inter">Inter (Limpio)</option>
                                    </select>
                                    
                                    <div class="flex gap-2">
                                        <div class="relative flex-1">
                                            <input type="number" x-model="activeElement.fontSize" class="w-full text-sm border-gray-300 rounded pl-8">
                                            <span class="absolute left-2 top-2 text-xs text-gray-400">Px</span>
                                        </div>
                                        <div class="relative w-12">
                                            <input type="color" x-model="activeElement.color" class="w-full h-[38px] p-0 border-gray-300 rounded cursor-pointer">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[10px] text-gray-400">Peso</label>
                                        <select x-model="activeElement.fontWeight" class="w-full text-xs border-gray-300 rounded">
                                            <option value="300">Light</option>
                                            <option value="400">Regular</option>
                                            <option value="600">Semi-Bold</option>
                                            <option value="700">Bold</option>
                                            <option value="900">Black</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-400">Transform</label>
                                        <select x-model="activeElement.textTransform" class="w-full text-xs border-gray-300 rounded">
                                            <option value="none">Normal</option>
                                            <option value="uppercase">MAYÚSCULAS</option>
                                            <option value="capitalize">Capitalizar</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="text-[10px] text-gray-400">Espaciado</label>
                                        <input type="number" step="0.5" x-model="activeElement.letterSpacing" class="w-full text-xs border-gray-300 rounded">
                                    </div>
                                    <div>
                                        <label class="text-[10px] text-gray-400">Interlineado</label>
                                        <input type="number" step="0.1" x-model="activeElement.lineHeight" class="w-full text-xs border-gray-300 rounded">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Geometría -->
                        <div>
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-2 block flex justify-between">
                                <span>Dimensiones</span>
                                <span class="text-[9px] bg-gray-100 px-1 rounded" x-text="'Rot: ' + (activeElement.rotation || 0) + '°'"></span>
                            </label>
                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <div class="relative">
                                    <span class="absolute left-2 top-2 text-xs text-gray-400">X</span>
                                    <input type="number" x-model.number="activeElement.x" class="w-full text-xs border-gray-300 rounded pl-5">
                                </div>
                                <div class="relative">
                                    <span class="absolute left-2 top-2 text-xs text-gray-400">Y</span>
                                    <input type="number" x-model.number="activeElement.y" class="w-full text-xs border-gray-300 rounded pl-5">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="relative">
                                    <span class="absolute left-2 top-2 text-xs text-gray-400">W</span>
                                    <input type="number" x-model.number="activeElement.width" class="w-full text-xs border-gray-300 rounded pl-6">
                                </div>
                                <template x-if="activeElement.height">
                                    <div class="relative">
                                        <span class="absolute left-2 top-2 text-xs text-gray-400">H</span>
                                        <input type="number" x-model.number="activeElement.height" class="w-full text-xs border-gray-300 rounded pl-6">
                                    </div>
                                </template>
                            </div>
                            
                            <div class="mt-2 flex items-center gap-2">
                                <span class="text-xs text-gray-400">Rotación</span>
                                <input type="range" min="-180" max="180" x-model.number="activeElement.rotation" class="flex-1 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                            </div>
                        </div>

                    </div>
                </div>
            </template>
        </aside>
    </div>

    <!-- Alpine Logic -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('certificateEditor', (initialElements) => ({
                elements: initialElements || [],
                selectedIds: [], // Ahora soporta multi-selección futura
                zoom: 1,
                activeTab: 'add',
                showGuides: true,
                previewMode: false,
                isDragging: false,
                
                // Historial
                history: [],
                historyStep: -1,
                
                // Estado Temporal de Interacción
                dragStart: { x: 0, y: 0 },
                elementStart: { x: 0, y: 0, w: 0, h: 0, r: 0 },
                interactionType: null, // 'move', 'resize', 'rotate'
                resizeHandle: null,

                variables: {
                    '{student_name}': 'Nombre Estudiante',
                    '{course_name}': 'Curso Avanzado Laravel',
                    '{date}': '18/01/2026',
                    '{folio}': 'CERT-2026-001',
                    '{director_name}': 'Dr. Juan Pérez',
                    '{institution_name}': 'SGA Academy'
                },

                get activeElement() {
                    if (this.selectedIds.length === 0) return {};
                    return this.elements[this.selectedIds[0]] || {};
                },

                init() {
                    // Inicializar historial
                    this.saveHistory();
                    
                    // Watchers para guardar historial en cambios significativos
                    // (Implementación básica debounceada)
                    this.$watch('elements', (val) => {
                        // Aquí podríamos implementar autoguardado local o debounce de historial
                    });
                },

                // --- Gestión de Elementos ---

                addElement(type, props = {}) {
                    const defaults = {
                        id: Date.now(), // ID único simple
                        type: type,
                        x: 100, y: 100, width: 300, height: type === 'text' ? null : 150,
                        content: type === 'text' ? 'Nuevo Texto' : (type === 'variable' ? '{student_name}' : ''),
                        fontFamily: 'Inter', fontSize: 24, fontWeight: '400', color: '#000000',
                        textAlign: 'left', rotation: 0, locked: false, hidden: false,
                        zIndex: this.elements.length + 1,
                        // Propiedades extra
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
                    copy.id = Date.now();
                    copy.x += 20;
                    copy.y += 20;
                    this.elements.push(copy);
                    this.selectElement(this.elements.length - 1);
                    this.saveHistory();
                },

                selectElement(index, multi = false) {
                    if (multi) {
                        // Lógica futura multi-selección
                    } else {
                        this.selectedIds = [index];
                    }
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
                        this.selectElement(index + 1); // Seguir selección
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
                    if (this.elements[index].locked) return;
                    
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
                    
                    // Centro del elemento para calcular ángulo
                    const el = this.elements[index];
                    // Nota: Esto es aproximado ya que el DOM element puede estar transformado por CSS zoom
                    // Para precisión total necesitaríamos getBoundingClientRect del elemento en el DOM
                    // Simplificamos usando coordenadas relativas al evento inicial
                    
                    this.elementStart = { ...el };
                    
                    window.addEventListener('mousemove', this.handleInteraction);
                    window.addEventListener('mouseup', this.stopInteraction);
                },

                handleInteraction: (e) => {
                    // Usamos función flecha vinculada en init o bind manual
                },

                // Implementación vinculada
                handleMove(e) {
                    if (!this.isDragging) return;
                    const idx = this.selectedIds[0];
                    const el = this.elements[idx];
                    const deltaX = (e.clientX - this.dragStart.x) / this.zoom;
                    const deltaY = (e.clientY - this.dragStart.y) / this.zoom;

                    if (this.interactionType === 'move') {
                        el.x = Math.round(this.elementStart.x + deltaX);
                        el.y = Math.round(this.elementStart.y + deltaY);
                        // Snap simple (opcional)
                        if (e.shiftKey) {
                            el.x = Math.round(el.x / 10) * 10;
                            el.y = Math.round(el.y / 10) * 10;
                        }
                    } 
                    else if (this.interactionType === 'resize') {
                        // Lógica básica de redimensionado (sin rotación compleja)
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
                        // Rotación simple basada en desplazamiento X (UX estilo Figma simple)
                        // O calcular arco tangente para precisión
                        const sensitivity = 0.5;
                        let newRot = this.elementStart.rotation + (deltaX * sensitivity);
                        if (e.shiftKey) newRot = Math.round(newRot / 15) * 15; // Snap 15 grados
                        el.rotation = Math.round(newRot);
                    }
                },

                stopInteraction() {
                    this.isDragging = false;
                    this.interactionType = null;
                    window.removeEventListener('mousemove', this.handleInteraction);
                    window.removeEventListener('mouseup', this.stopInteraction);
                    this.saveHistory(); // Guardar estado al soltar
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
                    // Buscar índice real en el array original (necesario cuando iteramos reversa)
                    return this.elements.indexOf(elementObj);
                },

                togglePreview() {
                    this.previewMode = !this.previewMode;
                    this.deselectAll();
                },

                getPreviewValue(content) {
                    if (!this.previewMode) return content;
                    // Reemplazar todas las claves
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
                    // Cortar futuro si estamos en el medio
                    if (this.historyStep < this.history.length - 1) {
                        this.history = this.history.slice(0, this.historyStep + 1);
                    }
                    // Guardar copia
                    this.history.push(JSON.parse(JSON.stringify(this.elements)));
                    this.historyStep++;
                    
                    // Limite opcional
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
                },

                align(type) {
                    if (this.selectedIds.length > 0) {
                        this.elements[this.selectedIds[0]].textAlign = type;
                        this.saveHistory();
                    }
                }
            }));
        });

        // Vinculación de eventos
        document.addEventListener('alpine:initialized', () => {
            const component = document.querySelector('[x-data]')._x_dataStack[0];
            component.handleInteraction = component.handleMove.bind(component);
            component.stopInteraction = component.stopInteraction.bind(component);
        });
    </script>
</div>