<div class="p-4 sm:p-8 lg:p-10">
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 uppercase">Cierre de Período Contable</h1>
            <p class="mt-2 text-sm text-gray-600">
                Bloquea los registros contables hasta una fecha específica para asegurar la integridad fiscal e impedir modificaciones a períodos que ya fueron reportados o auditados.
            </p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-xl bg-green-50 p-4 shadow-sm border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Cierre Actualizado</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden transform transition-all p-8 sm:max-w-xl mx-auto mt-10">
        
        <div class="flex items-center justify-center mb-6">
            <div class="rounded-full bg-red-100 p-4">
                <svg class="h-10 w-10 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
        </div>

        <h3 class="text-center text-xl font-bold text-gray-900 mb-2">Bloqueo de Transacciones</h3>
        <p class="text-center text-sm text-gray-500 mb-8">
            Establece la <strong>Fecha de Cierre</strong>. El motor contable rechazará automáticamente cualquier asiento, pago o factura que se intente crear o modificar con una fecha igual o anterior a la fecha seleccionada.
        </p>

        <form wire:submit.prevent="save">
            <div class="mb-6">
                <label for="lock_date" class="block text-sm font-bold leading-6 text-gray-900 mb-2">Fecha de Cierre (Cerrado Hasta)</label>
                <input type="date" id="lock_date" wire:model="lock_date" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-lg sm:leading-6 font-mono text-center bg-gray-50 hover:bg-white transition-colors cursor-pointer">
                @error('lock_date') <span class="text-red-500 font-medium text-xs block mt-2 text-center">{{ $message }}</span> @enderror
            </div>

            <div class="mt-8 flex items-center justify-center gap-x-4">
                <button type="button" wire:click="$set('lock_date', null)" class="text-sm font-semibold leading-6 text-gray-900 px-4 py-2 hover:bg-gray-100 rounded-lg transition-colors">Abrir Período (Limpiar)</button>
                <button type="submit" class="rounded-xl bg-red-600 px-8 py-3 text-sm font-bold shadow-md text-white hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 transition-all hover:shadow-lg hover:-translate-y-0.5">
                    Aplicar Cierre y Bloquear
                </button>
            </div>
        </form>

    </div>
</div>
