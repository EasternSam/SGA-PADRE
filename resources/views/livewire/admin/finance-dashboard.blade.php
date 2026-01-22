<div class="min-h-screen bg-gray-50 pb-12" wire:init="loadChart">
    
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ __('Finanzas y Caja') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Visión general del estado financiero de la academia.</p>
            </div>
            
            <div class="flex items-center gap-3">
                {{-- BOTÓN REGISTRAR COBRO --}}
                <button 
                    type="button"
                    onclick="Livewire.dispatch('openPaymentModal')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm"
                >
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    {{ __('Registrar Cobro') }}
                </button>

                {{-- Filtro de Fecha Rápido --}}
                <div class="flex bg-white rounded-lg shadow-sm p-1 border border-gray-200">
                    @foreach(['all' => 'Todo', 'this_month' => 'Este Mes', 'today' => 'Hoy'] as $key => $label)
                        <button 
                            wire:click="$set('dateFilter', '{{ $key }}')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition-all {{ $dateFilter === $key ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-8 space-y-8">

        {{-- 1. KPIs FINANCIEROS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Ingresos -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-emerald-100 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wide relative z-10">Ingresos Totales</p>
                <h3 class="text-3xl font-black text-gray-900 mt-2 relative z-10">RD$ {{ number_format($totalIncome, 2) }}</h3>
                <div class="mt-4 flex items-center text-xs font-medium text-emerald-600 relative z-10">
                    <span class="bg-emerald-100 px-2 py-1 rounded-full">Cobrado</span>
                </div>
            </div>

            <!-- Cuentas por Cobrar -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-amber-100 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-amber-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wide relative z-10">Cuentas por Cobrar</p>
                <h3 class="text-3xl font-black text-gray-900 mt-2 relative z-10">RD$ {{ number_format($totalPending, 2) }}</h3>
                <div class="mt-4 flex items-center text-xs font-medium text-amber-600 relative z-10">
                    <span class="bg-amber-100 px-2 py-1 rounded-full">Pendiente</span>
                </div>
            </div>

            <!-- Transacciones -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-blue-100 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wide relative z-10">Movimientos</p>
                <h3 class="text-3xl font-black text-gray-900 mt-2 relative z-10">{{ number_format($transactionsCount) }}</h3>
                <div class="mt-4 flex items-center text-xs font-medium text-blue-600 relative z-10">
                    <span class="bg-blue-100 px-2 py-1 rounded-full">Registros</span>
                </div>
            </div>
        </div>

        {{-- 2. GRÁFICO DE FLUJO DE CAJA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 relative overflow-hidden">
            <h3 class="text-lg font-bold text-gray-900 mb-6">Flujo de Caja (Últimos 6 Meses)</h3>
            
            <div class="h-[350px] w-full relative">
                @if(!$readyToLoad)
                    <div class="absolute inset-0 flex items-center justify-center bg-white/80 z-10 backdrop-blur-sm">
                        <div class="flex flex-col items-center animate-pulse">
                            <div class="h-8 w-8 border-b-2 border-indigo-600 rounded-full animate-spin mb-2"></div>
                            <span class="text-xs text-gray-500 font-medium">Cargando gráfico...</span>
                        </div>
                    </div>
                @endif
                <div id="financeChart" wire:ignore class="w-full h-full"></div>
            </div>
        </div>

        {{-- 3. TABLA DE TRANSACCIONES --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h3 class="text-lg font-bold text-gray-900">Transacciones Recientes</h3>
                
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    {{-- Buscador --}}
                    <div class="relative">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar alumno o ref..." 
                               class="w-full sm:w-64 pl-9 pr-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>

                    {{-- Filtro Estado --}}
                    <select wire:model.live="statusFilter" class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:w-auto p-2 shadow-sm">
                        <option value="">Todos los estados</option>
                        <option value="Completado">Completado</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Fallido">Fallido</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3">Fecha</th>
                            <th class="px-6 py-3">Estudiante</th>
                            <th class="px-6 py-3">Concepto</th>
                            <th class="px-6 py-3">Método</th>
                            <th class="px-6 py-3 text-center">Estado</th>
                            <th class="px-6 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payments as $payment)
                            <tr class="bg-white hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $payment->created_at->format('d/m/Y') }}
                                    <span class="block text-xs text-gray-400 font-normal">{{ $payment->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ substr($payment->student->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $payment->student->user->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-400">{{ $payment->student->user->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    {{ $payment->paymentConcept->name ?? 'General' }}
                                    @if($payment->transaction_id)
                                        <span class="block text-xs text-gray-400 font-mono mt-0.5">Ref: {{ $payment->transaction_id }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $payment->gateway }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statusClasses = match($payment->status) {
                                            'Completado', 'Pagado' => 'bg-emerald-100 text-emerald-700',
                                            'Pendiente' => 'bg-amber-100 text-amber-700',
                                            default => 'bg-red-100 text-red-700'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $statusClasses }}">
                                        {{ $payment->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">
                                    RD$ {{ number_format($payment->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    No se encontraron transacciones.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>

    </div>
    
    {{-- Inclusión del Modal de Pagos --}}
    <livewire:finance.payment-modal />

    <!-- Script para Gráficos (ApexCharts) -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('finance-chart-loaded', (data) => {
                const chartData = data[0];
                const chartElement = document.querySelector("#financeChart");
                if(!chartElement) return;

                const options = {
                    series: [{
                        name: 'Ingresos',
                        data: chartData.income || []
                    }, {
                        name: 'Pendiente de Cobro',
                        data: chartData.pending || []
                    }],
                    chart: {
                        type: 'bar',
                        height: 350,
                        fontFamily: 'Inter, sans-serif',
                        toolbar: { show: false },
                        zoom: { enabled: false }
                    },
                    colors: ['#10b981', '#f59e0b'], // Emerald, Amber
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded',
                            borderRadius: 4
                        },
                    },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 2, colors: ['transparent'] },
                    xaxis: {
                        categories: chartData.labels || [],
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: { style: { colors: '#64748b', fontSize: '12px' } }
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#64748b', fontSize: '12px' },
                            formatter: (value) => { return '$' + value.toLocaleString() }
                        }
                    },
                    fill: { opacity: 1 },
                    tooltip: {
                        y: { formatter: function (val) { return "$ " + val.toLocaleString() } }
                    },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
                    legend: { position: 'top' }
                };

                const chart = new ApexCharts(chartElement, options);
                chart.render();
            });

            // Manejar impresión de tickets
            Livewire.on('printTicket', (event) => {
                const url = event.url;
                if (url) {
                    const printWindow = window.open(url, 'Ticket', 'width=400,height=600');
                    if (printWindow) {
                        printWindow.focus();
                    }
                }
            });
        });
    </script>
</div>