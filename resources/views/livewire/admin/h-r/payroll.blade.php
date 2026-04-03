<div class="space-y-6 px-4 sm:px-6 lg:px-8 max-w-[90rem] mx-auto mt-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-sga-text">Gestión de Nóminas</h2>
            <p class="mt-1 text-sm text-sga-text-light">Cálculo automatizado cruzando contratos fijos y ponches del personal.</p>
        </div>
        <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-2">
            <button wire:click="create" class="inline-flex items-center justify-center rounded-md bg-sga-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sga-primary-dark">
                <i class="fas fa-calculator mr-2"></i> Generar Lote de Nómina
            </button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="rounded-md bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="ml-3"><h3 class="text-sm font-medium text-green-800">{{ session('success') }}</h3></div>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="rounded-md bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <div class="ml-3"><h3 class="text-sm font-medium text-red-800">{{ session('error') }}</h3></div>
            </div>
        </div>
    @endif

    <!-- Tabla -->
    <div class="overflow-hidden rounded-lg bg-sga-card shadow ring-1 ring-black ring-opacity-5">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50"><tr>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Referencia del Lote</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Período Evaluado</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Empleados</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Total Neto a Pagar</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Estado</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-right">Acciones</th>
            </tr></thead><tbody class="bg-white divide-y divide-gray-100">
                @forelse ($payrolls as $pay)
                    <tr class="hover:bg-gray-50/80 transition-colors duration-150 group">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="font-bold text-sga-text">{{ $pay->name }}</div>
                            <div class="text-xs text-sga-text-light">Lote #{{ str_pad($pay->id, 5, '0', STR_PAD_LEFT) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="text-sga-text text-sm">
                                {{ $pay->start_date->format('d/M/Y') }} <br>
                                <span class="text-xs text-sga-text-light">al</span> {{ $pay->end_date->format('d/M/Y') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="text-sga-text font-medium">{{ $pay->items_count }} calculados</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="text-green-700 font-bold text-lg">RD$ {{ number_format($pay->total_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if($pay->status === 'Borrador')
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-edit mr-1"></i> Borrador
                                </span>
                            @else
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Pagado / Desembolsado
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
                            @if($pay->status === 'Borrador')
                                <button wire:click="approveAndPay({{ $pay->id }})" wire:confirm="¿Estás seguro de Aprobar esta nómina? Esto inyectará el Gasto en Contabilidad restando del Flujo de Efectivo irreversiblemente." class="text-green-600 font-bold hover:text-green-800 mr-3 text-sm border-b border-green-600">
                                    APROBAR Y PAGAR
                                </button>
                                <button wire:click="delete({{ $pay->id }})" wire:confirm="¿Eliminar este lote en borrador?" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            @else
                                <button class="text-gray-400 cursor-not-allowed" title="Operación Inmutable">
                                    <i class="fas fa-lock"></i> Contabilizado
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-gray-50/80 transition-colors duration-150 group">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center" colspan="6">
                            No hay nóminas procesadas hasta la fecha.
                        </td>
                    </tr>
                @endforelse
            </tbody></table></div>
        <div class="px-4 py-3 border-t border-sga-gray">
            {{ $payrolls->links() }}
        </div>
    </div>

    <!-- Modal Formulario -->
    <x-modal name="payroll-modal" maxWidth="lg">
        <form wire:submit.prevent="generate">
            <div class="bg-sga-card px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg font-bold leading-6 text-sga-text mb-2 border-b border-sga-gray pb-2">
                            <i class="fas fa-cogs mr-1 text-sga-primary"></i> Automata de Nómina
                        </h3>
                        <p class="text-xs text-sga-text-light mb-4">El sistema iterará sobre todos los empleados, calculando contratos fijos exactos y sumando horas/días de docentes basándose en los registros de ZKTeco.</p>
                        
                        <label class="block text-sm font-medium text-sga-text mt-3">Título del Lote</label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary sm:text-sm">

                        <label class="block text-sm font-medium text-sga-text mt-3">Tipo de Ciclo de Pago</label>
                        <select wire:model="cycle_type" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary sm:text-sm">
                            <option value="Quincenal">Quincenal (Divide Salarios Fijos a la mitad)</option>
                            <option value="Mensual">Mensual Completo</option>
                        </select>

                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-sga-text">Lectura ZKTeco Desde</label>
                                <input type="date" wire:model="start_date" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-sga-text">Hasta (Corte)</label>
                                <input type="date" wire:model="end_date" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm sm:text-sm">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="bg-indigo-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-sga-primary px-3 py-2 text-sm font-bold text-white shadow-sm hover:bg-sga-primary-dark sm:ml-3 sm:w-auto border border-blue-800">
                    <i class="fas fa-bolt mr-2 mt-1"></i> Iniciar Procesamiento Masivo
                </button>
                <button type="button" x-on:click="$dispatch('close')" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    Cancelar
                </button>
            </div>
        </form>
    </x-modal>
</div>
