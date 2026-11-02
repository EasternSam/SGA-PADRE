<?php

namespace Gridbase\BillsCore\Services;

use Illuminate\Support\Collection;

/**
 * DgiiReportBuilderService — Gridbase Bills Core Engine
 *
 * Generates official DGII Format 606 (23 columns), Format 607 (23 columns),
 * Format 608 (3 columns), and full official declarations (IT-1 + Anexo A,
 * IR-2 + Anexo B-1/A-1/J/Activo, DSS-07, DAF, RS1, RS2, RS3, RS4) + Excel (.xls) exports.
 */
class DgiiReportBuilderService
{
    public const ANULATION_TYPES = [
        '01' => 'Deterioro de Factura Pre-Impresa',
        '02' => 'Errores de Impresión (Factura Pre-Impresa)',
        '03' => 'Impresión Defectuosa',
        '04' => 'Duplicidad de Factura',
        '05' => 'Corrección de la Información',
        '06' => 'Cambio de Productos',
        '07' => 'Devolución de Productos',
        '08' => 'Omisión de Productos',
        '09' => 'Errores en Secuencias de NCF',
    ];

    public function __construct(
        protected DgiiPrevalidatorService $prevalidator
    ) {
    }

    /**
     * Build official DGII 606 TXT (23 columns per detail line + header line).
     */
    public function build606Txt(string $companyRnc, string $periodYm, iterable $records): string
    {
        $rows = $records instanceof Collection ? $records->values()->all() : array_values((array) $records);
        $cleanRnc = preg_replace('/[^0-9]/', '', $companyRnc) ?: '101142456';
        $cleanPeriod = preg_replace('/[^0-9]/', '', $periodYm);

        $lines = [
            "606|{$cleanRnc}|{$cleanPeriod}|" . count($rows),
        ];

        foreach ($rows as $row) {
            $r = (array) $row;
            $rnc = preg_replace('/[^0-9A-Za-z]/', '', (string) ($r['rnc_proveedor'] ?? ''));
            $tipoId = (string) ($r['tipo_id'] ?? $r['tipo_identificacion'] ?? (strlen($rnc) === 9 ? '1' : '2'));
            $tipoBienes = str_pad((string) ($r['tipo_bien_servicio'] ?? $r['tipo_bienes'] ?? '02'), 2, '0', STR_PAD_LEFT);
            $ncf = trim((string) ($r['ncf'] ?? ''));
            $ncfMod = trim((string) ($r['ncf_modificado'] ?? ''));
            $fechaComp = trim((string) ($r['fecha_comprobante'] ?? ''));
            $fechaPago = trim((string) ($r['fecha_pago'] ?? $fechaComp));

            $montoServicios = (float) ($r['monto_servicios'] ?? 0);
            $montoBienes = (float) ($r['monto_bienes'] ?? 0);
            $totalFacturado = (float) ($r['total_facturado'] ?? $r['total_monto_facturado'] ?? ($montoServicios + $montoBienes));
            $itbisFacturado = (float) ($r['itbis_facturado'] ?? 0);
            $itbisRetenido = (float) ($r['itbis_retenido'] ?? 0);
            $itbisProporcionalidad = (float) ($r['itbis_proporcional'] ?? $r['itbis_proporcionalidad'] ?? 0);
            $itbisCosto = (float) ($r['itbis_costo'] ?? 0);
            $itbisAdelantar = (float) ($r['itbis_adelantar'] ?? max(0, $itbisFacturado - $itbisCosto));
            $itbisPercibido = $r['itbis_percibido'] ?? '';

            $tipoRetencionIsr = trim((string) ($r['tipo_retencion_isr'] ?? ''));
            $montoRetencionRenta = (float) ($r['isr_retenido'] ?? $r['monto_retencion_renta'] ?? 0);
            $isrPercibido = $r['isr_percibido'] ?? '';
            $impuestoSelectivo = (float) ($r['impuesto_selectivo_consumo'] ?? $r['isc'] ?? 0);
            $otrosImpuestos = (float) ($r['otros_impuestos'] ?? 0);
            $propinaLegal = (float) ($r['monto_propina_legal'] ?? $r['propina_legal'] ?? 0);
            $formaPago = str_pad((string) ($r['forma_pago'] ?? '02'), 2, '0', STR_PAD_LEFT);

            if ($formaPago === '04') {
                $fechaPago = '';
            }

            $columns = [
                $rnc,
                $tipoId,
                $tipoBienes,
                $ncf,
                $ncfMod,
                $fechaComp,
                $fechaPago,
                number_format($montoServicios, 2, '.', ''),
                number_format($montoBienes, 2, '.', ''),
                number_format($totalFacturado, 2, '.', ''),
                number_format($itbisFacturado, 2, '.', ''),
                number_format($itbisRetenido, 2, '.', ''),
                number_format($itbisProporcionalidad, 2, '.', ''),
                number_format($itbisCosto, 2, '.', ''),
                number_format($itbisAdelantar, 2, '.', ''),
                ($itbisPercibido === '' || (float) $itbisPercibido == 0.0) ? '0.00' : number_format((float) $itbisPercibido, 2, '.', ''),
                $tipoRetencionIsr,
                number_format($montoRetencionRenta, 2, '.', ''),
                ($isrPercibido === '' || (float) $isrPercibido == 0.0) ? '0.00' : number_format((float) $isrPercibido, 2, '.', ''),
                number_format($impuestoSelectivo, 2, '.', ''),
                number_format($otrosImpuestos, 2, '.', ''),
                number_format($propinaLegal, 2, '.', ''),
                $formaPago,
            ];

            $lines[] = implode('|', $columns);
        }

        return implode("\r\n", $lines);
    }

