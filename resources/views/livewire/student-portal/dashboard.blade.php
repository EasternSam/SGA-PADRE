<div>
    {{-- Slot del Encabezado --}}
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-sga-text">
            Mi Expediente
        </h2>
    </x-slot>

    <!-- Bloque de Mensajes Flash -->
    @if (session()->has('message'))
        <div class="mb-6 rounded-lg border border-green-400 bg-green-100 px-4 py-3 text-green-700 shadow" role="alert">
            <strong class="font-bold">¡Éxito!</strong>
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-6 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700 shadow" role="alert">
            <strong class="font-bold">¡Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    
    <!-- Alerta de Pagos Pendientes -->
    @if($pendingPayments->count() > 0)
        <div class="mb-6 rounded-lg border border-yellow-400 bg-yellow-50 p-4 shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.433-.813 1.601-.813 2.034 0l6.28 11.752c.433.813-.207 1.755-1.017 1.755H3.22c-.81 0-1.45-.942-1.017-1.755L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Tienes Pagos Pendientes</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Detectamos una o más inscripciones pendientes de pago.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Contenido Principal -->
    <div class="space-y-6">
        <!-- Tarjeta de Bienvenida y Datos -->
        <div class="overflow-hidden rounded-lg bg-sga-card shadow">
            <div class="p-4 sm:p-6 md:flex relative">
                
                {{-- BOTÓN DE EDITAR PERFIL --}}
                <button wire:click="openProfileModal" class="absolute top-4 right-4 text-sga-text-light hover:text-indigo-600 transition-colors" title="Editar Información">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </button>

                <div class="md:w-1/4 md:flex-shrink-0 md:text-center">
                    <img class="h-32 w-32 rounded-full mx-auto md:mx-0 md:mr-6 shadow-md"
                         src="https://placehold.co/100x100/e2e8f0/64748b?text={{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}"
                         alt="Avatar">
                </div>
                <div class="mt-4 md:mt-0 md:w-3/4">
                    <h2 class="text-2xl font-bold text-sga-text">¡Bienvenido, {{ $student->first_name }}!</h2>
                    <div class="mt-4 grid grid-cols-1 gap-4 border-t border-sga-gray pt-4 text-sm sm:grid-cols-2 md:grid-cols-4">
                        <div>
                            <strong class="text-sga-text-light block">Nombre:</strong>
                            <span class="text-sga-text">{{ $student->fullName }}</span>
                        </div>
                        <div>
                            <strong class="text-sga-text-light block">Móvil:</strong>
                            <span class="text-sga-text">{{ $student->mobile_phone ?? $student->phone ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <strong class="text-sga-text-light block">Ciudad:</strong>
                            <span class="text-sga-text">{{ $student->city ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <strong class="text-sga-text-light block">Sector:</strong>
                            <span class="text-sga-text">{{ $student->sector ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid de Cursos y Pagos -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <!-- Cursos Activos -->
                <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-sga-text">
                            <i class="fas fa-book-open mr-2 text-sga-primary"></i> Mis Cursos Activos
                        </h3>
                    </div>
                    <div class="flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-sga-gray">
                                    <thead class="bg-sga-bg">
                                        <tr>
                                            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-sga-text sm:pl-6">Curso</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Profesor</th>
                                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-sga-text">Horario</th>
                                            <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Ver</span></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-sga-card divide-y divide-sga-gray">
                                        @forelse ($activeEnrollments as $enrollment)
                                            <tr class="hover:bg-sga-bg">
                                                <td class="py-4 pl-4 pr-3 text-sm sm:pl-6">
                                                    <div class="font-medium text-sga-text">{{ $enrollment->courseSchedule->module->course->name ?? 'N/A' }}</div>
                                                    <div class="text-sga-text-light">{{ $enrollment->courseSchedule->module->name ?? 'N/A' }}</div>
                                                </td>
                                                <td class="px-3 py-4 text-sm text-sga-text-light">{{ $enrollment->courseSchedule->teacher->name ?? 'No asignado' }}</td>
                                                <td class="px-3 py-4 text-sm text-sga-text-light">
                                                    {{ $enrollment->courseSchedule->days_of_week ? implode(', ', $enrollment->courseSchedule->days_of_week) : 'N/A' }}
                                                </td>
                                                <td class="py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                                    <a href="{{ route('student.course.detail', $enrollment->id) }}" class="text-sga-secondary hover:text-sga-primary">Ver</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="px-3 py-4 text-center text-sm text-sga-text-light">Sin cursos activos.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6 lg:col-span-1">
                <!-- Historial de Pagos -->
                <div class="overflow-hidden rounded-lg bg-sga-card shadow">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-sga-text">
                            <i class="fas fa-dollar-sign mr-2 text-sga-primary"></i> Pagos Recientes
                        </h3>
                    </div>
                    <div class="border-t border-sga-gray p-4 sm:p-6">
                        <ul class="divide-y divide-sga-gray">
                            @forelse ($paymentHistory->take(5) as $payment)
                                <li class="flex justify-between py-3">
                                    <div>
                                        <p class="text-sm font-semibold text-sga-text">{{ $payment->description ?? 'Pago' }}</p>
                                        <p class="text-xs text-sga-text-light">{{ $payment->created_at->format('d/m/Y') }}</p>
                                    </div>
                                    <span class="text-sm font-bold text-sga-text">${{ number_format($payment->amount, 2) }}</span>
                                </li>
                            @empty
                                <li class="py-3 text-center text-sm text-sga-text-light">Sin pagos registrados.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE COMPLETAR PERFIL --}}
    <x-modal name="complete-profile-modal" :show="$showProfileModal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                📝 Completa tu Perfil
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Para brindarte un mejor servicio, por favor actualiza la siguiente información.
                <span class="block mt-2 italic text-xs text-gray-500">Estos campos son opcionales.</span>
            </p>

            <form wire:submit.prevent="saveProfile" class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <!-- Teléfono Móvil -->
                    <div>
                        <x-input-label for="mobile_phone" value="Teléfono Móvil" />
                        <x-text-input id="mobile_phone" type="text" class="mt-1 block w-full" 
                                      wire:model="mobile_phone" 
                                      placeholder="809-555-5555" />
                        @error('mobile_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <x-input-label for="birth_date" value="Fecha de Nacimiento" />
                        <x-text-input id="birth_date" type="date" class="mt-1 block w-full" 
                                      wire:model="birth_date" />
                        @error('birth_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Género -->
                    <div>
                        <x-input-label for="gender" value="Género" />
                        <select id="gender" wire:model="gender" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Seleccionar...</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                        @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Ciudad -->
                    <div>
                        <x-input-label for="city" value="Ciudad" />
                        <x-text-input id="city" type="text" class="mt-1 block w-full" 
                                      wire:model="city" placeholder="Ej: Santo Domingo" />
                        @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sector -->
                    <div>
                        <x-input-label for="sector" value="Sector" />
                        <x-text-input id="sector" type="text" class="mt-1 block w-full" 
                                      wire:model="sector" placeholder="Ej: Gazcue" />
                        @error('sector') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Dirección (Ocupa 2 columnas) -->
                    <div class="md:col-span-2">
                        <x-input-label for="address" value="Dirección Completa" />
                        <x-text-input id="address" type="text" class="mt-1 block w-full" 
                                      wire:model="address" placeholder="Calle, Número, Edificio..." />
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button wire:click="closeProfileModal">
                        Más tarde
                    </x-secondary-button>

                    <x-primary-button class="bg-indigo-600 hover:bg-indigo-700">
                        Guardar Información
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>