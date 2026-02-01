<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario Académico</title>
    <!-- Alpine.js para la interactividad del modal -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- RESET Y BASE --- */
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            color: #1f2937;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* --- LAYOUT PRINCIPAL --- */
        .calendar-wrapper {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        /* --- HEADER DEL CALENDARIO --- */
        .calendar-header {
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
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
        }

        .month-icon {
            color: #2563eb;
            background-color: #eff6ff;
            padding: 0.375rem;
            border-radius: 0.5rem;
            border: 1px solid #dbeafe;
            display: flex;
        }

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
            flex: 1;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .calendar-container {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }

        /* Días de la semana header */
        .week-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }

        .day-name {
            padding: 0.5rem;
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
            background-color: #e5e7eb; /* Color de las líneas del grid */
            gap: 1px; /* Espacio para las líneas */
            flex: 1;
            overflow-y: auto;
        }

        .day-cell {
            background-color: white;
            min-height: 120px;
            padding: 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            transition: background-color 0.2s;
            cursor: pointer;
            position: relative;
        }
        .day-cell:hover { background-color: #eff6ff; }
        .day-cell.empty { background-color: #f9fafb; cursor: default; }
        .day-cell.today { background-color: rgba(239, 246, 255, 0.6); }

        .day-number {
            font-size: 0.875rem;
            font-weight: 700;
            width: 1.75rem;
            height: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #374151;
            margin-bottom: 0.25rem;
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
            gap: 0.25rem;
            overflow-y: auto;
            max-height: 100px;
        }

        .event-chip {
            padding: 0.125rem 0.375rem;
            border-radius: 0.25rem;
            font-size: 0.625rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            border-left-width: 2px;
            border-left-style: solid;
        }

        .chip-academic { background-color: #d1fae5; color: #065f46; border-left-color: #10b981; }
        .chip-event { background-color: #fef3c7; color: #92400e; border-left-color: #f59e0b; }
        .chip-class { background-color: #eff6ff; color: #1d4ed8; border-left-color: #3b82f6; }

        /* --- SLIDE-OVER (PANEL LATERAL) --- */
        .slide-over-backdrop {
            position: fixed; inset: 0;
            background-color: rgba(107, 114, 128, 0.75);
            backdrop-filter: blur(4px);
            z-index: 40;
            transition: opacity 0.3s;
        }

        .slide-over-container {
            position: fixed; inset: 0; overflow: hidden; z-index: 50; pointer-events: none;
        }

        .slide-over-wrapper {
            position: absolute; inset: 0; overflow: hidden; display: flex; justify-content: flex-end;
        }

        .slide-over-panel {
            pointer-events: auto;
            width: 100%;
            max-width: 28rem;
            background-color: white;
            box-shadow: -4px 0 15px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 100%;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        /* Alpine se encargará de mostrar/ocultar esto, pero por defecto para CSS puro: */
        [x-show="showDetail"] .slide-over-panel { transform: translateX(0); }

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
        }
        .close-btn:hover { color: white; background: rgba(255,255,255,0.3); }

        .panel-date { margin-top: 0.5rem; font-size: 0.875rem; color: #dbeafe; font-weight: 500; text-transform: capitalize; }

        .panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f9fafb;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .section-title {
            display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;
        }
        .icon-box {
            padding: 0.375rem; border-radius: 0.375rem; display: flex;
        }
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

        .card-title { font-size: 0.875rem; font-weight: 600; color: #111827; margin: 0 0 0.25rem 0; }
        .card-desc { font-size: 0.75rem; color: #6b7280; margin: 0; }

        /* Timeline Items */
        .timeline-item {
            display: flex; gap: 1rem; position: relative; margin-bottom: 1rem;
        }
        .timeline-marker {
            display: flex; flex-direction: column; align-items: center; width: 1.5rem;
        }
        .timeline-dot {
            width: 0.75rem; height: 0.75rem; background-color: #3b82f6; border-radius: 50%;
            border: 2px solid #eff6ff; box-shadow: 0 0 0 1px #dbeafe; z-index: 10;
        }
        .timeline-line {
            width: 2px; background-color: #e2e8f0; flex: 1; margin-top: 0.25rem; margin-bottom: 0.25rem;
        }
        
        .class-card {
            flex: 1; background-color: white; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem;
            transition: border-color 0.2s;
        }
        .class-card:hover { border-color: #93c5fd; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }

        .class-time {
            display: inline-block; background-color: #eff6ff; color: #1d4ed8; font-size: 0.75rem;
            font-weight: 600; padding: 0.125rem 0.5rem; border-radius: 0.25rem; margin-bottom: 0.5rem;
        }
        .class-meta {
            display: flex; flex-direction: column; gap: 0.25rem; margin-top: 0.5rem;
            padding-top: 0.5rem; border-top: 1px solid #f3f4f6; font-size: 0.75rem; color: #6b7280;
        }
        .meta-item { display: flex; align-items: center; gap: 0.375rem; }

        .empty-state {
            text-align: center; padding: 2rem; background-color: white;
            border: 2px dashed #e5e7eb; border-radius: 0.5rem;
        }

        /* Utilidades SVG */
        .icon { width: 1rem; height: 1rem; }
        .icon-md { width: 1.25rem; height: 1.25rem; }
        .icon-lg { width: 1.5rem; height: 1.5rem; }
    </style>
</head>
<body>

<!-- El wrapper x-data debe estar aquí para que Alpine controle todo el estado -->
<div class="calendar-wrapper" x-data="{ showDetail: @entangle('selectedDate') }">
    
    <!-- HEADER -->
    <div class="calendar-header">
        <div class="header-content">
            <!-- Título y Navegación -->
            <div class="month-controls">
                <h1 class="month-title">
                    <span class="month-icon">
                        <svg class="icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </span>
                    <span style="text-transform: capitalize;">{{ \Carbon\Carbon::createFromDate($currentYear, $currentMonth, 1)->locale('es')->monthName }}</span>
                    <span class="year-text">{{ $currentYear }}</span>
                </h1>
                
                <div class="nav-buttons">
                    <button wire:click="previousMonth" class="btn-nav">
                        <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <div class="nav-divider"></div>
                    <button wire:click="nextMonth" class="btn-nav">
                        <svg class="icon-md" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
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
                            <div class="day-number">
                                {{ $dayData['day'] }}
                            </div>

                            <div class="event-chips-container">
                                @if($dayData['hasSystem'])
                                    <div class="event-chip chip-academic">Hitos Académicos</div>
                                @endif
                                
                                @if($dayData['hasEvents'])
                                    <div class="event-chip chip-event">Eventos</div>
                                @endif

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

    <!-- PANEL LATERAL (MODAL DETALLE) -->
    <div x-show="showDetail" style="display: none;">
        <!-- Backdrop -->
        <div 
            class="slide-over-backdrop"
            x-show="showDetail"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showDetail = false; $wire.set('selectedDate', null)"
        ></div>

        <!-- Panel -->
        <div class="slide-over-container">
            <div class="slide-over-wrapper">
                <div 
                    class="slide-over-panel"
                    x-show="showDetail"
                    x-transition:enter="transform transition ease-in-out duration-500"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-500"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                >
                    <!-- Header Panel -->
                    <div class="panel-header">
                        <div class="panel-title-row">
                            <h2 class="panel-title">Resumen del Día</h2>
                            <button class="close-btn" @click="showDetail = false; $wire.set('selectedDate', null)">
                                <svg class="icon-lg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
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
                                        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
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
                                        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                                    </div>
                                    <h3 class="section-heading">Clases Programadas</h3>
                                </div>
                                
                                <div style="margin-left: 0.5rem; border-left: 2px solid #e2e8f0; padding-left: 1.5rem;">
                                    @foreach($selectedDayData['sections'] as $section)
                                        <div class="timeline-item">
                                            <div class="class-card">
                                                <div style="display: flex; justify-content: space-between;">
                                                    <span class="class-time">
                                                        {{ \Carbon\Carbon::parse($section->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($section->end_time)->format('H:i') }}
                                                    </span>
                                                    <span style="font-size: 0.625rem; color: #9ca3af; font-family: monospace;">{{ $section->section_name }}</span>
                                                </div>
                                                <h4 class="card-title">{{ $section->module->name }}</h4>
                                                <div class="class-meta">
                                                    <div class="meta-item">
                                                        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                                        {{ $section->teacher->name ?? 'Sin Profesor' }}
                                                    </div>
                                                    <div class="meta-item">
                                                        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                                        {{ $section->classroom->name ?? 'Virtual' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @elseif($showClasses)
                            <div class="empty-state">
                                <svg class="icon-lg" style="margin: 0 auto; color: #d1d5db;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                <h3 style="font-size: 0.875rem; font-weight: 600; margin: 0.5rem 0 0.25rem 0;">Día libre</h3>
                                <p style="font-size: 0.75rem; color: #6b7280; margin: 0;">No hay clases hoy.</p>
                            </div>
                        @endif

                        <!-- 3. Eventos Extra -->
                        @if(!empty($selectedDayData['events']) && count($selectedDayData['events']) > 0)
                            <section>
                                <div class="section-title">
                                    <div class="icon-box amber">
                                        <svg class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" /></svg>
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
    </div>
</div>

</body>
</html>