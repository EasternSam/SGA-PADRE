<div class="p-4 sm:p-8 lg:p-10">
    <!-- Page Header -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-200">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </span>
                Asiento Contable Manual
            </h1>
            <p class="mt-2 text-base text-gray-600">Registra un ajuste, depreciación o nota de diario. El sistema validará la Partida Doble perfecta antes de guardar.</p>
        </div>
        <div class="sm:flex-none">
            <a href="{{ route('admin.finance.ledger') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 hover:text-indigo-600 transition-all duration-200">
                <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Ver Libro Mayor
            </a>
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
                    <h3 class="text-sm font-bold text-red-800 uppercase tracking-widest">Error de Validación</h3>
                    <p class="text-base font-medium text-red-900 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-xl ring-1 ring-gray-200 sm:rounded-3xl overflow-hidden relative">
        <!-- Top accent line -->
        <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600"></div>

        <!-- Header del Formulario -->
        <div class="px-6 py-8 md:px-10 md:py-10 border-b border-gray-100 bg-[#f8fafc]">
            <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Datos del Encabezado
            </h2>
            <div class="grid grid-cols-1 gap-y-8 gap-x-6 sm:grid-cols-12">
                <!-- Diario -->
                <div class="sm:col-span-4">
                    <label class="block text-sm font-semibold leading-6 text-gray-900">Tipo de Diario Contable</label>
                    <div class="mt-2 text-sm">
                        <select wire:model.live="journal_id" class="block w-full rounded-xl border-0 py-2.5 pl-4 pr-10 text-gray-900 ring-1 ring-inset {{ $errors->has('journal_id') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                            <option value="">Seleccione un Diario...</option>
                            @foreach($journals as $j)
                                <option value="{{ $j->id }}">{{ $j->name }}</option>
                            @endforeach
                        </select>
                        @error('journal_id') <span class="text-red-500 font-medium text-xs block mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Fecha -->
                <div class="sm:col-span-4">
                    <label class="block text-sm font-semibold leading-6 text-gray-900">Fecha del Asiento</label>
                    <div class="mt-2 text-sm">
                        <input type="date" wire:model.live="date" class="block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 ring-1 ring-inset {{ $errors->has('date') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                        @error('date') <span class="text-red-500 font-medium text-xs block mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Descripción General -->
                <div class="sm:col-span-12">
                    <label class="block text-sm font-semibold leading-6 text-gray-900">Concepto General / Descripción Breve</label>
                    <div class="mt-2 text-sm relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 4.014 1 5.426v5.148c0 1.413.993 2.67 2.43 2.902.848.137 1.705.248 2.57.331v3.443a.75.75 0 001.28.53l3.58-3.579a22.54 22.54 0 004.14-.467c1.437-.232 2.43-1.49 2.43-2.903V5.426c0-1.412-.993-2.671-2.43-2.902A41.289 41.289 0 0010 2zm0 7a1 1 0 100-2 1 1 0 000 2zM8 8a1 1 0 11-2 0 1 1 0 012 0zm5 1a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" /></svg>
                        </div>
                        <input type="text" wire:model.live="description" class="block w-full rounded-xl border-0 py-3 pl-11 pr-4 text-gray-900 ring-1 ring-inset {{ $errors->has('description') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors" placeholder="Ej. Depreciación de equipos de oficina Enero 2026">
                        @error('description') <span class="text-red-500 font-medium text-xs block mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Líneas del Asiento -->
        <div class="bg-white">
            <div class="px-6 py-4 md:px-10 border-b border-gray-100 flex justify-between items-center bg-white">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Movimientos Contables (Debe/Haber)
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-indigo-50/50">
                        <tr>
                            <th scope="col" class="py-4 pl-6 md:pl-10 pr-3 text-left text-sm font-bold text-indigo-900 uppercase tracking-wide w-1/3">Cuenta Contable</th>
                            <th scope="col" class="px-4 py-4 text-left text-sm font-bold text-indigo-900 uppercase tracking-wide w-1/3">Referencia / Glosa</th>
                            <th scope="col" class="px-4 py-4 text-right text-sm font-bold text-indigo-900 uppercase tracking-wide w-1/6">Débito (Debe)</th>
                            <th scope="col" class="px-4 py-4 text-right text-sm font-bold text-indigo-900 uppercase tracking-wide w-1/6">Crédito (Haber)</th>
                            <th scope="col" class="relative py-4 pl-3 pr-6 md:pr-10 w-12"><span class="sr-only">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($lines as $index => $line)
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="py-4 pl-6 md:pl-10 pr-3 align-top">
                                    <select wire:model.live="lines.{{ $index }}.account_id" class="block w-full rounded-xl border-0 py-2.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset {{ $errors->has('lines.'.$index.'.account_id') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-white shadow-sm">
                                        <option value="">Seleccione una cuenta...</option>
                                        @foreach($accounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('lines.'.$index.'.account_id') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <input type="text" wire:model.live="lines.{{ $index }}.description" placeholder="Explicación detallada de la línea" class="block w-full rounded-xl border-0 py-2.5 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('lines.'.$index.'.description') ? 'ring-red-300 focus:ring-red-500' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-white placeholder-gray-400">
                                    @error('lines.'.$index.'.description') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" wire:model.live="lines.{{ $index }}.debit" class="block w-full rounded-xl border-0 py-2.5 pl-8 pr-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-right sm:text-sm font-semibold bg-white placeholder-gray-300">
                                    </div>
                                </td>
                                <td class="px-3 py-4 align-top">
                                    <div class="relative">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" step="0.01" min="0" wire:model.live="lines.{{ $index }}.credit" class="block w-full rounded-xl border-0 py-2.5 pl-8 pr-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 text-right sm:text-sm font-semibold bg-white placeholder-gray-300">
                                    </div>
                                </td>
                                <td class="relative py-4 pl-3 pr-6 md:pr-10 text-right align-middle">
                                    @if(count($lines) > 2)
                                        <button wire:click="removeLine({{ $index }})" type="button" class="text-gray-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-lg transition-colors cursor-pointer" title="Eliminar línea">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="py-6 pl-6 md:pl-10 text-left bg-white border-t border-gray-100">
                                <button wire:click="addLine" type="button" class="inline-flex items-center gap-2 rounded-xl bg-white border border-dashed border-gray-300 px-5 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm hover:border-indigo-400 hover:bg-indigo-50 transition-all">
                                    <svg class="h-5 w-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                      <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                    </svg>
                                    Añadir Nueva Línea Contable
                                </button>
                            </td>
                        </tr>
                        <!-- TFOOT Totals -->
                        <tr class="bg-[#f8fafc] border-t-2 border-gray-200">
                            <td colspan="2" class="py-6 pl-6 pr-4 text-right text-lg font-black text-gray-900 uppercase tracking-widest">
                                Sumas Iguales
                            </td>
                            <td class="px-4 py-6 text-right">
                                <div class="text-2xl font-black {{ $total_debit === $total_credit && $total_debit > 0 ? 'text-green-600' : 'text-gray-900' }} tracking-tight">
                                    RD$ {{ number_format($total_debit, 2) }}
                                </div>
                            </td>
                            <td class="px-4 py-6 text-right">
                                <div class="text-2xl font-black {{ $total_debit === $total_credit && $total_credit > 0 ? 'text-green-600' : 'text-gray-900' }} tracking-tight border-r-0">
                                    RD$ {{ number_format($total_credit, 2) }}
                                </div>
                            </td>
                            <td class="bg-[#f8fafc]"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        
        <!-- Footer / Botón Guardar -->
        <div class="bg-white px-6 py-6 md:px-10 md:py-8 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="{{ $total_debit === $total_credit && $total_debit > 0 ? 'hidden' : 'block' }} w-full md:w-auto">
                @if($total_debit === 0 && $total_credit === 0)
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-50 border border-gray-200 text-gray-500 text-sm font-medium">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        El asiento actual está en cero.
                    </div>
                @elseif($total_debit !== $total_credit)
                    <div class="inline-flex items-center gap-3 px-5 py-3 rounded-xl bg-red-50 border-2 border-red-300 shadow-sm animate-pulse w-full md:w-auto">
                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div class="flex flex-col">
                            <span class="font-bold text-red-800 uppercase text-xs tracking-wider">Descuadre Detectado</span>
                            <span class="font-black text-red-700">RD$ {{ number_format(abs($total_debit - $total_credit), 2) }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="{{ $total_debit === $total_credit && $total_debit > 0 ? 'flex' : 'hidden' }} items-center gap-3 px-6 py-3 rounded-2xl bg-green-50 border border-green-200 shadow-sm animate-fade-in-up w-full md:w-auto">
                <div class="rounded-full bg-green-500 p-1">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                </div>
                <span class="text-sm font-bold text-green-800 tracking-widest uppercase">Partida Doble Balanceada</span>
            </div>

            <button 
                wire:click="saveEntry" 
                type="button" 
                class="inline-flex justify-center items-center gap-2 rounded-xl bg-indigo-600 px-8 py-3.5 text-base font-bold text-white shadow-md hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-indigo-300 w-full md:w-auto transition-all"
                @if($total_debit !== $total_credit || $total_debit == 0) disabled @endif
            >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Procesar y Contabilizar Asiento
            </button>
        </div>
    </div>
    
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
