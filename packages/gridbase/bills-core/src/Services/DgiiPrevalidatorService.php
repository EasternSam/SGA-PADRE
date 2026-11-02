<?php

namespace Gridbase\BillsCore\Services;

use Illuminate\Support\Collection;

/**
 * DgiiPrevalidatorService — Gridbase Bills Core Engine
 *
 * Pre-validates 606, 607, and 608 DGII report data before exporting or submitting.
 * Implements Dominican RNC (Modulo 11) and Cedula (Modulo 10 / Luhn) check-digit algorithms.
 */
class DgiiPrevalidatorService
{
    public const EXPENSE_TYPES = [
        '01' => 'Gastos de Personal',
        '02' => 'Gastos por Trabajos, Suministros y Servicios',
        '03' => 'Arrendamientos',
        '04' => 'Gastos de Activos Fijos',
        '05' => 'Gastos de Representación',
        '06' => 'Otras Deducciones Admitidas',
        '07' => 'Gastos Financieros',
        '08' => 'Gastos Extraordinarios',
        '09' => 'Compras y Gastos que forman parte del Costo de Venta',
        '10' => 'Adquisiciones de Activos',
        '11' => 'Gastos de Seguros',
    ];

    public const INCOME_TYPES = [
        '01' => 'Ingresos por Operaciones (No Financieros)',
        '02' => 'Ingresos Financieros',
        '03' => 'Ingresos Extraordinarios',
        '04' => 'Ingresos por Arrendamientos',
        '05' => 'Ingresos por Venta de Activo Depreciable',
        '06' => 'Otros Ingresos',
    ];

    public const PAYMENT_METHODS_606 = [
        '01' => 'Efectivo',
        '02' => 'Cheques/Transferencias/Depósito',
        '03' => 'Tarjeta Crédito/Débito',
        '04' => 'Compra a Crédito',
        '05' => 'Permuta',
        '06' => 'Nota de Crédito',
        '07' => 'Mixto',
    ];

    public const VOID_TYPES_608 = [
        '01' => 'Deterioro de Factura Pre-impresa',
        '02' => 'Errores de Impresión (Factura Pre-impresa)',
        '03' => 'Impresión Defectuosa',
        '04' => 'Corrección de la Información',
        '05' => 'Cambio de Productos',
        '06' => 'Devolución de Productos',
        '07' => 'Omisión de Productos',
        '08' => 'Errores en Secuencia de NCF',
        '09' => 'Por Cese de Operaciones',
        '10' => 'Pérdida o Hurto de Talonarios',
    ];

    /**
     * Validate a collection or array of report lines.
     *
     * @param string $type '606', '607', or '608'
     * @param iterable $data
     * @return array{isValid: bool, errorsCount: int, warningsCount: int, totalLines: int, issues: array}
     */
    public function validateData(string $type, iterable $data): array
    {
        $rows = $data instanceof Collection ? $data->values()->all() : array_values((array) $data);
        $errors = [];
        $warnings = [];
        $lineNumber = 1;

        foreach ($rows as $row) {
            $row = (array) $row;
            $lineErrors = [];
            $lineWarnings = [];

            if ($type === '606') {
                $this->validate606Row($row, $lineErrors, $lineWarnings);
            } elseif ($type === '607') {
                $this->validate607Row($row, $lineErrors, $lineWarnings);
            } elseif ($type === '608') {
                $this->validate608Row($row, $lineErrors, $lineWarnings);
            }

            if (!empty($lineErrors) || !empty($lineWarnings)) {
                $errors[] = [
                    'line' => $lineNumber,
                    'ncf' => $row['ncf'] ?? 'N/A',
                    'identifier' => $row['rnc_proveedor'] ?? $row['rnc_cliente'] ?? 'N/A',
                    'errors' => $lineErrors,
                    'warnings' => $lineWarnings,
                    'raw_data' => $row,
                ];
            }

            $lineNumber++;
        }

        $totalErrors = 0;
        $totalWarnings = 0;
        foreach ($errors as $item) {
            $totalErrors += count($item['errors']);
            $totalWarnings += count($item['warnings']);
        }

        return [
            'isValid' => $totalErrors === 0,
            'errorsCount' => $totalErrors,
            'warningsCount' => $totalWarnings,
            'totalLines' => count($rows),
            'issues' => $errors,
        ];
    }

