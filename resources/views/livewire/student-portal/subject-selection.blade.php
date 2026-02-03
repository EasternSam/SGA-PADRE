<div>
    {{-- Bloque de Estilos CSS Puro --}}
    <style>
        /* Reset básico y variables para este componente */
        .selection-wrapper {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            min-height: 100vh;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .container-custom {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Encabezado */
        .header-section {
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }

        .page-subtitle {
            font-size: 16px;
            color: #6c757d;
            margin: 0;
        }

        .highlight-career {
            color: #3498db; /* Azul institucional */
            font-weight: 600;
        }

        /* Alertas y Mensajes */
        .alert-box {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 5px solid;
            display: flex;
            align-items: center;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .alert-error {
            border-color: #e74c3c;
            color: #c0392b;
            background-color: #fadbd8;
        }

        .alert-success {
            border-color: #2ecc71;
            color: #27ae60;
            background-color: #d5f5e3;
        }

        .alert-warning {
            border-color: #f1c40f;
            color: #d35400;
            background-color: #fdebd0;
        }

        /* Tarjetas de Periodo */
        .period-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow: hidden;
            border: 1px solid #e1e4e8;
        }

        .period-header {
            background-color: #f1f3f5;
            padding: 15px 25px;
            border-bottom: 1px solid #e1e4e8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .period-badge {
            background-color: #34495e;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .period-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            margin-left: 10px;
        }

        /* Filas de Materias */
        .module-row {
            padding: 25px;
            border-bottom: 1px solid #eee;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            transition: background-color 0.2s ease;
        }

        .module-row:last-child {
            border-bottom: none;
        }

        .module-row:hover {
            background-color: #fcfcfc;
        }

        .module-row.approved {
            background-color: rgba(46, 204, 113, 0.05); /* Verde muy sutil */
        }

        /* Columna Izquierda: Detalles de Materia */
        .module-info {
            flex: 1;
            min-width: 300px;
        }

        .module-header-line {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .code-tag {
            font-family: 'Courier New', monospace;
            background-color: #e9ecef;
            color: #495057;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            border: 1px solid #ced4da;
        }

        .status-badge {
            font-size: 12px;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
        }

        .status-approved { background-color: #d5f5e3; color: #27ae60; }
        .status-blocked { background-color: #fadbd8; color: #c0392b; }

        .module-name {
            font-size: 20px;
            font-weight: 700;
            color: #2c3e50;
            margin: 5px 0;
            line-height: 1.3;
        }

        .module-meta {
            color: #7f8c8d;
            font-size: 14px;
            display: flex;
            align-items: center;
            margin-top: 5px;
        }

        .prereq-alert {
            margin-top: 12px;
            font-size: 13px;
            color: #c0392b;
            background-color: #fff5f5;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #feb2b2;
            display: inline-block;
        }

        /* Columna Derecha: Secciones */
        .module-sections {
            width: 100%;
            max-width: 450px;
        }

        .section-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #95a5a6;
            font-weight: 700;
            margin-bottom: 10px;
            display: block;
        }

        .no-sections-box {
            border: 2px dashed #dfe6e9;
            padding: 15px;
            text-align: center;
            border-radius: 6px;
            color: #b2bec3;
            font-style: italic;
            font-size: 14px;
        }

        /* Botones de Sección */
        .section-btn {
            display: block;
            width: 100%;
            background: white;
            border: 1px solid #dcdcdc;
            border-radius: 6px;
            padding: 12px 15px;
            margin-bottom: 10px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            outline: none;
        }

        .section-btn:hover:not(:disabled) {
            border-color: #3498db;
            box-shadow: 0 2px 8px rgba(52, 152, 219, 0.15);
        }

        .section-btn:disabled {
            background-color: #f8f9fa;
            color: #b2bec3;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .section-btn.selected {
            background-color: #ebf5fb; /* Azul muy claro */
            border-color: #3498db;
            border-width: 2px;
            padding: 11px 14px; /* Ajuste por borde */
        }

        .section-btn-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .sec-name {
            font-weight: 700;
            font-size: 15px;
            color: #34495e;
        }
        .section-btn.selected .sec-name { color: #2980b9; }

        /* Badges de Cupo */
        .quota-badge {
            font-size: 11px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .quota-full { background-color: #fadbd8; color: #e74c3c; }
        .quota-selected { background-color: #d6eaf8; color: #2980b9; }
        .quota-open { background-color: #e8f8f5; color: #27ae60; border: 1px solid #a9dfbf; }

        .sec-details {
            font-size: 13px;
            color: #7f8c8d;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .sec-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Iconos SVG sencillos */
        .icon-svg { width: 14px; height: 14px; fill: currentColor; opacity: 0.7; }

        /* Barra Inferior Fija */
        .fixed-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            border-top: 1px solid #dcdcdc;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.1);
            padding: 15px 0;
            z-index: 1000;
            transform: translateY(120%);
            transition: transform 0.3s ease-in-out;
        }

        .fixed-bottom-bar.is-visible {
            transform: translateY(0);
        }

        .bar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .summary-stats {
            display: flex;
            gap: 20px;
        }

        .stat-item {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 5px 15px;
            border-radius: 6px;
            text-align: center;
            min-width: 80px;
        }

        .stat-label { font-size: 10px; text-transform: uppercase; color: #95a5a6; font-weight: bold; display: block; }
        .stat-number { font-size: 20px; font-weight: 700; color: #2c3e50; display: block; }
        .stat-number.money { color: #27ae60; }

        .btn-confirm {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(52, 152, 219, 0.3);
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-confirm:hover { background-color: #2980b9; }
        .btn-confirm:disabled { background-color: #bdc3c7; cursor: not-allowed; box-shadow: none; }

        .spinner {
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 2px solid white;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Responsivo */
        @media (max-width: 768px) {
            .module-row { flex-direction: column; }
            .module-sections { max-width: 100%; }
            .bar-content { flex-direction: column; gap: 15px; }
            .summary-stats { width: 100%; justify-content: space-between; }
            .btn-confirm { width: 100%; justify-content: center; }
            .stat-item { flex: 1; }
        }
    </style>

    <div class="selection-wrapper">
        <div class="container-custom">
            
            <!-- Header -->
            <div class="header-section">
                <h1 class="page-title">Selección de Materias</h1>
                <p class="page-subtitle">
                    Ciclo Académico Actual • Carrera: 
                    <span class="highlight-career">{{ $career->name ?? 'Carrera General' }}</span>
                </p>
            </div>

            <!-- Mensajes -->
            @if ($errorMessage)
                <div class="alert-box alert-error">
                    <strong>Error:</strong>&nbsp; {{ $errorMessage }}
                </div>
            @endif

            @if ($successMessage)
                <div class="alert-box alert-success">
                    <strong>¡Éxito!</strong>&nbsp; {{ $successMessage }}
                </div>
            @endif

            @if ($debugMessage)
                <div class="alert-box alert-warning">
                    <strong>Diagnóstico:</strong>&nbsp; {{ $debugMessage }}
                </div>
            @endif

            <!-- Contenido Principal -->
            @if(empty($groupedModules))
                <div class="no-sections-box" style="padding: 50px;">
                    <h3>No hay oferta académica disponible</h3>
                    <p>No se encontraron materias habilitadas para tu perfil en este momento.</p>
                </div>
            @else
                <div style="padding-bottom: 80px;"> <!-- Espacio para la barra flotante -->
                    @foreach($groupedModules as $period => $modules)
                        <div class="period-card">
                            <div class="period-header">
                                <div style="display: flex; align-items: center;">
                                    <span class="period-badge">Periodo {{ $period }}</span>
                                    <h3 class="period-title">Materias del Nivel</h3>
                                </div>
                                <span style="font-size: 13px; color: #7f8c8d;">{{ count($modules) }} asignaturas</span>
                            </div>

                            <div>
                                @foreach($modules as $module)
                                    <div class="module-row {{ $module['status'] === 'aprobada' ? 'approved' : '' }}">
                                        
                                        <!-- Info Materia -->
                                        <div class="module-info">
                                            <div class="module-header-line">
                                                <span class="code-tag">{{ $module['code'] }}</span>
                                                
                                                @if($module['status'] === 'aprobada')
                                                    <span class="status-badge status-approved">
                                                        ✓ Aprobada
                                                    </span>
                                                @elseif($module['status'] === 'bloqueada')
                                                    <span class="status-badge status-blocked">
                                                        ⚠ Bloqueada
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <h4 class="module-name">{{ $module['name'] }}</h4>
                                            
                                            <div class="module-meta">
                                                <span>{{ $module['credits'] }} Créditos Académicos</span>
                                            </div>

                                            @if($module['status'] === 'bloqueada')
                                                <div class="prereq-alert">
                                                    <strong>Requisito pendiente:</strong> {{ implode(', ', $module['missing_prereqs']) }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Secciones Disponibles -->
                                        @if($module['status'] === 'disponible')
                                            <div class="module-sections">
                                                @if($module['schedules']->isEmpty())
                                                    <div class="no-sections-box">
                                                        No hay secciones abiertas
                                                    </div>
                                                @else
                                                    <span class="section-label">Secciones Disponibles</span>
                                                    
                                                    @foreach($module['schedules'] as $schedule)
                                                        @php
                                                            $isSelected = isset($selectedSchedules[$module['id']]) && $selectedSchedules[$module['id']] == $schedule->id;
                                                            $isFull = $schedule->isFull();
                                                            $days = is_array($schedule->days_of_week) ? implode(', ', $schedule->days_of_week) : $schedule->days_of_week;
                                                        @endphp

                                                        <button 
                                                            wire:click="toggleSection({{ $module['id'] }}, {{ $schedule->id }})"
                                                            @if($isFull && !$isSelected) disabled @endif
                                                            class="section-btn {{ $isSelected ? 'selected' : '' }}"
                                                        >
                                                            <div class="section-btn-header">
                                                                <span class="sec-name">Sección {{ $schedule->section_name }}</span>
                                                                
                                                                @if($isFull && !$isSelected)
                                                                    <span class="quota-badge quota-full">Agotada</span>
                                                                @elseif($isSelected)
                                                                    <span class="quota-badge quota-selected">Seleccionada</span>
                                                                @else
                                                                    <span class="quota-badge quota-open">{{ $schedule->available_spots }} cupos</span>
                                                                @endif
                                                            </div>
                                                            
                                                            <div class="sec-details">
                                                                <div class="sec-row">
                                                                    <span>📅 {{ $days }}</span>
                                                                    <span>⏰ {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}</span>
                                                                </div>
                                                                <div class="sec-row" style="margin-top:2px;">
                                                                    <span>👨‍🏫 {{ Str::limit($schedule->teacher->name ?? 'Por asignar', 25) }}</span>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    @endforeach
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Barra Inferior Fija -->
    <div class="fixed-bottom-bar {{ !empty($selectedSchedules) ? 'is-visible' : '' }}">
        <div class="bar-content">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="display: none; @media(min-width:768px){display:block;}">
                    <strong style="color: #2c3e50; display: block;">Resumen de Selección</strong>
                    <span style="font-size: 13px; color: #7f8c8d;">Confirma antes de salir</span>
                </div>
                
                <div class="summary-stats">
                    <div class="stat-item">
                        <span class="stat-label">Materias</span>
                        <span class="stat-number">{{ count($selectedSchedules) }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Créditos</span>
                        <span class="stat-number">{{ $totalCredits }}</span>
                    </div>
                    @if($totalCost > 0)
                    <div class="stat-item">
                        <span class="stat-label">Total</span>
                        <span class="stat-number money">${{ number_format($totalCost, 0) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <button 
                wire:click="confirmSelection"
                wire:loading.attr="disabled"
                class="btn-confirm"
            >
                <span wire:loading.remove wire:target="confirmSelection">Confirmar Inscripción</span>
                <span wire:loading wire:target="confirmSelection"><div class="spinner"></div> Procesando...</span>
            </button>
        </div>
    </div>
</div>