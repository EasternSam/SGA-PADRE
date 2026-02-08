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
        <x-modal name="process-modal" focusable>
            @if($selectedAdmission)
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6 border-b pb-4">
                        <h2 class="text-xl font-bold text-gray-900">
                            Solicitud #{{ $selectedAdmission->id }} - {{ $selectedAdmission->first_name }} {{ $selectedAdmission->last_name }}
                        </h2>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                            @if($selectedAdmission->status == 'approved') bg-green-100 text-green-800 
                            @elseif($selectedAdmission->status == 'rejected') bg-red-100 text-red-800 
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($selectedAdmission->status) }}
                        </span>
                    </div>

                    {{-- SECCIÓN DE INFORMACIÓN COMPLETA --}}
                    <div class="bg-gray-50 rounded-lg p-5 mb-6 border border-gray-200">
                        <h3 class="text-sm font-bold text-indigo-700 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Información del Aspirante</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 text-sm">
                            {{-- Columna 1: Personal --}}
                            <div class="space-y-3">
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 uppercase">Cédula / Pasaporte</span>
                                    <span class="block text-gray-900 font-semibold">{{ $selectedAdmission->identification_id }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 uppercase">Fecha de Nacimiento</span>
                                    <span class="block text-gray-900">{{ \Carbon\Carbon::parse($selectedAdmission->birth_date)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($selectedAdmission->birth_date)->age }} años)</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 uppercase">Email</span>
                                    <span class="block text-gray-900">{{ $selectedAdmission->email }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 uppercase">Teléfono</span>
                                    <span class="block text-gray-900">{{ $selectedAdmission->phone }}</span>
                                </div>
                            </div>

                            {{-- Columna 2: Académico y Dirección --}}
                            <div class="space-y-3">
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 uppercase">Carrera Solicitada</span>
                                    <span class="block text-indigo-700 font-bold text-base">{{ $selectedAdmission->course->name ?? 'No especificada' }}</span>
                                </div>
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 uppercase">Escuela de Procedencia</span>
                                    <span class="block text-gray-900">{{ $selectedAdmission->previous_school }}</span>
                                </div>
                                @if($selectedAdmission->previous_gpa)
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 uppercase">Promedio Anterior</span>
                                    <span class="block text-gray-900">{{ $selectedAdmission->previous_gpa }}</span>
                                </div>
                                @endif
                                <div>
                                    <span class="block text-xs font-medium text-gray-500 uppercase">Dirección / Nacionalidad</span>
                                    <span class="block text-gray-900">{{ $selectedAdmission->address }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Información Adicional (Trabajo/Salud) --}}
                        @if($selectedAdmission->work_place || $selectedAdmission->disease)
                            <div class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                @if($selectedAdmission->work_place)
                                    <div>
                                        <span class="block text-xs font-medium text-gray-500 uppercase">Lugar de Trabajo</span>
                                        <span class="block text-gray-900">{{ $selectedAdmission->work_place }}</span>
                                    </div>
                                @endif
                                @if($selectedAdmission->disease)
                                    <div>
                                        <span class="block text-xs font-medium text-gray-500 uppercase">Condición Médica / Riesgo</span>
                                        <span class="block text-red-600 font-semibold">{{ $selectedAdmission->disease }}</span>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- SECCIÓN DE DOCUMENTOS --}}
                    @php
                        $docLabels = [
                            'birth_certificate' => 'Acta de Nacimiento',
                            'id_card' => 'Cédula de Identidad',
                            'high_school_record' => 'Récord de Notas',
                            'medical_certificate' => 'Certificado Médico',
                            'payment_receipt' => 'Recibo de Pago',
                            'bachelor_certificate' => 'Certificado de Bachiller',
                            'photo' => 'Fotografía 2x2',
                        ];
                    @endphp

                    <h3 class="text-md font-bold text-gray-800 mb-4 px-1 border-l-4 border-indigo-500 pl-3">Revisión de Documentación</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        @foreach($selectedAdmission->documents as $key => $path)
                            <div class="border rounded-lg p-3 flex flex-col justify-between h-full shadow-sm {{ ($tempDocStatus[$key] ?? '') == 'rejected' ? 'bg-red-50 border-red-200' : (($tempDocStatus[$key] ?? '') == 'approved' ? 'bg-green-50 border-green-200' : 'bg-white') }}">
                                
                                <div class="mb-3">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="font-bold text-sm text-gray-700">
                                            {{ $docLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                        </span>
                                        
                                        @if($path)
                                            <a href="{{ route('admissions.document', ['admission' => $selectedAdmission->id, 'key' => $key]) }}" target="_blank" class="text-xs bg-white text-indigo-700 border border-indigo-200 px-3 py-1 rounded-full hover:bg-indigo-50 transition font-medium flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                                Ver
                                            </a>
                                        @else
                                            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded">No subido</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 truncate" title="{{ basename($path) }}">{{ basename($path) }}</p>
                                </div>

                                <div class="flex gap-2 mt-auto">
                                    <button type="button" wire:click="setDocStatus('{{ $key }}', 'approved')" 
                                        class="flex-1 text-xs font-semibold py-2 rounded transition-colors {{ ($tempDocStatus[$key] ?? '') == 'approved' ? 'bg-green-600 text-white shadow-inner' : 'bg-white text-gray-600 border hover:bg-green-50 hover:text-green-700 hover:border-green-300' }}">
                                        @if(($tempDocStatus[$key] ?? '') == 'approved') ✓ Aceptado @else Aceptar @endif
                                    </button>
                                    <button type="button" wire:click="setDocStatus('{{ $key }}', 'rejected')" 
                                        class="flex-1 text-xs font-semibold py-2 rounded transition-colors {{ ($tempDocStatus[$key] ?? '') == 'rejected' ? 'bg-red-600 text-white shadow-inner' : 'bg-white text-gray-600 border hover:bg-red-50 hover:text-red-700 hover:border-red-300' }}">
                                        @if(($tempDocStatus[$key] ?? '') == 'rejected') ✕ Rechazado @else Rechazar @endif
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Notas / Feedback para el aspirante</label>
                        <textarea wire:model="admissionNotes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" rows="3" placeholder="Ej: La foto debe tener fondo blanco, el acta no es legible..."></textarea>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end gap-3">
                        <x-secondary-button wire:click="closeProcessModal">
                            Cancelar
                        </x-secondary-button>
                        
                        <x-primary-button wire:click="saveReview" class="bg-indigo-600 hover:bg-indigo-700">
                            Guardar Decisión
                        </x-primary-button>
                    </div>
                </div>
            @else
                <div class="p-12 text-center text-gray-500">Cargando información...</div>
            @endif
        </x-modal>

    </div>
</div>