    protected function validate606Row(array $row, array &$errors, array &$warnings): void
    {
        $rnc = trim((string) ($row['rnc_proveedor'] ?? ''));
        $tipoId = (string) ($row['tipo_id'] ?? $row['tipo_identificacion'] ?? '');

        if ($rnc === '') {
            $errors[] = 'El RNC o Cédula del proveedor está vacío.';
        } else {
            if ($tipoId === '1' && strlen($rnc) !== 9) {
                $errors[] = "Tipo ID es 1 (RNC), pero '{$rnc}' no tiene 9 dígitos.";
            } elseif ($tipoId === '2' && strlen($rnc) !== 11) {
                $errors[] = "Tipo ID es 2 (Cédula), pero '{$rnc}' no tiene 11 dígitos.";
            } elseif (!in_array($tipoId, ['1', '2'], true)) {
                $errors[] = "El Tipo de Identificación '{$tipoId}' es inválido (debe ser 1 o 2).";
            }

            if (!$this->isValidDominicanId($rnc)) {
                $warnings[] = "El RNC/Cédula '{$rnc}' no supera el algoritmo verificador Módulo 11/10 de la DGII/JCE.";
            }
        }

        $tipoBienes = (string) ($row['tipo_bienes'] ?? '');
        if (!array_key_exists($tipoBienes, self::EXPENSE_TYPES)) {
            $errors[] = "El Tipo de Bienes y Servicios '{$tipoBienes}' no existe en la tabla DGII (01 al 11).";
        }

        $ncf = trim((string) ($row['ncf'] ?? ''));
        if (!$this->isValidNcfFormat($ncf, '606')) {
            $errors[] = "El NCF '{$ncf}' no tiene una estructura válida para compras (Serie B o E).";
        }

        $ncfMod = trim((string) ($row['ncf_modificado'] ?? ''));
        $isCreditOrDebitNote = str_starts_with($ncf, 'B03')
            || str_starts_with($ncf, 'B04')
            || str_starts_with($ncf, 'E33')
            || str_starts_with($ncf, 'E34');

        if ($isCreditOrDebitNote && $ncfMod === '') {
            $errors[] = "El comprobante '{$ncf}' es Nota de Crédito/Débito y exige un NCF Modificado.";
        }

        $montoServicios = (float) ($row['monto_servicios'] ?? 0);
        $montoBienes = (float) ($row['monto_bienes'] ?? 0);
        $totalFacturado = (float) ($row['total_monto_facturado'] ?? ($montoServicios + $montoBienes));
        $itbisFacturado = (float) ($row['itbis_facturado'] ?? 0);

        if (abs(($montoServicios + $montoBienes) - $totalFacturado) > 0.05) {
            $errors[] = "La suma de Servicios ({$montoServicios}) y Bienes ({$montoBienes}) no cuadra con el Total Facturado ({$totalFacturado}).";
        }

        if ($itbisFacturado > ($totalFacturado * 0.18) + 1.00) {
            $errors[] = "El ITBIS Facturado ({$itbisFacturado}) supera el 18% del Monto Facturado ({$totalFacturado}).";
        }

        $formaPago = (string) ($row['forma_pago'] ?? '02');
        if (!array_key_exists($formaPago, self::PAYMENT_METHODS_606)) {
            $errors[] = "La Forma de Pago '{$formaPago}' es inválida (debe ser 01 al 07).";
        }
    }

    protected function validate607Row(array $row, array &$errors, array &$warnings): void
    {
        $rnc = trim((string) ($row['rnc_cliente'] ?? ''));
        $tipoId = (string) ($row['tipo_id'] ?? $row['tipo_identificacion'] ?? '');
        $ncf = trim((string) ($row['ncf'] ?? ''));
        $montoFacturado = (float) ($row['monto_facturado'] ?? 0);

        $isConsumo = str_starts_with($ncf, 'B02') || str_starts_with($ncf, 'E32');

        if ($rnc === '') {
            if (!$isConsumo) {
                $errors[] = "El NCF '{$ncf}' requiere obligatoriamente el RNC/Cédula del comprador.";
            } elseif ($montoFacturado >= 250000) {
                $errors[] = "Las facturas de consumo ('{$ncf}') por RD$ 250,000.00 o más requieren identificar al comprador.";
            }
        } else {
            if ($tipoId === '1' && strlen($rnc) !== 9) {
                $errors[] = "Tipo ID es 1 (RNC), pero '{$rnc}' no tiene 9 dígitos.";
            } elseif ($tipoId === '2' && strlen($rnc) !== 11) {
                $errors[] = "Tipo ID es 2 (Cédula), pero '{$rnc}' no tiene 11 dígitos.";
            } elseif ($tipoId === '3' && strlen($rnc) < 5) {
                $errors[] = "Tipo ID es 3 (Pasaporte), pero '{$rnc}' es demasiado corto.";
            }

            if (in_array($tipoId, ['1', '2'], true) && !$this->isValidDominicanId($rnc)) {
                $warnings[] = "El RNC/Cédula '{$rnc}' parece no superar el algoritmo verificador Módulo 11/10.";
            }
        }

        if (!$this->isValidNcfFormat($ncf, '607')) {
            $errors[] = "El NCF '{$ncf}' no tiene estructura válida para ventas 607.";
        }

        $ncfMod = trim((string) ($row['ncf_modificado'] ?? ''));
        $isCreditOrDebitNote = str_starts_with($ncf, 'B03')
            || str_starts_with($ncf, 'B04')
            || str_starts_with($ncf, 'E33')
            || str_starts_with($ncf, 'E34');

        if ($isCreditOrDebitNote && $ncfMod === '') {
            $errors[] = "El comprobante '{$ncf}' es Nota de Crédito/Débito y exige un NCF Modificado.";
        }

        $tipoIngreso = (string) ($row['tipo_ingreso'] ?? '01');
        if (!array_key_exists($tipoIngreso, self::INCOME_TYPES)) {
            $errors[] = "El Tipo de Ingreso '{$tipoIngreso}' no es válido (01 al 06).";
        }

        $itbisFacturado = (float) ($row['itbis_facturado'] ?? 0);
        if ($itbisFacturado > ($montoFacturado * 0.18) + 1.00) {
            $errors[] = "El ITBIS Facturado ({$itbisFacturado}) excede el 18% del Monto Facturado ({$montoFacturado}).";
        }
    }

