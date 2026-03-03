<div class="p-4 sm:p-8 lg:p-10">
    <!-- Page Header -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-200">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </span>
                Cuentas por Pagar & Gastos
            </h1>
            <p class="mt-2 text-base text-gray-600">Registra compras, facturas de proveedores y gastos. El sistema contabilizará la Partida Doble automáticamente.</p>
        </div>
        <div class="sm:flex-none">
            <button wire:click="create" type="button" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Registrar Nuevo Gasto
            </button>
        </div>
    </div>

    <!-- Alertas -->
    @if (session()->has('success'))
        <div class="mb-8 rounded-2xl bg-green-50 p-6 border border-green-200 shadow-sm animate-fade-in-up">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-green-500 rounded-full p-1.5 shadow-sm">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-green-800 uppercase tracking-widest">Éxito Contable</h3>
                    <p class="text-base font-medium text-green-900 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-8 rounded-2xl bg-red-50 p-6 border border-red-200 shadow-sm animate-fade-in-up">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-red-500 rounded-full p-1.5 shadow-sm">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-red-800 uppercase tracking-widest">Error al Contabilizar</h3>
                    <p class="text-base font-medium text-red-900 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 p-6 mb-8 transition-all hover:shadow-md border border-gray-100">
        <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-12 md:items-end">
            <div class="sm:col-span-12 md:col-span-8 relative">
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Buscador de Gastos</label>
                <div class="pointer-events-none absolute inset-y-0 bottom-0 left-0 top-[2.1rem] flex items-center pl-4">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full rounded-xl border-0 py-3 pl-11 pr-4 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors" placeholder="Buscar por Proveedor, o NCF/Factura...">
            </div>
            
            <div class="sm:col-span-12 md:col-span-4">
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Estado del Pago</label>
                <select wire:model.live="status_filter" class="block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                    <option value="">Todos los Estados</option>
                    <option value="paid">Pagados / Contado</option>
                    <option value="pending">Pendientes de Pago (Crédito)</option>
                    <option value="void">Anulados</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Lista de Gastos / Tabla -->
    <div class="bg-white shadow-xl ring-1 ring-gray-200 sm:rounded-3xl overflow-hidden relative">
        <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600"></div>
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#f8fafc] border-b border-gray-100">
                <tr>
                    <th scope="col" class="py-5 pl-6 md:pl-8 pr-3 text-left text-sm font-bold text-gray-900 uppercase tracking-widest">Proveedor / Gasto</th>
                    <th scope="col" class="px-4 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-widest">NCF / Ref</th>
                    <th scope="col" class="px-4 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-widest">Cuentas Involucradas</th>
                    <th scope="col" class="px-4 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-widest">Fecha</th>
                    <th scope="col" class="px-4 py-5 text-right text-sm font-bold text-gray-900 uppercase tracking-widest">Monto (DOP)</th>
                    <th scope="col" class="px-4 py-5 text-center text-sm font-bold text-gray-900 uppercase tracking-widest">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($expenses as $expense)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <!-- Columna: Proveedor -->
                        <td class="whitespace-nowrap py-5 pl-6 md:pl-8 pr-3">
                            <div class="font-black text-gray-900 text-base">
                                {{ $expense->supplier ? $expense->supplier->name : 'N/A' }}
                            </div>
                            <div class="text-xs font-semibold text-gray-500 mt-0.5 truncate max-w-xs">
                                {{ $expense->description ?: 'Sin concepto' }}
                            </div>
                        </td>
                        
                        <!-- Columna: Ref / NCF -->
                        <td class="whitespace-nowrap px-4 py-5 text-sm">
                            <div class="font-mono font-bold text-gray-900">{{ $expense->ncf ?: 'Sin NCF' }}</div>
                            <div class="font-mono text-gray-500 text-xs mt-0.5">{{ $expense->reference_number ?: 'Sin Ref' }}</div>
                        </td>
                        
                        <!-- Columna: Cuentas -->
                        <td class="px-4 py-5 text-sm">
                            <div class="flex flex-col gap-1.5">
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100">
                                    <span class="text-blue-500">Db:</span> {{ $expense->expenseAccount->name }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-orange-700 bg-orange-50 px-2.5 py-1 rounded-md border border-orange-100">
                                    <span class="text-orange-500">Cr:</span> {{ $expense->paymentAccount->name }}
                                </span>
                            </div>
                        </td>

                        <!-- Columna: Fecha -->
                        <td class="whitespace-nowrap px-4 py-5 text-sm">
                            <div class="font-semibold text-gray-900">{{ $expense->expense_date->format('d M, Y') }}</div>
                            @if($expense->due_date)
                                <div class="text-xs font-medium text-red-500 mt-1 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Vence: {{ $expense->due_date->format('d/m') }}
                                </div>
                            @endif
                        </td>

                        <!-- Columna: Monto -->
                        <td class="whitespace-nowrap px-4 py-5 text-right font-black text-gray-900 text-lg">
                            ${{ number_format($expense->total_amount, 2) }}
                        </td>
                        
                        <!-- Columna: Estado -->
                        <td class="px-4 py-5 whitespace-nowrap text-center">
                            @if($expense->status === 'paid')
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 border border-green-200">
                                    Pagado
                                </span>
                            @elseif($expense->status === 'pending')
                                <div class="flex flex-col items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-yellow-50 px-2.5 py-1 text-xs font-bold text-yellow-800 border border-yellow-200 cursor-help" title="Falta por Saldar (CxP)">
                                        Pendiente
                                    </span>
                                    <button wire:click="openPayModal({{ $expense->id }})" class="text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-500 rounded-md px-3 py-1.5 shadow-sm transition-colors w-full sm:w-auto text-center inline-flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Saldar
                                    </button>
                                </div>
                            @else
                                <span class="inline-flex items-center rounded-md bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700 border border-red-200">
                                    Anulado
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75Zm0 0V6m15.797 12.75A60.03 60.03 0 0018 6.75V6m0 0V4.5A.75.75 0 0017.25 3.75h-2.25M18 6h-2.25m-13.5 0A.75.75 0 012.25 5.25V4.5A.75.75 0 013 3.75h2.25M6 6H3.75m0 0v2.25"></path></svg>
                            <h3 class="mt-4 text-sm font-semibold text-gray-900">No hay gastos registrados</h3>
                            <p class="mt-2 text-sm text-gray-500">Comienza a ingresar facturas de proveedores para llenar el catálogo.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-6 font-medium">
        {{ $expenses->links() }}
    </div>

    <!-- Modal Formulario de Gasto / Factura -->
    @if($showModal)
    <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">
                    
                    <!-- Modal Header -->
                    <div class="bg-indigo-600 px-6 py-6 flex items-start gap-4">
                        <div class="rounded-xl bg-indigo-500/50 p-3 shadow-inner">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold leading-6 text-white" id="modal-title">
                                Registrar Nueva Compra / Gasto
                            </h3>
                            <p class="text-indigo-100 text-sm mt-1">Ingresa los datos para registrar la obligación y procesar su asiento contable.</p>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 pb-6 pt-5 sm:p-8">
                        <div class="grid grid-cols-1 gap-y-6 sm:grid-cols-2 gap-x-6">
                            
                            <!-- Search RNC API -->
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1 flex items-center gap-2">
                                    RNC o Cédula (Suplidor)
                                </label>
                                <div class="relative flex">
                                    <input type="text" wire:model.live.debounce.1000ms="supplier_rnc" wire:keydown.enter="lookupRnc" placeholder="Digita el RNC y presiona Enter..." class="block w-full rounded-l-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('supplier_rnc') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 focus:bg-white transition-colors">
                                    <button wire:click="lookupRnc" type="button" class="bg-indigo-600 text-white px-4 rounded-r-xl font-bold hover:bg-indigo-500 transition-colors flex items-center justify-center shrink-0 border border-transparent">
                                        <svg wire:loading.remove wire:target="lookupRnc" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                                        <svg wire:loading wire:target="lookupRnc" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </button>
                                </div>
                                @error('supplier_rnc') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Supplier Name -->
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Nombre / Razón Social</label>
                                <input type="text" wire:model="supplier_name" placeholder="Ej. ACME Corp SRL" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('supplier_name') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 focus:bg-white transition-colors">
                                @error('supplier_name') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- NCF -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">NCF (Comprobante Fiscal)</label>
                                <input type="text" wire:model="ncf" placeholder="Ej. B0100000001" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('ncf') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors font-mono">
                                @error('ncf') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Tipo de Gasto 606 -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Tipo de Gasto (Para 606)</label>
                                <select wire:model="expense_type_606" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('expense_type_606') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                    @foreach($expenseTypes606 as $code => $label)
                                        <option value="{{ $code }}">{{ $code }} - {{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('expense_type_606') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Referencia NCF -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Referencia / Factura # Interna (Opcional)</label>
                                <input type="text" wire:model="reference_number" placeholder="Ej. F-1025" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('reference_number') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors font-mono">
                                @error('reference_number') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Separador Impositivo -->
                            <div class="sm:col-span-2 py-2">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-200"></div>
                                    </div>
                                    <div class="relative flex justify-center">
                                        <span class="bg-white px-3 text-xs font-black uppercase tracking-widest text-indigo-500">Desglose e Impuestos (DGII)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Subtotal -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Subtotal (Monto del Gasto)</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"><span class="text-gray-500 font-bold">$</span></div>
                                    <input type="number" step="0.01" wire:model.live="subtotal" class="block w-full rounded-xl border-0 py-3 pl-8 pr-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('subtotal') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors text-right font-black">
                                </div>
                                @error('subtotal') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- ITBIS Pagado -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">ITBIS Facturado</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"><span class="text-gray-500 font-bold">$</span></div>
                                    <input type="number" step="0.01" wire:model.live="itbis_amount" class="block w-full rounded-xl border-0 py-3 pl-8 pr-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('itbis_amount') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors text-right font-black">
                                </div>
                                @error('itbis_amount') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Retención ITBIS -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-orange-800 mb-1">ITBIS a Retener (Pasivo)</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"><span class="text-orange-500 font-bold">-$</span></div>
                                    <input type="number" step="0.01" wire:model.live="itbis_retained" class="block w-full rounded-xl border-0 py-3 pl-9 pr-4 text-orange-900 shadow-sm ring-1 ring-inset {{ $errors->has('itbis_retained') ? 'ring-red-300 focus:ring-red-600' : 'ring-orange-300 focus:ring-orange-600' }} sm:text-sm bg-orange-50 hover:bg-orange-100 transition-colors text-right font-black">
                                </div>
                                @error('itbis_retained') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Retención ISR -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-orange-800 mb-1">ISR a Retener (Pasivo)</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"><span class="text-orange-500 font-bold">-$</span></div>
                                    <input type="number" step="0.01" wire:model.live="isr_retained" class="block w-full rounded-xl border-0 py-3 pl-9 pr-4 text-orange-900 shadow-sm ring-1 ring-inset {{ $errors->has('isr_retained') ? 'ring-red-300 focus:ring-red-600' : 'ring-orange-300 focus:ring-orange-600' }} sm:text-sm bg-orange-50 hover:bg-orange-100 transition-colors text-right font-black">
                                </div>
                                @error('isr_retained') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Monto Total Pagado -->
                            <div class="sm:col-span-2 bg-indigo-50 p-4 rounded-xl border border-indigo-200 shadow-inner">
                                <label class="block text-sm font-bold leading-6 text-indigo-900 mb-1">Monto Neto a Pagar/Deudar (Automático)</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                        <span class="text-indigo-500 font-bold text-xl">$</span>
                                    </div>
                                    <input type="number" readonly wire:model="total_amount" class="block w-full rounded-xl border-0 py-3 pl-10 pr-4 text-indigo-900 ring-0 bg-transparent text-right font-black text-2xl" placeholder="0.00">
                                </div>
                                <p class="text-xs text-indigo-600 mt-2 font-medium">Fórmula: Subtotal + ITBIS Facturado - ITBIS Retenido - ISR Retenido</p>
                            </div>

                            <!-- Fechas -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Fecha del Gasto</label>
                                <input type="date" wire:model="expense_date" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('expense_date') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                @error('expense_date') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Fecha de Vencimiento</label>
                                <input type="date" wire:model="due_date" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('due_date') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                @error('due_date') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Separador Contable -->
                            <div class="sm:col-span-2 py-2">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-200"></div>
                                    </div>
                                    <div class="relative flex justify-center">
                                        <span class="bg-white px-3 text-xs font-black uppercase tracking-widest text-indigo-500">Distribución Contable (Partida Doble)</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Diario -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Diario Destino</label>
                                <select wire:model="selected_journal_id" class="block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('selected_journal_id') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                    <option value="">Seleccione el Diario...</option>
                                    @foreach($journals as $j)
                                        <option value="{{ $j->id }}">{{ $j->name }}</option>
                                    @endforeach
                                </select>
                                @error('selected_journal_id') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cuenta de Gasto (Débito) -->
                            <div class="sm:col-span-2 rounded-xl border-2 border-dashed border-blue-200 p-4 bg-blue-50/30">
                                <label class="block text-sm font-bold leading-6 text-blue-900 mb-1 flex items-center justify-between">
                                    <span>¿Qué Gasto es este? (Débito - Aumento de Gasto)</span>
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">+ Debe</span>
                                </label>
                                <select wire:model="expense_account_id" class="mt-2 block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-blue-900 shadow-sm ring-1 ring-inset {{ $errors->has('expense_account_id') ? 'ring-red-300 focus:ring-red-600' : 'ring-blue-300 focus:ring-blue-600' }} sm:text-sm bg-white font-medium">
                                    <option value="">Selecciona la cuenta de Gasto (Categoría)...</option>
                                    @foreach($expenseAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                @error('expense_account_id') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cuenta de Pago / Pasivo (Crédito) -->
                            <div class="sm:col-span-2 rounded-xl border-2 border-dashed border-orange-200 p-4 bg-orange-50/30">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-bold leading-6 text-orange-900">¿Cómo se pagó / Deudó? (Crédito)</label>
                                    <span class="text-xs bg-orange-100 text-orange-800 px-2 py-0.5 rounded-full">+ Haber</span>
                                </div>
                                <select wire:model.live="payment_account_id" class="mt-2 block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-orange-900 shadow-sm ring-1 ring-inset {{ $errors->has('payment_account_id') ? 'ring-red-300 focus:ring-red-600' : 'ring-orange-300 focus:ring-orange-600' }} sm:text-sm bg-white font-medium">
                                    <option value="">Selecciona Banco (Contado) o Cuentas por Pagar (Crédito)...</option>
                                    @foreach($paymentAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ $acc->type === 'asset' ? 'Caja/Banco' : 'Pasivo' }})</option>
                                    @endforeach
                                </select>
                                @error('payment_account_id') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Estado Interno Automático -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Estado de Pago a Proveedor</label>
                                <select wire:model="status" class="block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('status') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                    <option value="paid">✅ Pagado al Contado (Usando Caja/Bancos)</option>
                                    <option value="pending">📝 Pendiente (Usando Cuentas por Pagar al Proveedor)</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-2 font-medium bg-gray-100 p-2 rounded-md">
                                    <strong class="text-gray-900">Presten atención:</strong> Si eligió una cuenta de Activo arriba (Efectivo/Banco), el estado debería ser Pagado. Si eligió Pasivo (Cuentas por Pagar), debería ser Pendiente.
                                </p>
                            </div>

                            <!-- Concepto -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Concepto o Descripción Interna</label>
                                <textarea wire:model="description" rows="2" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('description') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors" placeholder="Razones del gasto, personal que lo autorizó, etc."></textarea>
                                @error('description') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="bg-[#f8fafc] px-6 py-5 flex flex-col sm:flex-row-reverse sm:px-8 border-t border-gray-200 gap-3 items-center">
                        <button wire:click="save" type="button" class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-base font-bold text-white shadow-md hover:bg-indigo-500 sm:w-auto transition-colors disabled:opacity-50" wire:loading.attr="disabled">
                            <svg class="h-5 w-5 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            <span wire:loading.remove wire:target="save">Registrar Gasto y Cuadrar Pliza</span>
                            <span wire:loading wire:target="save">Contabilizando...</span>
                        </button>
                        <button wire:click="closeModal" type="button" class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:w-auto transition-colors">
                            Cancelar Operación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Modal para SALDAR (Pagar) Cuenta por Pagar -->
    @if($showPayModal && $expense_to_pay)
    <div class="relative z-50" aria-labelledby="modal-pay-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100">
                    
                    <div class="bg-indigo-600 px-6 py-6 flex items-start gap-4">
                        <div class="rounded-xl bg-indigo-500/50 p-3 shadow-inner">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold leading-6 text-white" id="modal-pay-title">
                                Saldar Factura / Cuenta por Pagar
                            </h3>
                            <p class="text-indigo-100 text-sm mt-1">Se generará un asiento contable acreditando Caja/Bancos y debitando esta CxP.</p>
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-5 sm:p-8">
                        
                        <!-- Resumen del Gasto -->
                        <div class="mb-6 rounded-2xl bg-gray-50 p-5 border border-gray-200">
                            <h4 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">Detalle de la Deuda</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="text-gray-500 font-medium">Proveedor:</span> <br><span class="font-bold text-gray-900">{{ $expense_to_pay->supplier->name ?? 'N/A' }}</span></div>
                                <div><span class="text-gray-500 font-medium">Factura / Ref:</span> <br><span class="font-bold text-gray-900 font-mono">{{ $expense_to_pay->reference_number ?: 'N/A' }}</span></div>
                                <div><span class="text-gray-500 font-medium">NCF:</span> <br><span class="font-bold text-gray-900 font-mono">{{ $expense_to_pay->ncf ?: 'N/A' }}</span></div>
                                <div><span class="text-gray-500 font-medium">Cuenta Pasivo (A debitar):</span> <br><span class="font-bold text-gray-900">{{ $expense_to_pay->paymentAccount->name ?? 'N/A' }}</span></div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-500">Monto a Saldar:</span>
                                <span class="text-2xl font-black text-indigo-600">${{ number_format($expense_to_pay->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-y-6">
                            
                            <!-- Cuenta de Origen (Crédito) -->
                            <div class="rounded-xl border-2 border-dashed border-orange-200 p-4 bg-orange-50/30">
                                <label class="block text-sm font-bold leading-6 text-orange-900 mb-1 flex items-center justify-between">
                                    <span>¿De dónde sale el dinero? (Crédito - Disminuye Activo)</span>
                                    <span class="text-xs bg-orange-100 text-orange-800 px-2 py-0.5 rounded-full">+ Haber</span>
                                </label>
                                <select wire:model="pay_account_origin_id" class="mt-2 block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-orange-900 shadow-sm ring-1 ring-inset {{ $errors->has('pay_account_origin_id') ? 'ring-red-300 focus:ring-red-600' : 'ring-orange-300 focus:ring-orange-600' }} sm:text-sm bg-white font-medium">
                                    <option value="">Selecciona Caja o Banco...</option>
                                    @foreach($assetAccounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                @error('pay_account_origin_id') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Diario -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Diario Destino (Para el registro)</label>
                                <select wire:model="selected_journal_id" class="block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('selected_journal_id') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                    <option value="">Seleccione el Diario...</option>
                                    @foreach($journals as $j)
                                        <option value="{{ $j->id }}">{{ $j->name }}</option>
                                    @endforeach
                                </select>
                                @error('selected_journal_id') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </div>
                    
                    <div class="bg-[#f8fafc] px-6 py-5 flex flex-col sm:flex-row-reverse sm:px-8 border-t border-gray-200 gap-3 items-center">
                        <button wire:click="processPayment" type="button" class="inline-flex w-full justify-center items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-base font-bold text-white shadow-md hover:bg-indigo-500 sm:w-auto transition-colors disabled:opacity-50" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="processPayment">Procesar Pago y Contabilizar</span>
                            <span wire:loading wire:target="processPayment">Procesando...</span>
                        </button>
                        <button wire:click="closePayModal" type="button" class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:w-auto transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <style>
        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fade-in-up 0.4s ease-out forwards;
        }
    </style>
</div>
