{{-- 
Este componente utiliza el modal de Jetstream/Breeze.
Se abre con el evento: $dispatch('open-modal', 'payment-modal')
--}}
<x-modal name="payment-modal" focusable>
    {{-- 
    Usamos Alpine.js para escuchar el evento 'open-modal' y
    llamar al método @wire.resetForm() para limpiar el formulario cada vez que se abre.
    --}}
    <div x-data="{}" x-on:open-modal.window="$wire.resetForm()">
        
        {{-- Formulario de registro de pago --}}
        <form wire:submit.prevent="savePayment" class="p-6">

            <h2 class="text-lg font-medium text-gray-900">
                Registrar Pago para {{ $student->fullName }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Complete los detalles del pago.
            </p>

            {{-- Contenido del formulario --}}
            <div class="mt-6 space-y-4">

                {{-- Concepto de Pago --}}
                <div>
                    <x-input-label for="payment_concept_id" value="Concepto de Pago" />
                    {{-- 
                    Usamos wire:model.live para que la propiedad $payment_concept_id
                    se actualice en tiempo real y dispare el método updatedPaymentConceptId()
                    --}}
                    <select wire:model.live="payment_concept_id" id="payment_concept_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Seleccione un concepto...</option>
                        {{-- Iteramos sobre los conceptos cargados en el componente --}}
                        @if($payment_concepts)
                            @foreach($payment_concepts as $concept)
                                <option value="{{ $concept->id }}">
                                    {{ $concept->name }} 
                                    {{-- Si es monto fijo, mostramos el precio en el dropdown --}}
                                    @if($concept->is_fixed_amount)
                                        (${{ number_format($concept->default_amount, 2) }})
                                    @endif
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <x-input-error :messages="$errors->get('payment_concept_id')" class="mt-2" />
                </div>
                
                {{-- Monto --}}
                <div>
                    <x-input-label for="amount" value="Monto" />
                    <div class="relative mt-1 rounded-md shadow-sm">
                        {{-- Prefijo de moneda para el input --}}
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                          <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        {{-- 
                        Usamos :disabled="$isAmountDisabled" para deshabilitar el campo
                        basado en la propiedad del componente.
                        --}}
                        <x-text-input wire:model="amount" id="amount" class="block w-full pl-7" 
                                      type="number" step="0.01" 
                                      :disabled="$isAmountDisabled" />
                    </div>
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>

                {{-- Método de Pago --}}
                <div>
                    <x-input-label for="payment_method" value="Método de Pago" />
                    <select wire:model="payment_method" id="payment_method" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                        <option value="Depósito">Depósito</option>
                        <option value="Tarjeta de Crédito/Débito">Tarjeta de Crédito/Débito</option>
                        <option value="Otro">Otro</option>
                    </select>
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                </div>

                {{-- Estado --}}
                <div>
                    <x-input-label for="status" value="Estado" />
                    <select wire:model="status" id="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="Completado">Completado</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Fallido">Fallido</option>
                        <option value="Anulado">Anulado</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
                
                {{-- Descripción --}}
                <div>
                    <x-input-label for="description" value="Descripción (Opcional)" />
                    <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Ej: Nro. de referencia 12345..."></textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                </div>
            </div>

            {{-- Footer del modal con botones --}}
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancelar') }}
                </x-secondary-button>

                {{-- El botón se deshabilita mientras se procesa el pago --}}
                <x-primary-button class="ms-3" wire:loading.attr="disabled" wire:target="savePayment">
                    <span wire:loading.remove wire:target="savePayment">
                        {{ __('Registrar Pago') }}
                    </span>
                    {{-- Indicador de carga --}}
                    <span wire:loading wire:target="savePayment">
                        Procesando...
                    </span>
                </x-primary-button>
            </div>
        </form>
    </div>
</x-modal>