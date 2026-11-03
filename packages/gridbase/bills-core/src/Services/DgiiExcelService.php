<?php

namespace Gridbase\BillsCore\Services;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use PhpOffice\PhpSpreadsheet\Reader\Xls as XlsReader;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DgiiChunkFilter implements IReadFilter
{
    private int $maxRow;

    public function __construct(int $maxRow = 500)
    {
        $this->maxRow = $maxRow;
    }

    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        return $row <= $this->maxRow;
    }
}

/**
 * DgiiExcelService — Gridbase Bills Core Engine
 *
 * Pre-fills official DGII Excel workbooks (.xls / .xlsx) for:
 * - Formatos Informativos: 606, 607, 608
 * - Declaraciones Juradas: IT-1 (+ Anexo A), IR-2 (+ B-1, A-1, J, Activo, D, D-1, D-2, E, G, H-1),
 *   ITC-01 (IST), DSS-07, DAF, RS1, RS2, RS3, RS4.
 */
class DgiiExcelService
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

    /**
     * Generate pre-filled Formato 606 using the official DGII template (Formato_DGII_606.xls).
     */
    public function generate606Excel(string $companyTaxId, string $period, array $records): Spreadsheet
    {
        $templatePath = resource_path('templates/dgii/Formato_DGII_606.xls');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template Formato 606 no encontrado en: {$templatePath}");
        }

        $recordCount = count($records);
        $maxRow = max(25, 12 + $recordCount);

        $reader = IOFactory::createReader('Xls');
        $reader->setReadFilter(new DgiiChunkFilter($maxRow));
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B3', 'Gridbase Bills');
        $spreadsheet->getProperties()
            ->setCreator('Gridbase Bills')
            ->setLastModifiedBy('Gridbase Bills')
            ->setTitle("DGII 606 {$companyTaxId} {$period}")
            ->setCompany('Gridbase');

        // 1. Fill Header Metadata (Rows 4 - 7)
        $sheet->setCellValueExplicit('C4', $companyTaxId, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B4', $companyTaxId, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C5', $period, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B5', $period, DataType::TYPE_STRING);
        $sheet->setCellValue('C6', $recordCount);
        $sheet->setCellValue('K7', 0);

        // 2. Fill Detail Rows (Starting at Row 12)
        $row = 12;
        $line = 1;
        foreach ($records as $r) {
            $r = (array) $r;
            $sheet->setCellValue("A{$row}", $line);
            $sheet->setCellValueExplicit("B{$row}", (string) ($r['rnc_proveedor'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$row}", (string) ($r['tipo_identificacion'] ?? $r['tipo_id'] ?? '1'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$row}", (string) ($r['tipo_bien_servicio'] ?? $r['tipo_bienes'] ?? '02'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E{$row}", (string) ($r['ncf'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F{$row}", (string) ($r['ncf_modificado'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("G{$row}", (string) ($r['fecha_comprobante'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("I{$row}", (string) ($r['fecha_pago'] ?? ''), DataType::TYPE_STRING);

            $sheet->setCellValue("K{$row}", (float) ($r['monto_servicios'] ?? 0));
            $sheet->setCellValue("L{$row}", (float) ($r['monto_bienes'] ?? 0));
            $sheet->setCellValue("M{$row}", (float) ($r['total_facturado'] ?? $r['total_monto_facturado'] ?? 0));
            $sheet->setCellValue("N{$row}", (float) ($r['itbis_facturado'] ?? 0));
            $sheet->setCellValue("O{$row}", (float) ($r['itbis_retenido'] ?? 0));
            $sheet->setCellValue("P{$row}", (float) ($r['itbis_proporcional'] ?? $r['itbis_proporcionalidad'] ?? 0));
            $sheet->setCellValue("Q{$row}", (float) ($r['itbis_costo'] ?? 0));
            $sheet->setCellValue("R{$row}", (float) ($r['itbis_adelantar'] ?? 0));
            $sheet->setCellValue("S{$row}", (float) ($r['itbis_percibido'] ?? 0));

            $sheet->setCellValueExplicit("T{$row}", (string) ($r['tipo_retencion_isr'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValue("U{$row}", (float) ($r['isr_retenido'] ?? $r['monto_retencion_renta'] ?? 0));
            $sheet->setCellValue("V{$row}", (float) ($r['isr_percibido'] ?? 0));
            $sheet->setCellValue("W{$row}", (float) ($r['isc'] ?? $r['impuesto_selectivo_consumo'] ?? 0));
            $sheet->setCellValue("X{$row}", (float) ($r['otros_impuestos'] ?? 0));
            $sheet->setCellValue("Y{$row}", (float) ($r['propina_legal'] ?? $r['monto_propina_legal'] ?? 0));
            $sheet->setCellValueExplicit("Z{$row}", (string) ($r['forma_pago'] ?? '02'), DataType::TYPE_STRING);

            $row++;
            $line++;
        }

        return $spreadsheet;
    }

    /**
     * Generate pre-filled Formato 607 using the official DGII template (Formato_DGII_607.xls).
     */
    public function generate607Excel(string $companyTaxId, string $period, array $records): Spreadsheet
    {
        $templatePath = resource_path('templates/dgii/Formato_DGII_607.xls');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template Formato 607 no encontrado en: {$templatePath}");
        }

        $recordCount = count($records);
        $maxRow = max(25, 12 + $recordCount);

        $reader = IOFactory::createReader('Xls');
        $reader->setReadFilter(new DgiiChunkFilter($maxRow));
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B3', 'Gridbase Bills');
        $spreadsheet->getProperties()
            ->setCreator('Gridbase Bills')
            ->setLastModifiedBy('Gridbase Bills')
            ->setTitle("DGII 607 {$companyTaxId} {$period}")
            ->setCompany('Gridbase');

        // 1. Fill Header Metadata (Rows 4 - 7)
        $sheet->setCellValueExplicit('C4', $companyTaxId, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B4', $companyTaxId, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C5', $period, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B5', $period, DataType::TYPE_STRING);
        $sheet->setCellValue('C6', $recordCount);
        $sheet->setCellValue('G7', 0);

        // 2. Fill Detail Rows (Starting at Row 12)
        $totalMonto = 0.0;
        $row = 12;
        $line = 1;
        foreach ($records as $r) {
            $r = (array) $r;
            $monto = (float) ($r['monto_facturado'] ?? 0);
            $totalMonto += $monto;

            $sheet->setCellValue("A{$row}", $line);
            $sheet->setCellValueExplicit("B{$row}", (string) ($r['rnc_cliente'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$row}", (string) ($r['tipo_identificacion'] ?? $r['tipo_id'] ?? '1'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$row}", (string) ($r['ncf'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E{$row}", (string) ($r['ncf_modificado'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F{$row}", (string) ($r['tipo_ingreso'] ?? '01'), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("G{$row}", (string) ($r['fecha_comprobante'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("H{$row}", (string) ($r['fecha_pago'] ?? ''), DataType::TYPE_STRING);

            $sheet->setCellValue("I{$row}", $monto);
            $sheet->setCellValue("J{$row}", (float) ($r['itbis_facturado'] ?? 0));
            $sheet->setCellValue("K{$row}", (float) ($r['itbis_retenido'] ?? 0));

            if (!empty($r['itbis_percibido']) && (float) $r['itbis_percibido'] > 0) {
                $sheet->setCellValue("L{$row}", (float) $r['itbis_percibido']);
            } else {
                $sheet->setCellValue("L{$row}", '');
            }

            $sheet->setCellValue("M{$row}", (float) ($r['retencion_isr'] ?? 0));

            if (!empty($r['isr_percibido']) && (float) $r['isr_percibido'] > 0) {
                $sheet->setCellValue("N{$row}", (float) $r['isr_percibido']);
            } else {
                $sheet->setCellValue("N{$row}", '');
            }

            $sheet->setCellValue("O{$row}", (float) ($r['isc'] ?? 0));
            $sheet->setCellValue("P{$row}", (float) ($r['otros_impuestos'] ?? 0));
            $sheet->setCellValue("Q{$row}", (float) ($r['propina_legal'] ?? 0));

            // Formas de Pago (17 a 23)
            $sheet->setCellValue("R{$row}", (float) ($r['efectivo'] ?? 0));
            $sheet->setCellValue("S{$row}", (float) ($r['bancos'] ?? 0));
            $sheet->setCellValue("T{$row}", (float) ($r['tarjeta'] ?? 0));
            $sheet->setCellValue("U{$row}", (float) ($r['credito'] ?? 0));
            $sheet->setCellValue("V{$row}", (float) ($r['bonos'] ?? 0));
            $sheet->setCellValue("W{$row}", (float) ($r['permuta'] ?? 0));
            $sheet->setCellValue("X{$row}", (float) ($r['otras_formas'] ?? 0));

            $row++;
            $line++;
        }

        $sheet->setCellValue('E7', $totalMonto);

        return $spreadsheet;
    }

    /**
     * Generate pre-filled Formato 608 using the official DGII template (Formato_DGII_608.xls).
     */
    public function generate608Excel(string $companyTaxId, string $period, array $records): Spreadsheet
    {
        $templatePath = resource_path('templates/dgii/Formato_DGII_608.xls');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Template Formato 608 no encontrado en: {$templatePath}");
        }

        $recordCount = count($records);
        $maxRow = max(25, 12 + $recordCount);

        $reader = IOFactory::createReader('Xls');
        $reader->setReadFilter(new DgiiChunkFilter($maxRow));
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B3', 'Gridbase Bills');
        $spreadsheet->getProperties()
            ->setCreator('Gridbase Bills')
            ->setLastModifiedBy('Gridbase Bills')
            ->setTitle("DGII 608 {$companyTaxId} {$period}")
            ->setCompany('Gridbase');

        // 1. Fill Header Metadata (Rows 5 - 7)
        $sheet->setCellValueExplicit('C5', $companyTaxId, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B5', $companyTaxId, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C6', $period, DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B6', $period, DataType::TYPE_STRING);
        $sheet->setCellValue('C7', $recordCount);
        $sheet->setCellValue('E7', 0);

        // 2. Fill Detail Rows (Starting at Row 12)
        $row = 12;
        $line = 1;
        foreach ($records as $r) {
            $r = (array) $r;
            $sheet->setCellValue("A{$row}", $line);
            $sheet->setCellValueExplicit("B{$row}", (string) ($r['ncf'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$row}", (string) ($r['fecha_comprobante'] ?? ''), DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E{$row}", str_pad((string) ($r['tipo_anulacion'] ?? '05'), 2, '0', STR_PAD_LEFT), DataType::TYPE_STRING);

            $row++;
            $line++;
        }

        return $spreadsheet;
    }

    /**
     * Generate pre-filled official IT-1 and Anexo A Excel workbook (IT-1-2020.xls).
     */
    public function generateIt1Excel(array $data): Spreadsheet
    {
        $templatePath = resource_path('templates/dgii/IT-1-2020.xls');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("La plantilla oficial IT-1-2020.xls no fue encontrada en: {$templatePath}");
        }

        $reader = new XlsReader();
        $spreadsheet = $reader->load($templatePath);

        // 1. Llenar Hoja: Anexo A
        $sA = $spreadsheet->getSheetByName('Anexo A');
        if ($sA) {
            $sA->setCellValue('D7', $data['tax_id'] ?? '');
            $sA->setCellValue('I7', $data['company_name'] ?? '');
            $sA->setCellValue('Q7', $data['commercial_name'] ?? ($data['company_name'] ?? ''));
            $sA->setCellValue('D10', $data['email'] ?? '');
            $sA->setCellValue('M10', $data['phone'] ?? '');
            $sA->setCellValue('Q10', $data['period_formatted'] ?? '');
            $sA->setCellValue('T10', $data['deadline'] ?? '');
            $sA->setCellValue('W10', 'Normal');

            $ncfCounts = $data['anexo_a']['ncf_counts'] ?? [];
            $ncfAmounts = $data['anexo_a']['ncf_amounts'] ?? [];

            $ncfRowMap = [
                '01_31' => 15,
                '02_32' => 16,
                '03_33' => 17,
                '04_34' => 18,
                '12'    => 19,
                '14_44' => 20,
                '15_45' => 21,
                '16_46' => 22,
            ];
            foreach ($ncfRowMap as $key => $rNum) {
                if (!empty($ncfCounts[$key])) {
                    $sA->setCellValue("T{$rNum}", $ncfCounts[$key]);
                    $sA->setCellValue("W{$rNum}", $ncfAmounts[$key] ?? 0);
                }
            }

            $fp = $data['anexo_a']['formas_pago'] ?? [];
            if (($fp['efectivo'] ?? 0) > 0) $sA->setCellValue('W28', $fp['efectivo']);
            if (($fp['cheque_transferencia'] ?? 0) > 0) $sA->setCellValue('W29', $fp['cheque_transferencia']);
            if (($fp['tarjeta'] ?? 0) > 0) $sA->setCellValue('W30', $fp['tarjeta']);
            if (($fp['credito'] ?? 0) > 0) $sA->setCellValue('W31', $fp['credito']);
            if (($fp['bonos'] ?? 0) > 0) $sA->setCellValue('W32', $fp['bonos']);
            if (($fp['permuta'] ?? 0) > 0) $sA->setCellValue('W33', $fp['permuta']);
            if (($fp['otras'] ?? 0) > 0) $sA->setCellValue('W34', $fp['otras']);

            $ti = $data['anexo_a']['tipos_ingreso'] ?? [];
            if (($ti['01'] ?? 0) > 0) $sA->setCellValue('W38', $ti['01']);
            if (($ti['02'] ?? 0) > 0) $sA->setCellValue('W39', $ti['02']);
            if (($ti['03'] ?? 0) > 0) $sA->setCellValue('W40', $ti['03']);
            if (($ti['04'] ?? 0) > 0) $sA->setCellValue('W41', $ti['04']);
            if (($ti['05'] ?? 0) > 0) $sA->setCellValue('W42', $ti['05']);
            if (($ti['06'] ?? 0) > 0) $sA->setCellValue('W43', $ti['06']);

            if (!empty($data['anexo_a']['retenciones']) && $data['anexo_a']['retenciones'] > 0) {
                $sA->setCellValue('W47', $data['anexo_a']['retenciones']);
            }

            $itbisPagado = $data['anexo_a']['itbis_pagado'] ?? [];
            if (($itbisPagado['bienes'] ?? 0) > 0) {
                $sA->setCellValue('O80', $itbisPagado['bienes']);
            }
            if (($itbisPagado['servicios'] ?? 0) > 0) {
                $sA->setCellValue('P81', $itbisPagado['servicios']);
            }
        }

        // 2. Llenar Hoja: IT-1
        $sIT = $spreadsheet->getSheetByName('IT-1');
        if ($sIT) {
            $sIT->setCellValue('D7', $data['tax_id'] ?? '');
            $sIT->setCellValue('I7', $data['company_name'] ?? '');
            $sIT->setCellValue('P7', $data['commercial_name'] ?? ($data['company_name'] ?? ''));
            $sIT->setCellValue('D10', $data['email'] ?? '');
            $sIT->setCellValue('M10', $data['phone'] ?? '');
            $sIT->setCellValue('P10', $data['period_formatted'] ?? '');
            $sIT->setCellValue('S10', $data['deadline'] ?? '');
            $sIT->setCellValue('V10', 'Normal');

            $sIT->setCellValue('V26', $data['it1']['casilla_11_gravadas_18'] ?? 0);
        }

        return $spreadsheet;
    }

    /**
     * Generate pre-filled official IR-2 Excel workbook (IR-2-2026.xls).
     */
    public function generateIr2Excel(array $data): Spreadsheet
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(300);

        $templatePath = resource_path('templates/dgii/IR-2-2026.xls');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("La plantilla oficial IR-2-2026.xls no fue encontrada en: {$templatePath}");
        }

        $reader = new XlsReader();
        $spreadsheet = $reader->load($templatePath);

        $defSector = $spreadsheet->getDefinedName('SECTOR_ECONOMICO');
        if ($defSector) {
            $defSector->setValue('$AM$1');
        }

        $yearInt = (int) ($data['year'] ?? date('Y'));
        $taxId = (string) ($data['tax_id'] ?? '');
        $companyName = (string) ($data['company_name'] ?? '');
        $commercialName = (string) ($data['commercial_name'] ?? $companyName);
        $phone = (string) ($data['phone'] ?? '');
        $email = (string) ($data['email'] ?? '');

        // Hoja Principal: IR-2
        $sIR = $spreadsheet->getSheetByName('IR-2');
        if ($sIR) {
            $sIR->setCellValue('Z8', $yearInt);
            $sIR->setCellValue('AA8', $yearInt);
            $sIR->setCellValue('F13', 'NORMAL');
            $sIR->setCellValue('S13', 'NO');
            $sIR->setCellValue('D16', $taxId);
            $sIR->setCellValue('E16', $taxId);
            $sIR->setCellValue('L16', $companyName);
            $sIR->setCellValue('M16', $companyName);
            $sIR->setCellValue('F19', $commercialName);
            $sIR->setCellValue('H19', $commercialName);
            $sIR->setCellValue('F22', $phone);
            $sIR->setCellValue('I22', $phone);
            $sIR->setCellValue('Q22', $email);
            $sIR->setCellValue('F25', "01/01/{$yearInt}");
            $sIR->setCellValue('I25', "01/01/{$yearInt}");
            $sIR->setCellValue('S25', "01/01/{$yearInt}");
            $sIR->setCellValue('W25', "31/12/{$yearInt}");

            if (($data['ir2']['casilla_14_retenciones_estado'] ?? 0) > 0) {
                $sIR->setCellValue('AB46', $data['ir2']['casilla_14_retenciones_estado']);
            }
        }

        // Hoja: B-1
        $sB1 = $spreadsheet->getSheetByName('B-1');
        if ($sB1) {
            $sB1->setCellValue('K7', $yearInt);
            $sB1->setCellValue('D12', $taxId);
            $sB1->setCellValue('E12', $taxId);
            $sB1->setCellValue('G12', $companyName);

            $b1 = $data['b1'] ?? [];
            if (($b1['ventas_locales'] ?? 0) > 0) $sB1->setCellValue('I17', $b1['ventas_locales']);
            if (($b1['exportaciones'] ?? 0) > 0) $sB1->setCellValue('I18', $b1['exportaciones']);
            if (($b1['devoluciones_ventas'] ?? 0) > 0) $sB1->setCellValue('I19', $b1['devoluciones_ventas']);

            if (($b1['costo_venta'] ?? 0) > 0) $sB1->setCellValue('I37', $b1['costo_venta']);
            if (($b1['gastos_personal'] ?? 0) > 0) $sB1->setCellValue('I39', $b1['gastos_personal']);
            if (($b1['honorarios_fisicas'] ?? 0) > 0) $sB1->setCellValue('I47', $b1['honorarios_fisicas']);
            if (($b1['honorarios_morales'] ?? 0) > 0) $sB1->setCellValue('I48', $b1['honorarios_morales']);
            if (($b1['otros_servicios'] ?? 0) > 0) $sB1->setCellValue('I53', $b1['otros_servicios']);
            if (($b1['arrendamientos_fisicas'] ?? 0) > 0) $sB1->setCellValue('I56', $b1['arrendamientos_fisicas']);
            if (($b1['arrendamientos_morales'] ?? 0) > 0) $sB1->setCellValue('I57', $b1['arrendamientos_morales']);
            if (($b1['otros_arrendamientos'] ?? 0) > 0) $sB1->setCellValue('I58', $b1['otros_arrendamientos']);
            if (($b1['gastos_activos_fijos'] ?? 0) > 0) $sB1->setCellValue('I66', $b1['gastos_activos_fijos']);
            if (($b1['relaciones_publicas'] ?? 0) > 0) $sB1->setCellValue('I71', $b1['relaciones_publicas']);
            if (($b1['publicidad'] ?? 0) > 0) $sB1->setCellValue('I72', $b1['publicidad']);
            if (($b1['seguros'] ?? 0) > 0) $sB1->setCellValue('I80', $b1['seguros']);
            if (($b1['otras_deducciones'] ?? 0) > 0) $sB1->setCellValue('I81', $b1['otras_deducciones']);
            if (($b1['retencion_cheques'] ?? 0) > 0) $sB1->setCellValue('I91', $b1['retencion_cheques']);
            if (($b1['otros_financieros'] ?? 0) > 0) $sB1->setCellValue('I93', $b1['otros_financieros']);
            if (($b1['gastos_extraordinarios'] ?? 0) > 0) $sB1->setCellValue('I100', $b1['gastos_extraordinarios']);
        }

        // Hoja: A-1
        $sA1 = $spreadsheet->getSheetByName('A-1');
        if ($sA1) {
            $sA1->setCellValue('M8', $yearInt);
            $sA1->setCellValue('D12', $taxId);
            $sA1->setCellValue('E12', $taxId);
            $sA1->setCellValue('G12', $companyName);

            $a1 = $data['a1'] ?? [];
            if (($a1['caja_bancos'] ?? 0) > 0) $sA1->setCellValue('I17', $a1['caja_bancos']);
            if (($a1['cuentas_por_cobrar'] ?? 0) > 0) $sA1->setCellValue('I18', $a1['cuentas_por_cobrar']);
            if (($a1['cuentas_por_pagar'] ?? 0) > 0) $sA1->setCellValue('I57', $a1['cuentas_por_pagar']);
            if (($a1['impuestos_por_pagar'] ?? 0) > 0) $sA1->setCellValue('I58', $a1['impuestos_por_pagar']);
            if (($a1['capital_social'] ?? 0) > 0) $sA1->setCellValue('I73', $a1['capital_social']);
            if (($a1['reserva_legal'] ?? 0) > 0) $sA1->setCellValue('I74', $a1['reserva_legal']);
            if (($a1['beneficios_anteriores'] ?? 0) != 0) $sA1->setCellValue('I76', $a1['beneficios_anteriores']);
            if (($a1['beneficio_ejercicio'] ?? 0) != 0) $sA1->setCellValue('I77', $a1['beneficio_ejercicio']);
        }

        // Hoja: J
        $sJ = $spreadsheet->getSheetByName('J');
        if ($sJ) {
            $sJ->setCellValue('L8', $yearInt);
            $sJ->setCellValue('D13', $taxId);
            $sJ->setCellValue('E13', $taxId);
            $sJ->setCellValue('G13', $companyName);

            $jV = $data['anexo_j']['ventas'] ?? ['counts' => [], 'amounts' => []];
            $jVMap = [
                '01_31' => 18, '02_32' => 19, '03_33' => 20, '04_34' => 21,
                '14_44' => 23, '15_45' => 24, '16_46' => 25,
            ];
            foreach ($jVMap as $k => $rNum) {
                if (!empty($jV['counts'][$k])) {
                    $sJ->setCellValue("I{$rNum}", $jV['counts'][$k]);
                    $sJ->setCellValue("J{$rNum}", $jV['amounts'][$k] ?? 0);
                }
            }

            $jG = $data['anexo_j']['gastos'] ?? ['counts' => [], 'amounts' => []];
            $jGMap = [
                '01_31' => 28, '03_33' => 29, '04_34' => 30, '15_45' => 31,
                '14_44' => 32, '11_41' => 34, '13_43' => 35,
            ];
            foreach ($jGMap as $k => $rNum) {
                if (!empty($jG['counts'][$k])) {
                    $sJ->setCellValue("I{$rNum}", $jG['counts'][$k]);
                    $sJ->setCellValue("J{$rNum}", $jG['amounts'][$k] ?? 0);
                }
            }
        }

        // Hoja: Activo
        $sAct = $spreadsheet->getSheetByName('Activo');
        if ($sAct) {
            $sAct->setCellValue('Z5', $yearInt);
            $sAct->setCellValue('E9', 'NORMAL');
            $sAct->setCellValue('C11', $taxId);
            $sAct->setCellValue('L11', $companyName);
            $sAct->setCellValue('E13', $commercialName);
            $sAct->setCellValue('E15', $phone);
            $sAct->setCellValue('O15', $email);
            $sAct->setCellValue('E17', "01/01/{$yearInt}");
            $sAct->setCellValue('Q17', "01/01/{$yearInt}");
            $sAct->setCellValue('U17', "31/12/{$yearInt}");
        }

        // Anexos adicionales (E, D, D-1, D-2, G, H-1, A-2, A-3, B-2, B-3, B-4)
        $extraSheets = [
            'E'   => ['year' => 'Z6', 'tax_id' => 'E11', 'name' => 'L11'],
            'D'   => ['year' => 'P5', 'tax_id' => 'C10', 'name' => 'I10'],
            'D-1' => ['year' => 'W5', 'tax_id' => 'F9',  'name' => 'O9'],
            'D-2' => ['year' => 'N5', 'tax_id' => 'H8',  'name' => 'L8'],
            'G'   => ['year' => 'M5', 'tax_id' => 'D10', 'name' => 'G10'],
            'H-1' => ['year' => 'K9', 'tax_id' => 'C9',  'name' => 'H9'],
            'A-2' => ['year' => 'P6', 'tax_id' => 'D10', 'name' => 'I10'],
            'A-3' => ['year' => 'O7', 'tax_id' => 'D12', 'name' => 'I12'],
            'B-2' => ['year' => 'N5', 'tax_id' => 'C10', 'name' => 'I10'],
            'B-3' => ['year' => 'N5', 'tax_id' => 'C9',  'name' => 'I9'],
            'B-4' => ['year' => 'N6', 'tax_id' => 'C10', 'name' => 'J10'],
        ];
        foreach ($extraSheets as $sheetName => $coords) {
            $sSec = $spreadsheet->getSheetByName($sheetName);
            if ($sSec) {
                $sSec->setCellValue($coords['year'], $yearInt);
                $sSec->setCellValue($coords['tax_id'], $taxId);
                $sSec->setCellValue($coords['name'], $companyName);
            }
        }

        return $spreadsheet;
    }

    /**
     * Generate pre-filled official DSS-07 Excel workbook (DSS-07.xls).
     */
    public function generateDssExcel(array $data, string $year, string $month): Spreadsheet
    {
        $templatePath = resource_path('templates/dgii/DSS-07.xls');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("La plantilla oficial DSS-07.xls no fue encontrada en: {$templatePath}");
        }

        $reader = new XlsReader();
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getSheetByName('DSS') ?: $spreadsheet->getActiveSheet();

        $sheet->setCellValue('D9', str_pad($month, 2, '0', STR_PAD_LEFT));
        $sheet->setCellValue('E9', $year);
        $sheet->setCellValue('Q9', $data['deadline'] ?? '');
        $sheet->setCellValue('I11', 'X');
        $sheet->setCellValue('E13', $data['tax_id'] ?? '');
        $sheet->setCellValue('S13', $data['company_name'] ?? '');
        $sheet->setCellValue('G15', $data['commercial_name'] ?? ($data['company_name'] ?? ''));
        $sheet->setCellValue('AB15', $data['phone'] ?? '');
        $sheet->setCellValue('W17', $data['email'] ?? '');

        $totalOp = (float) ($data['dss']['casilla_12_total_operaciones'] ?? 0);
        if ($totalOp > 0) {
            $sheet->setCellValue('Y31', 1);
            $sheet->setCellValue('AB31', $totalOp);
        }

        return $spreadsheet;
    }

    /**
     * Generate pre-filled official DAF Excel workbook (DAF.xls).
     */
    public function generateDafExcel(array $data): Spreadsheet
    {
        $templatePath = resource_path('templates/dgii/DAF.xls');
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("La plantilla oficial DAF.xls no fue encontrada en: {$templatePath}");
        }

        $reader = new XlsReader();
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getSheetByName('DAF') ?: $spreadsheet->getActiveSheet();

        $year = (string) ($data['year'] ?? date('Y'));
        $sheet->setCellValue('AA4', (int) $year);
        $sheet->setCellValue('H7', 'X');
        $sheet->setCellValue('E8', $data['tax_id'] ?? '');
        $sheet->setCellValue('N8', $data['company_name'] ?? '');
        $sheet->setCellValue('H9', $data['commercial_name'] ?? ($data['company_name'] ?? ''));
        $sheet->setCellValue('I10', $data['phone'] ?? '');
        $sheet->setCellValue('U10', $data['email'] ?? '');

        $sheet->setCellValue('G12', '01');
        $sheet->setCellValue('I12', '01');
        $sheet->setCellValue('J12', $year);

        $sheet->setCellValue('P12', '01');
        $sheet->setCellValue('Q12', '01');
        $sheet->setCellValue('R12', $year);
        $sheet->setCellValue('U12', '31');
        $sheet->setCellValue('W12', '12');
        $sheet->setCellValue('Y12', $year);

        $daf = $data['daf'] ?? [];
        $sheet->setCellValue('AC13', $daf['casilla_1_activos_financieros'] ?? 0);
        $sheet->setCellValue('AC17', $daf['casilla_5_renta_neta_imponible'] ?? 0);

        return $spreadsheet;
    }

    /**
     * Generate pre-filled official RS1, RS2, RS3, or RS4 Excel workbook (.xlsx).
     */
    public function generateRstExcel(string $type, array $data): Spreadsheet
    {
        $upper = strtoupper($type);
        $templatePath = resource_path("templates/dgii/{$upper}-2021.xlsx");
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("La plantilla oficial {$upper}-2021.xlsx no fue encontrada en: {$templatePath}");
        }

        $reader = new XlsxReader();
        $spreadsheet = $reader->load($templatePath);
        $sheet = $spreadsheet->getSheetByName($upper) ?: $spreadsheet->getActiveSheet();

        $year = (string) ($data['year'] ?? date('Y'));
        $sheet->setCellValue('U7', "{$year}12");
        $sheet->setCellValue('F11', $data['tax_id'] ?? '');
        $sheet->setCellValue('P11', $data['company_name'] ?? '');
        $sheet->setCellValue('F13', $data['phone'] ?? '');
        $sheet->setCellValue('P13', $data['email'] ?? '');

        if (in_array($upper, ['RS1', 'RS2'], true)) {
            $sheet->setCellValue('F15', 'NORMAL');
            $sheet->setCellValue('P16', '28');
            $sheet->setCellValue('Q16', '02');
            $sheet->setCellValue('R16', (string) ((int) $year + 1));

            $sub = $data[strtolower($upper)] ?? [];
            $sheet->setCellValue('T19', $sub['casilla_1_ventas'] ?? 0);
            $sheet->setCellValue('T20', $sub['casilla_2_servicios'] ?? 0);
            $sheet->setCellValue('T21', $sub['casilla_3_alquileres'] ?? 0);
            if ($upper === 'RS1') {
                $sheet->setCellValue('T22', $sub['casilla_4_honorarios'] ?? 0);
            } elseif (!$sheet->getCell('L39')->getValue()) {
                $sheet->setCellValue('L39', 0.07);
            }
        } elseif ($upper === 'RS3') {
            $sheet->setCellValue('F15', 'Jurídica');
            $sheet->setCellValue('P15', 'SERVICIOS EDUCATIVOS');
            $sheet->setCellValue('F17', 'NORMAL');
            $sheet->setCellValue('P18', '28');
            $sheet->setCellValue('Q18', '02');
            $sheet->setCellValue('R18', (string) ((int) $year + 1));
            $sheet->setCellValue('T21', $data['rs3']['casilla_1_compras'] ?? 0);
        } elseif ($upper === 'RS4') {
            $sheet->setCellValue('F15', 'Jurídica');
            $sheet->setCellValue('F17', 'NORMAL');
            $sheet->setCellValue('P18', '28');
            $sheet->setCellValue('Q18', '02');
            $sheet->setCellValue('R18', (string) ((int) $year + 1));
            $sheet->setCellValue('T21', $data['rs4']['casilla_1_ingresos_agropecuarios'] ?? 0);
        }

        return $spreadsheet;
    }
}
