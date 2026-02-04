<div class="min-h-screen bg-gray-50/50 pb-12">
    
    {{-- Mensajes Flash --}}
    <div class="fixed top-24 right-6 z-50">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="mb-4 rounded-xl bg-emerald-50 border border-emerald-100 p-4 flex items-start gap-3 shadow-lg relative overflow-hidden">
                <div class="text-sm font-semibold text-emerald-800">
                    <strong class="block font-bold">¡Éxito!</strong>
                    {{ session('message') }}
                </div>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="mb-4 rounded-xl bg-rose-50 border border-rose-100 p-4 flex items-start gap-3 shadow-lg relative overflow-hidden">
                <div class="text-sm font-semibold text-rose-800">
                    <strong class="block font-bold">¡Error!</strong>
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Encabezado --}}
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Gestión de Carreras Universitarias</h1>
                    <p class="text-sm text-gray-500 mt-1">Administra los programas de grado, pensums y créditos.</p>
                </div>
                <button wire:click="create()" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg hover:bg-indigo-500 transition-all">
                    <svg class="mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nueva Carrera
                </button>
            </div>

            {{-- Buscador --}}
            <div class="mt-6 relative max-w-2xl">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar carrera por nombre o código..." 
                       class="block w-full pl-10 pr-10 py-3 border-gray-300 rounded-xl bg-gray-50 focus:bg-white focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition shadow-sm">
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-900/5 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Carrera</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Detalles Académicos</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Costos</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-500">Estado</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse ($careers as $career)
                            <tr class="hover:bg-gray-50/80 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-xs">
                                            {{ substr($career->name, 0, 2) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $career->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $career->degree_title }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span class="font-medium">Código:</span> {{ $career->code }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $career->total_credits }} Créditos • {{ $career->duration_periods }} Cuatrimestres
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">Inscripción: ${{ number_format($career->registration_fee, 2) }}</div>
                                    <div class="text-xs text-gray-500">Crédito: ${{ number_format($career->credit_price, 2) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $career->status === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $career->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    
                                    {{-- BOTÓN PENSUM (Ahora sí incluido explícitamente) --}}
                                    <a href="{{ route('admin.careers.curriculum', $career->id) }}" wire:navigate 
                                       class="inline-flex items-center px-3 py-1.5 border border-indigo-200 text-xs font-medium rounded-lg text-indigo-700 bg-indigo-50 hover:bg-indigo-100 mr-2 transition-colors">
                                        <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                        Pensum
                                    </a>

                                    <button wire:click="edit({{ $career->id }})" class="text-gray-400 hover:text-indigo-600 mr-2 transition-colors" title="Editar">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </button>
                                    {{-- BOTÓN ELIMINAR ACTUALIZADO --}}
                                    <button wire:click="delete({{ $career->id }})" wire:confirm="¡ADVERTENCIA DE SEGURIDAD! 

Al eliminar esta carrera, se borrarán PERMANENTEMENTE:
- Todas las materias del pensum.
- Todos los horarios y secciones creados.
- Las relaciones de prerrequisitos.

¿Estás absolutamente seguro de querer continuar?" class="text-gray-400 hover:text-red-600 transition-colors" title="Eliminar Carrera y Datos Asociados">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No hay carreras registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $careers->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Formulario --}}
    <x-modal name="career-form-modal" maxWidth="2xl">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">{{ $modalTitle }}</h2>
                <button x-on:click="$dispatch('close')" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form wire:submit.prevent="save" class="p-6 space-y-6">
                
                {{-- Nombre y Código --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <x-input-label for="name" value="Nombre de la Carrera" />
                        <x-text-input id="name" type="text" class="mt-1 w-full" wire:model="name" placeholder="Ej. Ingeniería en Sistemas" />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="code" value="Código" />
                        <x-text-input id="code" type="text" class="mt-1 w-full" wire:model="code" placeholder="Ej. ISC" />
                        <x-input-error :messages="$errors->get('code')" class="mt-1" />
                    </div>
                </div>

                {{-- Título y Descripción --}}
                <div>
                    <x-input-label for="degree_title" value="Título a Otorgar" />
                    <x-text-input id="degree_title" type="text" class="mt-1 w-full" wire:model="degree_title" placeholder="Ej. Licenciado en..." />
                    <x-input-error :messages="$errors->get('degree_title')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="description" value="Descripción (Opcional)" />
                    <textarea id="description" wire:model="description" rows="3" class="mt-1 w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>

                {{-- Datos Académicos --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="total_credits" value="Total Créditos" />
                        <x-text-input id="total_credits" type="number" class="mt-1 w-full" wire:model="total_credits" />
                        <x-input-error :messages="$errors->get('total_credits')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="duration_periods" value="Duración (Cuatrimestres)" />
                        <x-text-input id="duration_periods" type="number" class="mt-1 w-full" wire:model="duration_periods" />
                        <x-input-error :messages="$errors->get('duration_periods')" class="mt-1" />
                    </div>
                </div>

                {{-- Costos --}}
                <div class="grid grid-cols-3 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <div>
                        <x-input-label for="registration_fee" value="Inscripción ($)" />
                        <x-text-input id="registration_fee" type="number" step="0.01" class="mt-1 w-full" wire:model="registration_fee" />
                        <x-input-error :messages="$errors->get('registration_fee')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="monthly_fee" value="Mensualidad ($)" />
                        <x-text-input id="monthly_fee" type="number" step="0.01" class="mt-1 w-full" wire:model="monthly_fee" />
                        <x-input-error :messages="$errors->get('monthly_fee')" class="mt-1" />
                    </div>
                    <!-- Campo Nuevo: Precio por Crédito -->
                    <div>
                        <x-input-label for="credit_price" value="Precio Crédito ($)" />
                        <x-text-input id="credit_price" type="number" step="0.01" class="mt-1 w-full" wire:model="credit_price" placeholder="0.00" />
                        <x-input-error :messages="$errors->get('credit_price')" class="mt-1" />
                    </div>
                </div>
                
                {{-- Estado y Secuencialidad --}}
                <div class="flex gap-6">
                    <div>
                        <x-input-label for="status" value="Estado" />
                        <select id="status" wire:model="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                      <div class="flex items-center mt-6">
                        <input id="is_sequential" type="checkbox" wire:model="is_sequential" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <label for="is_sequential" class="ml-2 text-sm text-gray-600">Requiere orden estricto (Prerrequisitos)</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                    <x-primary-button>Guardar Carrera</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>