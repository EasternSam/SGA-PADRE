<div class="p-4 sm:p-8 lg:p-10">
    <!-- Page Header -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-200">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </span>
                Estados Financieros
            </h1>
            <p class="mt-2 text-base text-gray-600">Visualiza la salud financiera y la rentabilidad de la institución con reportes en tiempo real.</p>
        </div>
        <div class="sm:flex-none">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 hover:text-indigo-600 transition-all duration-200">
                <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.552c.377.046.752.097 1.126.153A2.212 2.212 0 0118 8.653v5.183A2.25 2.25 0 0115.75 16h-.75v2.25A1.75 1.75 0 0113.25 20h-6.5A1.75 1.75 0 015 18.25V16h-.75A2.25 2.25 0 012 13.836V8.653c0-1.082.775-2.034 1.874-2.198.374-.056.75-.107 1.127-.153L5 6.25v-3.5zm8.5 3.397a41.533 41.533 0 00-7 0V2.75a.25.25 0 01.25-.25h6.5a.25.25 0 01.25.25v3.397zM6.5 18a.25.25 0 00.25.25h6.5a.25.25 0 00.25-.25v-4H6.5v4z3.036 9.172a1.082 1.082 0 00-1.036.096A1.082 1.082 0 002 9.68v4.156A.75.75 0 002.75 14.5h.75v-2c0-.966.784-1.75 1.75-1.75h9.5c.966 0 1.75.784 1.75 1.75v2h.75a.75.75 0 00.75-.75V9.68a1.082 1.082 0 00-.036-.412 1.082 1.082 0 00-1.036-.096l-3.355.776a40.063 40.063 0 01-7.046 0l-3.355-.776zM11 12H9v-1h2v1z" clip-rule="evenodd" />
                </svg>
                Imprimir / PDF
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 p-6 mb-8 transition-all hover:shadow-md">
        <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-12 md:items-end">
            <div class="sm:col-span-12 md:col-span-4">
                <label class="block text-sm font-semibold leading-6 text-gray-900">Tipo de Documento Financiero</label>
                <select wire:model.live="report_type" class="mt-2 block w-full rounded-xl border-0 py-2.5 pl-4 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                    <option value="balance_sheet">Balance General (Estado de Situación)</option>
                    <option value="income_statement">Estado de Resultados (Pérdidas y Ganancias)</option>
                </select>
            </div>
            
            <div class="sm:col-span-12 md:col-span-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
                @if($report_type === 'income_statement')
                    <div>
                        <label class="block text-sm font-semibold leading-6 text-gray-900">Período Desde</label>
                        <input type="date" wire:model.live="date_from" class="mt-2 block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm transition-all focus:bg-white bg-gray-50">
                    </div>
                @else
                    <div class="flex items-center">
                        <div class="rounded-xl bg-blue-50 p-3 ring-1 ring-inset ring-blue-100 flex gap-3 items-center w-full">
                            <svg class="h-5 w-5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-sm text-blue-800 leading-tight">El Balance General muestra saldos acumulados históricamente hasta la fecha de corte seleccionada.</span>
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold leading-6 text-gray-900">Fecha de Corte (Hasta)</label>
                    <input type="date" wire:model.live="date_to" class="mt-2 block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm transition-all focus:bg-white bg-gray-50">
                </div>
            </div>
        </div>
    </div>

    <!-- The Report Container -->
    <div id="printable-area" class="bg-white rounded-2xl shadow-xl ring-1 ring-gray-200 overflow-hidden relative">
        <!-- Decoration bar -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600"></div>
        
        <!-- Header Documento -->
        <div class="px-8 py-12 md:px-16 text-center border-b border-gray-100 bg-[#f8fafc]">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 uppercase">SGA CENTU</h2>
            <h3 class="text-xl font-bold text-gray-700 mt-2 tracking-wide text-indigo-900">
                {{ $report_type === 'balance_sheet' ? 'BALANCE GENERAL' : 'ESTADO DE RESULTADOS' }}
            </h3>
            <p class="text-gray-500 font-medium mt-1">
                @if($report_type === 'income_statement')
                    Del {{ date('d/m/Y', strtotime($date_from)) }} al {{ date('d/m/Y', strtotime($date_to)) }}
                @else
                    Al {{ date('d/m/Y', strtotime($date_to)) }}
                @endif
            </p>
            <span class="inline-flex mt-4 items-center rounded-md bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 border border-gray-200 shadow-sm">
                Expresado en Pesos Dominicanos (DOP)
            </span>
        </div>

        <div class="px-6 py-10 md:px-16 md:py-14">
            {{-- ====== ESTADO DE RESULTADOS ====== --}}
            @if($report_type === 'income_statement')
                <div class="max-w-4xl mx-auto space-y-12">
                    <!-- Ingresos -->
                    <div>
                        <div class="flex items-center gap-3 border-b-2 border-indigo-900 pb-3 mb-6">
                            <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center text-green-700">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 uppercase tracking-wide">Ingresos Operativos</h4>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse($report['revenues'] as $acc)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-4 pl-6 pr-3 text-sm font-medium text-gray-700 w-3/4">{{ $acc->code }} - {{ $acc->name }}</td>
                                            <td class="py-4 px-6 text-sm text-right font-semibold text-gray-900">{{ number_format($acc->balance, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="py-8 text-center text-sm text-gray-400 italic">No hay registros de ingresos en este período.</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-indigo-50/50 border-t border-indigo-100">
                                    <tr>
                                        <td class="py-4 pl-6 text-sm font-bold text-indigo-900 uppercase tracking-wide">Total Ingresos</td>
                                        <td class="py-4 px-6 text-right font-extrabold text-indigo-900 text-lg">{{ number_format($report['total_revenue'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Gastos -->
                    <div>
                        <div class="flex items-center gap-3 border-b-2 border-indigo-900 pb-3 mb-6">
                            <div class="h-8 w-8 rounded-lg bg-red-100 flex items-center justify-center text-red-700">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </div>
                            <h4 class="text-xl font-bold text-gray-900 uppercase tracking-wide">Gastos Operativos</h4>
                        </div>
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse($report['expenses'] as $acc)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="py-4 pl-6 pr-3 text-sm font-medium text-gray-700 w-3/4">{{ $acc->code }} - {{ $acc->name }}</td>
                                            <td class="py-4 px-6 text-sm text-right font-semibold text-gray-900">{{ number_format($acc->balance, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="py-8 text-center text-sm text-gray-400 italic">No hay registros de gastos en este período.</td></tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-indigo-50/50 border-t border-indigo-100">
                                    <tr>
                                        <td class="py-4 pl-6 text-sm font-bold text-indigo-900 uppercase tracking-wide">Total Gastos</td>
                                        <td class="py-4 px-6 text-right font-extrabold text-indigo-900 text-lg">{{ number_format($report['total_expense'], 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Utilidad Neta -->
                    <div class="rounded-2xl {{ $report['net_income'] >= 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }} p-8 flex flex-col sm:flex-row items-center justify-between shadow-sm">
                        <div class="flex items-center gap-4 mb-4 sm:mb-0">
                            <div class="rounded-full p-3 {{ $report['net_income'] >= 0 ? 'bg-green-100' : 'bg-red-100' }} border {{ $report['net_income'] >= 0 ? 'border-green-200' : 'border-red-200' }}">
                                <svg class="w-8 h-8 {{ $report['net_income'] >= 0 ? 'text-green-600' : 'text-red-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    @if($report['net_income'] >= 0)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest leading-tight">Resultado del Ejercicio</h3>
                                <p class="text-2xl font-extrabold {{ $report['net_income'] >= 0 ? 'text-green-800' : 'text-red-800' }} tracking-tight">
                                    {{ $report['net_income'] >= 0 ? 'Utilidad Neta' : 'Pérdida Neta' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-4xl font-black {{ $report['net_income'] >= 0 ? 'text-green-700' : 'text-red-700' }} tracking-tight">
                            RD$ {{ number_format($report['net_income'], 2) }}
                        </div>
                    </div>
                </div>

            {{-- ====== BALANCE GENERAL ====== --}}
            @elseif($report_type === 'balance_sheet')
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">
                    <!--================ Columna Izquierda: Activos ================-->
                    <div class="flex flex-col h-full">
                        <div class="flex-grow">
                            <h4 class="text-xl font-black text-gray-900 border-b-[3px] border-indigo-900 pb-3 mb-6 uppercase tracking-wider flex justify-between items-end">
                                Activos
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-normal hidden sm:inline">(Recursos de la Empresa)</span>
                            </h4>
                            
                            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-8">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @forelse($report['assets'] as $acc)
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="py-4 pl-6 pr-3 text-sm font-medium text-gray-700 w-3/4">{{ $acc->code }} - {{ $acc->name }}</td>
                                                <td class="py-4 pr-6 text-right text-sm font-semibold text-gray-900">{{ number_format($acc->balance, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="py-8 text-center text-sm text-gray-400 italic">No hay cuentas de activo registradas.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Total Activos (Bottom Aligned via flex-grow on top container) -->
                        <div class="rounded-2xl bg-[#ebf4ff] border border-blue-100 p-6 flex flex-col sm:flex-row justify-between sm:items-center mt-auto shadow-sm">
                            <span class="font-bold text-lg text-blue-900 uppercase tracking-widest mb-2 sm:mb-0">Total Activos</span>
                            <span class="font-black text-2xl text-blue-900 border-b-4 border-blue-600 sm:border-b-[4px] double">{{ number_format($report['total_assets'], 2) }}</span>
                        </div>
                    </div>

                    <!--================ Columna Derecha: Pasivos y Capital ================-->
                    <div class="flex flex-col h-full">
                        <div class="flex-grow flex flex-col">
                            <!-- Pasivos -->
                            <div class="mb-10">
                                <h4 class="text-xl font-black text-gray-900 border-b-[3px] border-gray-800 pb-3 mb-6 uppercase tracking-wider flex justify-between items-end">
                                    Pasivos
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-normal hidden sm:inline">(Obligaciones)</span>
                                </h4>
                                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-4">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            @forelse($report['liabilities'] as $acc)
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="py-4 pl-6 pr-3 text-sm font-medium text-gray-700 w-3/4">{{ $acc->code }} - {{ $acc->name }}</td>
                                                    <td class="py-4 pr-6 text-right text-sm font-semibold text-gray-900">{{ number_format($acc->balance, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="2" class="py-8 text-center text-sm text-gray-400 italic">No hay obligaciones vigentes.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-6 py-4 flex justify-between items-center bg-gray-50 rounded-xl border border-gray-200">
                                    <span class="font-bold text-gray-700 uppercase tracking-wide text-sm">Suma Pasivos</span>
                                    <span class="font-black text-gray-900 text-lg">{{ number_format($report['total_liabilities'], 2) }}</span>
                                </div>
                            </div>

                            <!-- Capital -->
                            <div class="mb-8 flex-grow">
                                <h4 class="text-xl font-black text-gray-900 border-b-[3px] border-indigo-800 pb-3 mb-6 uppercase tracking-wider flex justify-between items-end">
                                    Capital
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-normal hidden sm:inline">(Patrimonio Neto)</span>
                                </h4>
                                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-4">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            @foreach($report['equity'] as $acc)
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="py-4 pl-6 pr-3 text-sm font-medium text-gray-700 w-3/4">{{ $acc->code }} - {{ $acc->name }}</td>
                                                    <td class="py-4 pr-6 text-right text-sm font-semibold text-gray-900">{{ number_format($acc->balance, 2) }}</td>
                                                </tr>
                                            @endforeach
                                            <!-- Injection of Net Income -->
                                            <tr class="bg-purple-50/50 hover:bg-purple-50 transition-colors">
                                                <td class="py-4 pl-6 pr-3 text-sm font-bold text-purple-800 flex items-center gap-2">
                                                    <div class="p-1 rounded bg-purple-100 flex-shrink-0">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                                    </div>
                                                    Utilidad/Pérdida del Ejercicio
                                                </td>
                                                <td class="py-4 pr-6 text-right text-sm font-black text-purple-900">{{ number_format($report['historical_net_income'], 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="px-6 py-4 flex justify-between items-center bg-purple-50 rounded-xl border border-purple-100">
                                    <span class="font-bold text-purple-900 uppercase tracking-wide text-sm">Suma Capital</span>
                                    <span class="font-black text-purple-900 text-lg">{{ number_format($report['total_equity'], 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Total Pasivo + Capital -->
                        <div class="rounded-2xl bg-gray-900 border border-gray-800 px-6 py-6 flex flex-col sm:flex-row justify-between sm:items-center mt-auto shadow-xl relative overflow-hidden">
                            <!-- Subtle Grid Background Decoration -->
                            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 16px 16px;"></div>
                            
                            <div class="relative z-10 hidden sm:block">
                                <span class="font-extrabold text-[10px] text-gray-400 uppercase tracking-widest block mb-1">Ecuación Contable Perfecta</span>
                                <span class="font-black text-lg text-white uppercase tracking-wider">Pasivo + Capital</span>
                            </div>
                            <!-- Mobile layout -->
                            <div class="relative z-10 sm:hidden mb-2">
                                <span class="font-black text-sm text-gray-300 uppercase tracking-widest block">Total P + C</span>
                            </div>
                            
                            <span class="relative z-10 font-black text-2xl text-white border-b-4 border-indigo-400 double">{{ number_format($report['total_liabilities_and_equity'], 2) }}</span>
                        </div>
                        
                        <!-- Verificación de Cuadre -->
                        <div class="mt-6 flex justify-end">
                            @if(round($report['total_assets'], 2) === round($report['total_liabilities_and_equity'], 2))
                                <div class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-green-50 border border-green-200 shadow-sm">
                                    <div class="rounded-full bg-green-500 p-1.5 shadow-sm">
                                        <svg class="h-4 w-4 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                                    </div>
                                    <span class="text-sm font-bold text-green-800 tracking-widest uppercase">Balance Cuadrado</span>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-3 px-6 py-4 rounded-2xl bg-red-50 border border-red-300 shadow-sm animate-pulse">
                                    <div class="rounded-full bg-red-600 p-1.5 shadow-sm">
                                        <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-red-900 uppercase">Error de Descuadre</span>
                                        <span class="text-xs text-red-700 font-bold mt-0.5">Diferencia: RD$ {{ number_format(abs($report['total_assets'] - $report['total_liabilities_and_equity']), 2) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Styles for Print -->
    <style>
        .double { border-bottom-style: double !important; }
        
        @media print {
            body * { visibility: hidden; }
            #printable-area, #printable-area * { visibility: visible; }
            #printable-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            .border-b-4.double { border-bottom-style: double !important; }
            /* Clean Print Adjustments */
            .bg-indigo-50, .bg-purple-50, .bg-red-50, .bg-green-50, .bg-gray-50, .bg-[#f8fafc], .bg-[#ebf4ff] {
                background-color: transparent !important;
            }
            .bg-gray-900 {
                background-color: white !important;
                border: 2px solid #111827 !important;
            }
            .text-white { color: #111827 !important; }
            .hidden.print\:block { display: block !important; }
            button { display: none !important; }
        }
    </style>
</div>
