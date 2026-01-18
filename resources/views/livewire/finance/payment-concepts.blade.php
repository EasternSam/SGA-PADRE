<div>
    {{-- Título y Botón --}}
    <header class="bg-white shadow-sm mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <h1 class="text-xl font-semibold text-gray-900">Conceptos de Pago</h1>
            <div class="flex space-x-2">
                <button 
                    wire:click="confirmMassDeletion"
                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150"
                    title="Eliminar todos los conceptos">
                    <i class="fas fa-trash-alt mr-2"></i>Borrado Masivo
                </button>
                <button 
                   wire:click="create"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                    <i class="fas fa-plus mr-2"></i>Añadir Concepto
                </button>
            </div>
        </div>
    </header>

    {{-- Contenido Principal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Búsqueda --}}
        <div class="mb-4">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre..." class="mt-1 block w-1/3 border-gray-300 rounded-md shadow-sm">
        </div>

        {{-- Tabla de Conceptos --}}
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="overflow-x-auto border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Fijo</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($paymentConcepts as $concept)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $concept->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $concept->description ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $concept->is_fixed_amount ? 'Sí ($' . number_format($concept->default_amount, 2) . ')' : 'No' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $concept->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3 transition ease-in-out duration-150" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button wire:click="confirmDeletion({{ $concept->id }})" class="text-red-600 hover:text-red-900 transition ease-in-out duration-150" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    No se encontraron conceptos de pago.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200">
                {{ $paymentConcepts->links() }}
            </div>
        </div>
    </div>

    {{-- Modal para Nuevo/Editar Concepto --}}
    <x-modal name="concept-modal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ $conceptId ? 'Editar' : 'Crear' }} Concepto de Pago
            </h2>
            <form wire:submit.prevent="store">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input id="name" wire:model.defer="name" type="text" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripción (Opcional)</label>
                        <textarea id="description" wire:model.defer="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input wire:model.live="is_fixed_amount" type="checkbox" class="rounded text-indigo-600">
                            <span class="ml-2 text-sm text-gray-700">¿Es un monto fijo?</span>
                        </label>
                    </div>
                    @if($is_fixed_amount)
                    <div>
                        <label for="default_amount" class="block text-sm font-medium text-gray-700">Monto Fijo</label>
                        <input id="default_amount" wire:model.defer="default_amount" type="number" step="0.01" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        @error('default_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    @endif
                </div>

                <div class="flex justify-end mt-6">
                    <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition ease-in-out duration-150">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                        {{ $conceptId ? 'Actualizar' : 'Guardar' }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Modal de Confirmación de Eliminación Individual --}}
    <x-modal :show="$confirmingDeletion">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                ¿Eliminar Concepto?
            </h2>
            <p class="text-gray-700 mb-6">
                ¿Estás seguro de que deseas eliminar este concepto de pago? Esta acción no se puede deshacer.
            </p>
            <div class="flex justify-end">
                <button type="button" wire:click="$set('confirmingDeletion', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <button type="button" wire:click="delete" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </x-modal>

    {{-- Modal de Confirmación de Eliminación Masiva --}}
    {{-- FIX: Cambiado de :show a name="..." para usar eventos, igual que concept-modal --}}
    <x-modal name="confirm-mass-deletion">
        <div class="p-6">
            <h2 class="text-lg font-medium text-red-600 mb-4">
                ⚠️ ¿Borrado Masivo de Conceptos?
            </h2>
            <p class="text-gray-700 mb-6">
                Estás a punto de eliminar <strong>TODOS</strong> los conceptos de pago. Esta acción es irreversible y podría afectar el historial de pagos si existen referencias. ¿Estás absolutamente seguro?
            </p>
            <div class="flex justify-end">
                <button type="button" x-on:click="$dispatch('close-modal', 'confirm-mass-deletion')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition ease-in-out duration-150">
                    Cancelar
                </button>
                <button type="button" wire:click="massDelete" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg shadow transition ease-in-out duration-150">
                    ¡Sí, Eliminar TODO!
                </button>
            </div>
        </div>
    </x-modal>

</div>