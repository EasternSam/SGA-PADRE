<div class="space-y-6 pb-12 px-4 sm:px-6 lg:px-8 max-w-[90rem] mx-auto mt-6" x-data="{ tab: 'finanzas' }" wire:ignore.self>
    <!-- Incluir biblioteca de iconos en caso que el sistema no lo tenga -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Flash Messages (Generic for quick actions) -->
    @if (session()->has('success'))
        <div class="rounded-md bg-green-50 p-4 border border-green-200 shadow-sm mb-4 font-medium text-green-800 flex items-center gap-2">
            <i class="fas fa-check-circle text-green-500 text-xl"></i> {{ session('success') }}
        </div>
    @endif
    
    <!-- Hero Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-sga-gray overflow-hidden relative">
        <div class="h-40 bg-gradient-to-r from-[#1E3A8A] via-blue-800 to-sga-primary w-full shadow-inner"></div>
        <div class="px-6 sm:px-10 pb-8 relative">
            <div class="flex flex-col sm:flex-row gap-6 items-start w-full">
                <img src="{{ $employee->user->profile_photo_url }}" 
                     class="-mt-16 h-32 w-32 rounded-full shadow-lg border-4 border-white object-cover bg-white z-10 shrink-0" 
                     alt="{{ $employee->user->name }}">
                
                <div class="flex-1 pt-4 sm:pt-6 pb-2 flex flex-col sm:flex-row justify-between w-full items-start sm:items-center">
                    <div>
                        <h1 class="text-3xl font-extrabold text-gray-900">{{ $employee->user->name }}</h1>
                        <p class="text-sga-text-light font-medium text-lg mt-1"><i class="fas fa-briefcase text-gray-400 mr-1"></i> {{ $employee->position ?? 'Sin cargo asignado' }} &bull; {{ $employee->department ?? 'Sin Dpto' }}</p>
                    </div>
                    
                    <div class="mt-4 sm:mt-0 flex flex-wrap gap-2 justify-end">
                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-bold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 shadow-sm">
                            <i class="fas fa-id-badge mr-2 opacity-50"></i> Rol: {{ optional($employee->user->roles->first())->name ?? 'Sin Rol' }}
                        </span>
                        <span class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-bold shadow-sm ring-1 ring-inset {{ $employee->status === 'Activo' ? 'bg-green-50 text-green-700 ring-green-600/20' : 'bg-red-50 text-red-700 ring-red-600/20' }}">
                            @if($employee->status === 'Activo')
                                <div class="h-2 w-2 rounded-full bg-green-500 mr-2 animate-pulse"></div> Activo
                            @else
                                <div class="h-2 w-2 rounded-full bg-red-500 mr-2"></div> Suspendido/Inactivo
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 pt-6 border-t border-gray-100">
                <div>   
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Antigüedad / Fecha Ingreso</p>
                    <p class="font-medium text-gray-900 mt-1">
                        @if($employee->hire_date)
                            {{ \Carbon\Carbon::parse($employee->hire_date)->diffForHumans(null, true) }}
                            <span class="text-gray-400 text-sm ml-1">({{ \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') }})</span>
                        @else
                            No registrada
                        @endif
                    </p>
                </div>
                <div>   
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Última Conexión</p>
                    <p class="font-medium text-gray-900 mt-1"><i class="fas fa-clock text-gray-300 mr-1"></i> No registrado</p>
                </div>
                <div>   
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Teléfono / Email</p>
                    <p class="font-medium text-gray-900 mt-1"><a href="mailto:{{ $employee->user->email }}" class="text-sga-primary hover:underline">{{ $employee->user->email }}</a></p>
                </div>
                <div>   
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Puntuación Rendimiento</p>
                    <div class="flex items-center mt-1">
                        @for($i=1; $i<=5; $i++)
                            <i class="fas fa-star text-yellow-400 text-sm"></i>
                        @endfor
                        <span class="ml-2 text-sm text-gray-500 font-medium">(100%)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick KPIs (Analíticas) -->
    <h3 class="text-lg font-bold text-gray-900 ml-1 mt-6">Rendimiento Inmediato (Mes Actual)</h3>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- KPI 1 -->
        <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 mt-5 mr-5 opacity-10 group-hover:scale-110 transition-transform"><i class="fas fa-coins text-4xl text-emerald-500"></i></div>
            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-1">PROYECCIÓN SALARIAL NETA</p>
            <h4 class="text-2xl font-extrabold text-gray-900">RD$ {{ number_format($employee->contract_type === 'Mensual' ? $employee->base_salary * 0.9409 : ($employee->attendances->where('punch_type', 0)->count() * 4 * $employee->hourly_rate * 0.9409), 2) }}</h4>
            <p class="text-xs text-gray-500 mt-2 font-medium">Estimado neto cobrando tras ley.</p>
        </div>
        <!-- KPI 2 -->
        <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 mt-5 mr-5 opacity-10 group-hover:scale-110 transition-transform"><i class="fas fa-fingerprint text-4xl text-blue-500"></i></div>
            <p class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">ASISTENCIAS (ÚLT 30 DÍAS)</p>
            <h4 class="text-2xl font-extrabold text-gray-900">{{ $employee->attendances->where('punch_time', '>=', now()->subDays(30))->count() }} Ponches</h4>
            <p class="text-xs text-gray-500 mt-2 font-medium">Reportados en ZKTeco Kiosk.</p>
        </div>
        <!-- KPI 3 -->
        <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 mt-5 mr-5 opacity-10 group-hover:scale-110 transition-transform"><i class="fas fa-wallet text-4xl text-indigo-500"></i></div>
            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-1">HISTÓRICO LIQUIDADO</p>
            <h4 class="text-2xl font-extrabold text-gray-900">RD$ {{ number_format($employee->payrollItems->sum('net_amount'), 2) }}</h4>
            <p class="text-xs text-gray-500 mt-2 font-medium">Vía {{ $employee->payrollItems->count() }} nóminas automáticas.</p>
        </div>
        <!-- KPI 4 -->
        <div class="bg-gradient-to-br from-white to-gray-50 border border-gray-200 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute right-0 top-0 mt-5 mr-5 opacity-10 group-hover:scale-110 transition-transform"><i class="fas fa-exclamation-triangle text-4xl text-amber-500"></i></div>
            <p class="text-xs font-bold uppercase tracking-wider text-amber-600 mb-1">INCIDENCIAS ACTIVAS</p>
            <h4 class="text-2xl font-extrabold text-gray-900">0 Alertas</h4>
            <p class="text-xs text-gray-500 mt-2 font-medium">Amonestaciones / Retrasos severos.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 mt-8">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button @click="tab = 'finanzas'" :class="{'border-sga-primary text-sga-primary': tab === 'finanzas', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'finanzas'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm tracking-wide transition-colors">
                <i class="fas fa-money-check-alt mr-2"></i> Finanzas y Nómina
            </button>
            <button @click="tab = 'asistencia'" :class="{'border-sga-primary text-sga-primary': tab === 'asistencia', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'asistencia'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm tracking-wide transition-colors">
                <i class="fas fa-clock mr-2"></i> Control ZKTeco & Tiempos
            </button>
            <button @click="tab = 'personal'" :class="{'border-sga-primary text-sga-primary': tab === 'personal', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'personal'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm tracking-wide transition-colors">
                <i class="fas fa-user-tie mr-2"></i> Gestión de Personal
            </button>
            <button @click="tab = 'seguridad'" :class="{'border-sga-primary text-sga-primary': tab === 'seguridad', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'seguridad'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm tracking-wide transition-colors">
                <i class="fas fa-shield-alt mr-2"></i> Seguridad de Cuenta
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->
    
    <!-- TAB 1: FINANZAS Y NÓMINA -->
    <div x-show="tab === 'finanzas'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna Izquierda: Acciones Rápidas Financieras -->
            <div class="col-span-1 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="font-bold text-gray-900 mb-4 border-b pb-2">Parámetros Salariales</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">Tipo Contrato</p>
                                <p class="font-bold text-gray-900">{{ $employee->contract_type }}</p>
                            </div>
                            <button wire:click.prevent="openSalaryModal" class="text-sga-primary hover:bg-blue-50 p-2 rounded-md"><i class="fas fa-edit"></i></button>
                        </div>
                        <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold">{{ $employee->contract_type === 'Mensual' ? 'Salario Base Bruto' : 'Tarifa por Hora' }}</p>
                                <p class="font-bold text-gray-900">RD$ {{ number_format($employee->contract_type === 'Mensual' ? $employee->base_salary : $employee->hourly_rate, 2) }}</p>
                            </div>
                            <button wire:click.prevent="openSalaryModal" class="text-sga-primary hover:bg-blue-50 p-2 rounded-md"><i class="fas fa-edit"></i></button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="font-bold text-gray-900 mb-4 border-b pb-2"><i class="fas fa-bolt text-amber-500 mr-2"></i> Operaciones Contables</h4>
                    <div class="space-y-2">
                        <button wire:click.prevent="openEventModal('bonus')" class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:border-emerald-300 hover:bg-emerald-50 transition-colors flex items-center group">
                            <div class="bg-emerald-100 text-emerald-600 rounded-lg p-2 mr-3 group-hover:bg-emerald-200"><i class="fas fa-plus"></i></div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Registrar Bono Extra</p>
                                <p class="text-xs text-gray-500">Incentivos o comisiones ad-hoc</p>
                            </div>
                        </button>
                        <button wire:click.prevent="openEventModal('deduction')" class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:border-rose-300 hover:bg-rose-50 transition-colors flex items-center group">
                            <div class="bg-rose-100 text-rose-600 rounded-lg p-2 mr-3 group-hover:bg-rose-200"><i class="fas fa-minus"></i></div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Registrar Deducción Judicial</p>
                                <p class="text-xs text-gray-500">Préstamos o retenciones especiales</p>
                            </div>
                        </button>
                        <button wire:click.prevent="showToast('Generando PDF corporativo... pronto estará en Descargas.')" class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors flex items-center group">
                            <div class="bg-indigo-100 text-indigo-600 rounded-lg p-2 mr-3 group-hover:bg-indigo-200"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Carta de Ingresos (PDF)</p>
                                <p class="text-xs text-gray-500">Para fines consulares o bancarios</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Histórico de Volantes y Breakdown -->
            <div class="col-span-1 lg:col-span-2 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h4 class="font-bold text-gray-900"><i class="fas fa-history text-gray-400 mr-2"></i> Histórico de Volantes (Últimos 10)</h4>
                        <span class="text-xs bg-sga-bg px-2 py-1 rounded-md text-sga-text font-medium border border-gray-200">Ley 87-01 Automática</span>
                    </div>
                    
                    @if($employee->payrollItems->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lote / Fecha</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Monto Bruto</th>
                                        <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deducciones (Ley)</th>
                                        <th class="px-5 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Monto Neto</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($employee->payrollItems->sortByDesc('id')->take(10) as $item)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <div class="font-bold text-sm text-gray-900">{{ $item->payroll->name }}</div>
                                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($item->payroll->end_date)->format('M y') }} &bull; Pagado</div>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap text-sm text-gray-700">RD$ {{ number_format($item->base_amount, 2) }}</td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="text-rose-600 font-medium text-sm">- RD$ {{ number_format($item->deductions, 2) }}</span>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap text-sm font-bold text-emerald-600 text-right bg-emerald-50/30">
                                            RD$ {{ number_format($item->net_amount, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <i class="fas fa-file-invoice text-4xl text-gray-300 mb-3"></i>
                            <p class="font-medium text-gray-900">Aún no hay registros de pago</p>
                            <p class="text-sm text-gray-500 mt-1">Cuando apruebes una nómina general, el histórico aparecerá aquí.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: ZKTECO Y ASISTENCIA -->
    <div x-show="tab === 'asistencia'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna Izquierda: Config ZK y Acciones -->
            <div class="col-span-1 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="font-bold text-gray-900 mb-4 border-b pb-2"><i class="fas fa-fingerprint text-blue-500 mr-2"></i> ID Biométrico ZKTeco</h4>
                    <div class="flex items-center justify-between bg-blue-50/50 p-4 rounded-xl border border-blue-100">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-full bg-white flex items-center justify-center border-2 border-blue-200 shadow-sm">
                                <i class="fas fa-id-card text-blue-500 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-blue-800 font-bold uppercase tracking-wider">Device User ID</p>
                                <p class="text-2xl font-black text-blue-900 tracking-widest">{{ $employee->biometric_id ?? '---' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <x-text-input type="number" wire:model="biometric_id" placeholder="Nuevo ID ZK..." class="w-full text-sm" />
                        <button wire:click.prevent="updateBiometricId" class="bg-sga-primary text-white px-3 py-2 rounded-md hover:bg-sga-primary-dark font-bold text-sm">Salvar</button>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="font-bold text-gray-900 mb-4 border-b pb-2"><i class="fas fa-calendar-check text-indigo-500 mr-2"></i> Cargar Excepción de Asistencia</h4>
                    <div class="space-y-2">
                        <button wire:click.prevent="openEventModal('medical')" class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:border-cyan-300 hover:bg-cyan-50 transition-colors flex items-center group">
                            <div class="bg-cyan-100 text-cyan-600 rounded-lg p-2 mr-3 group-hover:bg-cyan-200"><i class="fas fa-notes-medical"></i></div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Licencia Médica / Excusa</p>
                                <p class="text-xs text-gray-500">Justificar fechas para récord legal.</p>
                            </div>
                        </button>
                        <button wire:click.prevent="openEventModal('overtime')" class="w-full text-left px-4 py-3 rounded-lg border border-gray-200 hover:border-amber-300 hover:bg-amber-50 transition-colors flex items-center group">
                            <div class="bg-amber-100 text-amber-600 rounded-lg p-2 mr-3 group-hover:bg-amber-200"><i class="fas fa-business-time"></i></div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">Asignar Horas Extra</p>
                                <p class="text-xs text-gray-500">Aumentos salariales manuales de asiduidad.</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Feed de Ponches -->
            <div class="col-span-1 lg:col-span-2 space-y-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h4 class="font-bold text-gray-900"><i class="fas fa-stream text-gray-400 mr-2"></i> Feed de Registros de Reloj Kiosco/ZK</h4>
                        <span class="text-xs bg-sga-bg px-2 py-1 rounded-md text-sga-text font-medium border border-gray-200">50 más recientes</span>
                    </div>

                    @if($employee->attendances->count() > 0)
                        <div class="p-0 max-h-[500px] overflow-y-auto">
                            <ul class="divide-y divide-gray-100">
                                @foreach($employee->attendances as $punch)
                                <li class="p-4 hover:bg-gray-50 flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        @if($punch->punch_type == 0 || $punch->punch_type == 4) <!-- Check In / Overtime In -->
                                            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">Entrada (Check-In)</p>
                                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($punch->punch_time)->format('l, d M Y') }}</p>
                                            </div>
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-rose-100 flex items-center justify-center text-rose-600">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">Salida (Check-Out)</p>
                                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($punch->punch_time)->format('l, d M Y') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-black text-gray-900">{{ \Carbon\Carbon::parse($punch->punch_time)->format('h:i A') }}</div>
                                        <div class="text-xs text-gray-400 font-medium">Sincronizado via Local Kiosk</div>
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="p-8 text-center border-t border-gray-100">
                            <i class="fas fa-clock text-4xl text-gray-300 mb-3"></i>
                            <p class="font-medium text-gray-900">Sin huellas biológicas / ponches a mostrar.</p>
                            <p class="text-sm text-gray-500 mt-1">Asegúrate de que el ID ZKTeco corresponda al reloj o que el empleado haya ponchado mediante `/kiosk/hr-punch`.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- TAB 3: GESTIÓN PERSONAL (MÓDULOS DE ACCIÓN) -->
    <div x-show="tab === 'personal'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <i class="fas fa-arrow-up text-3xl text-emerald-500 mb-4"></i>
                <h4 class="font-bold text-gray-900 text-lg">Ascenso / Cambio Cargo</h4>
                <p class="text-sm text-gray-500 mt-1 mb-4">Registrar mérito y actualizar departamento o cargo público.</p>
                <button wire:click.prevent="openEventModal('promotion')" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-emerald-50 hover:text-emerald-700 transition">Asentar Ascenso</button>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <i class="fas fa-file-signature text-3xl text-amber-500 mb-4"></i>
                <h4 class="font-bold text-gray-900 text-lg">Amonestación Escrita</h4>
                <p class="text-sm text-gray-500 mt-1 mb-4">Emitir Memo de RRHH al expediente para sustento de despido legal.</p>
                <button wire:click.prevent="openEventModal('memo')" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-amber-50 hover:text-amber-700 transition">Añadir Memo</button>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <i class="fas fa-plane-departure text-3xl text-sky-500 mb-4"></i>
                <h4 class="font-bold text-gray-900 text-lg">Asignar Vacaciones</h4>
                <p class="text-sm text-gray-500 mt-1 mb-4">Programar período de 14+ días remunerados.</p>
                <button wire:click.prevent="openEventModal('vacation')" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-sky-50 hover:text-sky-700 transition">Agendar Fechas</button>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                <i class="fas fa-clipboard-list text-3xl text-fuchsia-500 mb-4"></i>
                <h4 class="font-bold text-gray-900 text-lg">Evaluación Desempeño</h4>
                <p class="text-sm text-gray-500 mt-1 mb-4">Registrar la matriz histórica de calificación (KRA / Métricas).</p>
                <button wire:click.prevent="openEventModal('evaluation')" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-2 rounded-lg font-semibold hover:bg-fuchsia-50 hover:text-fuchsia-700 transition">Evaluar KRA</button>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 md:col-span-2 hover:shadow-md transition-shadow border-l-4 border-l-rose-500">
                <div class="flex items-start justify-between">
                    <div>
                        <i class="fas fa-user-slash text-3xl text-rose-500 mb-4"></i>
                        <h4 class="font-bold text-gray-900 text-lg">Desvinculación Corporativa</h4>
                        <p class="text-sm text-gray-500 mt-1 max-w-md">Asentar reporte de desvinculación, registrar el motivo oficial para el cálculo de liquidación y preparar expediente offline.</p>
                    </div>
                    <button wire:click.prevent="openEventModal('termination')" class="bg-rose-100 text-rose-700 font-bold px-6 py-3 rounded-lg hover:bg-rose-600 hover:text-white transition-all shadow-sm">Iniciar Proceso</button>
                </div>
            </div>
        </div>
        
        <!-- Historial de Eventos -->
        <h4 class="font-bold text-gray-900 mt-6 mb-2 border-b pb-2"><i class="fas fa-book-open text-sga-primary mr-2"></i> Timeline Histórico de RRHH</h4>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            @if($employee->events->count() > 0)
            <div class="space-y-4">
                @foreach($employee->events as $event)
                <div class="flex items-start gap-4">
                    <div class="mt-1">
                        @if($event->type === 'bonus') <i class="fas fa-plus-circle text-emerald-500 text-xl"></i>
                        @elseif($event->type === 'deduction') <i class="fas fa-minus-circle text-rose-500 text-xl"></i>
                        @elseif($event->type === 'memo') <i class="fas fa-exclamation-triangle text-amber-500 text-xl"></i>
                        @elseif($event->type === 'vacation') <i class="fas fa-umbrella-beach text-sky-500 text-xl"></i>
                        @elseif($event->type === 'evaluation') <i class="fas fa-star text-yellow-400 text-xl"></i>
                        @elseif($event->type === 'promotion') <i class="fas fa-level-up-alt text-emerald-600 text-xl"></i>
                        @else <i class="fas fa-info-circle text-gray-400 text-xl"></i> @endif
                    </div>
                    <div class="flex-1 bg-gray-50 rounded p-3">
                        <p class="font-bold text-gray-900 text-sm uppercase">{{ $event->type }} &bull; {{ $event->event_date->format('d/m/Y') }}</p>
                        <p class="text-gray-700 text-sm mt-1">{{ $event->description }}</p>
                        @if($event->amount) <p class="text-xs text-gray-500 mt-2 font-bold">Monto: RD$ {{ number_format($event->amount, 2) }}</p>@endif
                        @if($event->score) <p class="text-xs text-gray-500 mt-2 font-bold">Calificación: {{ $event->score }}/5 Estrellas</p>@endif
                        @if($event->end_date) <p class="text-xs text-gray-500 mt-2 font-bold">Retorno: {{ $event->end_date->format('d/m/Y') }}</p>@endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-gray-500">El expediente táctico de RRHH de este empleado no tiene registros.</p>
            @endif
        </div>
    </div>

    <!-- TAB 4: SEGURIDAD CUENTA -->
    <div x-show="tab === 'seguridad'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 border-b pb-4 mb-6"><i class="fas fa-lock text-sga-primary mr-2"></i> Configuración Maestra de Sistema Acoplado</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Spatie Roles -->
                    <div class="bg-gray-50/50 p-5 rounded-xl border border-gray-200">
                        <label class="block text-sm font-bold text-gray-900 mb-1">Permisos Raíz (Rol Spatie)</label>
                        <p class="text-xs text-gray-500 mb-4">Al cambiar el rol, los nuevos menús aparecerán de inmediato para este usuario en SGA-CENTU.</p>
                        <div class="flex gap-2">
                            <select wire:model="spatie_role" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary focus:ring-sga-primary sm:text-sm">
                                <option value="">Remover todos los accesos...</option>
                                @foreach(\Spatie\Permission\Models\Role::orderBy('name')->get() as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            <button wire:click="updateSpatieRole" class="bg-gray-800 text-white px-4 py-2 rounded-md font-bold text-sm hover:bg-black transition-colors">Aplicar</button>
                        </div>
                    </div>
                    
                    <!-- Kiosk PIN -->
                    <div class="bg-gray-50/50 p-5 rounded-xl border border-gray-200 flex flex-col justify-between">
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-1">PIN Local (Kiosco Táctil)</label>
                            <p class="text-xs text-gray-500 mb-4">Autenticación para ponches sin tarjeta o reportes híbridos.</p>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-2xl font-black text-gray-800 bg-white px-4 py-1 rounded shadow-inner border border-gray-300">
                                **** <!-- Hidden for privacy -->
                            </span>
                            <button wire:click.prevent="regeneratePin" wire:confirm="¿Desea invalidar el PIN actual y generar uno nuevo?" class="text-sga-primary font-bold hover:underline text-sm"><i class="fas fa-sync-alt mr-1"></i> Regenerar PIN 4-dígitos</button>
                        </div>
                    </div>

                    <!-- Hard Security Actions -->
                    <div class="md:col-span-2 border-t border-gray-200 pt-6 mt-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <button wire:click.prevent="revokeTokens" wire:confirm="¿Seguro que deseas eliminar los tokens y desconectar este usuario?" class="col-span-1 p-4 rounded-xl border-2 border-slate-200 hover:border-slate-800 group transition-all text-left">
                            <i class="fas fa-sign-out-alt text-2xl text-slate-400 group-hover:text-slate-800 mb-2 transition-colors"></i>
                            <h4 class="font-bold text-gray-900">Revocar Tokens</h4>
                            <p class="text-xs text-gray-500 mt-1">Forzar cierre sesión todos los equipos.</p>
                        </button>
                        
                        <button wire:click.prevent="sendPasswordReset" class="col-span-1 p-4 rounded-xl border-2 border-blue-200 hover:border-blue-600 group transition-all text-left">
                            <i class="fas fa-key text-2xl text-blue-400 group-hover:text-blue-600 mb-2 transition-colors"></i>
                            <h4 class="font-bold text-gray-900">Resetear Contraseña</h4>
                            <p class="text-xs text-gray-500 mt-1">Enviar email con token de reseteo Mailable.</p>
                        </button>
                        
                        <button wire:click.prevent="banUser" wire:confirm="¿Expulsar del sistema y bloquear la cuenta a nivel de middleware? Esta acción es severa." class="col-span-1 p-4 rounded-xl border-2 border-red-200 hover:border-red-600 group transition-all text-left bg-red-50/30">
                            <i class="fas fa-ban text-2xl text-red-500 group-hover:scale-110 mb-2 transition-transform"></i>
                            <h4 class="font-bold text-red-700">Bloquear Accesos</h4>
                            <p class="text-xs text-red-500 mt-1 font-medium">Banear usuario a nivel middleware de inmediato.</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALES FUNCIONALES -->
    
    <!-- Modal: Editar Salario -->
    <x-modal name="salary-modal" maxWidth="md">
        <form wire:submit.prevent="saveSalary">
            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 rounded-t-lg">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-money-check-alt text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900 mb-4">Parámetros Salariales</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Contrato</label>
                                <select wire:model.live="edit_contract_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary sm:text-sm">
                                    <option value="Mensual">Fijo (Mensual)</option>
                                    <option value="Por Horas">Por Horas Impartidas</option>
                                </select>
                            </div>
                            
                            @if($edit_contract_type === 'Mensual')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Salario Base Bruto (RD$)</label>
                                    <input type="number" step="0.01" wire:model="edit_base_salary" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary sm:text-sm">
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tarifa por Hora (RD$)</label>
                                    <input type="number" step="0.01" wire:model="edit_hourly_rate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary sm:text-sm">
                                </div>
                            @endif
                            <p class="text-xs text-gray-500 bg-gray-50 p-2 rounded border border-gray-100">
                                <i class="fas fa-info-circle mr-1"></i> Este parámetro dictará los montos de deducciones de ley SFS/AFP mensuales.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 rounded-b-lg">
                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-sga-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto">
                    Guardar Cambios
                </button>
                <button type="button" x-on:click="$dispatch('close')" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    Cancelar
                </button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Agregar Evento Polimórfico al Expediente -->
    <x-modal name="event-modal" maxWidth="lg">
        <form wire:submit.prevent="saveEvent">
            <div class="bg-gray-50 px-4 pb-4 pt-5 sm:p-6 sm:pb-4 rounded-t-lg border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 capitalize" style="text-transform: capitalize;">
                    Registrar Acción: {{ str_replace('_', ' ', $event_type) }}
                </h3>
            </div>
            
            <div class="bg-white px-4 py-5 sm:p-6 space-y-4">
                <!-- Date -->
                <div>
                    <label class="block text-sm font-bold text-gray-700">Fecha Efectiva del Evento <span class="text-red-500">*</span></label>
                    <input type="date" wire:model="event_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary" required>
                    @error('event_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Fields based on Event Type -->
                @if(in_array($event_type, ['vacation', 'medical']))
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Fecha de Reintegro Proyectada <span class="text-red-500">*</span></label>
                        <input type="date" wire:model="event_end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary" required>
                        @error('event_end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if(in_array($event_type, ['bonus', 'deduction']))
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Monto Monetario Libre (RD$) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" wire:model="event_amount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary" placeholder="0.00" required>
                        @error('event_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if($event_type === 'evaluation')
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Puntaje General (Estrellas) <span class="text-red-500">*</span></label>
                        <select wire:model="event_score" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary" required>
                            <option value="">Seleccione puntuación (1 Pésimo - 5 Excelente)...</option>
                            <option value="1">1 Estrella - Deficiente</option>
                            <option value="2">2 Estrellas - Precisa Mejora</option>
                            <option value="3">3 Estrellas - Aceptable</option>
                            <option value="4">4 Estrellas - Bueno / Supera expectativas</option>
                            <option value="5">5 Estrellas - Excelente KRA</option>
                        </select>
                        @error('event_score') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Description / Justification -->
                <div>
                    <label class="block text-sm font-bold text-gray-700">Descripción / Motivo Oficial (Memoria) <span class="text-red-500">*</span></label>
                    <textarea wire:model="event_description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-sga-primary text-sm" placeholder="Explica detalladamente las razones corporativas del evento..." required></textarea>
                    @error('event_description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 rounded-b-lg border-t border-gray-200">
                <button type="submit" class="inline-flex w-full justify-center rounded-md bg-sga-primary px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 sm:ml-3 sm:w-auto mt-2 sm:mt-0">
                    Asentar Registro Legal
                </button>
                <button type="button" x-on:click="$dispatch('close')" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-4 py-2 text-sm font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                    Descartar Operación
                </button>
            </div>
        </form>
    </x-modal>

</div>
