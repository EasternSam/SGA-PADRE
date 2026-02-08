<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Solicitudes de Admisión</h2>
                <p class="text-gray-600">Gestiona y revisa los documentos de los aspirantes.</p>
            </div>
            
            <div class="mt-4 md:mt-0 flex gap-4">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o cédula..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <select wire:model.live="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Todos los estados</option>
                    <option value="pending">Pendientes</option>
                    <option value="approved">Aprobadas</option>
                    <option value="rejected">Rechazadas</option>
                </select>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aspirante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Carrera</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($admissions as $admission)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $admission->first_name }} {{ $admission->last_name }}</div>
                                <div class="text-sm text-gray-500">{{ $admission->email }}</div>
                                <div class="text-xs text-gray-400">{{ $admission->identification_id }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $admission->course->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $admission->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $admission->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($admission->status == 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendiente</span>
                                @elseif($admission->status == 'approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aprobada</span>
                                @elseif($admission->status == 'rejected')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Corrección</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="openProcessModal({{ $admission->id }})" class="text-indigo-600 hover:text-indigo-900 font-bold">
                                    Revisar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No se encontraron solicitudes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4">
                {{ $admissions->links() }}
            </div>
        </div>

        {{-- MODAL DE REVISIÓN --}}
        {{-- CORRECCIÓN: Quitamos :show="$showProcessModal" y usamos solo name para eventos --}}
        <x-modal name="process-modal" focusable>
            @if($selectedAdmission)
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        Revisión de Documentos: {{ $selectedAdmission->first_name }} {{ $selectedAdmission->last_name }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        @foreach($selectedAdmission->documents as $key => $path)
                            <div class="border rounded-lg p-3 {{ ($tempDocStatus[$key] ?? '') == 'rejected' ? 'bg-red-50 border-red-200' : 'bg-gray-50' }}">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="font-medium text-sm text-gray-700 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                    
                                    @if($path)
                                        <a href="{{ route('admissions.document', ['admission' => $selectedAdmission->id, 'key' => $key]) }}" target="_blank" class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded hover:bg-indigo-200">
                                            Ver Archivo
                                        </a>
                                    @else
                                        <span class="text-xs text-red-500">No subido</span>
                                    @endif
                                </div>

                                <div class="flex gap-2 mt-2">
                                    <button type="button" wire:click="setDocStatus('{{ $key }}', 'approved')" 
                                        class="flex-1 text-xs py-1 rounded border {{ ($tempDocStatus[$key] ?? '') == 'approved' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                        Aceptar
                                    </button>
                                    <button type="button" wire:click="setDocStatus('{{ $key }}', 'rejected')" 
                                        class="flex-1 text-xs py-1 rounded border {{ ($tempDocStatus[$key] ?? '') == 'rejected' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                                        Rechazar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Notas / Feedback para el aspirante</label>
                        <textarea wire:model="admissionNotes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3" placeholder="Indica qué documentos corregir si aplica..."></textarea>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        {{-- CORRECCIÓN: Usar método closeProcessModal en lugar de $set --}}
                        <x-secondary-button wire:click="closeProcessModal">
                            Cancelar
                        </x-secondary-button>
                        
                        <x-primary-button wire:click="saveReview">
                            Guardar Revisión
                        </x-primary-button>
                    </div>
                </div>
            @else
                <div class="p-6 text-center text-gray-500">Cargando información...</div>
            @endif
        </x-modal>

    </div>
</div>