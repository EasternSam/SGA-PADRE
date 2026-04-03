<div class="space-y-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-sga-text">Mis Nóminas y Pagos</h2>
            <p class="mt-1 text-sm text-sga-text-light">Consulta tu historial de pagos, deducciones y comprobantes.</p>
        </div>
    </div>

    @if(!$employee)
        <div class="rounded-md bg-yellow-50 p-4 border border-yellow-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">No tienes un expediente de empleado asociado a tu cuenta. Contacta a RRHH.</p>
                </div>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-sga-card shadow rounded-lg p-5 border-l-4 border-sga-primary">
                <div class="text-sm font-bold text-sga-text-light uppercase">Salario Base Fijo</div>
                <div class="mt-1 text-2xl font-bold text-sga-text">RD$ {{ number_format($employee->base_salary, 2) }}</div>
            </div>
            <div class="bg-sga-card shadow rounded-lg p-5 border-l-4 border-green-500">
                <div class="text-sm font-bold text-sga-text-light uppercase">Tarifa por Hora Extra/Docencia</div>
                <div class="mt-1 text-2xl font-bold text-sga-text">RD$ {{ number_format($employee->hourly_rate, 2) }} <span class="text-xs text-gray-400">/hr</span></div>
            </div>
            <div class="bg-sga-card shadow rounded-lg p-5 border-l-4 border-yellow-500">
                <div class="text-sm font-bold text-sga-text-light uppercase">Tipo de Contrato</div>
                <div class="mt-1 text-2xl font-bold text-sga-text">{{ $employee->contract_type }}</div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow ring-1 ring-black ring-opacity-5">
            <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50/50"><tr>
                    <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Referencia de Nómina</th>
                    <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Ciclo de Pago</th>
                    <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Cálculo/Horas</th>
                    <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Descuentos Ley</th>
                    <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Neto a Cobrar</th>
                    <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Estado</th>
                </tr></thead><tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50/80 transition-colors duration-150 group">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="font-bold text-sga-text">{{ $item->payroll->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="text-sga-text text-sm">
                                    {{ \Carbon\Carbon::parse($item->payroll->start_date)->format('d/M/Y') }} <br>
                                    <span class="text-xs text-center">hasta</span><br>
                                    {{ \Carbon\Carbon::parse($item->payroll->end_date)->format('d/M/Y') }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="text-sga-text-light text-xs font-mono max-w-[150px] truncate" title="{{ $item->details['formula'] ?? 'N/A' }}">
                                    {{ $item->details['formula'] ?? 'N/A' }}
                                </div>
                                <div class="font-bold">Bruto: RD$ {{ number_format($item->base_amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="text-red-500 text-sm">- RD$ {{ number_format($item->deductions, 2) }}</div>
                                <div class="text-[10px] text-gray-400">TSS/AFP (5.91%)</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div class="text-green-700 font-bold text-lg">RD$ {{ number_format($item->net_amount, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if($item->payroll->status === 'Borrador')
                                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-yellow-100 text-yellow-800">
                                        Procesando
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-green-100 text-green-800">
                                        Desembolsado
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="hover:bg-gray-50/80 transition-colors duration-150 group">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center" colspan="6">
                                Ningún pago de nómina ha sido emitido a su nombre todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody></table></div>
        </div>
    @endif
</div>