    /**
     * Build official DGII 607 TXT (23 columns per detail line + header line).
     */
    public function build607Txt(string $companyRnc, string $periodYm, iterable $records): string
    {
        $rows = $records instanceof Collection ? $records->values()->all() : array_values((array) $records);
        $cleanRnc = preg_replace('/[^0-9]/', '', $companyRnc) ?: '101142456';
        $cleanPeriod = preg_replace('/[^0-9]/', '', $periodYm);

        $lines = [
            "607|{$cleanRnc}|{$cleanPeriod}|" . count($rows),
        ];

        foreach ($rows as $row) {
            $r = (array) $row;
            $rnc = preg_replace('/[^0-9A-Za-z]/', '', (string) ($r['rnc_cliente'] ?? ''));
            $tipoId = (string) ($r['tipo_id'] ?? $r['tipo_identificacion'] ?? ($rnc === '' ? '3' : (strlen($rnc) === 9 ? '1' : '2')));
            $ncf = trim((string) ($r['ncf'] ?? ''));
            $ncfMod = trim((string) ($r['ncf_modificado'] ?? ''));
            $tipoIngreso = str_pad((string) ($r['tipo_ingreso'] ?? '01'), 2, '0', STR_PAD_LEFT);
            $fechaComp = trim((string) ($r['fecha_comprobante'] ?? ''));
            $fechaRetencion = trim((string) ($r['fecha_retencion'] ?? $r['fecha_pago'] ?? ''));

            $montoFacturado = (float) ($r['monto_facturado'] ?? 0);
            $itbisFacturado = (float) ($r['itbis_facturado'] ?? 0);
            $itbisRetenido = (float) ($r['itbis_retenido'] ?? 0);
            $itbisPercibido = $r['itbis_percibido'] ?? '';
            $retencionIsr = (float) ($r['retencion_isr'] ?? 0);
            $isrPercibido = $r['isr_percibido'] ?? '';
            $isc = (float) ($r['isc'] ?? $r['impuesto_selectivo_consumo'] ?? 0);
            $otrosImpuestos = (float) ($r['otros_impuestos'] ?? 0);
            $propinaLegal = (float) ($r['propina_legal'] ?? $r['monto_propina_legal'] ?? 0);

            $efectivo = (float) ($r['efectivo'] ?? 0);
            $bancos = (float) ($r['bancos'] ?? $r['cheque_transferencia'] ?? 0);
            $tarjeta = (float) ($r['tarjeta'] ?? 0);
            $credito = (float) ($r['credito'] ?? $r['venta_credito'] ?? 0);
            $bonos = (float) ($r['bonos'] ?? 0);
            $permuta = (float) ($r['permuta'] ?? 0);
            $otrasFormas = (float) ($r['otras_formas'] ?? 0);

            $columns = [
                $rnc,
                $tipoId,
                $ncf,
                $ncfMod,
                $tipoIngreso,
                $fechaComp,
                $fechaRetencion,
                number_format($montoFacturado, 2, '.', ''),
                number_format($itbisFacturado, 2, '.', ''),
                number_format($itbisRetenido, 2, '.', ''),
                ($itbisPercibido === '' || (float) $itbisPercibido <= 0) ? '' : number_format((float) $itbisPercibido, 2, '.', ''),
                number_format($retencionIsr, 2, '.', ''),
                ($isrPercibido === '' || (float) $isrPercibido <= 0) ? '' : number_format((float) $isrPercibido, 2, '.', ''),
                number_format($isc, 2, '.', ''),
                number_format($otrosImpuestos, 2, '.', ''),
                number_format($propinaLegal, 2, '.', ''),
                number_format($efectivo, 2, '.', ''),
                number_format($bancos, 2, '.', ''),
                number_format($tarjeta, 2, '.', ''),
                number_format($credito, 2, '.', ''),
                number_format($bonos, 2, '.', ''),
                number_format($permuta, 2, '.', ''),
                number_format($otrasFormas, 2, '.', ''),
            ];

            $lines[] = implode('|', $columns);
        }

        return implode("\r\n", $lines);
    }

    /**
     * Build official DGII 608 TXT (3 columns per detail line + header line).
     */
    public function build608Txt(string $companyRnc, string $periodYm, iterable $records): string
    {
        $rows = $records instanceof Collection ? $records->values()->all() : array_values((array) $records);
        $cleanRnc = preg_replace('/[^0-9]/', '', $companyRnc) ?: '101142456';
        $cleanPeriod = preg_replace('/[^0-9]/', '', $periodYm);

        $lines = [
            "608|{$cleanRnc}|{$cleanPeriod}|" . count($rows),
        ];

        foreach ($rows as $row) {
            $r = (array) $row;
            $ncf = trim((string) ($r['ncf'] ?? ''));
            $fecha = trim((string) ($r['fecha_comprobante'] ?? ''));
            $tipoAnulacion = str_pad((string) ($r['tipo_anulacion'] ?? '05'), 2, '0', STR_PAD_LEFT);

            $lines[] = "{$ncf}|{$fecha}|{$tipoAnulacion}";
        }

        return implode("\r\n", $lines);
    }

