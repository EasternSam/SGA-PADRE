<div class="h-[calc(100vh-65px)] flex flex-col" 
     x-data="certificateEditor(@entangle('elements'))">
    
    <!-- Barra Superior -->
    <div class="bg-gray-800 text-white p-4 flex justify-between items-center shadow-md z-20">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold">Editor de Certificados</h1>
            <input type="text" wire:model="name" class="bg-gray-700 border-none rounded px-2 py-1 text-sm focus:ring-1 focus:ring-blue-500" placeholder="Nombre del diseño">
        </div>
        <div class="flex gap-2">
            <button wire:click="addElement('text')" class="bg-gray-600 hover:bg-gray-500 px-3 py-1.5 rounded text-sm flex items-center gap-2">
                + Texto
            </button>
            <button wire:click="addElement('variable')" class="bg-blue-600 hover:bg-blue-500 px-3 py-1.5 rounded text-sm flex items-center gap-2">
                + Variable
            </button>
            <button wire:click="addElement('qr')" class="bg-purple-600 hover:bg-purple-500 px-3 py-1.5 rounded text-sm flex items-center gap-2">
                + QR
            </button>
            <button wire:click="save" class="bg-green-600 hover:bg-green-500 px-4 py-1.5 rounded text-sm font-bold shadow flex items-center gap-2">
                Guardar
            </button>
        </div>
    </div>

    <div class="flex-1 flex overflow-hidden">
        
        <!-- Panel de Propiedades -->
        <div class="w-80 bg-gray-100 border-r border-gray-300 flex flex-col overflow-y-auto p-4 z-10 shadow-inner">
            
            @if(session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('message') }}
                </div>
            @endif

            @if(!is_null($selectedElementIndex) && isset($elements[$selectedElementIndex]))
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">Propiedades</h3>
                
                <div class="space-y-4">
                    @if($elements[$selectedElementIndex]['type'] === 'text')
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Contenido</label>
                            <textarea wire:model.live="elements.{{ $selectedElementIndex }}.content" class="w-full mt-1 p-2 border rounded text-sm text-black"></textarea>
                        </div>
                    @elseif($elements[$selectedElementIndex]['type'] === 'variable')
                        <div>
                            <label class="text-xs font-semibold text-gray-500 uppercase">Variable Dinámica</label>
                            <select wire:model.live="elements.{{ $selectedElementIndex }}.content" class="w-full mt-1 p-2 border rounded text-sm text-black">
                                @foreach($variables as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if($elements[$selectedElementIndex]['type'] !== 'qr')
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Fuente</label>
                                <select wire:model.live="elements.{{ $selectedElementIndex }}.fontFamily" class="w-full mt-1 p-1 border rounded text-sm text-black">
                                    <option value="EB Garamond">Garamond</option>
                                    <option value="Cinzel Decorative">Cinzel</option>
                                    <option value="Pinyon Script">Script</option>
                                    <option value="Arial">Arial</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Tamaño (pt)</label>
                                <input type="number" wire:model.live="elements.{{ $selectedElementIndex }}.fontSize" class="w-full mt-1 p-1 border rounded text-sm text-black">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Color</label>
                                <input type="color" wire:model.live="elements.{{ $selectedElementIndex }}.color" class="w-full mt-1 h-8 cursor-pointer rounded">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-500 uppercase">Alineación</label>
                                <select wire:model.live="elements.{{ $selectedElementIndex }}.textAlign" class="w-full mt-1 p-1 border rounded text-sm text-black">
                                    <option value="left">Izquierda</option>
                                    <option value="center">Centro</option>
                                    <option value="right">Derecha</option>
                                </select>
                            </div>
                        </div>
                    @endif

                    <button wire:click="removeElement({{ $selectedElementIndex }})" class="w-full bg-red-100 text-red-600 border border-red-200 py-2 rounded text-sm hover:bg-red-200 mt-4">
                        Eliminar Elemento
                    </button>
                </div>
            @else
                <div class="text-center text-gray-500 mt-10">
                    <p class="text-sm">Selecciona un elemento para editarlo.</p>
                    <div class="mt-8 border-t pt-4">
                        <label class="text-xs font-semibold text-gray-500 uppercase mb-2 block">Imagen de Fondo</label>
                        <input type="file" wire:model="bgImage" class="text-xs w-full text-gray-600">
                    </div>
                </div>
            @endif
        </div>

        <!-- Área de Trabajo (Canvas) -->
        <div class="flex-1 bg-gray-500 overflow-auto flex justify-center p-8 relative">
            <div id="canvas-area"
                 class="bg-white shadow-2xl relative transition-all"
                 style="width: 1123px; height: 794px; min-width: 1123px; min-height: 794px; 
                        background-image: url('{{ $currentBg ? asset('storage/'.$currentBg) : "data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.05'/%3E%3C/svg%3E" }}');
                        background-size: cover; background-position: center;">
                
                <!-- Guías -->
                <div class="absolute inset-[40px] border border-dashed border-blue-300 pointer-events-none opacity-50"></div>

                <!-- Elementos -->
                <template x-for="(element, index) in elements" :key="index">
                    <div class="absolute cursor-move group select-none hover:outline hover:outline-2 hover:outline-blue-400 flex items-center"
                         :class="{'outline outline-2 outline-blue-600': selectedIndex === index, 'justify-center': element.textAlign === 'center', 'justify-end': element.textAlign === 'right'}"
                         :style="`
                            left: ${element.x}px; 
                            top: ${element.y}px; 
                            width: ${element.width}px;
                            height: ${element.height ? element.height + 'px' : 'auto'};
                            font-family: '${element.fontFamily}';
                            font-size: ${element.fontSize}px;
                            font-weight: ${element.fontWeight};
                            color: ${element.color};
                            z-index: ${element.zIndex || 10};
                         `"
                         @mousedown="startDrag($event, index)"
                         @click="$wire.selectElement(index); selectedIndex = index;">
                        
                        <!-- Renderizado condicional -->
                        <template x-if="element.type === 'qr'">
                            <div class="bg-gray-200 border-2 border-dashed border-gray-400 flex items-center justify-center w-full h-full text-xs text-gray-500">QR</div>
                        </template>
                        <template x-if="element.type !== 'qr'">
                            <div x-text="element.content" class="w-full" :style="`text-align: ${element.textAlign}`"></div>
                        </template>
                        
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Script de Arrastre -->
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
                    this.isDragging = true;
                    this.currentDragIndex = index;
                    this.selectedIndex = index;
                    this.$wire.selectElement(index);

                    const el = e.target.closest('.absolute');
                    const rect = el.getBoundingClientRect();
                    this.dragOffset = {
                        x: e.clientX - rect.left,
                        y: e.clientY - rect.top
                    };

                    window.addEventListener('mousemove', this.handleDrag);
                    window.addEventListener('mouseup', this.stopDrag);
                },

                handleDrag(e) {
                    if (!this.isDragging) return;
                    // Necesitamos acceder al contexto de Alpine, usaremos arrow function en variable externa si falla 'this'
                    // Pero aquí 'this' es el componente Alpine data
                    const canvas = document.getElementById('canvas-area');
                    const canvasRect = canvas.getBoundingClientRect();

                    let newX = e.clientX - canvasRect.left - this.dragOffset.x;
                    let newY = e.clientY - canvasRect.top - this.dragOffset.y;

                    // Snapping
                    newX = Math.round(newX / 5) * 5;
                    newY = Math.round(newY / 5) * 5;

                    this.elements[this.currentDragIndex].x = newX;
                    this.elements[this.currentDragIndex].y = newY;
                },

                stopDrag() {
                    if (this.isDragging) {
                        this.isDragging = false;
                        const el = this.elements[this.currentDragIndex];
                        this.$wire.updateElementPosition(this.currentDragIndex, el.x, el.y);
                        
                        window.removeEventListener('mousemove', this.handleDrag);
                        window.removeEventListener('mouseup', this.stopDrag);
                    }
                }
            }));
        });
        
        // Vincular funciones al objeto global para evitar problemas de scope
        document.addEventListener('alpine:initialized', () => {
            // Fix adicional si es necesario
        });
    </script>
    
    <!-- Fuentes para el Editor -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=EB+Garamond:ital,wght@0,400;0,600;1,400&family=Pinyon+Script&display=swap" rel="stylesheet">
</div>