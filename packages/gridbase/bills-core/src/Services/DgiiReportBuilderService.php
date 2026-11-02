<?php

namespace Gridbase\BillsCore\Services;

use Illuminate\Support\Collection;

/**
 * DgiiReportBuilderService — Gridbase Bills Core Engine
 *
 * Generates official DGII Format 606 (23 columns), Format 607 (23 columns),
 * Format 608 (3 columns), and Declaración Jurada IT-1 executive summary.
 */
class DgiiReportBuilderService
{
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
            $tipoBienes = str_pad((string) ($r['tipo_bienes'] ?? '02'), 2, '0', STR_PAD_LEFT);
            $ncf = trim((string) ($r['ncf'] ?? ''));
            $ncfMod = trim((string) ($r['ncf_modificado'] ?? ''));
            $fechaComp = trim((string) ($r['fecha_comprobante'] ?? ''));
            $fechaPago = trim((string) ($r['fecha_pago'] ?? $fechaComp));

            $montoServicios = (float) ($r['monto_servicios'] ?? 0);
            $montoBienes = (float) ($r['monto_bienes'] ?? 0);
            $totalFacturado = (float) ($r['total_monto_facturado'] ?? ($montoServicios + $montoBienes));
            $itbisFacturado = (float) ($r['itbis_facturado'] ?? 0);
            $itbisRetenido = (float) ($r['itbis_retenido'] ?? 0);
            $itbisProporcionalidad = (float) ($r['itbis_proporcionalidad'] ?? 0);
            $itbisCosto = (float) ($r['itbis_costo'] ?? 0);
            $itbisAdelantar = (float) ($r['itbis_adelantar'] ?? max(0, $itbisFacturado - $itbisCosto));
            $itbisPercibido = $r['itbis_percibido'] ?? '';

            $tipoRetencionIsr = trim((string) ($r['tipo_retencion_isr'] ?? ''));
            $montoRetencionRenta = (float) ($r['monto_retencion_renta'] ?? 0);
            $isrPercibido = $r['isr_percibido'] ?? '';
            $impuestoSelectivo = (float) ($r['impuesto_selectivo_consumo'] ?? $r['isc'] ?? 0);
            $otrosImpuestos = (float) ($r['otros_impuestos'] ?? 0);
            $propinaLegal = (float) ($r['monto_propina_legal'] ?? $r['propina_legal'] ?? 0);
            $formaPago = str_pad((string) ($r['forma_pago'] ?? '02'), 2, '0', STR_PAD_LEFT);

            // Official 23 columns for Format 606
            $columns = [
                $rnc,                                                   // 1. RNC o Cédula
                $tipoId,                                                // 2. Tipo Id
                $tipoBienes,                                            // 3. Tipo Bienes y Servicios Comprados
                $ncf,                                                   // 4. NCF
                $ncfMod,                                                // 5. NCF o Documento Modificado
                $fechaComp,                                             // 6. Fecha Comprobante (AAAAMMDD)
                $fechaPago,                                             // 7. Fecha Pago (AAAAMMDD)
                number_format($montoServicios, 2, '.', ''),             // 8. Monto Facturado en Servicios
                number_format($montoBienes, 2, '.', ''),                // 9. Monto Facturado en Bienes
                number_format($totalFacturado, 2, '.', ''),             // 10. Total Monto Facturado
                number_format($itbisFacturado, 2, '.', ''),             // 11. ITBIS Facturado
                number_format($itbisRetenido, 2, '.', ''),              // 12. ITBIS Retenido
                number_format($itbisProporcionalidad, 2, '.', ''),      // 13. ITBIS sujeto a Proporcionalidad
                number_format($itbisCosto, 2, '.', ''),                 // 14. ITBIS llevado al Costo
                number_format($itbisAdelantar, 2, '.', ''),             // 15. ITBIS por Adelantar
                $itbisPercibido === '' ? '' : number_format((float) $itbisPercibido, 2, '.', ''), // 16. ITBIS percibido en compras
                $tipoRetencionIsr,                                      // 17. Tipo de Retención en ISR
                number_format($montoRetencionRenta, 2, '.', ''),        // 18. Monto Retención Renta
                $isrPercibido === '' ? '' : number_format((float) $isrPercibido, 2, '.', ''), // 19. ISR Percibido en compras
                number_format($impuestoSelectivo, 2, '.', ''),          // 20. Impuesto Selectivo al Consumo
                number_format($otrosImpuestos, 2, '.', ''),             // 21. Otros Impuestos/Tasas
                number_format($propinaLegal, 2, '.', ''),               // 22. Monto Propina Legal
                $formaPago,                                             // 23. Forma de Pago
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

            // Official 23 columns for Format 607
            $columns = [
                $rnc,                                                   // 1. RNC/Cédula o Pasaporte
                $tipoId,                                                // 2. Tipo Identificación
                $ncf,                                                   // 3. Número Comprobante Fiscal
                $ncfMod,                                                // 4. Número Comprobante Fiscal Modificado
                $tipoIngreso,                                           // 5. Tipo de Ingreso
                $fechaComp,                                             // 6. Fecha Comprobante (AAAAMMDD)
                $fechaRetencion,                                        // 7. Fecha de Retención / Pago (AAAAMMDD)
                number_format($montoFacturado, 2, '.', ''),             // 8. Monto Facturado
                number_format($itbisFacturado, 2, '.', ''),             // 9. ITBIS Facturado
                number_format($itbisRetenido, 2, '.', ''),              // 10. ITBIS Retenido por Terceros
                $itbisPercibido === '' ? '' : number_format((float) $itbisPercibido, 2, '.', ''), // 11. ITBIS Percibido
                number_format($retencionIsr, 2, '.', ''),               // 12. Retención Renta por Terceros
                $isrPercibido === '' ? '' : number_format((float) $isrPercibido, 2, '.', ''), // 13. ISR Percibido
                number_format($isc, 2, '.', ''),                        // 14. Impuesto Selectivo al Consumo
                number_format($otrosImpuestos, 2, '.', ''),             // 15. Otros Impuestos/Tasas
                number_format($propinaLegal, 2, '.', ''),               // 16. Monto Propina Legal
                number_format($efectivo, 2, '.', ''),                   // 17. Efectivo
                number_format($bancos, 2, '.', ''),                     // 18. Cheque/Transferencia/Depósito
                number_format($tarjeta, 2, '.', ''),                    // 19. Tarjeta Débito/Crédito
                number_format($credito, 2, '.', ''),                    // 20. Venta a Crédito
                number_format($bonos, 2, '.', ''),                      // 21. Bonos o Certificados de Regalo
                number_format($permuta, 2, '.', ''),                    // 22. Permuta
                number_format($otrasFormas, 2, '.', ''),                // 23. Otras Formas de Ventas
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
            $tipoAnulacion = str_pad((string) ($r['tipo_anulacion'] ?? '04'), 2, '0', STR_PAD_LEFT);

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

        // Educational institutions often have high exempt operations (Art. 344 CT)
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
            $totalCompras += (float) ($r['total_monto_facturado'] ?? 0);
            $itbisPagadoCompras += (float) ($r['itbis_facturado'] ?? 0);
            $itbisAdelantar += (float) ($r['itbis_adelantar'] ?? $r['itbis_facturado'] ?? 0);
            $itbisRetenidoATerceros += (float) ($r['itbis_retenido'] ?? 0);
            $isrRetenidoATerceros += (float) ($r['monto_retencion_renta'] ?? 0);
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
}
