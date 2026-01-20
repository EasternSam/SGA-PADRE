<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Solicitudes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if (session()->has('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Filtros -->
                    <div class="mb-4">
                        <x-input-label for="filterStatus" :value="__('Filtrar por Estado')" />
                        <select wire:model.live="filterStatus" id="filterStatus" class="block mt-1 w-full md:w-1/3 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('Todas') }}</option>
                            <option value="pendiente">{{ __('Pendientes') }}</option>
                            <option value="aprobado">{{ __('Aprobadas') }}</option>
                            <option value="rechazado">{{ __('Rechazadas') }}</option>
                        </select>
                    </div>

                    <!-- Tabla de Solicitudes -->
                    <div class="overflow-x-auto bg-white rounded-lg shadow">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estudiante</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo / Curso</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cobro Generado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($requests as $request)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="font-bold">{{ $request->student->user->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-gray-500">{{ $request->student->user->email ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <span class="block font-medium text-gray-700">{{ $request->type }}</span>
                                            @if($request->course)
                                                <span class="text-xs text-gray-500">{{ $request->course->name }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($request->status == 'aprobado') bg-green-100 text-green-800
                                                @elseif($request->status == 'rechazado') bg-red-100 text-red-800
                                                @else bg-yellow-100 text-yellow-800 @endif">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($request->payment)
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-bold {{ $request->payment->status == 'Pagado' ? 'text-green-600' : 'text-orange-600' }}">
                                                        {{ strtoupper($request->payment->status) }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ number_format($request->payment->amount, 2) }} {{ $request->payment->currency }}
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <x-primary-button type="button" wire:click="viewRequest({{ $request->id }})">
                                                {{ __('Gestionar') }}
                                            </x-primary-button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No se encontraron solicitudes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $requests->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Gestionar Solicitud -->
    @if($showingModal)
        <x-modal name="request-modal" :show="$showingModal" max-width="lg" x-on:close="$wire.closeModal()">
            
            @if($selectedRequest)
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ __('Detalles de la Solicitud') }} #{{ $selectedRequest->id }}
                    </h3>

                    <div class="space-y-4 text-sm">
                        <!-- Info Estudiante -->
                        <div class="bg-gray-50 p-3 rounded-md">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">Estudiante</span>
                                    <span class="font-medium">{{ $selectedRequest->student->user->name ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs text-gray-500 uppercase">Curso</span>
                                    <span class="font-medium">{{ $selectedRequest->course->name ?? 'No especificado' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles Solicitud -->
                        <div>
                            <span class="block text-xs text-gray-500 uppercase mb-1">Motivo / Detalles</span>
                            <div class="p-3 bg-white border border-gray-200 rounded-md text-gray-700" style="white-space: pre-wrap; word-break: break-word;">{!! nl2br(e($selectedRequest->details)) !!}</div>
                        </div>

                        <!-- Información de Pago Generado (si existe) -->
                        @if($selectedRequest->payment)
                            <div class="border-l-4 border-green-400 bg-green-50 p-3">
                                <h4 class="text-green-800 font-bold text-xs uppercase mb-1">Cobro Asociado Generado</h4>
                                <div class="flex justify-between items-center">
                                    <span>Monto: <strong>{{ number_format($selectedRequest->payment->amount, 2) }}</strong></span>
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $selectedRequest->payment->status == 'Pagado' ? 'bg-green-200 text-green-800' : 'bg-orange-200 text-orange-800' }}">
                                        {{ $selectedRequest->payment->status }}
                                    </span>
                                </div>
                            </div>
                        @endif

                        <hr>

                        <!-- Formulario de Admin -->
                        <div>
                            <x-input-label for="adminNotes" :value="__('Respuesta / Notas Admin')" />
                            <textarea wire:model="adminNotes" id="adminNotes" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="{{ __('Escriba la respuesta para el estudiante...') }}"></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <x-secondary-button type="button" wire:click="closeModal">
                            {{ __('Cancelar') }}
                        </x-secondary-button>
                        
                        @if($selectedRequest->status !== 'rechazado')
                            <x-danger-button type="button" wire:click="updateRequest('rechazado')" wire:confirm="¿Seguro que desea rechazar esta solicitud?">
                                {{ __('Rechazar') }}
                            </x-danger-button>
                        @endif

                        @if($selectedRequest->status !== 'aprobado')
                            <x-primary-button type="button" class="bg-green-600 hover:bg-green-700" wire:click="updateRequest('aprobado')">
                                {{ __('Aprobar y Procesar') }}
                            </x-primary-button>
                        @endif
                    </div>
                </div>
            @endif
        </x-modal>
    @endif
</div>