    /**
     * Calculate IT-1 executive summary from 606 and 607 datasets.
     */
    public function calculateIt1Summary(iterable $records606, iterable $records607): array
    {
        $c606 = $records606 instanceof \Traversable ? iterator_to_array($records606) : (array) $records606;
        $c607 = $records607 instanceof \Traversable ? iterator_to_array($records607) : (array) $records607;

        $totalVentasBrutas = 0.0;
        $itbisCobrado = 0.0;
        $itbisRetenidoPorTerceros = 0.0;

        foreach ($c607 as $r) {
            $r = (array) $r;
            $totalVentasBrutas += (float) ($r['monto_facturado'] ?? 0);
            $itbisCobrado += (float) ($r['itbis_facturado'] ?? 0);
            $itbisRetenidoPorTerceros += (float) ($r['itbis_retenido'] ?? 0);
        }

        $ventasGravadas = $itbisCobrado > 0 ? round($itbisCobrado / 0.18, 2) : 0.0;
        if ($ventasGravadas > $totalVentasBrutas) {
            $ventasGravadas = $totalVentasBrutas;
        }
        $ventasExentas = max(0.0, round($totalVentasBrutas - $ventasGravadas, 2));

        $totalCompras = 0.0;
        $itbisPagadoCompras = 0.0;
        $itbisAdelantar = 0.0;
        $itbisRetenidoATerceros = 0.0;
        $isrRetenidoATerceros = 0.0;

        foreach ($c606 as $r) {
            $r = (array) $r;
            $totalCompras += (float) ($r['total_facturado'] ?? $r['total_monto_facturado'] ?? 0);
            $itbisPagadoCompras += (float) ($r['itbis_facturado'] ?? 0);
            $itbisAdelantar += (float) ($r['itbis_adelantar'] ?? $r['itbis_facturado'] ?? 0);
            $itbisRetenidoATerceros += (float) ($r['itbis_retenido'] ?? 0);
            $isrRetenidoATerceros += (float) ($r['isr_retenido'] ?? $r['monto_retencion_renta'] ?? 0);
        }

        $impuestoResultante = round($itbisCobrado - $itbisAdelantar - $itbisRetenidoPorTerceros, 2);

        return [
            'ingresos_operaciones_netos' => round($totalVentasBrutas, 2),
            'operaciones_exentas' => $ventasExentas,
            'operaciones_gravadas' => $ventasGravadas,
            'itbis_cobrado' => round($itbisCobrado, 2),
            'itbis_retenido_por_terceros' => round($itbisRetenidoPorTerceros, 2),
            'compras_locales_total' => round($totalCompras, 2),
            'itbis_pagado_compras' => round($itbisPagadoCompras, 2),
            'itbis_adelantar' => round($itbisAdelantar, 2),
            'itbis_retenido_a_terceros' => round($itbisRetenidoATerceros, 2),
            'isr_retenido_a_terceros' => round($isrRetenidoATerceros, 2),
            'itbis_a_pagar' => $impuestoResultante > 0 ? $impuestoResultante : 0.0,
            'saldo_a_favor' => $impuestoResultante < 0 ? abs($impuestoResultante) : 0.0,
            'total_compromiso_dgii' => round(max(0.0, $impuestoResultante) + $itbisRetenidoATerceros, 2),
        ];
    }

