{{-- 
Modal de Pago con Funcionalidad POS
--}}
<div 
    x-data="{ show: $wire.entangle('show') }" 
    x-show="show" 
    @keydown.escape.window="show = false" 
    x-cloak
    class="relative z-50"
>
    <!-- Fondo oscuro -->
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" x-show="show"></div>

    <!-- Contenedor del Modal -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            
            <div 
                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
                x-show="show"
                @click.away="show = false"
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
                
                <!-- Header -->
                <div class="bg-indigo-600 px-4 py-3 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg font-semibold leading-6 text-white">
                        <i class="fas fa-cash-register mr-2"></i> 
                        {{ $payment_id ? 'Editar Pago' : 'Terminal de Pagos' }}
                    </h3>
                    <button @click="show = false" class="text-indigo-100 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-4 py-5 sm:p-6">
                    
                    @if ($errors->has('general'))
                        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4">
                            <p class="text-red-700 font-bold">Error:</p>
                            <p class="text-red-600">{{ $errors->first('general') }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        
                        <!-- COLUMNA IZQUIERDA: SELECCIÓN DE ESTUDIANTE -->
                        <div class="border-b lg:border-b-0 lg:border-r border-gray-200 pb-6 lg:pb-0 lg:pr-6">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Cliente / Estudiante</h4>

                            <!-- Buscador -->
                            @if(!$student)
                                <div class="relative mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar Estudiante</label>
                                    <input 
                                        type="text" 
                                        wire:model.live.debounce.300ms="search_query"
                                        placeholder="Nombre, Matrícula o Cédula..." 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-4 pr-10"
                                    >
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>

                                    <!-- Resultados -->
                                    @if(count($student_results) > 0)
                                        <ul class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                                            @foreach($student_results as $result)
                                                <li 
                                                    wire:click="selectStudent({{ $result->id }})"
                                                    class="relative cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-indigo-50 text-gray-900 border-b last:border-0"
                                                >
                                                    <div class="flex flex-col">
                                                        <span class="font-semibold">{{ $result->first_name }} {{ $result->last_name }}</span>
                                                        <span class="text-xs text-gray-500">ID: {{ $result->id_number }} | {{ $result->email }}</span>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 text-center py-4 italic">
                                    Busque un estudiante para habilitar el formulario de pago.
                                </p>
                            @else
                                <!-- Tarjeta Estudiante Seleccionado -->
                                <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100 relative mb-4">
                                    <button wire:click="clearStudent" class="absolute top-2 right-2 text-gray-400 hover:text-red-500" title="Cambiar estudiante">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-indigo-200 flex items-center justify-center text-indigo-700 font-bold text-xl">
                                            {{ substr($student->first_name, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-bold text-indigo-900">{{ $student->first_name }} {{ $student->last_name }}</h3>
                                            <p class="text-sm text-indigo-700">ID: {{ $student->id_number }}</p>
                                            <p class="text-xs text-indigo-500">{{ $student->email }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        </div>

                        <!-- COLUMNA DERECHA: FORMULARIO -->
                        <div class="opacity-{{ $student ? '100' : '50 pointer-events-none' }} transition-opacity duration-200">
                            <form wire:submit.prevent="savePayment">
                                <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Detalles de la Transacción</h4>

                                <div class="space-y-4">
                                    
                                    <!-- Inscripciones Pendientes -->
                                    @if($studentEnrollments && $studentEnrollments->count() > 0)
                                        <div class="bg-yellow-50 p-3 rounded-md border border-yellow-200">
                                            <label for="enrollment_id" class="block text-sm font-medium text-yellow-800 mb-1">Pagos Pendientes / Inscripciones</label>
                                            <select 
                                                id="enrollment_id" 
                                                wire:model.live="enrollment_id" 
                                                class="block w-full border-yellow-300 rounded-md shadow-sm focus:border-yellow-500 focus:ring-yellow-500 sm:text-sm text-gray-700"
                                            >
                                                <option value="">-- Seleccionar Pago Pendiente --</option>
                                                @foreach($studentEnrollments as $enrollment)
                                                    <option value="{{ $enrollment->id }}">
                                                        {{ $enrollment->courseSchedule->module->name ?? 'Módulo' }} 
                                                        - ${{ number_format($enrollment->courseSchedule->module->price ?? 0, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <!-- Concepto y Monto Row -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="payment_concept_id" class="block text-sm font-medium text-gray-700">Concepto</label>
                                            <select 
                                                id="payment_concept_id" 
                                                wire:model.live="payment_concept_id" 
                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                {{ $isConceptDisabled ? 'disabled' : '' }}
                                            >
                                                <option value="">Seleccione...</option>
                                                @foreach($payment_concepts as $concept)
                                                    <option value="{{ $concept->id }}">{{ $concept->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('payment_concept_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="amount" class="block text-sm font-medium text-gray-700">Monto (DOP)</label>
                                            <div class="relative mt-1 rounded-md shadow-sm">
                                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input 
                                                    type="number" 
                                                    step="0.01" 
                                                    id="amount" 
                                                    wire:model.live="amount" 
                                                    class="block w-full rounded-md border-gray-300 pl-7 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-lg font-bold text-gray-900 text-right"
                                                    {{ $isAmountDisabled ? 'readonly' : '' }}
                                                >
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <span class="text-gray-500 sm:text-sm">DOP</span>
                                                </div>
                                            </div>
                                            @error('amount') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- Método y Estado Row -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label for="gateway" class="block text-sm font-medium text-gray-700">Método Pago</label>
                                            <select 
                                                id="gateway" 
                                                wire:model.live="gateway" 
                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                                <option value="Efectivo">Efectivo</option>
                                                <option value="Transferencia">Transferencia</option>
                                                <option value="Tarjeta">Tarjeta</option>
                                                <option value="Otro">Otro</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label for="status" class="block text-sm font-medium text-gray-700">Estado</label>
                                            <select 
                                                id="status" 
                                                wire:model="status" 
                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                                <option value="Completado">Completado</option>
                                                <option value="Pendiente">Pendiente</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- ÁREA DINÁMICA: DETALLES DE PAGO -->
                                    @if($gateway === 'Efectivo')
                                        <div class="bg-gray-50 p-4 rounded-md border border-gray-200 mt-2">
                                            <div class="flex justify-between items-center mb-2">
                                                <label for="cash_received" class="block text-sm font-medium text-gray-700">Efectivo Recibido:</label>
                                                <div class="relative w-1/2">
                                                    <span class="absolute inset-y-0 left-0 pl-2 flex items-center text-gray-500">$</span>
                                                    <input 
                                                        type="number" 
                                                        step="0.01" 
                                                        id="cash_received" 
                                                        wire:model.live="cash_received" 
                                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm pl-6"
                                                    >
                                                </div>
                                            </div>
                                            <div class="flex justify-between items-center border-t border-gray-200 pt-2">
                                                <span class="text-sm font-bold text-gray-700">DEVUELTA:</span>
                                                <span class="text-2xl font-bold {{ $change_amount < 0 ? 'text-red-600' : 'text-green-600' }}">
                                                    RD$ {{ number_format($change_amount, 2) }}
                                                </span>
                                            </div>
                                            @error('cash_received') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                    @else
                                        <!-- Referencia para Transferencia/Tarjeta -->
                                        <div>
                                            <label for="transaction_id" class="block text-sm font-medium text-gray-700">
                                                {{ $gateway === 'Tarjeta' ? 'Últimos 4 dígitos / Auth Code' : 'Nro. Referencia / Comprobante' }}
                                            </label>
                                            <input 
                                                type="text" 
                                                id="transaction_id" 
                                                wire:model="transaction_id" 
                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Ej: 0045992..."
                                            >
                                            @error('transaction_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        </div>
                                    @endif

                                </div>

                                <!-- Footer Botones -->
                                <div class="flex justify-end pt-6 mt-6 space-x-3 border-t">
                                    <button 
                                        type="button" 
                                        wire:click="closeModal"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none"
                                    >
                                        Cancelar
                                    </button>
                                    <button 
                                        type="submit"
                                        wire:loading.attr="disabled"
                                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span wire:loading wire:target="savePayment" class="mr-2">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </span>
                                        {{ $payment_id ? 'Actualizar' : 'Cobrar' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>