<div class="min-h-screen bg-gray-50/50 pb-12">
    
    <x-action-message on="message" />

    {{-- Encabezado --}}
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Portal de Admisiones</h1>
                    <p class="text-sm text-gray-500 mt-1">Gestiona las solicitudes de ingreso a carreras universitarias.</p>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="mt-6 flex flex-col sm:flex-row gap-4">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar aspirante..." 
                           class="block w-full pl-10 pr-3 py-2 border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                </div>
                
                <select wire:model.live="statusFilter" class="block w-full sm:w-48 py-2 border-gray-300 rounded-lg bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">Todos los Estados</option>
                    <option value="pending">Pendientes</option>
                    <option value="approved">Aprobados</option>
                    <option value="rejected">Rechazados</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Lista de Solicitudes --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Aspirante</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Carrera de Interés</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Fecha Solicitud</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Estado</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($admissions as $admission)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                                            {{ substr($admission->first_name, 0, 1) }}{{ substr($admission->last_name, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $admission->full_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $admission->email }}</div>
                                            <div class="text-xs text-gray-400">{{ $admission->phone }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">{{ $admission->course->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">Código: {{ $admission->course->code ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $admission->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pendiente',
                                            'approved' => 'Aprobado',
                                            'rejected' => 'Rechazado',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$admission->status] }}">
                                        {{ $statusLabels[$admission->status] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($admission->status === 'pending')
                                        <button wire:click="openProcessModal({{ $admission->id }}, 'approve')" class="text-green-600 hover:text-green-900 mr-3" title="Aprobar e Inscribir">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                        <button wire:click="openProcessModal({{ $admission->id }}, 'reject')" class="text-red-600 hover:text-red-900" title="Rechazar">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs italic">Procesado</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay solicitudes de admisión.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $admissions->links() }}
            </div>
        </div>
    </div>

    {{-- Modal de Procesamiento --}}
    <x-modal name="process-admission-modal" :show="$showProcessModal" maxWidth="lg">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-bold {{ $processAction === 'approve' ? 'text-green-700' : 'text-red-700' }}">
                    {{ $processAction === 'approve' ? 'Aprobar Solicitud' : 'Rechazar Solicitud' }}
                </h3>
                <button wire:click="$set('showProcessModal', false)" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <div class="p-6">
                <p class="text-sm text-gray-600 mb-4">
                    @if($processAction === 'approve')
                        Estás a punto de aprobar esta solicitud. Esto <strong>creará automáticamente un usuario estudiante</strong> en el sistema y enviará las credenciales por correo.
                    @else
                        Estás a punto de rechazar esta solicitud. Puedes agregar una nota explicando el motivo.
                    @endif
                </p>

                <div class="mb-4">
                    <x-input-label for="admissionNotes" value="Notas / Observaciones" />
                    <textarea id="admissionNotes" wire:model="admissionNotes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <x-secondary-button wire:click="$set('showProcessModal', false)">Cancelar</x-secondary-button>
                    <button wire:click="processAdmission" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150 {{ $processAction === 'approve' ? 'bg-green-600 hover:bg-green-700 focus:bg-green-700 active:bg-green-900' : 'bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-900' }}">
                        Confirmar {{ $processAction === 'approve' ? 'Aprobación' : 'Rechazo' }}
                    </button>
                </div>
            </div>
        </div>
    </x-modal>

</div>