    /**
     * Compute the full official IT-1 and Anexo A declaration data (identical to Gridbase Bills DgiiDeclarationService::calculateIt1Data).
     */
    public function calculateIt1Declaration(iterable $records606, iterable $records607, array $companyMeta, string $year, string $month): array
    {
        $c606 = $records606 instanceof \Traversable ? iterator_to_array($records606) : (array) $records606;
        $c607 = $records607 instanceof \Traversable ? iterator_to_array($records607) : (array) $records607;

        $yearInt = (int) $year;
        $monthStr = str_pad((string) (int) $month, 2, '0', STR_PAD_LEFT);
        $periodFormatted = "{$monthStr}/{$yearInt}";
        $periodRaw = "{$yearInt}{$monthStr}";

        $nextMonth = (int) $month === 12 ? 1 : ((int) $month + 1);
        $nextYear = (int) $month === 12 ? ($yearInt + 1) : $yearInt;
        $deadlineDate = sprintf('20/%02d/%04d', $nextMonth, $nextYear);

        $ncfCounts = [
            '01_31' => 0, '02_32' => 0, '03_33' => 0, '04_34' => 0,
            '12' => 0, '14_44' => 0, '15_45' => 0, '16_46' => 0,
        ];
        $ncfAmounts = [
            '01_31' => 0.0, '02_32' => 0.0, '03_33' => 0.0, '04_34' => 0.0,
            '12' => 0.0, '14_44' => 0.0, '15_45' => 0.0, '16_46' => 0.0,
        ];
        $formasPago = [
            'efectivo' => 0.0,
            'cheque_transferencia' => 0.0,
            'tarjeta' => 0.0,
            'credito' => 0.0,
            'bonos' => 0.0,
            'permuta' => 0.0,
            'otras' => 0.0,
        ];
        $tiposIngreso = [
            '01' => 0.0, '02' => 0.0, '03' => 0.0,
            '04' => 0.0, '05' => 0.0, '06' => 0.0,
        ];

        $totalRetencionesPercibidas = 0.0;
        $totalItbisFacturadoVentas = 0.0;
        $totalOperacionesGravadas18 = 0.0;
        $totalOperacionesGravadas16 = 0.0;
        $totalOperacionesExentas = 0.0;

        foreach ($c607 as $row) {
            $r = (array) $row;
            $ncf = strtoupper(trim((string) ($r['ncf'] ?? '')));
            $netSubtotalDop = (float) ($r['monto_facturado'] ?? 0);
            $taxDop = (float) ($r['itbis_facturado'] ?? 0);

            $prefix = substr($ncf, 0, 3);
            if (in_array($prefix, ['B01', 'E31'], true)) {
                $ncfCounts['01_31']++;
                $ncfAmounts['01_31'] += $netSubtotalDop;
            } elseif (in_array($prefix, ['B02', 'E32'], true)) {
                $ncfCounts['02_32']++;
                $ncfAmounts['02_32'] += $netSubtotalDop;
            } elseif (in_array($prefix, ['B03', 'E33'], true)) {
                $ncfCounts['03_33']++;
                $ncfAmounts['03_33'] += $netSubtotalDop;
            } elseif (in_array($prefix, ['B04', 'E34'], true)) {
                $ncfCounts['04_34']++;
                $ncfAmounts['04_34'] += $netSubtotalDop;
            } elseif ($prefix === 'B12') {
                $ncfCounts['12']++;
                $ncfAmounts['12'] += $netSubtotalDop;
            } elseif (in_array($prefix, ['B14', 'E44'], true)) {
                $ncfCounts['14_44']++;
                $ncfAmounts['14_44'] += $netSubtotalDop;
            } elseif (in_array($prefix, ['B15', 'E45'], true)) {
                $ncfCounts['15_45']++;
                $ncfAmounts['15_45'] += $netSubtotalDop;
            } elseif (in_array($prefix, ['B16', 'E46'], true)) {
                $ncfCounts['16_46']++;
                $ncfAmounts['16_46'] += $netSubtotalDop;
            } else {
                $ncfCounts['02_32']++;
                $ncfAmounts['02_32'] += $netSubtotalDop;
            }

            $tipoIng = str_pad((string) ($r['tipo_ingreso'] ?? '01'), 2, '0', STR_PAD_LEFT);
            if (isset($tiposIngreso[$tipoIng])) {
                $tiposIngreso[$tipoIng] += $netSubtotalDop;
            } else {
                $tiposIngreso['01'] += $netSubtotalDop;
            }

            $isCreditNote = in_array($prefix, ['B04', 'E34'], true);
            if ($taxDop > 0) {
                $baseGravada = min($netSubtotalDop, round($taxDop / 0.18, 2));
                $baseExenta = max(0.0, round($netSubtotalDop - $baseGravada, 2));
                if ($isCreditNote) {
                    $totalItbisFacturadoVentas -= $taxDop;
                    $totalOperacionesGravadas18 -= $baseGravada;
                    $totalOperacionesExentas -= $baseExenta;
                } else {
                    $totalItbisFacturadoVentas += $taxDop;
                    $totalOperacionesGravadas18 += $baseGravada;
                    $totalOperacionesExentas += $baseExenta;
                }
            } else {
                if ($isCreditNote) {
                    $totalOperacionesExentas -= $netSubtotalDop;
                } else {
                    $totalOperacionesExentas += $netSubtotalDop;
                }
            }

            $formasPago['efectivo'] += (float) ($r['efectivo'] ?? 0);
            $formasPago['cheque_transferencia'] += (float) ($r['bancos'] ?? 0);
            $formasPago['tarjeta'] += (float) ($r['tarjeta'] ?? 0);
            $formasPago['credito'] += (float) ($r['credito'] ?? 0);
            $formasPago['bonos'] += (float) ($r['bonos'] ?? 0);
            $formasPago['permuta'] += (float) ($r['permuta'] ?? 0);
            $formasPago['otras'] += (float) ($r['otras_formas'] ?? 0);
            $totalRetencionesPercibidas += (float) ($r['itbis_retenido'] ?? 0);
        }

        $totalOperacionesAnexoA = $ncfAmounts['01_31'] + $ncfAmounts['02_32'] + $ncfAmounts['03_33']
            - $ncfAmounts['04_34'] + $ncfAmounts['12'] + $ncfAmounts['14_44'] + $ncfAmounts['15_45'] + $ncfAmounts['16_46'];

        $itbisDeducibleBienes = 0.0;
        $itbisDeducibleServicios = 0.0;
        $itbisNoDeducibleCosto = 0.0;

        foreach ($c606 as $row) {
            $r = (array) $row;
            $tax = (float) ($r['itbis_facturado'] ?? 0);
            $tipoGasto = str_pad((string) ($r['tipo_bien_servicio'] ?? $r['tipo_bienes'] ?? '02'), 2, '0', STR_PAD_LEFT);
            if (in_array($tipoGasto, ['09', '10'], true)) {
                $itbisDeducibleBienes += round($tax, 2);
            } else {
                $itbisDeducibleServicios += round($tax, 2);
            }
        }

        $totalItbisDeducibleCompras = round($itbisDeducibleBienes + $itbisDeducibleServicios, 2);

        $casilla1 = round($totalOperacionesAnexoA, 2);
        $casilla4 = max(0.0, round($totalOperacionesExentas, 2));
        $casilla11 = max(0.0, round($totalOperacionesGravadas18, 2));
        $casilla12 = max(0.0, round($totalOperacionesGravadas16, 2));
        $casilla10 = round($casilla11 + $casilla12, 2);
        $casilla16 = round($totalItbisFacturadoVentas, 2);
        $casilla21 = $casilla16;
        $casilla22 = round($itbisDeducibleBienes, 2);
        $casilla23 = round($itbisDeducibleServicios, 2);
        $casilla25 = round($casilla22 + $casilla23, 2);
        $diferencia = round($casilla21 - $casilla25, 2);
        $impuestoAPagar = max(0.0, $diferencia);
        $saldoAFavor = ($diferencia < 0) ? abs($diferencia) : 0.0;

        return [
            'period' => $periodRaw,
            'period_formatted' => $periodFormatted,
            'deadline' => $deadlineDate,
            'tax_id' => $companyMeta['rnc'] ?? '101142456',
            'company_name' => $companyMeta['name'] ?? 'Institución Educativa',
            'commercial_name' => $companyMeta['commercial_name'] ?? ($companyMeta['name'] ?? 'Institución Educativa'),
            'phone' => $companyMeta['phone'] ?? '',
            'email' => $companyMeta['email'] ?? '',
            'anexo_a' => [
                'ncf_counts' => $ncfCounts,
                'ncf_amounts' => $ncfAmounts,
                'total_operaciones' => $totalOperacionesAnexoA,
                'formas_pago' => $formasPago,
                'tipos_ingreso' => $tiposIngreso,
                'retenciones' => $totalRetencionesPercibidas,
                'itbis_pagado' => [
                    'bienes' => $itbisDeducibleBienes,
                    'servicios' => $itbisDeducibleServicios,
                    'costo' => $itbisNoDeducibleCosto,
                    'total_deducible' => $totalItbisDeducibleCompras,
                ],
            ],
            'it1' => [
                'casilla_1_total_operaciones' => $casilla1,
                'casilla_4_exentas' => $casilla4,
                'casilla_10_ingresos_gravados' => $casilla10,
                'casilla_11_gravadas_18' => $casilla11,
                'casilla_12_gravadas_16' => $casilla12,
                'casilla_16_itbis_cobrado' => $casilla16,
                'casilla_21_total_itbis_cobrado' => $casilla21,
                'casilla_22_itbis_bienes' => $casilla22,
                'casilla_23_itbis_servicios' => $casilla23,
                'casilla_25_total_itbis_deducible' => $casilla25,
                'casilla_26_impuesto_a_pagar' => $impuestoAPagar,
                'casilla_27_saldo_a_favor' => $saldoAFavor,
                'casilla_30_retenciones_computables' => $totalRetencionesPercibidas,
                'casilla_33_diferencia_a_pagar' => $impuestoAPagar,
                'casilla_34_nuevo_saldo_a_favor' => $saldoAFavor,
                'casilla_38_total_a_pagar' => $impuestoAPagar,
            ],
        ];
    }

