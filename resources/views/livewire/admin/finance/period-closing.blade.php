<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="mb-12">
            <h1 class="text-3xl font-semibold text-gray-900">Cierre de Período</h1>
            <p class="mt-3 text-gray-600 max-w-2xl">
                Bloquea transacciones hasta una fecha específica para proteger la integridad de los registros contables.
            </p>
        </div>

        @if (session()->has('success'))
            <div class="mb-8 bg-green-50 border-l-4 border-green-500 p-4">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white border border-gray-200">
            <div class="border-b border-gray-200 bg-gray-50 px-8 py-6">
                <h2 class="text-lg font-medium text-gray-900">Configuración de Bloqueo</h2>
                <p class="mt-1 text-sm text-gray-600">Las transacciones anteriores a esta fecha no podrán ser modificadas.</p>
            </div>
            
            <form wire:submit.prevent="save" class="p-8">
                <div class="space-y-6">
                    <div>
                        <label for="lock_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Fecha de cierre
                        </label>
                        <input 
                            type="date" 
                            id="lock_date" 
                            wire:model="lock_date" 
                            class="block w-full max-w-xs border-gray-300 focus:border-gray-900 focus:ring-gray-900 sm:text-sm"
                        >
                        @error('lock_date') 
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-4 pt-4">
                        <button 
                            type="submit" 
                            class="px-6 py-2 bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
                        >
                            Guardar configuración
                        </button>
                        <button 
                            type="button" 
                            wire:click="$set('lock_date', null)" 
                            class="px-6 py-2 border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
