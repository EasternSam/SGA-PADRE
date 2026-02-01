<div class="min-h-screen bg-gray-50/50 pb-12">
    
    <x-action-message on="message" />

    {{-- Encabezado --}}
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Gestión de Admisiones</h1>
            {{-- Filtros (Igual que antes) --}}
            <div class="mt-4 flex gap-4">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o cédula..." class="rounded-lg border-gray-300 w-full md:w-1/3">
                <select wire:model.live="statusFilter" class="rounded-lg border-gray-300">
                    <option value="">Todos</option>
                    <option value="pending">Pendientes</option>
                    <option value="approved">Aprobados</option>
                    <option value="rejected">Con Correcciones</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Tabla Resumen --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Aspirante</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Carrera</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($admissions as $adm)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $adm->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $adm->identification_id }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $adm->course->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $adm->status == 'approved' ? 'bg-green-100 text-green-800' : 
                                       ($adm->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($adm->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button wire:click="openProcessModal({{ $adm->id }})" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">
                                    Revisar Detalle
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $admissions->links() }}</div>
        </div>
    </div>

    {{-- MODAL DE REVISIÓN DETALLADA --}}
    <x-modal name="review-modal" :show="$showProcessModal" maxWidth="4xl">
        @if($selectedAdmission)
        <div class="bg-white rounded-lg overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header Modal -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center sticky top-0 z-10">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Revisión de Solicitud #{{ $selectedAdmission->id }}</h3>
                    <p class="text-sm text-gray-500">{{ $selectedAdmission->full_name }} - {{ $selectedAdmission->identification_id }}</p>
                </div>
                <button wire:click="$set('showProcessModal', false)" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Content Scrollable -->
            <div class="p-6 overflow-y-auto flex-1">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Columna Izquierda: Datos -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <h4 class="font-bold text-gray-700 mb-2 border-b pb-1">Datos Personales</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><span class="text-gray-500 block">Nacionalidad:</span> {{ $selectedAdmission->address }}</div> {{-- Usando address campo compuesto --}}
                                <div><span class="text-gray-500 block">Email:</span> {{ $selectedAdmission->email }}</div>
                                <div><span class="text-gray-500 block">Teléfono:</span> {{ $selectedAdmission->phone }}</div>
                                <div><span class="text-gray-500 block">Enfermedad:</span> {{ $selectedAdmission->disease ?? 'Ninguna' }}</div>
                                <div><span class="text-gray-500 block">Trabaja:</span> {{ $selectedAdmission->work_place ?? 'No' }}</div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <h4 class="font-bold text-gray-700 mb-2 border-b pb-1">Datos Académicos</h4>
                            <div class="text-sm space-y-2">
                                <p><span class="text-gray-500">Carrera Interés:</span> <span class="font-medium text-indigo-700">{{ $selectedAdmission->course->name ?? 'N/A' }}</span></p>
                                <p><span class="text-gray-500">Escuela Procedencia:</span> {{ $selectedAdmission->previous_school }}</p>
                                <p><span class="text-gray-500">GPA Anterior:</span> {{ $selectedAdmission->previous_gpa }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notas Generales / Feedback</label>
                            <textarea wire:model="admissionNotes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Escribe aquí las razones de rechazo o instrucciones para el estudiante..."></textarea>
                        </div>
                    </div>

                    <!-- Columna Derecha: Documentos -->
                    <div>
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            Validación de Documentos
                        </h4>
                        
                        <div class="space-y-3">
                            @foreach($selectedAdmission->documents as $key => $path)
                                @if($path)
                                <div class="border rounded-lg p-3 {{ $tempDocStatus[$key] == 'approved' ? 'bg-green-50 border-green-200' : ($tempDocStatus[$key] == 'rejected' ? 'bg-red-50 border-red-200' : 'bg-white') }}">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-sm font-medium text-gray-700">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                        <a href="{{ asset('storage/'.$path) }}" target="_blank" class="text-xs text-blue-600 hover:underline flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            Ver
                                        </a>
                                    </div>
                                    
                                    <div class="flex gap-2">
                                        <button wire:click="setDocStatus('{{ $key }}', 'approved')" class="flex-1 py-1 text-xs rounded border transition-colors {{ $tempDocStatus[$key] == 'approved' ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-50' }}">
                                            Aprobar
                                        </button>
                                        <button wire:click="setDocStatus('{{ $key }}', 'rejected')" class="flex-1 py-1 text-xs rounded border transition-colors {{ $tempDocStatus[$key] == 'rejected' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-500 border-gray-300 hover:bg-gray-50' }}">
                                            Rechazar
                                        </button>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Sticky -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3 sticky bottom-0 z-10">
                <x-secondary-button wire:click="$set('showProcessModal', false)">Cancelar</x-secondary-button>
                <x-primary-button wire:click="saveReview">Guardar Revisión</x-primary-button>
            </div>
        </div>
        @endif
    </x-modal>
</div>