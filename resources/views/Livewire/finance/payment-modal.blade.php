<x-modal name="payment-modal" focusable>
    <form wire:submit.prevent="savePayment">
        <div class="p-6 bg-white dark:bg-gray-800">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Registrar Pago para {{ $student->fullName }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Complete los detalles del pago.
            </p>

            <div class="mt-6 space-y-4">
                {{-- Concepto de Pago --}}
                <div>
                    <x-input-label for="payment_concept_id" value="Concepto de Pago" />
                    <select wire:model.live="payment_concept_id" id="payment_concept_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="">Seleccione un concepto...</option>
                        @if($payment_concepts)
                            @foreach($payment_concepts as $concept)
                                <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <x-input-error :messages="$errors->get('payment_concept_id')" class="mt-2" />
                </div>
                
                {{-- Monto --}}
                <div>
                    <x-input-label for="amount" value="Monto" />
                    <x-text-input wire:model.live="amount" id="amount" class="block mt-1 w-full" type="number" step="0.01" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                {{-- Método de Pago --}}
                <div>
                    <x-input-label for="payment_method" value="Método de Pago" />
                    <select wire:model.live="payment_method" id="payment_method" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                        <option value="Otro">Otro</option>
                    </select>
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                </div>

                {{-- Estado --}}
                <div>
                    <x-input-label for="status" value="Estado" />
                    <select wire:model.live="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                        <option value="Completado">Completado</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Fallido">Fallido</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
                
                {{-- Descripción --}}
                <div>
                    <x-input-label for="description" value="Descripción (Opcional)" />
                    <textarea wire:model.live="description" id="description" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="flex justify-end p-6 bg-gray-100 dark:bg-gray-800 rounded-b-lg">
            {{-- El x-on:click="$dispatch('close')" cierra el modal (controlado por Alpine) --}}
            <x-secondary-button x-on:click="$dispatch('close')">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-primary-button class="ms-3" wire:loading.attr="disabled">
                {{ __('Registrar Pago') }}
            </x-primary-button>
        </div>
    </form>
</x-modal>