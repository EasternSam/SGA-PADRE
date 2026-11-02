<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Gridbase\BillsCore\Contracts\IssuerConfigInterface;
use Gridbase\BillsCore\Services\DgiiPrevalidatorService;
use Gridbase\BillsCore\Services\DgiiReportBuilderService;

/**
 * DgiiExportService — Powered by Gridbase Bills Core
 *
 * Construye los datasets 606, 607, 608 y Declaraciones Oficiales (IT-1, IR-2, RST, DSS-07, DAF)
 * de Academic+ y los procesa con DgiiReportBuilderService y DgiiPrevalidatorService.
 */
class DgiiExportService
{
    public function __construct(
        protected DgiiReportBuilderService $reportBuilder,
        protected DgiiPrevalidatorService $prevalidator,
        protected IssuerConfigInterface $issuer,
    ) {
    }

    /**
     * Obtiene los registros normalizados de Formato 606 para un período YYYY-MM o un rango.
     */
    public function getRecords606(string $yearMonth, ?Carbon $customStart = null, ?Carbon $customEnd = null): array
    {
        $startDate = $customStart ?: Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = $customEnd ?: Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        $expenses = Expense::with('supplier')
            ->whereNotNull('ncf')
            ->whereNotNull('supplier_id')
            ->where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'asc')
            ->get();

        $records = [];
        foreach ($expenses as $expense) {
            $rnc = preg_replace('/[^0-9]/', '', $expense->supplier->rnc_cedula ?? '');
            $tipoId = (strlen($rnc) === 9) ? '1' : '2';

            $subtotal = (float) $expense->subtotal;
            $itbisFacturado = (float) $expense->itbis_amount;
            $itbisRetenido = (float) $expense->itbis_retained;
            $itbisPorAdelantar = max(0.0, $itbisFacturado - $itbisRetenido);
            $isrRetenido = (float) $expense->isr_retained;
            $tipoBienes = str_pad((string) ($expense->expense_type_606 ?: '02'), 2, '0', STR_PAD_LEFT);
            $supplierName = $expense->supplier->name ?? 'Proveedor de Gasto';

            $records[] = [
                'id' => $expense->id,
                'supplier_name' => $supplierName,
                'proveedor_nombre' => $supplierName,
                'rnc_proveedor' => $rnc,
                'tipo_id' => $tipoId,
                'tipo_identificacion' => $tipoId,
                'tipo_bienes' => $tipoBienes,
                'tipo_bien_servicio' => $tipoBienes,
                'ncf' => trim((string) $expense->ncf),
                'ncf_modificado' => '',
                'fecha_comprobante' => $expense->expense_date ? $expense->expense_date->format('Ymd') : $startDate->format('Ymd'),
                'fecha_pago' => $expense->expense_date ? $expense->expense_date->format('Ymd') : $startDate->format('Ymd'),
                'monto_servicios' => $subtotal,
                'monto_bienes' => 0.00,
                'total_monto_facturado' => $subtotal,
                'total_facturado' => $subtotal,
                'itbis_facturado' => $itbisFacturado,
                'itbis_retenido' => $itbisRetenido,
                'itbis_proporcionalidad' => 0.00,
                'itbis_proporcional' => 0.00,
                'itbis_costo' => 0.00,
                'itbis_adelantar' => $itbisPorAdelantar,
                'itbis_percibido' => '',
                'tipo_retencion_isr' => $isrRetenido > 0 ? '08' : '',
                'monto_retencion_renta' => $isrRetenido,
                'isr_retenido' => $isrRetenido,
                'isr_percibido' => '',
                'impuesto_selectivo_consumo' => 0.00,
                'isc' => 0.00,
                'otros_impuestos' => 0.00,
                'monto_propina_legal' => 0.00,
                'propina_legal' => 0.00,
                'forma_pago' => '02',
            ];
        }

