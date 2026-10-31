@extends('reports.layouts.pdf')

@section('title', $reportTitle ?? 'Reporte Institucional')
@section('subtitle')
    {{ $reportSubtitle ?? 'Centro de Reportes Institucionales' }}
@endsection

@section('content')
    {{-- KPI Summary Row --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-card" style="width: 33.33%;">
                <div class="kpi-label">Total de Registros</div>
                <div class="kpi-value">{{ $summary['total_records'] ?? count($reportData) }}</div>
            </td>
            <td class="kpi-card success" style="width: 33.33%;">
                <div class="kpi-label">{{ $summary['primary_label'] ?? 'Indicador Principal' }}</div>
                <div class="kpi-value">{{ $summary['primary_metric'] ?? '0' }}</div>
            </td>
            <td class="kpi-card accent" style="width: 33.33%;">
                <div class="kpi-label">{{ $summary['secondary_label'] ?? 'Indicador Secundario' }}</div>
                <div class="kpi-value">{{ $summary['secondary_metric'] ?? '0' }}</div>
            </td>
        </tr>
    </table>

    {{-- 1. REPORTE DE COBROS Y RECAUDACIÓN --}}
    @if($reportType === 'payments')
        @php
            $totalCollected = 0;
            $totalPending = 0;
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 13%;">Fecha</th>
                    <th style="width: 11%;">Matrícula</th>
                    <th style="width: 24%;">Estudiante</th>
                    <th style="width: 20%;">Concepto</th>
                    <th style="width: 10%;">Método</th>
                    <th style="width: 11%;">NCF</th>
                    <th class="text-right" style="width: 11%;">Monto (RD$)</th>
                    <th class="text-center" style="width: 9%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    @php
                        if (($row['status'] ?? '') === 'Pagado') {
                            $totalCollected += (float) ($row['amount'] ?? 0);
                        } else {
                            $totalPending += (float) ($row['amount'] ?? 0);
                        }
                    @endphp
                    <tr>
                        <td class="font-mono" style="font-size: 7.5pt;">{{ $row['date'] }}</td>
                        <td class="font-mono font-bold">{{ $row['student_code'] }}</td>
                        <td class="font-bold">{{ $row['student_name'] }}</td>
                        <td>{{ $row['concept'] }}</td>
                        <td>{{ $row['gateway'] }}</td>
                        <td class="font-mono" style="font-size: 7.5pt;">{{ $row['ncf'] }}</td>
                        <td class="text-right font-mono font-bold">
                            {{ number_format((float) ($row['amount'] ?? 0), 2) }}
                        </td>
                        <td class="text-center">
                            @if(($row['status'] ?? '') === 'Pagado')
                                <span class="badge badge-success">PAGADO</span>
                            @elseif(($row['status'] ?? '') === 'Vencido')
                                <span class="badge badge-danger">VENCIDO</span>
                            @else
                                <span class="badge badge-warning">{{ strtoupper($row['status'] ?? 'PENDIENTE') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center" style="padding: 18px; color: #64748b;">
                            No se encontraron registros de cobros para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($reportData) > 0)
                <tfoot>
                    <tr class="totals-row">
                        <td colspan="6" class="text-right">TOTAL RECAUDADO (PAGADO)</td>
                        <td class="text-right font-mono text-green">RD$ {{ number_format($totalCollected, 2) }}</td>
                        <td class="text-center" style="font-size: 7.5pt;">Pend: RD$ {{ number_format($totalPending, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

    {{-- 2. REPORTE DE ASISTENCIA --}}
    @if($reportType === 'attendance')
        @php
            $statusLabels = [
                'present' => ['label' => 'PRESENTE', 'class' => 'badge-success'],
                'Presente' => ['label' => 'PRESENTE', 'class' => 'badge-success'],
                'absent' => ['label' => 'AUSENTE', 'class' => 'badge-danger'],
                'Ausente' => ['label' => 'AUSENTE', 'class' => 'badge-danger'],
                'late' => ['label' => 'TARDANZA', 'class' => 'badge-warning'],
                'Tardanza' => ['label' => 'TARDANZA', 'class' => 'badge-warning'],
                'excused' => ['label' => 'EXCUSADO', 'class' => 'badge-neutral'],
                'Excusado' => ['label' => 'EXCUSADO', 'class' => 'badge-neutral'],
            ];
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Fecha</th>
                    <th style="width: 13%;">Matrícula</th>
                    <th style="width: 28%;">Estudiante</th>
                    <th style="width: 18%;">Sección</th>
                    <th class="text-center" style="width: 12%;">Asistencia</th>
                    <th style="width: 17%;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    @php
                        $st = $statusLabels[$row['status'] ?? 'present'] ?? ['label' => strtoupper($row['status'] ?? 'PRESENTE'), 'class' => 'badge-neutral'];
                    @endphp
                    <tr>
                        <td class="font-mono">{{ $row['date'] }}</td>
                        <td class="font-mono font-bold">{{ $row['student_code'] }}</td>
                        <td class="font-bold">{{ $row['student_name'] }}</td>
                        <td>{{ $row['section'] }}</td>
                        <td class="text-center">
                            <span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span>
                        </td>
                        <td>{{ $row['notes'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 18px; color: #64748b;">
                            No se encontraron registros de asistencia para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- 3. REPORTE DE CALIFICACIONES --}}
    @if($reportType === 'grades')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Matrícula</th>
                    <th style="width: 28%;">Estudiante</th>
                    <th style="width: 28%;">Asignatura / Módulo</th>
                    <th style="width: 10%;">Período</th>
                    <th class="text-right" style="width: 10%;">Calificación</th>
                    <th class="text-center" style="width: 10%;">Condición</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    <tr>
                        <td class="font-mono font-bold">{{ $row['student_code'] }}</td>
                        <td class="font-bold">{{ $row['student_name'] }}</td>
                        <td>{{ $row['subject'] }}</td>
                        <td class="text-center">{{ $row['period'] }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format((float) ($row['score'] ?? 0), 1) }}</td>
                        <td class="text-center">
                            @if(($row['status'] ?? '') === 'Aprobado')
                                <span class="badge badge-success">APROBADO</span>
                            @else
                                <span class="badge badge-danger">REPROBADO</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 18px; color: #64748b;">
                            No se encontraron calificaciones registradas para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- 4. PADRÓN ESTUDIANTIL --}}
    @if($reportType === 'students')
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">#</th>
                    <th style="width: 13%;">Matrícula</th>
                    <th style="width: 25%;">Nombre del Estudiante</th>
                    <th style="width: 22%;">Correo Electrónico</th>
                    <th style="width: 12%;">Teléfono</th>
                    <th style="width: 15%;">Carrera / Curso</th>
                    <th class="text-center" style="width: 8%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $idx => $row)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold">{{ $row['student_code'] }}</td>
                        <td class="font-bold">{{ $row['student_name'] }}</td>
                        <td class="font-mono" style="font-size: 7.5pt;">{{ $row['email'] }}</td>
                        <td>{{ $row['phone'] }}</td>
                        <td>{{ $row['career'] }}</td>
                        <td class="text-center">
                            @if(($row['status'] ?? 'Activo') === 'Activo')
                                <span class="badge badge-success">ACTIVO</span>
                            @else
                                <span class="badge badge-neutral">{{ strtoupper($row['status'] ?? 'INACTIVO') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 18px; color: #64748b;">
                            No se encontraron estudiantes para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    {{-- 5. REPORTE DE MOROSOS Y COBRANZAS --}}
    @if($reportType === 'debtors')
        @php
            $grandDebtTotal = 0;
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Matrícula</th>
                    <th style="width: 22%;">Estudiante</th>
                    <th style="width: 18%;">Carrera / Curso</th>
                    <th style="width: 18%;">Concepto Vencido</th>
                    <th style="width: 11%;">Contacto</th>
                    <th style="width: 9%;">Vencimiento</th>
                    <th class="text-right" style="width: 10%;">Balance (RD$)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reportData as $row)
                    @php
                        $grandDebtTotal += (float) ($row['amount'] ?? 0);
                    @endphp
                    <tr>
                        <td class="font-mono font-bold">{{ $row['student_code'] }}</td>
                        <td class="font-bold">{{ $row['student_name'] }}</td>
                        <td>{{ $row['career'] }}</td>
                        <td>{{ $row['concept'] }}</td>
                        <td>{{ $row['phone'] }}</td>
                        <td class="font-mono text-red">{{ $row['due_date'] }}</td>
                        <td class="text-right font-mono font-bold text-red">
                            {{ number_format((float) ($row['amount'] ?? 0), 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 18px; color: #64748b;">
                            No se encontraron balances vencidos o cuentas por cobrar pendientes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($reportData) > 0)
                <tfoot>
                    <tr class="totals-row">
                        <td colspan="6" class="text-right">TOTAL CARTERA PENDIENTE / VENCIDA</td>
                        <td class="text-right font-mono text-red">RD$ {{ number_format($grandDebtTotal, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

    {{-- Official Institutional Signatures --}}
    <table class="signatures-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line">
                    {{ auth()->user()->name ?? 'Departamento de Registro / Administración' }}
                </div>
                <div class="signature-sub">Elaborado y Revisado por</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="signature-cell">
                <div class="signature-line">
                    Dirección Académica / Financiera
                </div>
                <div class="signature-sub">Firma y Sello Autorizado</div>
            </td>
        </tr>
    </table>
@endsection
