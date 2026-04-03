<div class="space-y-6 px-4 sm:px-6 lg:px-8 max-w-[90rem] mx-auto mt-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-sga-text">Control de Asistencia Biométrico (ZK)</h2>
            <p class="mt-1 text-sm text-sga-text-light">Supervisión de entradas y salidas del personal.</p>
        </div>
        <div class="mt-4 sm:ml-4 sm:mt-0 flex flex-col sm:flex-row gap-2">
            <div class="relative">
                <input wire:model.live="dateFilter" type="date" class="block w-full sm:w-48 rounded-md border-0 py-1.5 text-sga-text ring-1 ring-inset ring-sga-gray focus:ring-2 focus:ring-inset focus:ring-sga-primary sm:text-sm sm:leading-6 bg-sga-bg">
            </div>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-sga-gray" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model.live="search" type="text" class="block w-full sm:w-64 rounded-md border-0 py-1.5 pl-10 text-sga-text ring-1 ring-inset ring-sga-gray focus:ring-2 focus:ring-inset focus:ring-sga-primary sm:text-sm sm:leading-6 bg-sga-bg" placeholder="Buscar empleado...">
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="overflow-hidden rounded-lg bg-sga-card shadow ring-1 ring-black ring-opacity-5">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50"><tr>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Empleado</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Puesto</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Hora de Marca</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Tipo de Ponche</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Dispositivo ZK</th>
            </tr></thead><tbody class="bg-white divide-y divide-gray-100">
                @forelse ($attendances as $att)
                    <tr class="hover:bg-gray-50/80 transition-colors duration-150 group" wire:key="row-{{ $att->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ optional(optional($att->employee)->user)->profile_photo_url ?? 'https://ui-avatars.com/api/?name=Desc' }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-sga-text">{{ optional(optional($att->employee)->user)->name ?? 'Desconocido' }}</div>
                                    <div class="text-sga-text-light text-xs">ID Emp: {{ $att->biometric_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="text-sga-text">{{ optional($att->employee)->department ?? 'N/A' }}</div>
                            <div class="text-sga-text-light text-xs">{{ optional($att->employee)->position ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="text-sga-text font-medium">{{ $att->punch_time->format('h:i:s A') }}</div>
                            <div class="text-sga-text-light text-xs">{{ $att->punch_time->format('Y-m-d') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            @if($att->punch_type === 0)
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-green-100 text-green-800">
                                    <i class="fas fa-sign-in-alt mr-1"></i> Entrada
                                </span>
                            @elseif($att->punch_type === 1)
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-red-100 text-red-800">
                                    <i class="fas fa-sign-out-alt mr-1"></i> Salida
                                </span>
                            @else
                                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-gray-100 text-gray-800">
                                    Extra/Otro ({{ $att->punch_type }})
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="text-sga-text text-sm">{{ $att->device_serial ?? 'Desconocido' }}</div>
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-gray-50/80 transition-colors duration-150 group">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center" colspan="5">
                            No se registraron ponches en este criterio de búsqueda.
                        </td>
                    </tr>
                @endforelse
            </tbody></table></div>
        <div class="px-4 py-3 border-t border-sga-gray">
            {{ $attendances->links() }}
        </div>
    </div>
</div>
