{{-- 
Modal de Pago. 
Este archivo corresponde a 'app/Livewire/Finance/PaymentModal.php'.
HEMOS AÑADIDO EL DROPDOWN DE "INSCRIPCIONES PENDIENTES".
--}}
<div 
    x-data="{ show: $wire.entangle('show') }" 
    x-show="show" 
    @keydown.escape.window="show = false" 
    x-cloak
>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <!-- Fondo oscuro -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75" @click="show = false"></div>

        <!-- Contenedor del Modal -->
        <div class="relative w-full max-w-lg p-6 mx-auto bg-white rounded-lg shadow-xl" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <form wire:submit.prevent="savePayment">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    {{ $payment_id ? 'Editar Pago' : 'Registrar Nuevo Pago' }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Registrando pago para: <span class="font-semibold">{{ $student?->fullName }}</span>
                </p>

                <div class="mt-4 space-y-4">
                    
                    <!-- ============================================= -->
                    <!--     CAMPO AÑADIDO: Inscripciones Pendientes     -->
                    <!-- ============================================= -->
                    @if($studentEnrollments->count() > 0)
                    <div>
                        <label for="enrollment_id" class="block text-sm font-medium text-gray-700">Vincular a Inscripción Pendiente</label>
                        <select 
                            id="enrollment_id" 
                            wire:model.live="enrollment_id" 
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="">-- No vincular (pago manual) --</option>
                            @foreach($studentEnrollments as $enrollment)
                                <option value="{{ $enrollment->id }}">
                                    {{ $enrollment->courseSchedule->module->name ?? 'N/A' }} 
                                    ({{ $enrollment->courseSchedule->section_name ?? 'N/A' }})
                                    - (Monto: ${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }})
                                </option>
                            @endforeach
                        </select>
                        <span class="text-xs text-gray-500">Seleccionar esto auto-rellenará el monto y concepto.</span>
                    </div>
                    @endif

                    <!-- Concepto de Pago -->
                    <div>
                        <label for="payment_concept_id" class="block text-sm font-medium text-gray-700">Concepto de Pago</label>
                        <select 
                            id="payment_concept_id" 
                            wire:model.live="payment_concept_id" 
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            {{ $isConceptDisabled ? 'disabled' : '' }} {{-- CORREGIDO: Usar la nueva variable --}}
                        >
                            <option value="">Seleccione un concepto...</option>
                            {{-- ¡¡¡CORRECCIÓN AQUÍ!!! $paymentConcepts -> $payment_concepts --}}
                            @foreach($payment_concepts as $concept)
                                <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                            @endforeach
                        </select>
                        @error('payment_concept_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Monto -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700">Monto (DOP)</label>
                        <input 
                            type="number" 
                            step="0.01" 
                            id="amount" 
                            wire:model="amount" 
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            {{ $isAmountDisabled ? 'readonly' : '' }} {{-- CORREGIDO: Simplificado --}}
                        >
                        @error('amount') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Método de Pago (Gateway) -->
                    <div>
                        <label for="gateway" class="block text-sm font-medium text-gray-700">Método de Pago</label>
                        <select 
                            id="gateway" 
                            wire:model="gateway" 
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="Efectivo">Efectivo</option>
                            <option value="Transferencia">Transferencia</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('gateway') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Nro. de Referencia/Transacción -->
                    <div>
                        <label for="transaction_id" class="block text-sm font-medium text-gray-700">Nro. Referencia (Opcional)</label>
                        <input 
                            type="text" 
                            id="transaction_id" 
                            wire:model="transaction_id" 
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                        @error('transaction_id') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado del Pago -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                        <select 
                            id="status" 
                            wire:model="status" 
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="Completado">Completado</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Rechazado">Rechazado</option>
                            <option value="Reembolsado">Reembolsado</option>
                        </select>
                        @error('status') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                </div>

                <!-- Botones del Modal -->
                <div class="flex justify-end pt-6 mt-6 space-x-3 border-t">
                    <button 
                        type="button" 
                        @click="show = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                    >
                        <span wire:loading wire:target="savePayment" class="mr-2">
                            <svg class="w-5 h-5 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="savePayment">
                            {{ $payment_id ? 'Actualizar Pago' : 'Guardar Pago' }}
                        </span>
                        <span wire:loading wire:target="savePayment">
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>