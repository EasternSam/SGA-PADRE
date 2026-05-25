<div class="p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Centro de Reportes</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Genera reportes MINERD, documentos oficiales y estadísticas</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sección</label>
            <select wire:model.live="selectedSection" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Seleccionar sección...</option>
                @foreach($sections as $s)
                    <option value="{{ $s->id }}">{{ $s->gradeLevel?->short_name }} {{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Período</label>
            <select wire:model.live="selectedPeriod" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Seleccionar período...</option>
                @foreach($periods as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mes (Asistencia)</label>
            <select wire:model.live="selectedMonth" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                @foreach($months as $num => $name)
                    <option value="{{ $num }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- REPORTES MINERD --}}
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        Reportes MINERD Oficiales
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <a href="{{ route('reports.minerd.re1') }}" target="_blank" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition dark:bg-gray-800 dark:border-gray-700">
            <div class="text-2xl mb-2"></div>
            <h3 class="font-bold text-gray-900 dark:text-white">RE-1</h3>
            <p class="text-xs text-gray-500 mt-1">Registro de Estudiantes — Matrícula completa por sección</p>
        </a>

        @if($selectedPeriod)
            <a href="{{ route('reports.minerd.re2', $selectedPeriod) }}" target="_blank" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition dark:bg-gray-800 dark:border-gray-700">
                <div class="text-2xl mb-2"></div>
                <h3 class="font-bold text-gray-900 dark:text-white">RE-2</h3>
                <p class="text-xs text-gray-500 mt-1">Calificaciones por Período — Todas las secciones</p>
            </a>
        @else
            <div class="rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-5 text-center dark:bg-gray-700/30 dark:border-gray-600">
                <div class="text-2xl mb-2 opacity-40"></div>
                <h3 class="font-bold text-gray-400">RE-2</h3>
                <p class="text-xs text-gray-400 mt-1">Selecciona un período arriba</p>
            </div>
        @endif

        <a href="{{ route('reports.minerd.re3', ['month' => $selectedMonth]) }}" target="_blank" class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-blue-300 transition dark:bg-gray-800 dark:border-gray-700">
            <div class="text-2xl mb-2"></div>
            <h3 class="font-bold text-gray-900 dark:text-white">RE-3</h3>
            <p class="text-xs text-gray-500 mt-1">Asistencia Mensual — {{ $months[$selectedMonth] ?? '' }}</p>
        </a>
    </div>

    {{-- REPORTES POR SECCIÓN --}}
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        Reportes por Sección
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        @if($selectedSection)
            <a href="{{ route('reports.attendance.section', $selectedSection) }}" target="_blank" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition dark:bg-gray-800 dark:border-gray-700">
                <div class="text-lg mb-1"></div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Asistencia</h3>
                <p class="text-[10px] text-gray-500">Matriz mensual de asistencia</p>
            </a>
            <a href="{{ route('reports.schedule.section', $selectedSection) }}" target="_blank" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition dark:bg-gray-800 dark:border-gray-700">
                <div class="text-lg mb-1"></div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Horario</h3>
                <p class="text-[10px] text-gray-500">Horario semanal de la sección</p>
            </a>
            <a href="{{ route('documents.lista', $selectedSection) }}" target="_blank" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition dark:bg-gray-800 dark:border-gray-700">
                <div class="text-lg mb-1"></div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Lista de Clase</h3>
                <p class="text-[10px] text-gray-500">Nómina con datos básicos</p>
            </a>
            @if($selectedPeriod)
                <a href="{{ route('reports.grades.section', [$selectedSection, $selectedPeriod]) }}" target="_blank" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition dark:bg-gray-800 dark:border-gray-700">
                    <div class="text-lg mb-1"></div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Calificaciones</h3>
                    <p class="text-[10px] text-gray-500">Matriz de notas de la sección</p>
                </a>
            @endif
            @if($selectedPeriod)
                <a href="{{ route('reports.report-cards.batch', [$selectedSection, $selectedPeriod]) }}" target="_blank" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition dark:bg-gray-800 dark:border-gray-700">
                    <div class="text-lg mb-1"></div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Boletines Batch</h3>
                    <p class="text-[10px] text-gray-500">Todos los boletines de la sección</p>
                </a>
            @endif
        @else
            <div class="col-span-4 rounded-xl border-2 border-dashed border-gray-300 p-6 text-center dark:border-gray-600">
                <p class="text-sm text-gray-400">Selecciona una sección para ver los reportes disponibles</p>
            </div>
        @endif
    </div>

    {{-- EXPORTACIONES CSV --}}
    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
        Exportaciones CSV / Excel
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        @if($selectedSection)
            <a href="{{ route('export.students', $selectedSection) }}" class="rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm hover:shadow-md transition dark:bg-green-900/20 dark:border-green-800">
                <div class="text-lg mb-1"></div>
                <h3 class="text-sm font-bold text-green-900 dark:text-green-400">Lista CSV</h3>
                <p class="text-[10px] text-green-700">Exportar nómina de estudiantes</p>
            </a>
            <a href="{{ route('export.attendance', $selectedSection) }}" class="rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm hover:shadow-md transition dark:bg-green-900/20 dark:border-green-800">
                <div class="text-lg mb-1"></div>
                <h3 class="text-sm font-bold text-green-900 dark:text-green-400">Asistencia CSV</h3>
                <p class="text-[10px] text-green-700">Exportar asistencia anual</p>
            </a>
            @if($selectedPeriod)
                <a href="{{ route('export.grades', [$selectedSection, $selectedPeriod]) }}" class="rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm hover:shadow-md transition dark:bg-green-900/20 dark:border-green-800">
                    <div class="text-lg mb-1"></div>
                    <h3 class="text-sm font-bold text-green-900 dark:text-green-400">Notas CSV</h3>
                    <p class="text-[10px] text-green-700">Exportar calificaciones</p>
                </a>
            @endif
        @endif
        <a href="{{ route('export.payments') }}" class="rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm hover:shadow-md transition dark:bg-green-900/20 dark:border-green-800">
            <div class="text-lg mb-1"></div>
            <h3 class="text-sm font-bold text-green-900 dark:text-green-400">Pagos CSV</h3>
            <p class="text-[10px] text-green-700">Exportar todos los pagos</p>
        </a>
    </div>

    {{-- INFO --}}
    <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
        <h4 class="text-sm font-bold text-blue-800 dark:text-blue-400 mb-2">Documentos Individuales</h4>
        <p class="text-xs text-blue-700 dark:text-blue-300">
            Para documentos por estudiante (Constancia, Certificado, Conducta, Récord, Ficha, Boletín Final), 
            ve a <strong>Ficha del Estudiante</strong> y selecciona el documento deseado desde el perfil.
        </p>
    </div>
</div>