    protected function validate608Row(array $row, array &$errors, array &$warnings): void
    {
        $ncf = trim((string) ($row['ncf'] ?? ''));
        if (!$this->isValidNcfFormat($ncf, '608')) {
            $errors[] = "El NCF '{$ncf}' no tiene una estructura válida para anulación.";
        }

        $fecha = trim((string) ($row['fecha_comprobante'] ?? ''));
        if (strlen($fecha) !== 8 || !ctype_digit($fecha)) {
            $errors[] = "La Fecha de Comprobante '{$fecha}' no cumple con el formato AAAAMMDD.";
        }

        $tipoAnulacion = str_pad((string) ($row['tipo_anulacion'] ?? ''), 2, '0', STR_PAD_LEFT);
        if (!array_key_exists($tipoAnulacion, self::VOID_TYPES_608)) {
            $errors[] = "El Tipo de Anulación '{$tipoAnulacion}' no es válido (debe estar entre 01 y 10).";
        }
    }

    /**
     * Validate NCF or e-CF structure according to DGII Norma 06-2018 & Norma 01-2020.
     */
    public function isValidNcfFormat(string $ncf, string $reportType = '607'): bool
    {
        $ncf = strtoupper(trim($ncf));

        // Serie B: B + 2 digits type + 8 digits sequence = 11 chars
        // Serie E: E + 2 digits type + 10 digits sequence = 13 chars
        if (str_starts_with($ncf, 'B')) {
            if (strlen($ncf) !== 11) {
                return false;
            }
            $validTypes = $reportType === '606'
                ? ['01', '03', '04', '11', '12', '13', '14', '15']
                : ['01', '02', '03', '04', '12', '14', '15', '16'];
            if ($reportType === '608') {
                $validTypes = ['01', '02', '03', '04', '11', '12', '13', '14', '15', '16', '17'];
            }
            $type = substr($ncf, 1, 2);
            $seq = substr($ncf, 3);
            return in_array($type, $validTypes, true) && ctype_digit($seq);
        }

        if (str_starts_with($ncf, 'E')) {
            if (strlen($ncf) !== 13) {
                return false;
            }
            $validTypes = $reportType === '606'
                ? ['31', '33', '34', '41', '43', '44', '45']
                : ['31', '32', '33', '34', '44', '45', '46'];
            if ($reportType === '608') {
                $validTypes = ['31', '32', '33', '34', '41', '43', '44', '45', '46', '47'];
            }
            $type = substr($ncf, 1, 2);
            $seq = substr($ncf, 3);
            return in_array($type, $validTypes, true) && ctype_digit($seq);
        }

        return false;
    }

    /**
     * Validate Dominican RNC (9 digits, Modulo 11) or Cedula (11 digits, Modulo 10 / Luhn).
     */
    public function isValidDominicanId(string $id): bool
    {
        $id = preg_replace('/[^0-9]/', '', $id);

        if (strlen($id) === 9) {
            return $this->isValidRnc($id);
        }

        if (strlen($id) === 11) {
            return $this->isValidCedula($id);
        }

        return false;
    }

    public function isValidRnc(string $rnc): bool
    {
        if (strlen($rnc) !== 9 || !ctype_digit($rnc)) {
            return false;
        }

        $weights = [7, 9, 8, 6, 5, 4, 3, 2];
        $sum = 0;

        for ($i = 0; $i < 8; $i++) {
            $sum += ((int) $rnc[$i]) * $weights[$i];
        }

        $remainder = $sum % 11;
        if ($remainder === 0) {
            $checkDigit = 2;
        } elseif ($remainder === 1) {
            $checkDigit = 1;
        } else {
            $checkDigit = 11 - $remainder;
        }

        return ((int) $rnc[8]) === $checkDigit;
    }

    public function isValidCedula(string $cedula): bool
    {
        if (strlen($cedula) !== 11 || !ctype_digit($cedula)) {
            return false;
        }

        $weights = [1, 2, 1, 2, 1, 2, 1, 2, 1, 2];
        $sum = 0;

        for ($i = 0; $i < 10; $i++) {
            $prod = ((int) $cedula[$i]) * $weights[$i];
            if ($prod >= 10) {
                $prod = (int) floor($prod / 10) + ($prod % 10);
            }
            $sum += $prod;
        }

        $nextTen = (int) ceil($sum / 10) * 10;
        $checkDigit = ($nextTen - $sum) % 10;

        return ((int) $cedula[10]) === $checkDigit;
    }
}
