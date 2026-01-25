<div>
    {{-- Título y Botón --}}
    <header class="bg-white shadow-sm mb-6 rounded-lg border border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Conceptos de Pago</h1>
                <p class="text-sm text-gray-500">Administra los precios y servicios (ej. Diplomas, Inscripciones).</p>
            </div>
            <div class="flex space-x-2">
                <button 
                    wire:click="confirmMassDeletion"
                    class="bg-white border border-red-200 text-red-600 hover:bg-red-50 font-medium py-2 px-4 rounded-lg shadow-sm transition ease-in-out duration-150 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Limpiar Sin Uso
                </button>
                <button 
                   wire:click="create"
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition ease-in-out duration-150 flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Nuevo Concepto
                </button>
            </div>
        </div>
    </header>

    {{-- Contenido Principal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Mensajes Flash --}}
        @if (session()->has('message'))
            <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Filtros y Tabla --}}
        <div class="bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100">
            <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                <div class="relative max-w-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar concepto..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 sm:text-sm transition duration-150 ease-in-out">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Descripción</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Precio / Monto</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($paymentConcepts as $concept)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $concept->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" title="{{ $concept->description }}">
                                    {{ $concept->description ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($concept->amount > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            RD$ {{ number_format($concept->amount, 2) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Variable
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button wire:click="edit({{ $concept->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button wire:click="confirmDeletion({{ $concept->id }})" class="text-red-600 hover:text-red-900 transition-colors" title="Eliminar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <p>No se encontraron conceptos de pago.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $paymentConcepts->links() }}
            </div>
        </div>
    </div>

    {{-- Modal para Nuevo/Editar Concepto --}}
    <x-modal name="concept-modal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                @if($conceptId) 
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Editar Concepto
                @else
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Nuevo Concepto
                @endif
            </h2>
            
            <form wire:submit.prevent="store">
                <div class="space-y-4">
                    <div>
                        <x-input-label for="name" value="Nombre del Concepto" />
                        <x-text-input id="name" wire:model="name" type="text" class="mt-1 block w-full" placeholder="Ej: Solicitud de Diploma" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    
                    <div>
                        <x-input-label for="description" value="Descripción (Opcional)" />
                        <textarea id="description" wire:model="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Detalles sobre este cobro..."></textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                    
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input wire:model.live="is_fixed_amount" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm font-medium text-gray-700">¿Tiene un precio fijo?</span>
                        </label>
                        
                        @if($is_fixed_amount)
                            <div class="mt-3 animate-in fade-in slide-in-from-top-1">
                                <x-input-label for="amount" value="Monto (RD$)" />
                                <x-text-input id="amount" wire:model="amount" type="number" step="0.01" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">Este monto se aplicará automáticamente al seleccionar este concepto.</p>
                            </div>
                        @else
                            <p class="text-xs text-gray-500 mt-2 ml-6">Si no es fijo, el monto se ingresará manualmente al momento del cobro.</p>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end mt-6 gap-3">
                    <x-secondary-button wire:click="$dispatch('close-modal', 'concept-modal')" type="button">
                        Cancelar
                    </x-secondary-button>
                    <x-primary-button>
                        {{ $conceptId ? 'Actualizar' : 'Guardar' }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    {{-- Modal de Confirmación de Eliminación Individual --}}
    <x-modal name="confirm-deletion-modal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                ¿Eliminar Concepto?
            </h2>
            <p class="text-gray-700 mb-6">
                ¿Estás seguro de que deseas eliminar este concepto? Esta acción no se puede deshacer.
            </p>
            <div class="flex justify-end gap-3">
                <x-secondary-button wire:click="$dispatch('close-modal', 'confirm-deletion-modal')">
                    Cancelar
                </x-secondary-button>
                <x-danger-button wire:click="delete">
                    Sí, Eliminar
                </x-danger-button>
            </div>
        </div>
    </x-modal>

    {{-- Modal de Confirmación de Eliminación Masiva --}}
    <x-modal name="confirm-mass-deletion" focusable>
        <div class="p-6">
            <h2 class="text-lg font-bold text-red-600 mb-4">
                ⚠️ Limpieza de Conceptos
            </h2>
            <p class="text-gray-700 mb-4">
                Esta acción eliminará <strong>SOLO los conceptos que no tienen historial de pagos</strong>. Los conceptos que ya han sido utilizados se conservarán por seguridad.
            </p>
            <p class="text-sm text-gray-500 mb-6">
                ¿Deseas proceder con la limpieza?
            </p>
            <div class="flex justify-end gap-3">
                <x-secondary-button wire:click="$dispatch('close-modal', 'confirm-mass-deletion')">
                    Cancelar
                </x-secondary-button>
                <x-danger-button wire:click="massDelete">
                    Ejecutar Limpieza
                </x-danger-button>
            </div>
        </div>
    </x-modal>

</div>