<div class="p-4 sm:p-8 lg:p-10">
    <!-- Page Header -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-200">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </span>
                Libro Mayor (General Ledger)
            </h1>
            <p class="mt-2 text-base text-gray-600">Registro histórico de asientos bajo el principio de Partida Doble. Cada transacción evidencia débitos y créditos perfectamente balanceados.</p>
        </div>
        <div class="sm:flex-none">
            <a href="{{ route('admin.finance.manual-entry') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-indigo-500 focus-visible:outline focus-visible:outline-1 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Asiento Manual
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 p-6 mb-8 transition-all hover:shadow-md border border-gray-100">
        <div class="grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-2 lg:grid-cols-4 md:items-end">
            <!-- Diario Filter -->
            <div>
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Filtrar por Diario</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                    </div>
                    <select wire:model.live="journal_id" class="block w-full rounded-xl border-0 py-3 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                        <option value="">Todos los Diarios</option>
                        @foreach($journals as $j)
                            <option value="{{ $j->id }}">{{ $j->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Fecha Desde</label>
                <input type="date" wire:model.live="date_from" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Fecha Hasta</label>
                <input type="date" wire:model.live="date_to" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Estado del Asiento</label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <select wire:model.live="status" class="block w-full rounded-xl border-0 py-3 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                        <option value="all">Ver Todos</option>
                        <option value="posted">✅ Contabilizados (Firmes)</option>
                        <option value="draft">📝 En Borrador</option>
                        <option value="void">❌ Anulados</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Asientos Contables -->
    <div class="space-y-6">
        @forelse($entries as $entry)
            <div class="bg-white shadow-xl ring-1 ring-gray-200 sm:rounded-3xl overflow-hidden relative group transition-all hover:shadow-2xl">
                <!-- Color decoration per status -->
                @if($entry->status === 'posted')
                    <div class="absolute inset-y-0 left-0 w-1.5 bg-green-500 rounded-l-3xl"></div>
                @elseif($entry->status === 'draft')
                    <div class="absolute inset-y-0 left-0 w-1.5 bg-yellow-400 rounded-l-3xl"></div>
                @else
                    <div class="absolute inset-y-0 left-0 w-1.5 bg-red-500 rounded-l-3xl"></div>
                @endif
                
                <!-- Header del Asiento -->
                <div class="border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center relative bg-[#f8fafc] px-6 py-5 md:px-8 shadow-sm z-10">
                    <div class="pl-2">
                        <div class="flex items-center gap-3">
                            <h3 class="text-xl font-black text-gray-900 tracking-tight">
                                Asiento #{{ str_pad($entry->id, 5, '0', STR_PAD_LEFT) }}
                            </h3>
                            <span class="inline-flex items-center rounded-full bg-gray-200 px-3 py-0.5 text-xs font-bold text-gray-800 shadow-sm border border-gray-300">
                                {{ $entry->journal->name ?? 'N/A' }}
                            </span>
                            @if($entry->status === 'posted')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-0.5 text-xs font-bold text-green-800 border border-green-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Contabilizado
                                </span>
                            @elseif($entry->status === 'draft')
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-3 py-0.5 text-xs font-bold text-yellow-800 border border-yellow-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span> Borrador
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-0.5 text-xs font-bold text-red-800 border border-red-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Anulado
                                </span>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center gap-4 text-sm font-medium text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $entry->date->format('d M, Y') }}
                            </span>
                            @if($entry->reference_type)
                                <span class="flex items-center gap-1 text-indigo-600">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                                    Ref: {{ class_basename($entry->reference_type) }} #{{ $entry->reference_id }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0 md:text-right pl-2 md:pl-0 w-full md:w-1/3">
                        <p class="text-sm font-bold text-gray-900 border-l-4 md:border-l-0 md:border-r-4 border-indigo-400 pl-3 md:pl-0 md:pr-3 py-1 bg-white md:bg-transparent rounded-r-md md:rounded-l-md shadow-sm md:shadow-none inline-block">
                            {{ $entry->description ?: 'Sin concepto general' }}
                        </p>
                    </div>
                </div>
                
                <!-- Líneas del Asiento (Haber / Debe) -->
                <div class="overflow-x-auto bg-white">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-slate-50 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="py-4 pl-6 md:pl-10 pr-3 text-left text-xs font-bold text-gray-500 uppercase tracking-widest w-2/5">Cuenta Contable</th>
                                <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-widest w-2/5">Explicación de Línea</th>
                                <th scope="col" class="px-4 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest bg-blue-50/50">Débito (Debe)</th>
                                <th scope="col" class="px-4 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-widest bg-orange-50/50">Crédito (Haber)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @php 
                                $totalDebit = 0; 
                                $totalCredit = 0; 
                            @endphp
                            @foreach($entry->lines as $line)
                                @php 
                                    $totalDebit += $line->debit;
                                    $totalCredit += $line->credit;
                                    $isDebit = $line->debit > 0;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-6 md:pl-10 pr-3 text-sm font-bold {{ $isDebit ? 'text-gray-900' : 'text-gray-600 pl-10 md:pl-16' }}">
                                        {{ $line->account->code ?? '---' }} - {{ $line->account->name ?? 'Cuenta Eliminada' }}
                                    </td>
                                    <td class="py-4 px-3 text-sm font-medium text-gray-500 truncate max-w-xs" title="{{ $line->description }}">
                                        {{ $line->description ?: '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-right font-black {{ $line->debit > 0 ? 'text-blue-700 bg-blue-50/20' : 'text-gray-300' }}">
                                        RD$ {{ number_format($line->debit, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-sm text-right font-black {{ $line->credit > 0 ? 'text-orange-700 bg-orange-50/20' : 'text-gray-300' }}">
                                        RD$ {{ number_format($line->credit, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[#f8fafc] border-t-2 border-gray-200">
                            <tr>
                                <th scope="row" colspan="2" class="py-4 pl-6 md:pl-10 pr-4 text-right text-sm font-black text-gray-900 uppercase tracking-widest">
                                    Sumas Iguales:
                                </th>
                                <td class="px-4 py-4 text-right text-base font-black border-t-2 border-gray-900 {{ round($totalDebit, 2) === round($totalCredit, 2) ? 'text-gray-900' : 'text-red-600' }}">
                                    RD$ {{ number_format($totalDebit, 2) }}
                                </td>
                                <td class="px-4 py-4 text-right text-base font-black border-t-2 border-gray-900 {{ round($totalDebit, 2) === round($totalCredit, 2) ? 'text-gray-900' : 'text-red-600' }}">
                                    RD$ {{ number_format($totalCredit, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center bg-white p-16 shadow-xl ring-1 ring-gray-200 sm:rounded-3xl border border-gray-100">
                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <h3 class="mt-4 text-lg font-bold text-gray-900 tracking-tight">Registro Contable Vacío</h3>
                <p class="mt-2 text-sm text-gray-500 max-w-sm mx-auto">Aún no se han generado transacciones financieras a través de matriculaciones, pagos o asientos manuales con los filtros seleccionados.</p>
                <div class="mt-8">
                    <a href="{{ route('admin.finance.manual-entry') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-xl shadow-indigo-200 hover:bg-indigo-500 hover:-translate-y-0.5 transition-all">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Crear Primer Asiento Manual
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginación -->
    <div class="mt-8 font-medium">
        {{ $entries->links() }}
    </div>
</div>
