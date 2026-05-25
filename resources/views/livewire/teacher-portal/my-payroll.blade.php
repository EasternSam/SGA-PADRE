<div class="space-y-6">
    <!-- Encabezado -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Mis Nóminas y Pagos</h2>
            <p class="mt-1 text-sm text-gray-500 font-medium">Consulta tu historial de pagos, deducciones y comprobantes de nómina oficiales.</p>
        </div>
    </div>

    @if(!$employee)
        <div class="rounded-xl bg-amber-50 p-4 border border-amber-200 shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0 text-amber-500 mt-0.5">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-amber-800">Expediente de Empleado no Vinculado</h3>
                    <p class="text-xs text-amber-700 mt-1">No tienes un expediente de empleado asociado a tu cuenta de usuario docente. Por favor, comunícate con el departamento de Recursos Humanos o Administración para registrar tu ficha de nómina.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Tarjetas de Información Financiera -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="workspace-panel bg-white shadow-sm rounded-xl p-5 border border-gray-200/80 border-l-4 border-l-indigo-600">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Salario Base Fijo</div>
                <div class="mt-2 text-2xl font-extrabold text-gray-900 tracking-tight">RD$ {{ number_format($employee->base_salary, 2) }}</div>
                <span class="text-[10px] text-gray-400 font-semibold mt-1 block">Mensual ordinario</span>
            </div>
            
            <div class="workspace-panel bg-white shadow-sm rounded-xl p-5 border border-gray-200/80 border-l-4 border-l-green-600">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tarifa de Docencia Extra</div>
                <div class="mt-2 text-2xl font-extrabold text-gray-900 tracking-tight">RD$ {{ number_format($employee->hourly_rate, 2) }} <span class="text-xs text-gray-400 font-bold">/hora</span></div>
                <span class="text-[10px] text-gray-400 font-semibold mt-1 block">Horas adicionales contratadas</span>
            </div>
            
            <div class="workspace-panel bg-white shadow-sm rounded-xl p-5 border border-gray-200/80 border-l-4 border-l-amber-600">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tipo de Contrato</div>
                <div class="mt-2 text-2xl font-extrabold text-gray-900 tracking-tight">{{ $employee->contract_type }}</div>
                <span class="text-[10px] text-gray-400 font-semibold mt-1 block">Estatus legal del colaborador</span>
            </div>
        </div>

        <!-- Tabla Historial -->
        <div class="workspace-panel overflow-hidden rounded-xl bg-white border border-gray-200/80 shadow-sm mt-6">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/30">
                <h3 class="text-base font-bold text-gray-900">Historial de Desembolsos</h3>
                <p class="text-xs text-gray-400 mt-0.5">Pagos procesados y transferidos por la institución.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Referencia de Nómina</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Ciclo de Pago</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Detalles y Bruto</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Descuentos Ley</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Neto Transferido</th>
                            <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($items as $item)
                            <tr class="hover:bg-gray-50/50 transition-colors duration-150 group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="font-bold text-gray-900">{{ $item->payroll->name }}</div>
                                    <span class="text-[10px] text-gray-400 font-semibold block mt-0.5">ID: #{{ $item->payroll->id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="text-gray-700 text-xs font-semibold">
                                        {{ \Carbon\Carbon::parse($item->payroll->start_date)->format('d/M/Y') }} 
                                        <span class="text-gray-400 font-normal">al</span>
                                        {{ \Carbon\Carbon::parse($item->payroll->end_date)->format('d/M/Y') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-gray-400 text-xs font-semibold max-w-[200px] truncate" title="{{ $item->details['formula'] ?? 'N/A' }}">
                                        {{ $item->details['formula'] ?? 'N/A' }}
                                    </div>
                                    <div class="font-extrabold text-gray-800 mt-1 text-xs">Bruto: RD$ {{ number_format($item->base_amount, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-red-600 font-bold text-sm">- RD$ {{ number_format($item->deductions, 2) }}</div>
                                    <div class="text-[10px] text-gray-400 font-medium">TSS/AFP de Ley (5.91%)</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="text-green-700 font-black text-base">RD$ {{ number_format($item->net_amount, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($item->payroll->status === 'Borrador')
                                        <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-bold text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                            Procesando
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20">
                                            Desembolsado
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-6 py-12 whitespace-nowrap text-sm text-gray-500 text-center font-semibold" colspan="6">
                                    Ningún desembolso de nómina ha sido emitido a su nombre todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
