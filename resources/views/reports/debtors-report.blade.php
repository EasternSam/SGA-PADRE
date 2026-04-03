<div id="printable-area" class="p-6 bg-white shrink-0">
    <div class="mb-8 border-b-2 border-red-600 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 uppercase">Cuentas por Cobrar (Morosos)</h2>
        <p class="text-sm text-gray-500">Al corte de caja actual</p>
    </div>

    @if(empty($data['students']))
        <div class="p-8 text-center text-gray-500 italic bg-gray-50 rounded-lg border border-gray-200">
            No se detectaron deudas pendientes en el sistema en este momento.
        </div>
    @else
        <div class="bg-red-50 text-red-800 rounded-xl p-6 shadow-sm border border-red-200 flex justify-between items-center mb-8">
            <div>
                <h3 class="text-sm font-bold opacity-80 uppercase tracking-widest">Cartera de Crédito Acumulada</h3>
                <p class="text-xs opacity-75 mt-1 text-red-600">Dinero pendiente de cobro en estado activo/mora.</p>
            </div>
            <div class="text-3xl font-black">
                RD$ {{ number_format($data['grand_total'], 2) }}
            </div>
        </div>

        <!-- Tabla Principal -->
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante / Contacto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concepto(s) Adeudado(s)</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Vencido</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acción Sugerida</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($data['students'] as $info)
                        <tr class="hover:bg-red-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="ml-0">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ $info['student']->first_name }} {{ $info['student']->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center">
                                            <svg class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            {{ $info['student']->mobile_phone ?? 'No registrado' }}
                                        </div>
                                        @if($info['student']->email)
                                            <div class="text-xs text-gray-400 font-mono">{{ $info['student']->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <ul class="list-disc pl-4 space-y-1">
                                    @foreach($info['debts'] as $debt)
                                        <li class="text-xs">
                                            {{ $debt->paymentConcept ? $debt->paymentConcept->name : 'Concepto General' }} 
                                            <span class="text-red-500 font-medium">(Venc.: {{ \Carbon\Carbon::parse($debt->due_date)->format('d/m/Y') }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-red-600 text-right">
                                RD$ {{ number_format($info['total_debt'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="https://wa.me/1{{ preg_replace('/[^0-9]/', '', $info['student']->mobile_phone) }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-bold rounded text-white bg-green-500 hover:bg-green-600 shadow-sm transition">
                                    <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12c0 2.17.68 4.18 1.83 5.86L2 22l4.24-1.7C7.8 21.09 9.83 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm4.35 14.07c-.18.5-.96.95-1.39 1.04-.42.08-1 .18-3.08-.68-2.5-1.04-4.14-3.6-4.27-3.77-.13-.17-1.02-1.36-1.02-2.6 0-1.24.64-1.85.87-2.09.21-.21.46-.26.61-.26.15 0 .31 0 .44.02.16.02.37-.06.57-.49.2-.43.51-1.26.56-1.36.05-.11.1-.18.02-.28-.08-.11-.18-.18-.36-.36-.18-.18-.39-.39-.56-.56-.2-.2-.41-.42-.17-.83.24-.41 1.07-1.74 1.91-2.43.91-.74 1.34-.95 1.76-.74.43.21 1.04.52 1.34 2.14.04.22-.05.43-.19.61-.23.32-.51.62-.75.9-.22.25-.46.52-.2.93.27.42.85 1.18 1.48 1.74.82.72 1.57 1.03 2.01 1.21.42.18.66.15.91-.12.25-.27 1.07-1.23 1.36-1.65.29-.42.57-.35.96-.21.4.14 2.51 1.18 2.94 1.39.43.21.72.33.82.51.11.19.11 1.08-.07 1.58z" clip-rule="evenodd" /></svg>
                                    WhatsApp
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