        return $records;
    }

    /**
     * Obtiene los registros normalizados de Formato 607 para un período YYYY-MM o un rango.
     */
    public function getRecords607(string $yearMonth, ?Carbon $customStart = null, ?Carbon $customEnd = null): array
    {
        $startDate = $customStart ?: Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = $customEnd ?: Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        $payments = Payment::with('student')
            ->whereIn('status', ['paid', 'Completado'])
            ->whereNotNull('ncf')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        $records = [];
        foreach ($payments as $payment) {
            $ncf = trim((string) $payment->ncf);
            $rncCliente = preg_replace('/[^0-9]/', '', (string) ($payment->rnc_client ?: ($payment->student->cedula ?? '')));
            $tipoId = $rncCliente === '' ? '3' : (strlen($rncCliente) === 9 ? '1' : '2');

            $monto = (float) $payment->amount;
            $gateway = strtolower((string) $payment->gateway);

            $efectivo = 0.0;
            $bancos = 0.0;
            $tarjeta = 0.0;

            if (in_array($gateway, ['cash', 'efectivo', 'manual'], true)) {
                $efectivo = $monto;
            } elseif (in_array($gateway, ['transfer', 'transferencia', 'bank', 'check', 'cheque'], true)) {
                $bancos = $monto;
            } else {
                $tarjeta = $monto;
            }

            $clientName = $payment->company_name ?: ($payment->student ? trim($payment->student->first_name . ' ' . $payment->student->last_name) : 'Cliente General');

            $records[] = [
                'id' => $payment->id,
                'client_name' => $clientName,
                'cliente_nombre' => $clientName,
                'rnc_cliente' => $rncCliente,
                'tipo_id' => $tipoId,
                'tipo_identificacion' => $tipoId,
                'ncf' => $ncf,
                'ncf_modificado' => '',
                'tipo_ingreso' => '01',
                'fecha_comprobante' => $payment->created_at ? $payment->created_at->format('Ymd') : $startDate->format('Ymd'),
                'fecha_retencion' => '',
                'fecha_pago' => $payment->paid_at ? Carbon::parse($payment->paid_at)->format('Ymd') : ($payment->created_at ? $payment->created_at->format('Ymd') : ''),
                'monto_facturado' => $monto,
                'itbis_facturado' => 0.00,
                'itbis_retenido' => 0.00,
                'itbis_percibido' => '',
                'retencion_isr' => 0.00,
                'isr_percibido' => '',
                'isc' => 0.00,
                'otros_impuestos' => 0.00,
                'propina_legal' => 0.00,
                'efectivo' => $efectivo,
                'bancos' => $bancos,
                'tarjeta' => $tarjeta,
                'credito' => 0.00,
                'bonos' => 0.00,
                'permuta' => 0.00,
                'otras_formas' => 0.00,
            ];
        }

        return $records;
    }

    /**
     * Obtiene los registros normalizados de Formato 608 (Comprobantes Anulados) para un período YYYY-MM.
     */
    public function getRecords608(string $yearMonth): array
    {
        $startDate = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        $voidedPayments = Payment::with('student')
            ->whereIn('status', ['cancelled', 'void', 'Anulado', 'refunded'])
            ->whereNotNull('ncf')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->orderBy('updated_at', 'asc')
            ->get();

        $records = [];
        foreach ($voidedPayments as $payment) {
            $clientName = $payment->company_name ?: ($payment->student ? trim($payment->student->first_name . ' ' . $payment->student->last_name) : 'General');
            $records[] = [
                'id' => $payment->id,
                'ncf' => trim((string) $payment->ncf),
                'fecha_comprobante' => $payment->created_at ? $payment->created_at->format('Ymd') : $startDate->format('Ymd'),
                'fecha_anulacion' => $payment->updated_at ? $payment->updated_at->format('Ymd') : $startDate->format('Ymd'),
                'tipo_anulacion' => '05',
                'motivo_descripcion' => DgiiReportBuilderService::ANULATION_TYPES['05'],
                'monto' => (float) $payment->amount,
                'cliente_nombre' => $clientName,
            ];
        }

        return $records;
    }

    /**
     * Genera el archivo TXT oficial de 23 columnas para el Formato 606 (Compras y Gastos).
     */
    public function generate606(string $yearMonth, ?array $customRecords = null): array
    {
        $records = $customRecords !== null ? $customRecords : $this->getRecords606($yearMonth);
        $rncEmpresa = $this->issuer->getEmitterRnc();
        $periodYm = str_replace('-', '', $yearMonth);

        $content = $this->reportBuilder->build606Txt($rncEmpresa, $periodYm, $records);

        return [
            'filename' => "DGII_606_{$rncEmpresa}_{$periodYm}.txt",
            'content' => $content,
            'count' => count($records),
        ];
    }

    /**
     * Genera el archivo TXT oficial de 23 columnas para el Formato 607 (Ventas e Ingresos).
     */
    public function generate607(string $yearMonth, ?array $customRecords = null): array
    {
        $records = $customRecords !== null ? $customRecords : $this->getRecords607($yearMonth);
        $rncEmpresa = $this->issuer->getEmitterRnc();
        $periodYm = str_replace('-', '', $yearMonth);

        $content = $this->reportBuilder->build607Txt($rncEmpresa, $periodYm, $records);

        return [
            'filename' => "DGII_607_{$rncEmpresa}_{$periodYm}.txt",
            'content' => $content,
            'count' => count($records),
        ];
    }

    /**
     * Genera el archivo TXT oficial para el Formato 608 (Comprobantes Anulados).
     */
    public function generate608(string $yearMonth, ?array $customRecords = null): array
    {
        $records = $customRecords !== null ? $customRecords : $this->getRecords608($yearMonth);
        $rncEmpresa = $this->issuer->getEmitterRnc();
        $periodYm = str_replace('-', '', $yearMonth);

        $content = $this->reportBuilder->build608Txt($rncEmpresa, $periodYm, $records);

        return [
            'filename' => "DGII_608_{$rncEmpresa}_{$periodYm}.txt",
            'content' => $content,
            'count' => count($records),
        ];
    }

    /**
     * Ejecuta el Pre-validador DGII y calcula todas las declaraciones (IT-1, IR-2, RST, DSS-07, DAF).
     */
    public function getFullTaxAnalysis(string $yearMonth): array
    {
        [$year, $month] = array_pad(explode('-', $yearMonth), 2, date('m'));
        $periodYm = "{$year}{$month}";
        $rncEmpresa = $this->issuer->getEmitterRnc();
        $emitterName = $this->issuer->getEmitterName();

        $records606 = $this->getRecords606($yearMonth);
        $records607 = $this->getRecords607($yearMonth);
        $records608 = $this->getRecords608($yearMonth);

        // Annual records for IR-2, DAF, RS1-RS4
        $yearStart = Carbon::create((int) $year, 1, 1)->startOfDay();
        $yearEnd = Carbon::create((int) $year, 12, 31)->endOfDay();
        $annual606 = $this->getRecords606($yearMonth, $yearStart, $yearEnd);
        $annual607 = $this->getRecords607($yearMonth, $yearStart, $yearEnd);

        $companyMeta = [
            'rnc' => $rncEmpresa,
            'name' => $emitterName,
            'commercial_name' => $emitterName,
            'phone' => '',
            'email' => '',
            'capital' => 100000.0,
        ];

        $it1Declaration = $this->reportBuilder->calculateIt1Declaration($records606, $records607, $companyMeta, $year, $month);
        $ir2Declaration = $this->reportBuilder->calculateIr2Declaration($annual606, $annual607, $companyMeta, $year);
        $otherDeclarations = $this->reportBuilder->calculateOtherDeclarations($ir2Declaration, $it1Declaration, $companyMeta, $year, $month);

        return [
            'records606' => $records606,
            'records607' => $records607,
            'records608' => $records608,
            'validation606' => $this->prevalidator->validateData('606', $rncEmpresa, $periodYm, $records606),
            'validation607' => $this->prevalidator->validateData('607', $rncEmpresa, $periodYm, $records607),
            'validation608' => $this->prevalidator->validateData('608', $rncEmpresa, $periodYm, $records608),
            'it1Summary' => $this->reportBuilder->calculateIt1Summary($records606, $records607),
            'it1Declaration' => $it1Declaration,
            'ir2Declaration' => $ir2Declaration,
            'otherDeclarations' => $otherDeclarations,
            'emitterRnc' => $rncEmpresa,
            'emitterName' => $emitterName,
            'environment' => $this->issuer->getEnvironment(),
        ];
    }
}
