<div class="space-y-6 px-4 sm:px-6 lg:px-8 max-w-[90rem] mx-auto mt-6">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-sga-text">Empleados</h2>
            <p class="mt-1 text-sm text-sga-text-light">Administración del personal, salarios y vinculación con reloj ZKTeco.</p>
        </div>
        <div class="mt-4 sm:ml-4 sm:mt-0 flex gap-2">
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-sga-gray" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model.live="search" type="text" class="block w-full sm:w-64 rounded-md border-0 py-1.5 pl-10 text-sga-text ring-1 ring-inset ring-sga-gray focus:ring-2 focus:ring-inset focus:ring-sga-primary sm:text-sm sm:leading-6 bg-sga-bg" placeholder="Buscar por nombre o ZK ID...">
            </div>
            <button wire:click="create" class="inline-flex items-center justify-center rounded-md bg-sga-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sga-primary-dark">
                + Nuevo Empleado
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">{{ session('message') }}</h3>
                </div>
            </div>
        </div>
    @endif

    <!-- Tabla -->
    <div class="overflow-hidden rounded-lg bg-sga-card shadow ring-1 ring-black ring-opacity-5">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50"><tr>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Empleado</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Puesto / Depto</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Remuneración</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Biométrico ZK</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-left">Estado</th>
                <th scope="col" class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 whitespace-nowrap text-right">Acciones</th>
            </tr></thead><tbody class="bg-white divide-y divide-gray-100">
                @forelse ($employees as $emp)
                    <tr class="hover:bg-gray-50/80 transition-colors duration-150 group" wire:key="row-{{ $emp->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full object-cover" src="{{ $emp->user->profile_photo_url }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-sga-text">{{ $emp->user->name }}</div>
                                    <div class="text-sga-text-light text-xs">{{ $emp->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="text-sga-text">{{ $emp->position ?? 'No definido' }}</div>
                            <div class="text-sga-text-light text-xs">{{ $emp->department ?? 'No definido' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <div class="text-sga-text font-medium">{{ $emp->contract_type }}</div>
                            <div class="text-sga-text-light text-xs">
                                @if($emp->contract_type === 'Mensual')
                                    RD$ {{ number_format($emp->base_salary, 2) }} / mes
                                @else
                                    RD$ {{ number_format($emp->hourly_rate, 2) }} / hora
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                ID: {{ $emp->biometric_id ?? 'No enlazado' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $emp->status === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $emp->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
                            <a href="{{ route('admin.hr.employees.profile', $emp->id) }}" wire:navigate class="inline-flex items-center rounded-md bg-sga-primary px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-sga-primary-dark cursor-pointer">
                                Ver Expediente Completo
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr class="hover:bg-gray-50/80 transition-colors duration-150 group">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center" colspan="6">
                            No se encontraron empleados registrados en la base de datos de RRHH.
                        </td>
                    </tr>
                @endforelse
            </tbody></table></div>
        <div class="px-4 py-3 border-t border-sga-gray">
            {{ $employees->links() }}
        </div>
    </div>

    <!-- Modal Formulario -->
    <x-modal name="employee-modal" maxWidth="2xl">
        <form wire:submit.prevent="store">
            <div class="bg-sga-card px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg font-semibold leading-6 text-sga-text mb-4">
                            {{ $employeeId ? 'Editar Empleado' : 'Nuevo Empleado' }}
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            
                            <div class="col-span-1 md:col-span-2">
                                @if(!$employeeId)
                                <div class="mb-4 flex items-center">
                                    <input wire:model.live="is_new_user" id="is_new_user" type="checkbox" class="h-4 w-4 text-sga-primary border-sga-gray rounded focus:ring-sga-primary">
                                    <label for="is_new_user" class="ml-2 block text-sm font-medium text-sga-text font-bold">
                                        Crear un nuevo usuario simultáneamente
                                    </label>
                                </div>
                                @endif

                                @if($is_new_user && !$employeeId)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-md border border-gray-200 mb-4">
                                        <div>
                                            <label class="block text-sm font-medium text-sga-text">Nombre Completo</label>
                                            <input type="text" wire:model="new_user_name" class="mt-1 block w-full rounded-md border-sga-gray bg-white text-sga-text shadow-sm focus:border-sga-primary sm:text-sm" placeholder="Ej: Juan Pérez">
                                            @error('new_user_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-sga-text">Correo Electrónico</label>
                                            <input type="email" wire:model="new_user_email" class="mt-1 block w-full rounded-md border-sga-gray bg-white text-sga-text shadow-sm focus:border-sga-primary sm:text-sm" placeholder="juan@centu.edu.do">
                                            @error('new_user_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-sga-text">Contraseña Temporal</label>
                                            <input type="text" wire:model="new_user_password" class="mt-1 block w-full rounded-md border-sga-gray bg-white text-sga-text shadow-sm focus:border-sga-primary sm:text-sm" placeholder="Mínimo 8 caracteres">
                                            @error('new_user_password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @else
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-sga-text">Usuario Vinculado</label>
                                        <select wire:model="user_id" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary sm:text-sm" @if($employeeId) disabled @endif>
                                            <option value="">Seleccione un usuario existente...</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                            @endforeach
                                        </select>
                                        @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-sm font-medium text-sga-text">Nivel de Acceso (Rol) <span class="text-red-500">*</span></label>
                                    <select wire:model="role_name" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary sm:text-sm">
                                        <option value="">Seleccione un rol de sistema...</option>
                                        @foreach($roles as $r)
                                            <option value="{{ $r->name }}">{{ $r->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('role_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <p class="text-xs text-sga-text-light mt-1">Define los permisos globales que este usuario tendrá al entrar al panel. Se asignará automáticamente en Spatie.</p>
                                </div>
                            </div>

                            <div class="col-span-1 border-t border-sga-gray pt-4 mt-2">
                                <h4 class="font-medium text-sm text-sga-text mb-3">Datos Corporativos</h4>
                                
                                <label class="block text-sm font-medium text-sga-text mt-3">ID Biométrico (ZK)</label>
                                <input type="number" wire:model="biometric_id" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm" placeholder="Ej: 105">
                                @error('biometric_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                                <label class="block text-sm font-medium text-sga-text mt-3">Cargo Militar/Civil</label>
                                <input type="text" wire:model="position" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm">

                                <label class="block text-sm font-medium text-sga-text mt-3">Departamento</label>
                                <input type="text" wire:model="department" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm">

                                <label class="block text-sm font-medium text-sga-text mt-3">Fecha de Contratación</label>
                                <input type="date" wire:model="hire_date" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm">
                                
                                <label class="block text-sm font-medium text-sga-text mt-3">Estado</label>
                                <select wire:model="status" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo / Desvinculado</option>
                                </select>
                            </div>

                            <div class="col-span-1 border-t border-sga-gray pt-4 mt-2">
                                <h4 class="font-medium text-sm text-sga-text mb-3">Remuneración y Nómina</h4>

                                <label class="block text-sm font-medium text-sga-text mt-3">Tipo de Contrato</label>
                                <select wire:model.live="contract_type" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm">
                                    <option value="Mensual">Fijo (Mensual)</option>
                                    <option value="Por Horas">Por Horas Impartidas (Docente)</option>
                                </select>
                                
                                @if($contract_type === 'Mensual')
                                    <label class="block text-sm font-medium text-sga-text mt-3">Salario Base Bruto (RD$)</label>
                                    <input type="number" step="0.01" wire:model="base_salary" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm">
                                @else
                                    <label class="block text-sm font-medium text-sga-text mt-3">Tarifa por Hora (RD$)</label>
                                    <input type="number" step="0.01" wire:model="hourly_rate" class="mt-1 block w-full rounded-md border-sga-gray bg-sga-bg text-sga-text shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm">
                                @endif
                                <p class="text-xs text-sga-text-light mt-2 italic">
                                    Nota: Los cálculos de nómina ignorarán la tarifa base si el contrato es "Por Horas".
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-sga-bg px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-sga-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sga-primary-dark sm:ml-3 sm:w-auto">
                    Guardar Cambios
                </button>
                <button type="button" x-on:click="$dispatch('close')" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    Cancelar
                </button>
            </div>
        </form>
    </x-modal>
</div>
