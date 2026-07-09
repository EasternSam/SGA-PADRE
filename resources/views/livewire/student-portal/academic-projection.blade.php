<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6" x-data="{ activeTab: 'plan' }">
    <!-- Mensajes de Alerta -->
    @if ($successMessage || $errorMessage)
        <div class="space-y-2">
            @if ($successMessage)
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-sm flex items-center justify-between animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $successMessage }}</span>
                    </div>
                    <button wire:click="resetMessages" class="text-emerald-500 hover:text-emerald-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
            @if ($errorMessage)
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm flex items-center justify-between animate-fade-in">
                    <div class="flex items-center space-x-2">
                        <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ $errorMessage }}</span>
                    </div>
                    <button wire:click="resetMessages" class="text-rose-500 hover:text-rose-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif
        </div>
    @endif

    <!-- Banner Premium -->
    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_120%,rgba(99,102,241,0.15),transparent)] pointer-events-none"></div>
        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 text-xs font-semibold uppercase tracking-wider rounded-full border border-indigo-500/30">
                    Proyección de Carrera
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-2 text-transparent bg-clip-text bg-gradient-to-r from-white via-indigo-100 to-indigo-200">
                    Plan de Estudios Proyectado
                </h1>
                <p class="text-slate-400 mt-2 text-sm sm:text-base">
                    Estudiante: <span class="text-white font-medium">{{ $student->full_name }}</span> | Programa: <span class="text-white font-medium">{{ $career->name }}</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button wire:click="autoGeneratePlan" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-indigo-500/25 transition duration-300 flex items-center space-x-2 text-sm border border-indigo-500/50">
                    <svg class="h-5 w-5 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <span>Auto-Proyectar Carrera</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Módulo de Progreso / Auditoría de Grado -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Barra de Progreso Circular / Panel -->
        <div class="md:col-span-1 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col items-center justify-center text-center">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Progreso Académico</h3>
            <div class="relative w-36 h-36 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="72" cy="72" r="60" stroke="#f1f5f9" stroke-width="12" fill="transparent" />
                    <circle cx="72" cy="72" r="60" stroke="#6366f1" stroke-width="12" fill="transparent" 
                            stroke-dasharray="377" 
                            stroke-dashoffset="{{ 377 - (377 * $progressPercentage) / 100 }}"
                            stroke-linecap="round"
                            class="transition-all duration-1000 ease-out" />
                </svg>
                <div class="absolute flex flex-col items-center">
                    <span class="text-3xl font-extrabold text-slate-800">{{ $progressPercentage }}%</span>
                    <span class="text-xs font-semibold text-slate-400">Completado</span>
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-4 leading-relaxed">
                Progreso calculado según créditos aprobados y cursando sobre el total del pensum.
            </p>
        </div>

        <!-- Indicadores Numéricos -->
        <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:border-emerald-200 transition duration-300">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-emerald-500 bg-emerald-50 px-2 py-1 rounded">Aprobado</span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-extrabold text-slate-800">{{ $completedCredits }}</span>
                    <span class="text-sm font-medium text-slate-500 block">Créditos Aprobados</span>
                </div>
                <div class="mt-2 text-xs text-slate-400">De un total de {{ $totalCredits }} créditos</div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:border-indigo-200 transition duration-300">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-indigo-500 bg-indigo-50 px-2 py-1 rounded">Cursando</span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-extrabold text-slate-800">{{ $inProgressCredits }}</span>
                    <span class="text-sm font-medium text-slate-500 block">Créditos Cursando</span>
                </div>
                <div class="mt-2 text-xs text-slate-400">Inscritas actualmente</div>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col justify-between group hover:border-violet-200 transition duration-300">
                <div class="flex items-center justify-between">
                    <div class="p-3 rounded-xl bg-violet-50 text-violet-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-violet-500 bg-violet-50 px-2 py-1 rounded">Planificado</span>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-extrabold text-slate-800">{{ $plannedCredits }}</span>
                    <span class="text-sm font-medium text-slate-500 block">Créditos Proyectados</span>
                </div>
                <div class="mt-2 text-xs text-slate-400">Planificados para el futuro</div>
            </div>
        </div>
    </div>

    <!-- Ajustes del Plan -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-800">Ajustar Carga de Estudio</h4>
                <p class="text-xs text-slate-400">Define cuántas asignaturas planeas tomar por período</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs text-slate-500">Ritmo:</span>
            <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50">
                <button wire:click="changePace(4)" class="px-3 py-1 rounded-md text-xs font-semibold transition {{ $pace === 4 ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">4 Materias</button>
                <button wire:click="changePace(5)" class="px-3 py-1 rounded-md text-xs font-semibold transition {{ $pace === 5 ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">5 Materias</button>
                <button wire:click="changePace(6)" class="px-3 py-1 rounded-md text-xs font-semibold transition {{ $pace === 6 ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">6 Materias</button>
            </div>
        </div>
    </div>

    <!-- Navegación de Pestañas -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-8" aria-label="Tabs">
            <button @click="activeTab = 'plan'" :class="activeTab === 'plan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition duration-150">
                Línea de Tiempo Proyectada (Semestres)
            </button>
            <button @click="activeTab = 'pensum'" :class="activeTab === 'pensum' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-semibold text-sm transition duration-150">
                Malla Curricular Completa (Pensum)
            </button>
        </nav>
    </div>

    <!-- Pestaña 1: Línea de Tiempo Proyectada -->
    <div x-show="activeTab === 'plan'" class="space-y-6">
        @php
            $anyPlanned = count($plannedModulesGrouped) > 0;
        @endphp

        @if (!$anyPlanned)
            <div class="bg-slate-50 border border-dashed border-slate-200 rounded-2xl p-12 text-center max-w-lg mx-auto space-y-4">
                <div class="inline-flex p-4 rounded-full bg-indigo-50 text-indigo-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Aún no has planificado tu carrera</h3>
                <p class="text-sm text-slate-500">
                    Haz clic en el botón "Auto-Proyectar Carrera" en la parte superior para generar una proyección automática de materias basada en tus prerrequisitos pendientes.
                </p>
                <button wire:click="autoGeneratePlan" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-indigo-500 transition">
                    Proyectar Automáticamente
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach (collect($plannedModulesGrouped)->keys()->sort() as $period)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between group hover:border-indigo-100 transition duration-300">
                        <!-- Card Header -->
                        <div class="p-4 bg-slate-50/50 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-800">Período / Semestre {{ $period }}</span>
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-700 text-xs font-semibold rounded">
                                {{ collect($plannedModulesGrouped[$period])->sum('module.credits') }} Créditos
                            </span>
                        </div>
                        <!-- Card Body -->
                        <div class="p-4 flex-1 divide-y divide-slate-100">
                            @foreach ($plannedModulesGrouped[$period] as $plannedItem)
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $plannedItem->module->code }}</p>
                                        <p class="text-sm font-bold text-slate-800 truncate" title="{{ $plannedItem->module->name }}">
                                            {{ $plannedItem->module->name }}
                                        </p>
                                        <span class="text-xs text-slate-400">{{ $plannedItem->module->credits }} créditos</span>
                                    </div>
                                    <button wire:click="unplanModule({{ $plannedItem->id }})" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Quitar de la proyección">
                                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Pestaña 2: Malla Curricular Completa -->
    <div x-show="activeTab === 'pensum'" class="space-y-8">
        @foreach (collect($modulesByPeriod)->keys()->sort() as $periodNumber)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Pensum - Período {{ $periodNumber }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Asignatura</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Créditos</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($modulesByPeriod[$periodNumber] as $module)
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-400">{{ $module['code'] }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-slate-800">{{ $module['name'] }}</div>
                                        @if (!empty($module['missing_prereqs']))
                                            <div class="text-xs text-rose-500 font-semibold mt-1">
                                                Requisitos faltantes: {{ implode(', ', $module['missing_prereqs']) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center font-semibold">{{ $module['credits'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($module['status'] === 'aprobada')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                Aprobada
                                            </span>
                                        @elseif ($module['status'] === 'cursando')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                Cursando
                                            </span>
                                        @elseif ($module['status'] === 'planificada')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-800">
                                                Proyectada (Período {{ $module['planned_period'] }})
                                            </span>
                                        @elseif ($module['status'] === 'disponible')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">
                                                Disponible
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-800">
                                                Bloqueada
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        @if ($module['status'] === 'disponible')
                                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                                <button @click="open = !open" type="button" class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition">
                                                    Planificar
                                                    <svg class="ml-1.5 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white border border-slate-100 ring-1 ring-black ring-opacity-5 z-20 overflow-hidden divide-y divide-slate-100" style="display: none;">
                                                    <div class="py-1">
                                                        @for ($p = 1; $p <= 12; $p++)
                                                            <button wire:click="planModuleManually({{ $module['id'] }}, {{ $p }}); open = false" class="block w-full text-left px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                                Planificar Período {{ $p }}
                                                            </button>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif ($module['status'] === 'planificada')
                                            <button wire:click="unplanModule({{ $module['planned_id'] }})" class="inline-flex items-center px-3 py-1.5 border border-rose-200 text-rose-700 rounded-lg text-xs font-semibold bg-rose-50 hover:bg-rose-100 transition">
                                                Desplanificar
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400 italic">No disponible</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