    /**
     * Compute the full official IR-2 and Anexo B-1 / A-1 / J / Activo declaration data.
     */
    public function calculateIr2Declaration(iterable $annualRecords606, iterable $annualRecords607, array $companyMeta, string $year): array
    {
        $c606 = $annualRecords606 instanceof \Traversable ? iterator_to_array($annualRecords606) : (array) $annualRecords606;
        $c607 = $annualRecords607 instanceof \Traversable ? iterator_to_array($annualRecords607) : (array) $annualRecords607;

        $yearInt = (int) $year;
        $deadlineDate = sprintf('30/04/%04d', $yearInt + 1);
        $capitalSocial = (float) ($companyMeta['capital'] ?? 100000.0);

        $jVentasCounts = [
            '01_31' => 0, '02_32' => 0, '03_33' => 0, '04_34' => 0,
            '12' => 0, '14_44' => 0, '15_45' => 0, '16_46' => 0, 'otras' => 0,
        ];
        $jVentasAmounts = [
            '01_31' => 0.0, '02_32' => 0.0, '03_33' => 0.0, '04_34' => 0.0,
            '12' => 0.0, '14_44' => 0.0, '15_45' => 0.0, '16_46' => 0.0, 'otras' => 0.0,
        ];

        $totalVentasLocales = 0.0;
        $totalExportaciones = 0.0;
        $totalDevolucionesVentas = 0.0;
        $totalRetencionesEstado = 0.0;
        $totalCobrosRecibidos = 0.0;
        $totalFacturadoAnual = 0.0;

        foreach ($c607 as $row) {
            $r = (array) $row;
            $ncf = strtoupper(trim((string) ($r['ncf'] ?? '')));
            $netSubtotalDop = (float) ($r['monto_facturado'] ?? 0);
            $taxDop = (float) ($r['itbis_facturado'] ?? 0);
            $totalDop = round($netSubtotalDop + $taxDop, 2);
            $prefix = substr($ncf, 0, 3);

            $totalFacturadoAnual += $totalDop;
            $totalCobrosRecibidos += (float) ($r['efectivo'] ?? 0) + (float) ($r['bancos'] ?? 0) + (float) ($r['tarjeta'] ?? 0);

            if (in_array($prefix, ['B15', 'E45'], true)) {
                $totalRetencionesEstado += round($netSubtotalDop * 0.05, 2);
            }

            if (in_array($prefix, ['B01', 'E31'], true)) {
                $jVentasCounts['01_31']++;
                $jVentasAmounts['01_31'] += $netSubtotalDop;
                $totalVentasLocales += $netSubtotalDop;
            } elseif (in_array($prefix, ['B02', 'E32'], true)) {
                $jVentasCounts['02_32']++;
                $jVentasAmounts['02_32'] += $netSubtotalDop;
                $totalVentasLocales += $netSubtotalDop;
            } elseif (in_array($prefix, ['B03', 'E33'], true)) {
                $jVentasCounts['03_33']++;
                $jVentasAmounts['03_33'] += $netSubtotalDop;
                $totalVentasLocales += $netSubtotalDop;
            } elseif (in_array($prefix, ['B04', 'E34'], true)) {
                $jVentasCounts['04_34']++;
                $jVentasAmounts['04_34'] += $netSubtotalDop;
                $totalDevolucionesVentas += $netSubtotalDop;
            } elseif (in_array($prefix, ['B14', 'E44'], true)) {
                $jVentasCounts['14_44']++;
                $jVentasAmounts['14_44'] += $netSubtotalDop;
                $totalVentasLocales += $netSubtotalDop;
            } elseif (in_array($prefix, ['B15', 'E45'], true)) {
                $jVentasCounts['15_45']++;
                $jVentasAmounts['15_45'] += $netSubtotalDop;
                $totalVentasLocales += $netSubtotalDop;
            } elseif (in_array($prefix, ['B16', 'E46'], true)) {
                $jVentasCounts['16_46']++;
                $jVentasAmounts['16_46'] += $netSubtotalDop;
                $totalExportaciones += $netSubtotalDop;
            } else {
                $jVentasCounts['02_32']++;
                $jVentasAmounts['02_32'] += $netSubtotalDop;
                $totalVentasLocales += $netSubtotalDop;
            }
        }

        $cuentasPorCobrarClientes = max(0.0, round($totalFacturadoAnual - $totalCobrosRecibidos, 2));
        $totalIngresosNetosB1 = round($totalVentasLocales + $totalExportaciones - $totalDevolucionesVentas, 2);

        $b1Details = [
            'costo_venta' => 0.0,
            'gastos_personal' => 0.0,
            'honorarios_fisicas' => 0.0,
            'honorarios_morales' => 0.0,
            'otros_servicios' => 0.0,
            'arrendamientos_fisicas' => 0.0,
            'arrendamientos_morales' => 0.0,
            'otros_arrendamientos' => 0.0,
            'gastos_activos_fijos' => 0.0,
            'relaciones_publicas' => 0.0,
            'publicidad' => 0.0,
            'seguros' => 0.0,
            'otras_deducciones' => 0.0,
            'retencion_cheques' => 0.0,
            'otros_financieros' => 0.0,
            'gastos_extraordinarios' => 0.0,
        ];

        $jGastosCounts = [
            '01_31' => 0, '03_33' => 0, '04_34' => 0, '15_45' => 0, '14_44' => 0,
            '11_41' => 0, '13_43' => 0,
        ];
        $jGastosAmounts = [
            '01_31' => 0.0, '03_33' => 0.0, '04_34' => 0.0, '15_45' => 0.0, '14_44' => 0.0,
            '11_41' => 0.0, '13_43' => 0.0,
        ];

        $totalGastosFacturados = 0.0;
        $totalGastosPagados = 0.0;

        foreach ($c606 as $row) {
            $r = (array) $row;
            $montoNeto = (float) ($r['total_facturado'] ?? $r['total_monto_facturado'] ?? 0);
            $tax = (float) ($r['itbis_facturado'] ?? 0);
            $totalMonto = $montoNeto + $tax;
            $totalGastosFacturados += $totalMonto;
            $totalGastosPagados += $totalMonto;

            $tipoGasto = str_pad((string) ($r['tipo_bien_servicio'] ?? $r['tipo_bienes'] ?? '02'), 2, '0', STR_PAD_LEFT);
            $cleanRnc = preg_replace('/[^0-9]/', '', (string) ($r['rnc_proveedor'] ?? ''));
            $isFisica = strlen($cleanRnc) === 11;

            switch ($tipoGasto) {
                case '01': $b1Details['gastos_personal'] += $montoNeto; break;
                case '02':
                    if ($isFisica) $b1Details['honorarios_fisicas'] += $montoNeto;
                    else $b1Details['honorarios_morales'] += $montoNeto;
                    break;
                case '03':
                    if ($isFisica) $b1Details['arrendamientos_fisicas'] += $montoNeto;
                    else $b1Details['arrendamientos_morales'] += $montoNeto;
                    break;
                case '04': $b1Details['gastos_activos_fijos'] += $montoNeto; break;
                case '05': $b1Details['relaciones_publicas'] += $montoNeto; break;
                case '06': $b1Details['otras_deducciones'] += $montoNeto; break;
                case '07': $b1Details['otros_financieros'] += $montoNeto; break;
                case '08': $b1Details['gastos_extraordinarios'] += $montoNeto; break;
                case '09': $b1Details['costo_venta'] += $montoNeto; break;
                case '11': $b1Details['seguros'] += $montoNeto; break;
                default: $b1Details['otros_servicios'] += $montoNeto; break;
            }

            $prefix = substr(strtoupper(trim((string) ($r['ncf'] ?? ''))), 0, 3);
            if (in_array($prefix, ['B11', 'E41'], true)) {
                $jGastosCounts['11_41']++;
                $jGastosAmounts['11_41'] += $montoNeto;
            } elseif (in_array($prefix, ['B13', 'E43'], true)) {
                $jGastosCounts['13_43']++;
                $jGastosAmounts['13_43'] += $montoNeto;
            } else {
                $jGastosCounts['01_31']++;
                $jGastosAmounts['01_31'] += $montoNeto;
            }
        }

        $totalCostosYGastos = round(array_sum($b1Details), 2);
        $beneficioNetoAntesImpuesto = round($totalIngresosNetosB1 - $totalCostosYGastos, 2);
        $rentaNetaImponible = max(0.0, $beneficioNetoAntesImpuesto);
        $impuestoLiquidado = round($rentaNetaImponible * 0.27, 2);
        $diferenciaPagar = max(0.0, round($impuestoLiquidado - $totalRetencionesEstado, 2));
        $saldoAFavor = ($impuestoLiquidado < $totalRetencionesEstado) ? round($totalRetencionesEstado - $impuestoLiquidado, 2) : 0.0;

        $cajaYBancos = round(max(50000.0, $totalCobrosRecibidos - $totalGastosPagados), 2);
        $totalActivos = round($cajaYBancos + $cuentasPorCobrarClientes, 2);
        $cuentasPorPagar = round(max(0.0, $totalGastosFacturados - $totalGastosPagados), 2);
        $impuestosPorPagar = round($impuestoLiquidado, 2);
        $totalPasivos = round($cuentasPorPagar + $impuestosPorPagar, 2);

        $reservaLegal = ($beneficioNetoAntesImpuesto > 0) ? round(min($capitalSocial * 0.10, $beneficioNetoAntesImpuesto * 0.05), 2) : 0.0;
        $beneficioEjercicio = round($beneficioNetoAntesImpuesto - $impuestoLiquidado, 2);
        $patrimonioObjetivo = round($totalActivos - $totalPasivos, 2);
        $beneficiosAnteriores = round($patrimonioObjetivo - ($capitalSocial + $reservaLegal + $beneficioEjercicio), 2);
        $totalPatrimonio = round($capitalSocial + $reservaLegal + $beneficiosAnteriores + $beneficioEjercicio, 2);
        $totalPasivosYPatrimonio = round($totalPasivos + $totalPatrimonio, 2);

        $impuestoActivos1Pct = round($totalActivos * 0.01, 2);
        $diferenciaImpuestoActivos = max(0.0, round($impuestoActivos1Pct - $impuestoLiquidado, 2));

        return [
            'year' => (string) $yearInt,
            'period_formatted' => "01/01/{$yearInt} - 31/12/{$yearInt}",
            'deadline' => $deadlineDate,
            'tax_id' => $companyMeta['rnc'] ?? '101142456',
            'company_name' => $companyMeta['name'] ?? 'Institución Educativa',
            'commercial_name' => $companyMeta['commercial_name'] ?? ($companyMeta['name'] ?? 'Institución Educativa'),
            'phone' => $companyMeta['phone'] ?? '',
            'email' => $companyMeta['email'] ?? '',
            'sector' => 'Educación, Servicios y Comercio',
            'b1' => array_merge($b1Details, [
                'ventas_locales' => $totalVentasLocales,
                'exportaciones' => $totalExportaciones,
                'devoluciones_ventas' => $totalDevolucionesVentas,
                'total_ingresos_netos' => $totalIngresosNetosB1,
                'gastos_servicios' => round($b1Details['honorarios_fisicas'] + $b1Details['honorarios_morales'] + $b1Details['otros_servicios'], 2),
                'arrendamientos' => round($b1Details['arrendamientos_fisicas'] + $b1Details['arrendamientos_morales'] + $b1Details['otros_arrendamientos'], 2),
                'gastos_representacion' => round($b1Details['relaciones_publicas'] + $b1Details['publicidad'], 2),
                'otras_deducciones' => round($b1Details['seguros'] + $b1Details['otras_deducciones'], 2),
                'gastos_financieros' => round($b1Details['retencion_cheques'] + $b1Details['otros_financieros'], 2),
                'total_costos_gastos' => $totalCostosYGastos,
                'beneficio_neto' => $beneficioNetoAntesImpuesto,
            ]),
            'anexo_j' => [
                'ventas' => [
                    'counts' => $jVentasCounts,
                    'amounts' => $jVentasAmounts,
                    'total' => $totalIngresosNetosB1,
                ],
                'gastos' => [
                    'counts' => $jGastosCounts,
                    'amounts' => $jGastosAmounts,
                    'total' => $totalCostosYGastos,
                ],
            ],
            'a1' => [
                'caja_bancos' => $cajaYBancos,
                'cuentas_por_cobrar' => $cuentasPorCobrarClientes,
                'total_activos' => $totalActivos,
                'cuentas_por_pagar' => $cuentasPorPagar,
                'impuestos_por_pagar' => $impuestosPorPagar,
                'total_pasivos' => $totalPasivos,
                'capital_social' => $capitalSocial,
                'reserva_legal' => $reservaLegal,
                'beneficios_anteriores' => $beneficiosAnteriores,
                'beneficio_ejercicio' => $beneficioEjercicio,
                'total_patrimonio' => $totalPatrimonio,
                'total_pasivos_patrimonio' => $totalPasivosYPatrimonio,
                'cuadrado' => abs($totalActivos - $totalPasivosYPatrimonio) < 0.01,
            ],
            'ir2' => [
                'casilla_A_total_ingresos' => $totalIngresosNetosB1,
                'casilla_1_beneficio_neto' => $beneficioNetoAntesImpuesto,
                'casilla_7_renta_neta' => $rentaNetaImponible,
                'casilla_11_renta_imponible' => $rentaNetaImponible,
                'casilla_12_impuesto_liquidado' => $impuestoLiquidado,
                'casilla_14_retenciones_estado' => $totalRetencionesEstado,
                'casilla_23_diferencia_pagar' => $diferenciaPagar,
                'casilla_24_saldo_favor' => $saldoAFavor,
                'casilla_31_total_a_pagar' => $diferenciaPagar,
                'tasa_aplicada' => '27%',
            ],
            'activo' => [
                'total_activos' => $totalActivos,
                'impuesto_1pct' => $impuestoActivos1Pct,
                'isr_liquidado' => $impuestoLiquidado,
                'diferencia_pagar' => $diferenciaImpuestoActivos,
            ],
        ];
    }

