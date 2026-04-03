<div class="min-h-screen bg-gray-50 pb-8">
    {{-- Mensajes de Sesión (Flash) --}}
    <div class="fixed top-24 right-6 z-50">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg" role="alert">
                <strong class="font-bold">¡Éxito!</strong>
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif
    </div>

    {{-- Encabezado y Búsqueda --}}
    <header class="bg-white shadow-sm mb-6 sticky top-0 z-40">
        <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h1 class="text-xl font-semibold text-gray-900 w-full sm:w-auto">
                <i class="fas fa-ticket-alt mr-2 text-indigo-600"></i> Gestión de Becas
            </h1>
            <div class="w-full sm:w-1/3 relative">
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Buscar becas por nombre..." 
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
            </div>
            <button wire:click="create" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-700 transition flex border border-indigo-700">
                <i class="fas fa-plus mt-1 mr-2"></i> Nueva Beca
            </button>
        </div>
    </header>

    <div class="mx-auto w-full max-w-[95%] px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-lg overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre de Beca</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descuento (%)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($scholarships as $s)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $s->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-green-700 font-bold">-{{ number_format($s->discount_percentage, 0) }}%</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($s->description, 50, '...') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button wire:click="toggleActive({{ $s->id }})" title="Alternar Estado" class="focus:outline-none">
                                @if($s->is_active)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Activa</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactiva</span>
                                @endif
                            </button>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button wire:click="edit({{ $s->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button wire:click="confirmDelete({{ $s->id }})" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No hay becas registradas. Añade una para empezar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($scholarships->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $scholarships->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Editar / Crear -->
    <x-modal name="scholarship-modal" maxWidth="md">
        <form wire:submit.prevent="save">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $scholarshipId ? 'Editar Beca' : 'Nueva Beca' }}</h2>
                
                <div class="space-y-4">
                    <div>
                        <x-input-label for="name" value="Nombre de la Beca" />
                        <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="discount_percentage" value="Porcentaje de Descuento (%)" />
                        <div class="relative mt-1">
                            <x-text-input wire:model="discount_percentage" id="discount_percentage" type="number" step="0.01" min="0" max="100" class="block w-full pr-8" required />
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 font-bold">%</span>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('discount_percentage')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Descripción (Opcional)" />
                        <textarea wire:model="description" id="description" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="3"></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>

                    <div class="flex items-center mt-4 pb-2">
                        <input wire:model="is_active" id="is_active" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Activar Beca Inmediatamente</label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <x-secondary-button x-on:click="$dispatch('close-modal', 'scholarship-modal')">Cancelar</x-secondary-button>
                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">Guardar</x-primary-button>
                </div>
            </div>
        </form>
    </x-modal>

    <!-- Modal Eliminar -->
    <x-modal name="confirm-delete-modal" maxWidth="sm">
        <div class="p-6">
            <h2 class="text-lg font-medium text-red-600 mb-4">Confirmar Eliminación</h2>
            <div class="bg-red-50 p-3 rounded text-sm text-red-800 border-l-4 border-red-500 mb-4">
                ¿Estás seguro de que deseas eliminar permanentemente esta beca? 
            </div>
            <p class="text-xs text-gray-500 mb-6">Nota: Los estudiantes actualmente asignados podrían perder su descuento activo a menos que mantengas la beca solo marcada como inactiva.</p>
            <div class="flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', 'confirm-delete-modal')">Cancelar</x-secondary-button>
                <x-danger-button wire:click="delete">Sí, Eliminar</x-danger-button>
            </div>
        </div>
    </x-modal>
</div>
