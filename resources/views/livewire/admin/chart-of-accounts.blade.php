<div class="p-4 sm:p-8 lg:p-10">
    <!-- Page Header -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-200">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </span>
                Catálogo de Cuentas (Chart of Accounts)
            </h1>
            <p class="mt-2 text-base text-gray-600">Administra la jerarquía de cuentas, subcuentas y grupos contables de forma visual y estructurada.</p>
        </div>
        <div class="sm:flex-none">
            <button wire:click="create" type="button" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-md hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all duration-200">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Nueva Cuenta
            </button>
        </div>
    </div>

    <!-- Alertas -->
    @if (session()->has('message'))
        <div class="mb-8 rounded-2xl bg-green-50 p-6 border border-green-200 shadow-sm animate-fade-in-up">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-green-500 rounded-full p-1.5 shadow-sm">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" /></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-green-800 uppercase tracking-widest">Éxito</h3>
                    <p class="text-base font-medium text-green-900 mt-0.5">{{ session('message') }}</p>
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
                    <h3 class="text-sm font-bold text-red-800 uppercase tracking-widest">Error</h3>
                    <p class="text-base font-medium text-red-900 mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 p-6 mb-8 transition-all hover:shadow-md border border-gray-100">
        <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-12 md:items-end">
            <div class="sm:col-span-12 md:col-span-8 relative">
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Buscador Inteligente</label>
                <div class="pointer-events-none absolute inset-y-0 bottom-0 left-0 top-[2.1rem] flex items-center pl-4">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" /></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full rounded-xl border-0 py-3 pl-11 pr-4 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors" placeholder="Buscar por código estructurado (Ej. 1.1) o nombre de la cuenta...">
            </div>
            
            <div class="sm:col-span-12 md:col-span-4">
                <label class="block text-sm font-semibold leading-6 text-gray-900 mb-2">Filtrar por Naturaleza</label>
                <select wire:model.live="type_filter" class="block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                    <option value="">🏠 Todas las Naturalezas</option>
                    <option value="asset">🔹 Activos (Assets)</option>
                    <option value="liability">🔸 Pasivos (Liabilities)</option>
                    <option value="equity">🟣 Capital (Equity)</option>
                    <option value="revenue">🟢 Ingresos (Revenue)</option>
                    <option value="cost">🟤 Costos (Cost)</option>
                    <option value="expense">🔴 Gastos (Expense)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Lista Jerárquica / Tabla -->
    <div class="bg-white shadow-xl ring-1 ring-gray-200 sm:rounded-3xl overflow-hidden relative">
        <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-600"></div>
        
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#f8fafc] border-b border-gray-100">
                <tr>
                    <th scope="col" class="py-5 pl-6 md:pl-8 pr-3 text-left text-sm font-bold text-gray-900 uppercase tracking-widest bg-[#f8fafc]">Estructura y Código</th>
                    <th scope="col" class="px-4 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-widest bg-[#f8fafc]">Nombre de la Cuenta Contable</th>
                    <th scope="col" class="px-4 py-5 text-left text-sm font-bold text-gray-900 uppercase tracking-widest bg-[#f8fafc]">Naturaleza (Tipo)</th>
                    <th scope="col" class="px-4 py-5 text-center text-sm font-bold text-gray-900 uppercase tracking-widest bg-[#f8fafc]">Estado</th>
                    <th scope="col" class="relative py-5 pl-3 pr-6 md:pr-8 bg-[#f8fafc] w-24"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse ($accounts as $account)
                    @php
                        // Calcular nivel de indentación basado en los puntos del código para simular árbol
                        $dots = substr_count($account->code, '.');
                        $padding = $dots > 0 ? ($dots * 2) . 'rem' : '0';
                        $isParent = $dots === 0 || $dots === 1; // Simplificación visual
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors {{ $account->is_active ? '' : 'bg-gray-50 border-dashed border-gray-300 opacity-80' }} group">
                        
                        <!-- Columna: Código e Indentación Jerárquica -->
                        <td class="whitespace-nowrap py-5 pl-6 md:pl-8 pr-3" style="padding-left: calc({{ $padding }} + 2rem);">
                            <div class="flex items-center gap-2">
                                @if($dots > 0)
                                    <svg class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                @endif
                                <span class="font-mono text-sm {{ $isParent ? 'font-black text-gray-900' : 'font-medium text-gray-600' }}">
                                    {{ $account->code }}
                                </span>
                            </div>
                        </td>
                        
                        <!-- Columna: Nombre -->
                        <td class="px-4 py-5 text-sm">
                            <div class="{{ $isParent ? 'font-black text-gray-900 text-base' : 'font-semibold text-gray-700' }}">
                                {{ $account->name }}
                            </div>
                            @if($account->parent_id)
                                <div class="text-xs font-medium text-indigo-500 mt-0.5 flex items-center gap-1">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Subcuenta de: {{ $account->parent->code }}
                                </div>
                            @endif
                        </td>
                        
                        <!-- Columna: Naturaleza con Badges Modernos -->
                        <td class="px-4 py-5 whitespace-nowrap">
                            @if($account->type == 'asset') 
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-600/20 shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> Activo (Deudora)
                                </span>
                            @elseif($account->type == 'liability') 
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-700 ring-1 ring-inset ring-orange-600/20 shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Pasivo (Acreedora)
                                </span>
                            @elseif($account->type == 'equity') 
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-50 px-3 py-1.5 text-xs font-bold text-purple-700 ring-1 ring-inset ring-purple-600/20 shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full bg-purple-500"></span> Capital (Acreedora)
                                </span>
                            @elseif($account->type == 'revenue') 
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-3 py-1.5 text-xs font-bold text-green-700 ring-1 ring-inset ring-green-600/20 shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Ingreso (Acreedora)
                                </span>
                            @elseif($account->type == 'cost') 
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-stone-50 px-3 py-1.5 text-xs font-bold text-stone-700 ring-1 ring-inset ring-stone-600/20 shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full bg-stone-500"></span> Costo (Deudora)
                                </span>
                            @elseif($account->type == 'expense') 
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700 ring-1 ring-inset ring-red-600/20 shadow-sm">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span> Gasto (Deudora)
                                </span>
                            @endif
                        </td>
                        
                        <!-- Columna: Estado -->
                        <td class="px-4 py-5 whitespace-nowrap text-center">
                            @if($account->is_active)
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2.5 py-1 text-xs font-bold text-green-700 border border-green-200">
                                    Operativa
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 border border-gray-300">
                                    Inactiva / Oculta
                                </span>
                            @endif
                        </td>
                        
                        <!-- Columna: Acciones (Menu modernizado) -->
                        <td class="relative py-5 pl-3 pr-6 md:pr-8 text-right whitespace-nowrap font-medium">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="edit({{ $account->id }})" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Editar Cuenta">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <button wire:click="delete({{ $account->id }})" wire:confirm="¿Seguro que deseas eliminar esta cuenta? Esto no será posible si la cuenta ya tiene transacciones en el Libro Mayor." class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar Cuenta">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                            <h3 class="mt-4 text-sm font-semibold text-gray-900">No hay cuentas contables</h3>
                            <p class="mt-2 text-sm text-gray-500">Ninguna cuenta coincide con los filtros de búsqueda aplicados.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-6 font-medium">
        {{ $accounts->links() }}
    </div>

    <!-- Modal Formulario -->
    @if($showModal)
    <div class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-gray-100">
                    
                    <!-- Modal Header -->
                    <div class="bg-indigo-600 px-6 py-6">
                        <h3 class="text-xl font-bold leading-6 text-white" id="modal-title">
                            {{ $account_id ? 'Editar Cuenta Contable' : 'Nueva Cuenta Contable Libre' }}
                        </h3>
                        <p class="text-indigo-100 text-sm mt-1">Configura los parámetros para el catálogo general.</p>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 pb-6 pt-5 sm:p-8">
                        <div class="space-y-6">
                            
                            <!-- Select Padre -->
                            <div>
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Cuenta Padre (Rubro Agrupador)</label>
                                <select wire:model.live="parent_id" class="block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('parent_id') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                    <option value="">-- Sin Padre (Crear un Rubro General) --</option>
                                    @foreach($potentialParents as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->code }} - {{ $parent->name }} ({{ ucfirst($parent->type) }})</option>
                                    @endforeach
                                </select>
                                @error('parent_id') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                                <p class="text-xs font-semibold text-indigo-500 mt-2 flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    El tipo de naturaleza se heredará si eliges un padre.
                                </p>
                            </div>

                            <!-- Código y Nombre Gird -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Estructura de Código</label>
                                    <input type="text" wire:model="code" placeholder="Ej. 1.1.2.0" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('code') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors font-mono font-medium">
                                    @error('code') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Nombre Técnico</label>
                                    <input type="text" wire:model="name" placeholder="Ej. Banco BHD" class="block w-full rounded-xl border-0 py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('name') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                    @error('name') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Tipo (Naturaleza) -->
                            <div class="{{ $parent_id ? 'opacity-50 pointer-events-none' : '' }}">
                                <label class="block text-sm font-bold leading-6 text-gray-900 mb-1">Naturaleza Contable</label>
                                <select wire:model="type" {{ $parent_id ? 'disabled' : '' }} class="block w-full rounded-xl border-0 py-3 pl-4 pr-10 text-gray-900 shadow-sm ring-1 ring-inset {{ $errors->has('type') ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600' }} sm:text-sm bg-gray-50 hover:bg-white transition-colors">
                                    <option value="">Seleccione una naturaleza financiera...</option>
                                    <option value="asset">Activo (Asset - Naturaleza Deudora)</option>
                                    <option value="liability">Pasivo (Liability - Naturaleza Acreedora)</option>
                                    <option value="equity">Capital (Equity - Naturaleza Acreedora)</option>
                                    <option value="revenue">Ingresos (Revenue - Naturaleza Acreedora)</option>
                                    <option value="cost">Costos (Cost - Naturaleza Deudora)</option>
                                    <option value="expense">Gastos (Expense - Naturaleza Deudora)</option>
                                </select>
                                @error('type') <span class="text-red-500 font-medium text-xs block mt-1">{{ $message }}</span> @enderror
                                @if($parent_id)
                                    <div class="rounded-lg bg-blue-50 p-3 ring-1 ring-inset ring-blue-100 flex gap-2 items-center w-full mt-3">
                                        <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        <span class="text-xs text-blue-800 font-semibold">Tipo bloqueado (Heredando Naturaleza Restringida)</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Estado -->
                            <div class="relative flex items-start bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <div class="flex h-6 items-center">
                                    <input wire:model="is_active" id="is_active" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 cursor-pointer">
                                </div>
                                <div class="ml-3 text-sm leading-6">
                                    <label for="is_active" class="font-bold text-gray-900 cursor-pointer">Mantener Cuenta Activa</label>
                                    <p class="text-gray-500 text-xs">Si está inactiva, no podrá ser seleccionada por el motor contable para nuevos asientos.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse sm:px-8 border-t border-gray-100 gap-3">
                        <button wire:click="save" type="button" class="inline-flex w-full justify-center rounded-xl bg-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-500 sm:w-auto transition-colors">
                            <svg class="h-5 w-5 mr-2 -ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Confirmar y Guardar
                        </button>
                        <button wire:click="closeModal" type="button" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">
                            Cancelar Operación
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