    /**
     * Compute RST & Sectorial declarations (RS1, RS2, RS3, RS4, DSS-07, DAF)
     */
    public function calculateOtherDeclarations(array $ir2Data, array $it1Data, array $companyMeta, string $year, string $month): array
    {
        $yearInt = (int) $year;
        $monthStr = str_pad((string) (int) $month, 2, '0', STR_PAD_LEFT);
        $totalIngresos = (float) ($ir2Data['b1']['total_ingresos_netos'] ?? 0.0);
        $totalCompras = (float) ($ir2Data['b1']['total_costos_gastos'] ?? 0.0);
        $rstDeadline = sprintf('28/02/%04d', $yearInt + 1);

        // RS1
        $rentaEstimadaRs1 = round($totalIngresos * 0.60, 2);
        $impuestoRs1 = 0.0;
        if ($rentaEstimadaRs1 > 867123) {
            $impuestoRs1 = 142208.15 + (($rentaEstimadaRs1 - 867123) * 0.25);
        } elseif ($rentaEstimadaRs1 > 624329) {
            $impuestoRs1 = 93649.35 + (($rentaEstimadaRs1 - 624329) * 0.20);
        } elseif ($rentaEstimadaRs1 > 416220) {
            $impuestoRs1 = ($rentaEstimadaRs1 - 416220) * 0.15;
        }
        $impuestoRs1 = round($impuestoRs1, 2);

        // RS2
        $impuestoRs2 = round($totalIngresos * 0.07, 2);

        // RS3
        $ventasEstimadasRs3 = round($totalCompras * 1.0653, 2);
        $margenBrutoRs3 = round($ventasEstimadasRs3 - $totalCompras, 2);
        $isrRs3 = round($margenBrutoRs3 * 0.27, 2);
        $itbisRs3 = round($margenBrutoRs3 * 0.60 * 0.18, 2);
        $totalRs3 = round($isrRs3 + $itbisRs3, 2);

        // RS4
        $impuestoRs4 = round($totalIngresos * 0.061, 2);

        // DSS-07
        $dssCategories = [
            1 => ['label' => 'Vida Colectivo', 'count' => 0, 'amount' => 0.0],
            2 => ['label' => 'Vida Individual', 'count' => 0, 'amount' => 0.0],
            3 => ['label' => 'Salud', 'count' => 0, 'amount' => 0.0],
            4 => ['label' => 'Accidentes Personales y Salud', 'count' => 0, 'amount' => 0.0],
            5 => ['label' => 'Incendios y Aliados', 'count' => 0, 'amount' => 0.0],
            6 => ['label' => 'Naves Marítimas y Aéreas', 'count' => 0, 'amount' => 0.0],
            7 => ['label' => 'Transporte de Carga', 'count' => 0, 'amount' => 0.0],
            8 => ['label' => 'Vehículo de Motor', 'count' => 0, 'amount' => 0.0],
            9 => ['label' => 'Agrícolas Pecuarias', 'count' => 0, 'amount' => 0.0],
            10 => ['label' => 'Fianzas', 'count' => 0, 'amount' => 0.0],
            11 => ['label' => 'Otros Seguros / Servicios', 'count' => 0, 'amount' => 0.0],
        ];
        $dssTotalOp = (float) ($it1Data['it1']['casilla_1_total_operaciones'] ?? 0.0);
        $dssImpuesto = round($dssTotalOp * 0.16, 2);

        // DAF
        $activosFinancieros = (float) ($ir2Data['a1']['total_activos'] ?? 0.0);
        $exencionDaf = 700000000.0;
        $activosDespuesExencion = max(0.0, $activosFinancieros - $exencionDaf);
        $impuestoDaf = round($activosDespuesExencion * 0.0048, 2);
        $rentaNetaIr2 = (float) ($ir2Data['ir2']['casilla_7_renta_neta'] ?? 0.0);
        $impuestoPagarDaf = min($impuestoDaf, $rentaNetaIr2);

        $common = [
            'year' => (string) $yearInt,
            'tax_id' => $companyMeta['rnc'] ?? '101142456',
            'company_name' => $companyMeta['name'] ?? 'Institución Educativa',
        ];

        return [
            'rs1' => array_merge($common, [
                'deadline' => $rstDeadline,
                'rs1' => [
                    'casilla_1_ventas' => 0.0,
                    'casilla_2_servicios' => $totalIngresos,
                    'casilla_3_alquileres' => 0.0,
                    'casilla_4_honorarios' => 0.0,
                    'casilla_5_total_ingresos' => $totalIngresos,
                    'casilla_8_renta_estimada' => $rentaEstimadaRs1,
                    'casilla_11_impuesto_liquidado' => $impuestoRs1,
                    'casilla_16_total_a_pagar' => $impuestoRs1,
                ],
            ]),
            'rs2' => array_merge($common, [
                'deadline' => $rstDeadline,
                'rs2' => [
                    'casilla_1_ventas' => 0.0,
                    'casilla_2_servicios' => $totalIngresos,
                    'casilla_3_alquileres' => 0.0,
                    'casilla_5_total_ingresos' => $totalIngresos,
                    'casilla_8_impuesto_liquidado' => $impuestoRs2,
                    'casilla_14_total_a_pagar' => $impuestoRs2,
                ],
            ]),
            'rs3' => array_merge($common, [
                'deadline' => $rstDeadline,
                'rs3' => [
                    'casilla_1_compras' => $totalCompras,
                    'casilla_3_total_compras' => $totalCompras,
                    'casilla_5_ventas_estimadas' => $ventasEstimadasRs3,
                    'casilla_7_margen_bruto' => $margenBrutoRs3,
                    'casilla_10_isr_liquidado' => $isrRs3,
                    'casilla_11_itbis_liquidado' => $itbisRs3,
                    'casilla_16_total_a_pagar' => $totalRs3,
                ],
            ]),
            'rs4' => array_merge($common, [
                'deadline' => $rstDeadline,
                'rs4' => [
                    'casilla_1_ingresos_agropecuarios' => $totalIngresos,
                    'casilla_6_total_ingresos' => $totalIngresos,
                    'casilla_7_impuesto_liquidado' => $impuestoRs4,
                    'casilla_15_total_a_pagar' => $impuestoRs4,
                ],
            ]),
            'dss' => array_merge($common, [
                'period_formatted' => "{$monthStr}/{$yearInt}",
                'deadline' => $it1Data['deadline'] ?? '20/' . $monthStr . '/' . $yearInt,
                'categories' => $dssCategories,
                'dss' => [
                    'casilla_12_total_operaciones' => $dssTotalOp,
                    'casilla_13_operaciones_exentas' => $dssTotalOp,
                    'casilla_14_operaciones_gravadas' => 0.0,
                    'casilla_15_impuesto_a_pagar' => 0.0,
                    'casilla_19_diferencia_a_pagar' => 0.0,
                    'casilla_24_total_a_pagar' => 0.0,
                ],
            ]),
            'daf' => array_merge($common, [
                'period_formatted' => "01/01/{$yearInt} - 31/12/{$yearInt}",
                'deadline' => $ir2Data['deadline'] ?? "30/04/" . ($yearInt + 1),
                'daf' => [
                    'casilla_1_activos_financieros' => $activosFinancieros,
                    'casilla_2_exencion' => $exencionDaf,
                    'casilla_3_activos_despues_exencion' => $activosDespuesExencion,
                    'casilla_4_impuesto_liquidado' => $impuestoDaf,
                    'casilla_5_renta_neta_imponible' => $rentaNetaIr2,
                    'casilla_6_gastos_deducibles' => 0.0,
                    'casilla_7_renta_despues_gasto' => $rentaNetaIr2,
                    'casilla_8_impuesto_a_pagar' => $impuestoPagarDaf,
                    'casilla_13_diferencia_a_pagar' => $impuestoPagarDaf,
                    'casilla_17_total_a_pagar' => $impuestoPagarDaf,
                ],
            ]),
        ];
    }
}
