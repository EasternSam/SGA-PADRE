<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Académico Oficial - {{ $student->fullName }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #334155; /* slate-700 */
            line-height: 1.5;
        }

        /* Encabezado Limpio Print-Friendly */
        .header-table {
            width: 100%;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 160px;
            height: auto;
        }
        .header-title {
            color: #0f172a;
            font-size: 20pt;
            margin: 0;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            color: #64748b;
            margin: 5px 0 0 0;
            font-size: 9pt;
        }
        
        h2 {
            font-size: 13pt;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
            margin-top: 35px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Status Badges para impresion DOMPDF */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid transparent;
        }
        .status-activo { background-color: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .status-inactivo { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .status-egresado { background-color: #dbeafe; color: #1e40af; border-color: #bfdbfe; }
        
        .program-badge {
            display: inline-block;
            padding: 3px 8px;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            color: #475569;
            font-weight: bold;
            font-size: 8pt;
            margin-top: 5px;
            text-transform: uppercase;
        }

        /* Contenedor de Perfil Moderno tipo Tabla Grid */
        .profile-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .profile-container td {
            padding: 12px 15px;
            vertical-align: top;
        }
        .profile-label {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 3px;
            font-weight: bold;
            display: block;
        }
        .profile-value {
            font-size: 11pt;
            color: #0f172a;
            font-weight: bold;
            display: block;
        }

        /* Estilos de Tabla de Datos Modernos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
            border-top: 1px solid #cbd5e1;
        }
        .data-table td {
            padding: 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc; /* Efecto Zebra Sutil */
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Estilizado Dinámico de Notas */
        .grade { font-weight: bold; font-size: 10pt; }
        .grade-pass { color: #059669; }
        .grade-fail { color: #dc2626; }
        .grade-pending { color: #d97706; }

        /* Cuadro de Balance Destacado */
        .balance-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 30px;
        }
        .balance-box {
            background-color: #fef2f2;
            border: 2px solid #fca5a5;
            padding: 15px 25px;
            border-radius: 6px;
            text-align: right;
            width: 250px;
        }
        .balance-box .label {
            color: #991b1b;
            font-size: 9pt;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .balance-box .amount {
            color: #7f1d1d;
            font-size: 18pt;
            font-weight: bold;
            letter-spacing: -0.5px;
        }

        /* Footer Sticky */
        footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            height: 15mm;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="50%" valign="middle" align="left">
                <img src="{{ public_path($branding->logo_url ?? 'centuu.png') }}" class="logo" alt="Logo">
            </td>
            <td width="50%" valign="middle" align="right">
                <h1 class="header-title">Reporte Académico</h1>
                <p class="header-subtitle">Generado el: {{ now()->format('d/m/Y h:i A') }}</p>
            </td>
        </tr>
    </table>

    @php
        // Análisis Adaptativo del Tipo de Estudiante (Curricular vs K-12/Technical)
        // Soporta Universidades, Institutos o General
        $course = $student->course;
        $programType = 'Programa General';
        
        if ($course) {
            if ($course->program_type === 'degree') {
                $programType = 'Carrera Universitaria';
            } elseif ($course->program_type === 'technical') {
                $programType = 'Curso / Certificación Libre';
            }
        }
        
        $programName = $course->name ?? 'Estudiante General';
        $studentCode = $student->student_code ?? $student->id;
        
        // Estilización de Badges
        $statusClass = 'status-inactivo';
        if(strtolower($student->status) === 'activo') $statusClass = 'status-activo';
        if(strtolower($student->status) === 'egresado') $statusClass = 'status-egresado';
    @endphp

    <main>
        <!-- SECCIÓN: PERFIL Y DATOS PERSONALES -->
        <table class="profile-container">
            <tr>
                <td style="width: 50%; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <span class="profile-label">Nombre del Estudiante</span>
                    <span class="profile-value">{{ $student->fullName }}</span>
                </td>
                <td style="width: 50%; border-bottom: 1px solid #e2e8f0;">
                    <span class="profile-label">Matrícula Escolar</span>
                    <span class="profile-value" style="font-family: monospace; font-size: 13pt;">{{ $studentCode }}</span>
                </td>
            </tr>
            <tr>
                <td style="border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <span class="profile-label">Documento de Identidad (DNI)</span>
                    <span class="profile-value">{{ $student->cedula ?? 'N/A' }}</span>
                </td>
                <td style="border-bottom: 1px solid #e2e8f0;">
                    <span class="profile-label">Programa / Matrícula Actual</span>
                    <span class="profile-value">{{ $programName }}</span>
                    <div>
                        <span class="program-badge">{{ $programType }}</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="border-right: 1px solid #e2e8f0;">
                    <span class="profile-label">Vías de Contacto</span>
                    <span style="font-size: 10pt; color: #0f172a; font-weight: bold; display: block; margin-top: 2px;">
                        TEL: {{ $student->mobile_phone ?? $student->home_phone ?? 'N/A' }}<br>
                        Email: {{ $student->email }}
                    </span>
                </td>
                <td>
                    <span class="profile-label">Estatus Académico</span>
                    <span class="badge {{ $statusClass }}" style="margin-top: 4px;">{{ $student->status ?? 'Desconocido' }}</span>
                </td>
            </tr>
        </table>

        <!-- SECCIÓN: RÉCORD DE NOTAS Y MATERIAS INSCRITAS -->
        <h2>Resumen Académico Generado</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="15%">Código</th>
                    <th width="35%">Asignatura / Módulo</th>
                    <th width="20%">Docente Asignado</th>
                    <th width="15%" class="text-center">Condición</th>
                    <th width="15%" class="text-center">Calificación</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($enrollments as $enrollment)
                    @php
                        $module = $enrollment->courseSchedule->module ?? null;
                        $teacher = $enrollment->courseSchedule->teacher ?? null;
                        
                        // Parseo Inteligente de Calificaciones
                        $gradeClass = 'grade-pending';
                        if(is_numeric($enrollment->final_grade)) {
                            // Si la nota final es >= 70, aprueba en la mayoria de estandares
                            $gradeClass = $enrollment->final_grade >= 70 ? 'grade-pass' : 'grade-fail';
                        }
                    @endphp
                    <tr>
                        <td style="font-family: monospace; color: #64748b; font-weight: bold;">{{ $module?->id ? 'MOD-'.str_pad($module->id, 4, '0', STR_PAD_LEFT) : 'N/A' }}</td>
                        <td style="font-weight: bold;">{{ $module?->name ?? 'N/A' }}</td>
                        <td>
                            @if($teacher)
                                {{ $teacher->first_name ?? '' }} {{ $teacher->last_name ?? '' }}
                            @else
                                <span style="color: #94a3b8; font-style: italic;">No Asignado</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(strtolower($enrollment->status) === 'aprobado' || strtolower($enrollment->status) === 'completado')
                                <span style="color: #059669; font-weight: bold; text-transform: uppercase; font-size: 8pt;">Aprobado</span>
                            @elseif(strtolower($enrollment->status) === 'reprobado')
                                <span style="color: #dc2626; font-weight: bold; text-transform: uppercase; font-size: 8pt;">Reprobado</span>
                            @else
                                <span style="color: #d97706; text-transform: uppercase; font-size: 8pt; font-weight: bold;">{{ $enrollment->status }}</span>
                            @endif
                        </td>
                        <td class="text-center grade {{ $gradeClass }}">
                            {{ $enrollment->final_grade ?? '--' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 25px; color: #94a3b8; font-style: italic;">
                            -- El estudiante no tiene asignaturas en su historial --
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- SECCIÓN: FINANZAS Y PAGOS -->
        <!-- Forzamos un ligero salto para separar los temas, si hay poco espacio DOMPDF lo pasa a la sig pág automáticamente -->
        <div style="margin-top: 20px;"></div>
        
        <h2>Auditoría de Pagos y Transacciones</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th width="15%">Fecha Trans.</th>
                    <th width="35%">Concepto Descriptivo</th>
                    <th width="20%">Agente Recaudador</th>
                    <th width="15%">Canal de Pago</th>
                    <th width="15%" class="text-right">Abono (Voucher)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr>
                        <td>{{ $payment->created_at->format('d/m/Y') }}</td>
                        <td>{{ $payment->paymentConcept->name ?? $payment->description ?? 'Pago Registrado' }}</td>
                        <td>{{ $payment->user->name ?? 'Autoservicio/Kiosco' }}</td>
                        <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                        <td class="text-right" style="font-weight: bold; color: #0f172a;">RD$ {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 25px; color: #94a3b8; font-style: italic;">
                            -- Sin transacciones procesadas --
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary Total a Pagar -->
        <table class="balance-table">
            <tr>
                <td width="60%"></td>
                <td width="40%" align="right">
                    <div class="balance-box">
                        <div class="label">Total Deuda Pendiente</div>
                        <div class="amount">RD$ {{ number_format($student->balance ?? 0, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

    </main>

    <footer>
        <table width="100%" style="font-size: 8pt; color: #64748b; border-collapse: collapse;">
            <tr>
                <td align="left" width="70%">
                    <strong style="color: #0f172a;">{{ config('app.name', 'SGA CENTU') }}</strong> &mdash; 
                    Firma Electrónica Autorizada Vía Sistema - Emitido el {{ now()->format('d/m/Y') }}
                </td>
                <td align="right" width="30%">
                    Página <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </footer>

</body>
</html>