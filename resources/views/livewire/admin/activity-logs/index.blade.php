<div class="min-h-screen bg-gray-50/50 pb-8">
    {{-- Header --}}
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Registro de Actividades y Auditoría</h1>
                <p class="mt-1 text-sm text-gray-500">Auditoría estricta de acciones y modificaciones profundas realizadas en el sistema (Time-Travel).</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                    <svg class="mr-1.5 h-2 w-2 text-indigo-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                    Monitoreo Estricto Activo
                </span>
            </div>
        </div>
    </x-slot>

    <div class="mx-auto max-w-[98%] px-4 sm:px-6 lg:px-8 mt-8">
        
        {{-- Filtros --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                Filtros Avanzados
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Búsqueda --}}
                <div class="lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Buscar</label>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar transacción, acción..." class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                {{-- Usuario --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Responsable</label>
                    <select wire:model.live="user_id" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los usuarios</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Fechas --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                    <input type="date" wire:model.live="date_from" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                    <input type="date" wire:model.live="date_to" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        {{-- Tabla de Registros --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsable</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción / Evento</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo Afectado</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha / Hora</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Detalles</span>
                            </th>
                        </tr>
                    </thead>
                    @forelse($logs as $log)
                        <tbody x-data="{ open: false }" class="bg-white divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50 transition-colors">
                                {{-- Usuario --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs shrink-0">
                                            @if($log->causer)
                                                {{ substr($log->causer->name, 0, 1) }}
                                            @else
                                                ?
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $log->causer ? $log->causer->name : 'Sistema / Automático' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $log->causer ? $log->causer->email : '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Acción --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $color = match($log->event) {
                                            'created' => 'bg-green-100 text-green-800',
                                            'updated' => 'bg-blue-100 text-blue-800',
                                            'deleted' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                        $actionText = match($log->event) {
                                            'created' => 'Creó un Registro',
                                            'updated' => 'Actualizó un Registro',
                                            'deleted' => 'Eliminó un Registro',
                                            default => ucfirst($log->event)
                                        };
                                        // Legacy description si existe
                                        $legacyParams = $log->description !== $log->event ? $log->description : null;
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                        {{ $actionText }}
                                    </span>
                                    @if($legacyParams)
                                        <div class="text-[10px] text-gray-500 mt-1 max-w-xs truncate" title="{{ $legacyParams }}">{{ $legacyParams }}</div>
                                    @endif
                                </td>

                                {{-- Modelo Afectado --}}
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-mono text-xs">
                                        {{ class_basename($log->subject_type) }} <span class="text-gray-400">#{{ $log->subject_id }}</span>
                                    </div>
                                </td>

                                {{-- Fecha y Día --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $log->created_at->diffForHumans() }}</div>
                                </td>

                                {{-- Acciones (Ver Cambios) --}}
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($log->changes()->isNotEmpty())
                                        <button @click="open = !open" type="button" class="text-indigo-600 hover:text-indigo-900 focus:outline-none flex outline-none ml-auto items-center hover:underline">
                                            <span x-text="open ? 'Ocultar Nivel Profundo' : 'Ver Nivel Profundo'">Ver Nivel Profundo</span>
                                            <svg class="ml-1 h-5 w-5 transform transition-transform" :class="{'rotate-180': open}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Sin detalle profundo</span>
                                    @endif
                                    
                                    <button wire:click="viewDetails({{ $log->id }})" class="text-gray-500 hover:text-gray-700 font-medium hover:underline block mt-2 ml-auto text-xs">
                                        Data Cruda (Modal)
                                    </button>
                                </td>
                            </tr>
                            
                            {{-- Fila desplegable con el Time-Travel (Viejo vs Nuevo) --}}
                            @if($log->changes()->isNotEmpty())
                                <tr x-show="open" style="display: none;" class="bg-gray-50 border-b border-gray-200 shadow-inner">
                                    <td colspan="5" class="px-6 py-4">
                                        <div class="rounded-md bg-white p-4 ring-1 ring-black ring-opacity-5 relative shadow">
                                            <h4 class="text-sm font-bold text-gray-700 mb-2 uppercase tracking-wide">Desglose de Modificaciones Exactas</h4>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <!-- Valor Anterior -->
                                                @if(isset($log->changes()['old']))
                                                    <div class="rounded-md border border-red-200 bg-red-50 p-3 relative overflow-hidden">
                                                        <div class="absolute top-0 left-0 w-1 h-full bg-red-400"></div>
                                                        <div class="text-xs font-bold text-red-800 mb-2 flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                            ANTES (VALOR VIEJO)
                                                        </div>
                                                        <pre class="text-xs text-red-700 font-mono whitespace-pre-wrap">@json($log->changes()['old'], JSON_PRETTY_PRINT)</pre>
                                                    </div>
                                                @endif

                                                <!-- Valor Nuevo -->
                                                @if(isset($log->changes()['attributes']))
                                                    <div class="rounded-md border border-green-200 bg-green-50 p-3 relative overflow-hidden">
                                                        <div class="absolute top-0 left-0 w-1 h-full bg-green-400"></div>
                                                        <div class="text-xs font-bold text-green-800 mb-2 flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                            DESPUÉS (NUEVO VALOR)
                                                        </div>
                                                        <pre class="text-xs text-green-700 font-mono whitespace-pre-wrap">@json($log->changes()['attributes'], JSON_PRETTY_PRINT)</pre>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    @empty
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                                        <p>No se encontraron registros de actividad con los filtros seleccionados.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>
            
            {{-- Paginación --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

    {{-- MODAL DE DETALLES --}}
    @if($showDetailsModal && $selectedLog)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDetailsModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Data Cruda #{{ $selectedLog->id }}
                                </h3>
                                <div class="mt-4 space-y-3">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold">Responsable</p>
                                            <p class="text-gray-900">{{ $selectedLog->causer ? $selectedLog->causer->name : 'Sistema Automático' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold">Fecha</p>
                                            <p class="text-gray-900">{{ $selectedLog->created_at->format('d/m/Y h:i A') }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold">Evento</p>
                                            <p class="text-gray-900 font-semibold">{{ $selectedLog->event }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold">Modelo Afectado</p>
                                            <p class="font-mono text-gray-600">{{ $selectedLog->subject_type }} (ID: {{ $selectedLog->subject_id }})</p>
                                        </div>
                                    </div>

                                    @if($selectedLog->description && $selectedLog->description !== $selectedLog->event)
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Descripción / Notas Adicionales</p>
                                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-sm text-gray-800">
                                                {{ $selectedLog->description }}
                                            </div>
                                        </div>
                                    @endif

                                    @if(isset($selectedLog->properties))
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Payload JSON Completo</p>
                                            <div class="max-h-64 overflow-y-auto w-full bg-gray-800 rounded-lg">
                                                <pre class="text-green-400 p-3 text-xs overflow-x-auto font-mono w-full">@json($selectedLog->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="closeDetailsModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>