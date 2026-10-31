@extends('reports.layouts.pdf')

@section('title', $reportType === 'income_statement' ? 'Estado de Resultados (Ganancias y Pérdidas)' : 'Balance General (Estado de Situación)')
@section('subtitle')
    @if($reportType === 'income_statement')
        Período Fiscal: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }} · Expresado en Pesos Dominicanos (RD$)
    @else
        Al Corte del: {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }} · Expresado en Pesos Dominicanos (RD$)
    @endif
@endsection

@section('content')
    @if($reportType === 'income_statement')
        <table class="kpi-table">
            <tr>
                <td class="kpi-card success" style="width: 33.33%;">
                    <div class="kpi-label">Total Ingresos</div>
                    <div class="kpi-value">RD$ {{ number_format((float) ($report['total_revenue'] ?? 0), 2) }}</div>
                </td>
                <td class="kpi-card accent" style="width: 33.33%;">
                    <div class="kpi-label">Total Gastos</div>
                    <div class="kpi-value">RD$ {{ number_format((float) ($report['total_expense'] ?? 0), 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 33.33%;">
                    <div class="kpi-label">Utilidad / Resultado Neto</div>
                    <div class="kpi-value {{ ($report['net_income'] ?? 0) >= 0 ? 'text-green' : 'text-red' }}">
                        RD$ {{ number_format((float) ($report['net_income'] ?? 0), 2) }}
                    </div>
                </td>
            </tr>
        </table>

        <h3 style="margin: 10px 0 4px 0; font-size: 9.5pt; color: #0f172a; text-transform: uppercase;">1. Ingresos Operativos</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Código</th>
                    <th style="width: 57%;">Cuenta Contable</th>
                    <th class="text-right" style="width: 25%;">Saldo (RD$)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['revenues'] ?? [] as $acc)
                    <tr>
                        <td class="font-mono font-bold">{{ $acc->code }}</td>
                        <td>{{ $acc->name }}</td>
                        <td class="text-right font-mono font-bold text-green">{{ number_format((float) $acc->balance, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray">Sin movimientos de ingresos en el período seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTAL INGRESOS OPERATIVOS</td>
                    <td class="text-right font-mono text-green">RD$ {{ number_format((float) ($report['total_revenue'] ?? 0), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <h3 style="margin: 16px 0 4px 0; font-size: 9.5pt; color: #0f172a; text-transform: uppercase;">2. Costos y Gastos Operativos</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Código</th>
                    <th style="width: 57%;">Cuenta Contable</th>
                    <th class="text-right" style="width: 25%;">Saldo (RD$)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['expenses'] ?? [] as $acc)
                    <tr>
                        <td class="font-mono font-bold">{{ $acc->code }}</td>
                        <td>{{ $acc->name }}</td>
                        <td class="text-right font-mono font-bold text-red">{{ number_format((float) $acc->balance, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray">Sin movimientos de gastos en el período seleccionado.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTAL COSTOS Y GASTOS</td>
                    <td class="text-right font-mono text-red">RD$ {{ number_format((float) ($report['total_expense'] ?? 0), 2) }}</td>
                </tr>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">RESULTADO NETO DEL PERÍODO</td>
                    <td class="text-right font-mono {{ ($report['net_income'] ?? 0) >= 0 ? 'text-green' : 'text-red' }}">
                        RD$ {{ number_format((float) ($report['net_income'] ?? 0), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <table class="kpi-table">
            <tr>
                <td class="kpi-card success" style="width: 33.33%;">
                    <div class="kpi-label">Total Activos</div>
                    <div class="kpi-value">RD$ {{ number_format((float) ($report['total_assets'] ?? 0), 2) }}</div>
                </td>
                <td class="kpi-card accent" style="width: 33.33%;">
                    <div class="kpi-label">Total Pasivos</div>
                    <div class="kpi-value">RD$ {{ number_format((float) ($report['total_liabilities'] ?? 0), 2) }}</div>
                </td>
                <td class="kpi-card" style="width: 33.33%;">
                    <div class="kpi-label">Total Patrimonio / Capital</div>
                    <div class="kpi-value">RD$ {{ number_format((float) ($report['total_equity'] ?? 0), 2) }}</div>
                </td>
            </tr>
        </table>

        <h3 style="margin: 10px 0 4px 0; font-size: 9.5pt; color: #0f172a; text-transform: uppercase;">1. Activos</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Código</th>
                    <th style="width: 57%;">Cuenta Contable</th>
                    <th class="text-right" style="width: 25%;">Saldo (RD$)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['assets'] ?? [] as $acc)
                    <tr>
                        <td class="font-mono font-bold">{{ $acc->code }}</td>
                        <td>{{ $acc->name }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format((float) $acc->balance, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray">Sin cuentas de activo con saldo al corte.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTAL ACTIVOS</td>
                    <td class="text-right font-mono">RD$ {{ number_format((float) ($report['total_assets'] ?? 0), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <h3 style="margin: 16px 0 4px 0; font-size: 9.5pt; color: #0f172a; text-transform: uppercase;">2. Pasivos</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Código</th>
                    <th style="width: 57%;">Cuenta Contable</th>
                    <th class="text-right" style="width: 25%;">Saldo (RD$)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['liabilities'] ?? [] as $acc)
                    <tr>
                        <td class="font-mono font-bold">{{ $acc->code }}</td>
                        <td>{{ $acc->name }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format((float) $acc->balance, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-gray">Sin cuentas de pasivo con saldo al corte.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTAL PASIVOS</td>
                    <td class="text-right font-mono">RD$ {{ number_format((float) ($report['total_liabilities'] ?? 0), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <h3 style="margin: 16px 0 4px 0; font-size: 9.5pt; color: #0f172a; text-transform: uppercase;">3. Patrimonio / Capital Contable</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Código</th>
                    <th style="width: 57%;">Cuenta Contable</th>
                    <th class="text-right" style="width: 25%;">Saldo (RD$)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['equity'] ?? [] as $acc)
                    <tr>
                        <td class="font-mono font-bold">{{ $acc->code }}</td>
                        <td>{{ $acc->name }}</td>
                        <td class="text-right font-mono font-bold">{{ number_format((float) $acc->balance, 2) }}</td>
                    </tr>
                @empty
                @endforelse
                <tr>
                    <td class="font-mono font-bold">3999</td>
                    <td>Resultado Acumulado del Ejercicio</td>
                    <td class="text-right font-mono font-bold">{{ number_format((float) ($report['historical_net_income'] ?? 0), 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTAL PATRIMONIO</td>
                    <td class="text-right font-mono">RD$ {{ number_format((float) ($report['total_equity'] ?? 0), 2) }}</td>
                </tr>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTAL PASIVO + PATRIMONIO</td>
                    <td class="text-right font-mono">RD$ {{ number_format((float) ($report['total_liabilities_and_equity'] ?? 0), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    <table class="signatures-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-line">
                    {{ auth()->user()->name ?? 'Contador General' }}
                </div>
                <div class="signature-sub">Departamento de Contabilidad</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="signature-cell">
                <div class="signature-line">
                    Dirección Financiera / Rectoría
                </div>
                <div class="signature-sub">Aprobado y Auditado</div>
            </td>
        </tr>
    </table>
@endsection
