<div class="flex flex-col h-[calc(100vh-65px)] bg-gray-50 overflow-hidden font-sans" 
     x-data="certificateEditor(@entangle('elements'))">
    
    <!-- Fuentes Web -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=EB+Garamond:ital,wght@0,400;0,600;1,400&family=Pinyon+Script&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        .font-cinzel { font-family: 'Cinzel Decorative', cursive; }
        .font-garamond { font-family: 'EB Garamond', serif; }
        .font-pinyon { font-family: 'Pinyon Script', cursive; }
        
        /* Scrollbars personalizados */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { bg-gray-100; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Patrón de fondo del canvas */
        .canvas-bg {
            background-color: #e2e8f0;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>

    <!-- Barra Superior de Herramientas -->
    <header class="bg-white border-b border-gray-200 px-6 py-3 flex justify-between items-center shadow-sm z-30 flex-shrink-0">
        <div class="flex items-center gap-4">
            <div class="bg-indigo-600 p-2 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div>
                <h1 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Editor de Plantillas</h1>
                <input type="text" wire:model="name" class="border-none p-0 text-lg font-semibold text-gray-700 focus:ring-0 bg-transparent placeholder-gray-400 w-64" placeholder="Nombre del Diploma...">
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400 mr-2" x-show="isDragging">Editando...</span>
            <button wire:click="save" wire:loading.attr="disabled" class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-full text-sm font-medium transition shadow-md shadow-indigo-200">
                <span wire:loading.remove wire:target="save">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                </span>
                <span wire:loading wire:target="save" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                Guardar Cambios
            </button>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden">
        
        <!-- Barra de Herramientas Izquierda (Componentes) -->
        <aside class="w-72 bg-white border-r border-gray-200 flex flex-col z-20 shadow-xl flex-shrink-0">
            <div class="p-5 border-b border-gray-100">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Elementos</h2>
                <div class="grid grid-cols-2 gap-3">
                    <button wire:click="addElement('text')" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-indigo-500 hover:bg-indigo-50 transition group bg-white shadow-sm">
                        <svg class="w-6 h-6 text-gray-500 group-hover:text-indigo-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                        <span class="text-xs font-medium text-gray-600 group-hover:text-indigo-700">Texto</span>
                    </button>
                    <button wire:click="addElement('variable')" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-blue-500 hover:bg-blue-50 transition group bg-white shadow-sm">
                        <svg class="w-6 h-6 text-gray-500 group-hover:text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        <span class="text-xs font-medium text-gray-600 group-hover:text-blue-700">Variable</span>
                    </button>
                    <button wire:click="addElement('qr')" class="flex flex-col items-center justify-center p-4 border border-gray-200 rounded-xl hover:border-purple-500 hover:bg-purple-50 transition group bg-white shadow-sm col-span-2">
                        <svg class="w-6 h-6 text-gray-500 group-hover:text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        <span class="text-xs font-medium text-gray-600 group-hover:text-purple-700">Código QR</span>
                    </button>
                </div>
            </div>

            <!-- Capas / Lista de elementos -->
            <div class="flex-1 overflow-y-auto p-4">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Capas</h2>
                <div class="space-y-2">
                    @foreach(array_reverse($elements, true) as $index => $element)
                        <div wire:click="selectElement({{ $index }})" 
                             class="flex items-center p-2 rounded-lg cursor-pointer transition text-sm {{ $selectedElementIndex === $index ? 'bg-indigo-50 border border-indigo-200 text-indigo-700' : 'hover:bg-gray-50 border border-transparent text-gray-600' }}">
                            
                            <span class="mr-3 text-gray-400">
                                @if($element['type'] === 'text') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                @elseif($element['type'] === 'variable') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                                @else <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4z"></path></svg>
                                @endif
                            </span>
                            
                            <span class="truncate flex-1">
                                {{ Str::limit($element['content'] ?? $element['type'], 20) }}
                            </span>

                            @if($selectedElementIndex === $index)
                                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>

        <!-- Área Central (Lienzo) -->
        <main class="flex-1 canvas-bg relative overflow-auto flex items-center justify-center p-12">
            
            <!-- Lienzo A4 Landscape -->
            <!-- 297mm x 210mm ~ 1123px x 794px @ 96dpi -->
            <div id="canvas-area"
                 class="bg-white shadow-2xl relative transition-all duration-75 ease-out select-none ring-1 ring-black/5"
                 style="width: 1123px; height: 794px; min-width: 1123px; min-height: 794px; 
                        background-image: url('{{ $currentBg ? asset('storage/'.$currentBg) : "data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.05'/%3E%3C/svg%3E" }}');
                        background-size: cover; background-position: center;">
                
                <!-- Guías de margen (Visual Only) -->
                <div class="absolute top-[40px] bottom-[40px] left-[40px] right-[40px] border border-dashed border-indigo-200 pointer-events-none opacity-0 hover:opacity-100 transition-opacity z-50"></div>

                <!-- Elementos -->
                <template x-for="(element, index) in elements" :key="index">
                    <div class="absolute group flex items-center"
                         :class="{'cursor-move': true}"
                         :style="`
                            left: ${element.x}px; 
                            top: ${element.y}px; 
                            width: ${element.width}px;
                            height: ${element.height ? element.height + 'px' : 'auto'};
                            font-family: '${element.fontFamily}';
                            font-size: ${element.fontSize}px;
                            font-weight: ${element.fontWeight};
                            color: ${element.color};
                            text-align: ${element.textAlign};
                            z-index: ${element.zIndex || 10};
                            transform: translate(0, 0); /* Fix for rendering */
                         `"
                         @mousedown.stop="startDrag($event, index)"
                         @click.stop="$wire.selectElement(index); selectedIndex = index;">
                        
                        <!-- Caja de selección activa -->
                        <div x-show="selectedIndex === index" 
                             class="absolute -inset-1 border-2 border-indigo-500 rounded-sm pointer-events-none z-50 shadow-sm">
                             <!-- Puntos de anclaje visuales -->
                             <div class="absolute -top-1.5 -left-1.5 w-3 h-3 bg-white border border-indigo-500 rounded-full"></div>
                             <div class="absolute -top-1.5 -right-1.5 w-3 h-3 bg-white border border-indigo-500 rounded-full"></div>
                             <div class="absolute -bottom-1.5 -left-1.5 w-3 h-3 bg-white border border-indigo-500 rounded-full"></div>
                             <div class="absolute -bottom-1.5 -right-1.5 w-3 h-3 bg-white border border-indigo-500 rounded-full"></div>
                        </div>

                        <!-- Contenido -->
                        <template x-if="element.type === 'qr'">
                            <div class="bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center w-full aspect-square text-gray-400">
                                <svg class="w-1/2 h-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4h2v-4zM5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            </div>
                        </template>
                        
                        <template x-if="element.type === 'variable'">
                            <div class="w-full bg-blue-50/50 border border-blue-200/50 px-1" x-text="element.content"></div>
                        </template>

                        <template x-if="element.type === 'text'">
                            <div class="w-full whitespace-pre-wrap" x-text="element.content"></div>
                        </template>
                        
                    </div>
                </template>
            </div>
        </main>

        <!-- Panel Derecho (Propiedades) -->
        <aside class="w-80 bg-white border-l border-gray-200 flex flex-col z-20 shadow-xl flex-shrink-0" x-cloak>
            @if(!is_null($selectedElementIndex) && isset($elements[$selectedElementIndex]))
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-sm font-bold text-gray-800">Propiedades</h3>
                    <button wire:click="removeElement({{ $selectedElementIndex }})" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-1 rounded transition" title="Eliminar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-5 space-y-6">
                    
                    <!-- Sección de Contenido -->
                    <div class="space-y-3">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Contenido</label>
                        @if($elements[$selectedElementIndex]['type'] === 'text')
                            <textarea wire:model.live.debounce.300ms="elements.{{ $selectedElementIndex }}.content" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50" placeholder="Escribe aquí..."></textarea>
                        @elseif($elements[$selectedElementIndex]['type'] === 'variable')
                            <select wire:model.live="elements.{{ $selectedElementIndex }}.content" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 bg-blue-50">
                                @foreach($variables as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    @if($elements[$selectedElementIndex]['type'] !== 'qr')
                        <hr class="border-gray-100">

                        <!-- Sección de Tipografía -->
                        <div class="space-y-4">
                            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Apariencia</label>
                            
                            <div>
                                <span class="text-xs text-gray-400 mb-1 block">Fuente</span>
                                <select wire:model.live="elements.{{ $selectedElementIndex }}.fontFamily" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="EB Garamond">Garamond (Clásico)</option>
                                    <option value="Cinzel Decorative">Cinzel (Título)</option>
                                    <option value="Pinyon Script">Script (Manuscrito)</option>
                                    <option value="Inter">Inter (Moderno)</option>
                                    <option value="Arial">Arial (Básico)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <span class="text-xs text-gray-400 mb-1 block">Tamaño (px)</span>
                                    <input type="number" wire:model.live="elements.{{ $selectedElementIndex }}.fontSize" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 mb-1 block">Peso</span>
                                    <select wire:model.live="elements.{{ $selectedElementIndex }}.fontWeight" class="w-full rounded-lg border-gray-300 text-sm">
                                        <option value="normal">Normal</option>
                                        <option value="bold">Negrita</option>
                                        <option value="300">Ligero</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <span class="text-xs text-gray-400 mb-1 block">Color</span>
                                    <div class="flex items-center gap-2 border border-gray-300 rounded-lg p-1 bg-white">
                                        <input type="color" wire:model.live="elements.{{ $selectedElementIndex }}.color" class="w-8 h-8 rounded cursor-pointer border-0 p-0">
                                        <span class="text-xs text-gray-500 uppercase">{{ $elements[$selectedElementIndex]['color'] }}</span>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 mb-1 block">Alineación</span>
                                    <div class="flex bg-gray-100 rounded-lg p-1">
                                        <button wire:click="$set('elements.{{ $selectedElementIndex }}.textAlign', 'left')" class="flex-1 py-1 rounded hover:bg-white hover:shadow-sm {{ $elements[$selectedElementIndex]['textAlign'] === 'left' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400' }}">
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h7"></path></svg>
                                        </button>
                                        <button wire:click="$set('elements.{{ $selectedElementIndex }}.textAlign', 'center')" class="flex-1 py-1 rounded hover:bg-white hover:shadow-sm {{ $elements[$selectedElementIndex]['textAlign'] === 'center' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400' }}">
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M7 18h10"></path></svg>
                                        </button>
                                        <button wire:click="$set('elements.{{ $selectedElementIndex }}.textAlign', 'right')" class="flex-1 py-1 rounded hover:bg-white hover:shadow-sm {{ $elements[$selectedElementIndex]['textAlign'] === 'right' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-400' }}">
                                            <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M13 18h7"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <hr class="border-gray-100">

                    <!-- Posición y Dimensiones -->
                    <div class="space-y-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">Geometría</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs text-gray-400">X</span>
                                <input type="number" value="{{ $elements[$selectedElementIndex]['x'] }}" readonly class="w-full pl-8 rounded-lg border-gray-300 text-sm bg-gray-50 text-gray-500">
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs text-gray-400">Y</span>
                                <input type="number" value="{{ $elements[$selectedElementIndex]['y'] }}" readonly class="w-full pl-8 rounded-lg border-gray-300 text-sm bg-gray-50 text-gray-500">
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs text-gray-400">W</span>
                                <input type="number" wire:model.live.debounce.300ms="elements.{{ $selectedElementIndex }}.width" class="w-full pl-8 rounded-lg border-gray-300 text-sm">
                            </div>
                            @if($elements[$selectedElementIndex]['type'] === 'qr')
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-xs text-gray-400">H</span>
                                <input type="number" wire:model.live.debounce.300ms="elements.{{ $selectedElementIndex }}.height" class="w-full pl-8 rounded-lg border-gray-300 text-sm">
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="h-full flex flex-col items-center justify-center text-gray-400 p-8 text-center bg-gray-50">
                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                    <p class="text-sm font-medium text-gray-500">Selecciona un elemento</p>
                    <p class="text-xs mt-2">Haz clic en cualquier elemento del lienzo para editar sus propiedades.</p>
                    
                    <div class="w-full border-t border-gray-200 mt-8 pt-6">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Fondo del Diploma</label>
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-white hover:bg-gray-50 transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="text-xs text-gray-500"><span class="font-semibold">Subir imagen</span></p>
                            </div>
                            <input type="file" wire:model="bgImage" class="hidden" accept="image/*">
                        </label>
                    </div>
                </div>
            @endif
        </aside>
    </div>

    <!-- JavaScript para Drag & Drop (Optimizado) -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('certificateEditor', (wireElements) => ({
                elements: wireElements,
                selectedIndex: null,
                isDragging: false,
                dragOffset: { x: 0, y: 0 },
                currentDragIndex: null,

                init() {
                    this.$watch('$wire.selectedElementIndex', (value) => {
                        this.selectedIndex = value;
                    });
                },

                startDrag(e, index) {
                    if (e.button !== 0) return; // Solo click izquierdo
                    
                    this.isDragging = true;
                    this.currentDragIndex = index;
                    this.selectedIndex = index;
                    this.$wire.selectElement(index);

                    const el = e.target.closest('.absolute');
                    const rect = el.getBoundingClientRect();
                    const canvas = document.getElementById('canvas-area');
                    const canvasRect = canvas.getBoundingClientRect();

                    // Offset relativo al elemento clicado
                    this.dragOffset = {
                        x: e.clientX - rect.left,
                        y: e.clientY - rect.top
                    };

                    // Listeners globales para seguimiento fuera del elemento
                    window.addEventListener('mousemove', this.handleDrag);
                    window.addEventListener('mouseup', this.stopDrag);
                },

                handleDrag(e) {
                    if (!this.isDragging) return;
                    e.preventDefault();

                    const canvas = document.getElementById('canvas-area');
                    const canvasRect = canvas.getBoundingClientRect();

                    // Calcular posición relativa al canvas
                    let newX = e.clientX - canvasRect.left - this.dragOffset.x;
                    let newY = e.clientY - canvasRect.top - this.dragOffset.y;

                    // Limites (opcional, permite salir un poco)
                    // newX = Math.max(0, Math.min(newX, canvasRect.width - 50));
                    // newY = Math.max(0, Math.min(newY, canvasRect.height - 20));

                    // Snapping a la cuadrícula (5px)
                    newX = Math.round(newX / 5) * 5;
                    newY = Math.round(newY / 5) * 5;

                    // Actualización local rápida
                    this.elements[this.currentDragIndex].x = newX;
                    this.elements[this.currentDragIndex].y = newY;
                },

                stopDrag() {
                    if (this.isDragging) {
                        this.isDragging = false;
                        
                        // Guardar posición final en Livewire
                        const el = this.elements[this.currentDragIndex];
                        this.$wire.updateElementPosition(this.currentDragIndex, el.x, el.y);
                        
                        window.removeEventListener('mousemove', this.handleDrag);
                        window.removeEventListener('mouseup', this.stopDrag);
                    }
                }
            }));
        });
        
        // Vincular métodos al contexto si es necesario (generalmente Alpine lo maneja bien con data())
        document.addEventListener('alpine:initialized', () => {
            const editorData = document.querySelector('[x-data="certificateEditor"]')._x_dataStack[0];
            if(editorData) {
                editorData.handleDrag = editorData.handleDrag.bind(editorData);
                editorData.stopDrag = editorData.stopDrag.bind(editorData);
            }
        });
    </script>
</div>