<div class="calendar-wrapper" x-data="{ showDetail: @entangle('selectedDate') }">
    
    <style>
        /* --- RESET Y BASE --- */
        .calendar-wrapper {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            height: calc(100vh - 64px); /* Ajuste para navbar existente */
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Importante */
        }

        /* --- HEADER DEL CALENDARIO --- */
        .calendar-header {
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            flex: none; /* No encoger/crecer */
            z-index: 20;
            padding: 1rem 1.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .month-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .month-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            text-transform: capitalize;
        }

        .month-icon {
            color: #2563eb;
            background-color: #eff6ff;
            padding: 0.375rem;
            border-radius: 0.5rem;
            border: 1px solid #dbeafe;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .month-icon svg { width: 1.5rem; height: 1.5rem; }

        .year-text {
            color: #9ca3af;
            font-weight: 400;
        }

        .nav-buttons {
            display: flex;
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.125rem;
        }

        .btn-nav {
            padding: 0.375rem;
            background: none;
            border: none;
            cursor: pointer;
            color: #6b7280;
            border-radius: 0.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-nav svg { width: 1.25rem; height: 1.25rem; }
        .btn-nav:hover { background-color: #f9fafb; color: #2563eb; }
        .nav-divider { width: 1px; background-color: #e5e7eb; margin: 0.25rem 0; }

        /* Filtros */
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .filter-label { cursor: pointer; user-select: none; }
        .filter-chip {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid #e5e7eb;
            background-color: white;
            color: #4b5563;
            transition: all 0.2s;
        }
        
        /* Estados de los checkbox (Simulados con clases activas o checked) */
        input:checked + .filter-chip.chip-blue { background-color: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        input:checked + .filter-chip.chip-green { background-color: #ecfdf5; color: #047857; border-color: #a7f3d0; }
        input:checked + .filter-chip.chip-amber { background-color: #fffbeb; color: #b45309; border-color: #fde68a; }

        .dot { width: 0.5rem; height: 0.5rem; border-radius: 50%; }
        .bg-blue { background-color: #3b82f6; }
        .bg-green { background-color: #10b981; }
        .bg-amber { background-color: #f59e0b; }

        /* --- GRID DEL CALENDARIO --- */
        .calendar-body {
            flex: 1; /* Ocupa el resto de la altura */
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Evita scroll doble */
        }

        .calendar-container {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            flex: 1; /* Se estira para llenar el body */
            overflow: hidden;
        }

        /* Días de la semana header */
        .week-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            flex: none; /* Altura fija */
        }

        .day-name {
            padding: 0.75rem 0.5rem;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            border-right: 1px solid #e5e7eb;
        }
        .day-name:last-child { border-right: none; }

        /* Rejilla de días */
        .days-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            /* Usamos auto-rows-fr para que todas las filas tengan la misma altura y llenen el espacio */
            grid-auto-rows: 1fr; 
            background-color: #e5e7eb; /* Color de las líneas del grid */
            gap: 1px; /* Espacio para las líneas */
            flex: 1; /* Se estira */
            overflow-y: auto;
        }

        .day-cell {
            background-color: white;
            min-height: 100px; /* Altura mínima visual */
            padding: 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            transition: background-color 0.2s;
            cursor: pointer;
            position: relative;
            overflow: hidden; /* Para contener chips */
        }
        .day-cell:hover { background-color: #eff6ff; }
        .day-cell.empty { background-color: #f9fafb; cursor: default; }
        .day-cell.today { background-color: rgba(239, 246, 255, 0.6); }

        .day-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .day-number {
            font-size: 0.875rem;
            font-weight: 600;
            width: 1.75rem;
            height: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #374151;
        }
        .day-cell.today .day-number {
            background-color: #2563eb;
            color: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        /* Chips de Eventos en el día */
        .event-chips-container {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
            overflow-y: auto; /* Scroll interno si hay muchos eventos */
            flex: 1; /* Ocupa espacio disponible */
            /* Ocultar scrollbar */
            scrollbar-width: none; 
            -ms-overflow-style: none;
        }
        .event-chips-container::-webkit-scrollbar { display: none; }

        .event-chip {
            padding: 0.25rem 0.5rem; 
            border-radius: 0.25rem;
            font-size: 0.9rem; /* Aumentado a casi 1rem para mejor lectura dentro de la celda */
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border-left-width: 3px;
            border-left-style: solid;
        }

        .chip-academic { background-color: #ecfdf5; color: #047857; border-left-color: #10b981; }
        .chip-event { background-color: #fffbeb; color: #b45309; border-left-color: #f59e0b; }
        .chip-class { background-color: #eff6ff; color: #1d4ed8; border-left-color: #3b82f6; }

        /* --- MODAL (CENTRAL) --- */
        .modal-overlay {
            position: fixed; inset: 0; z-index: 50; overflow-y: auto;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }

        .modal-backdrop {
            position: fixed; inset: 0;
            background-color: rgba(107, 114, 128, 0.75);
            backdrop-filter: blur(4px);
            transition: opacity 0.3s ease-out;
        }

        .modal-panel {
            position: relative;
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 32rem; /* Ancho máximo del modal */
            width: 100%;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 85vh; /* Altura máxima */
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s ease-out;
        }
        
        /* Estado activo del modal */
        .modal-open .modal-panel {
            transform: scale(1);
            opacity: 1;
        }

        .panel-header {
            background-color: #2563eb;
            padding: 1.5rem;
            color: white;
            position: relative;
        }
        
        .panel-title-row { display: flex; justify-content: space-between; align-items: center; }
        .panel-title { font-size: 1.25rem; font-weight: 600; margin: 0; }
        .close-btn {
            background: rgba(255,255,255,0.2); border: none; border-radius: 50%;
            padding: 0.25rem; color: #bfdbfe; cursor: pointer; display: flex;
            transition: background 0.2s;
        }
        .close-btn svg { width: 1.5rem; height: 1.5rem; }
        .close-btn:hover { color: white; background: rgba(255,255,255,0.3); }

        .panel-date { margin-top: 0.5rem; font-size: 0.875rem; color: #dbeafe; font-weight: 500; text-transform: capitalize; }

        .panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f9fafb;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .section-title {
            display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;
        }
        .icon-box {
            padding: 0.375rem; border-radius: 0.375rem; display: flex;
        }
        .icon-box svg { width: 1rem; height: 1rem; }
        .icon-box.green { background-color: #d1fae5; color: #059669; }
        .icon-box.blue { background-color: #dbeafe; color: #2563eb; }
        .icon-box.amber { background-color: #fef3c7; color: #d97706; }
        
        .section-heading { font-size: 0.875rem; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: 0.05em; margin: 0; }

        .card {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-bottom: 0.75rem;
        }
        .card-l-green { border-left: 4px solid #10b981; }
        .card-l-amber { border-left: 4px solid #f59e0b; }
        .card-l-blue { border-left: 4px solid #3b82f6; }

        .card-title { font-size: 0.95rem; font-weight: 600; color: #111827; margin: 0 0 0.25rem 0; }
        .card-desc { font-size: 0.8rem; color: #6b7280; margin: 0; }
        
        /* Utility */
        .empty-text { text-align: center; color: #9ca3af; font-size: 0.875rem; margin-top: 2rem; }
    </style>
    
    <!-- HEADER -->
    <div class="calendar-header">
        <div class="header-content">
            <!-- Título y Navegación -->
            <div class="month-controls">
                <h1 class="month-title">
                    <span class="month-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </span>
                    <span>{{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->locale('es')->monthName }}</span>
                    <span class="year-text">{{ $currentYear }}</span>
                </h1>
                
                <div class="nav-buttons">
                    <button wire:click="previousMonth" class="btn-nav">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <div class="nav-divider"></div>
                    <button wire:click="nextMonth" class="btn-nav">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filters">
                <label class="filter-label">
                    <input type="checkbox" wire:model.live="showClasses" style="display: none;">
                    <span class="filter-chip chip-blue">
                        <span class="dot bg-blue"></span> Clases
                    </span>
                </label>
                <label class="filter-label">
                    <input type="checkbox" wire:model.live="showStartsEnds" style="display: none;">
                    <span class="filter-chip chip-green">
                        <span class="dot bg-green"></span> Hitos
                    </span>
                </label>
                <label class="filter-label">
                    <input type="checkbox" wire:model.live="showAdmin" style="display: none;">
                    <span class="filter-chip chip-amber">
                        <span class="dot bg-amber"></span> Admin
                    </span>
                </label>
            </div>
        </div>
    </div>

    <!-- GRID CALENDARIO -->
    <div class="calendar-body">
        <div class="calendar-container">
            
            <!-- Header Días Semana -->
            <div class="week-header">
                @foreach(['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $day)
                    <div class="day-name">{{ $day }}</div>
                @endforeach
            </div>

            <!-- Celdas Días -->
            <div class="days-grid">
                @foreach($calendarDays as $dayData)
                    @if(is_null($dayData))
                        <div class="day-cell empty"></div>
                    @else
                        <div 
                            wire:click="selectDay({{ $dayData['day'] }})"
                            class="day-cell {{ $dayData['isToday'] ? 'today' : '' }}"
                        >
                            <div class="day-header">
                                <div class="day-number">
                                    {{ $dayData['day'] }}
                                </div>
                            </div>

                            <div class="event-chips-container">
                                {{-- 1. Hitos --}}
                                @if($dayData['hasSystem'])
                                    <div class="event-chip chip-academic">Hitos Académicos</div>
                                @endif
                                
                                {{-- 2. Eventos --}}
                                @if($dayData['hasEvents'])
                                    <div class="event-chip chip-event">Eventos</div>
                                @endif

                                {{-- 3. Clases --}}
                                @if($dayData['hasClasses'])
                                    <div class="event-chip chip-class">Clases</div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- MODAL (CENTRAL) -->
    <div x-show="showDetail" style="display: none;" class="modal-container">
        
        <!-- Backdrop -->
        <div 
            class="modal-backdrop"
            x-show="showDetail"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showDetail = false; $wire.set('selectedDate', null)"
        ></div>

        <!-- Panel del Modal -->
        <div 
            class="modal-panel"
            :class="{ 'modal-open': showDetail }"
            x-show="showDetail"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.stop
        >
            <!-- Header Panel -->
            <div class="panel-header">
                <div class="panel-title-row">
                    <h2 class="panel-title">Resumen del Día</h2>
                    <button class="close-btn" @click="showDetail = false; $wire.set('selectedDate', null)">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <p class="panel-date">{{ $selectedDayData['date_human'] ?? 'Seleccione un día' }}</p>
            </div>

            <!-- Contenido Panel -->
            <div class="panel-content">
                
                <!-- 1. Hitos -->
                @if(!empty($selectedDayData['system_events']) && count($selectedDayData['system_events']) > 0)
                    <section>
                        <div class="section-title">
                            <div class="icon-box green">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <h3 class="section-heading">Hitos Académicos</h3>
                        </div>
                        <div>
                            @foreach($selectedDayData['system_events'] as $sysEvent)
                                <div class="card card-l-green">
                                    <p class="card-title">{{ $sysEvent['title'] }}</p>
                                    <p class="card-desc">{{ $sysEvent['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- 2. Clases -->
                @if(!empty($selectedDayData['sections']) && count($selectedDayData['sections']) > 0)
                    <section>
                        <div class="section-title">
                            <div class="icon-box blue">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                            </div>
                            <h3 class="section-heading">Clases Programadas</h3>
                        </div>
                        
                        <div>
                            @foreach($selectedDayData['sections'] as $section)
                                <div class="card card-l-blue">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.25rem;">
                                        <p class="card-title" style="margin:0;">{{ $section->module->name }}</p>
                                        <span style="font-size:0.75rem; background:#eff6ff; padding:2px 6px; border-radius:4px; color:#1d4ed8; font-weight:600;">
                                            {{ \Carbon\Carbon::parse($section->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('H:i') }}
                                        </span>
                                    </div>
                                    <div class="card-desc" style="display:flex; gap:1rem;">
                                        <span>👨‍🏫 {{ $section->teacher->name ?? 'Sin Profesor' }}</span>
                                        <span>🏫 {{ $section->classroom->name ?? 'Virtual' }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @elseif($showClasses)
                    <div class="empty-text">No hay clases programadas para hoy.</div>
                @endif

                <!-- 3. Eventos Extra -->
                @if(!empty($selectedDayData['events']) && count($selectedDayData['events']) > 0)
                    <section>
                        <div class="section-title">
                            <div class="icon-box amber">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
                            </div>
                            <h3 class="section-heading">Agenda Extra</h3>
                        </div>
                        <div>
                            @foreach($selectedDayData['events'] as $event)
                                <div class="card card-l-amber">
                                    <p class="card-title">{{ $event['title'] }}</p>
                                    <p class="card-desc">{{ $event['description'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

            </div>
        </div>
    </div>
</div>