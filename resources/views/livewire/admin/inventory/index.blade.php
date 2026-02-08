<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Inventario Institucional</h2>
                <p class="text-gray-600">Gestión de activos, equipos y mobiliario.</p>
            </div>
            <button wire:click="create" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrar Nuevo Activo
            </button>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-indigo-500">
                <div class="text-gray-500 text-sm font-medium">Total Activos</div>
                <div class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <div class="text-gray-500 text-sm font-medium">En Almacén</div>
                <div class="text-2xl font-bold text-gray-800">{{ $stats['warehouse'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
                <div class="text-gray-500 text-sm font-medium">Con Defectos / Reparación</div>
                <div class="text-2xl font-bold text-gray-800">{{ $stats['defective'] }}</div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <div class="text-gray-500 text-sm font-medium">Valor Total (Estimado)</div>
                <div class="text-2xl font-bold text-gray-800">RD$ {{ number_format($stats['value'], 2) }}</div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="bg-white p-4 rounded-lg shadow mb-6 flex flex-col md:flex-row gap-4 items-center">
            <div class="flex-1 w-full">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, serie o etiqueta..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <select wire:model.live="locationFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas las Ubicaciones</option>
                <option value="warehouse">📦 Solo Almacén</option>
                @foreach($classrooms as $room)
                    <option value="{{ $room->id }}">🏫 {{ $room->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="categoryFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todas las Categorías</option>
                <option value="PC">Computadoras</option>
                <option value="Monitor">Monitores</option>
                <option value="Proyector">Proyectores</option>
                <option value="Mobiliario">Mobiliario</option>
                <option value="Otro">Otro</option>
            </select>
            <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Todos los Estados</option>
                <option value="Operativo">✅ Operativo</option>
                <option value="Defectuoso">❌ Defectuoso</option>
                <option value="En Reparación">🔧 En Reparación</option>
                <option value="Obsoleto">🗑 Obsoleto</option>
            </select>
        </div>

        {{-- Tabla --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Identificación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ubicación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->category }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($item->asset_tag)
                                        <div class="text-xs font-mono bg-gray-100 px-2 py-1 rounded inline-block text-gray-700">TAG: {{ $item->asset_tag }}</div>
                                    @endif
                                    @if($item->serial_number)
                                        <div class="text-xs text-gray-500 mt-1">S/N: {{ $item->serial_number }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $item->classroom_id ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $item->location_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $colors = [
                                            'Operativo' => 'bg-green-100 text-green-800',
                                            'Defectuoso' => 'bg-red-100 text-red-800',
                                            'En Reparación' => 'bg-yellow-100 text-yellow-800',
                                            'Obsoleto' => 'bg-gray-100 text-gray-800',
                                        ];
                                        $colorClass = $colors[$item->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full {{ $colorClass }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $item->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Editar</button>
                                    <button wire:click="delete({{ $item->id }})" wire:confirm="¿Seguro?" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay activos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">{{ $items->links() }}</div>
        </div>

        {{-- MODAL (Usando name para eventos) --}}
        <x-modal name="inventory-modal" focusable>
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    {{ $itemId ? 'Editar Activo' : 'Registrar Nuevo Activo' }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <x-input-label value="Nombre del Equipo / Mueble" />
                        <x-text-input wire:model="name" class="w-full" placeholder="Ej: Computadora Dell Optiplex" />
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    
                    <div>
                        <x-input-label value="Categoría" />
                        <select wire:model="category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Seleccionar...</option>
                            <option value="PC">Computadora (PC)</option>
                            <option value="Monitor">Monitor</option>
                            <option value="Proyector">Proyector</option>
                            <option value="Mobiliario">Mobiliario</option>
                            <option value="Redes">Equipos de Red</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('category') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-input-label value="Estado" />
                        <select wire:model="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Operativo">✅ Operativo</option>
                            <option value="Defectuoso">❌ Defectuoso</option>
                            <option value="En Reparación">🔧 En Reparación</option>
                            <option value="Obsoleto">🗑 Obsoleto</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label value="Número de Serie (S/N)" />
                        <x-text-input wire:model="serial_number" class="w-full" placeholder="Opcional" />
                    </div>

                    <div>
                        <x-input-label value="Etiqueta de Activo (Tag)" />
                        <x-text-input wire:model="asset_tag" class="w-full" placeholder="Código Interno" />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label value="Ubicación Actual" />
                        <select wire:model="classroom_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">📦 Almacén Central / Bodega</option>
                            @foreach($classrooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }} ({{ $room->building->name ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label value="Notas / Detalles" />
                        <textarea wire:model="notes" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="2"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button wire:click="closeModal">Cancelar</x-secondary-button>
                    <x-primary-button wire:click="save">Guardar Activo</x-primary-button>
                </div>
            </div>
        </x-modal>

    </div>
</div>