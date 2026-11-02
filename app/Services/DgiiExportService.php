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
 * Construye los datasets 606, 607 y 608 de Academic+ y los procesa con
 * DgiiReportBuilderService y DgiiPrevalidatorService del core de Gridbase Bills.
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
     * Obtiene los registros normalizados de Formato 606 para un período YYYY-MM.
     */
    public function getRecords606(string $yearMonth): array
    {
        $startDate = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        $expenses = Expense::with('supplier')
            ->whereNotNull('ncf')
            ->whereNotNull('supplier_id')
            ->where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate])
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

            $records[] = [
                'id' => $expense->id,
                'supplier_name' => $expense->supplier->name ?? 'Suplidor',
                'rnc_proveedor' => $rnc,
                'tipo_id' => $tipoId,
                'tipo_identificacion' => $tipoId,
                'tipo_bienes' => $expense->expense_type_606 ?: '02',
                'ncf' => trim((string) $expense->ncf),
                'ncf_modificado' => '',
                'fecha_comprobante' => $expense->expense_date ? $expense->expense_date->format('Ymd') : $startDate->format('Ymd'),
                'fecha_pago' => $expense->expense_date ? $expense->expense_date->format('Ymd') : $startDate->format('Ymd'),
                'monto_servicios' => $subtotal,
                'monto_bienes' => 0.00,
                'total_monto_facturado' => $subtotal,
                'itbis_facturado' => $itbisFacturado,
                'itbis_retenido' => $itbisRetenido,
                'itbis_proporcionalidad' => 0.00,
                'itbis_costo' => 0.00,
                'itbis_adelantar' => $itbisPorAdelantar,
                'itbis_percibido' => '',
                'tipo_retencion_isr' => $isrRetenido > 0 ? '08' : '',
                'monto_retencion_renta' => $isrRetenido,
                'isr_percibido' => '',
                'impuesto_selectivo_consumo' => 0.00,
                'otros_impuestos' => 0.00,
                'monto_propina_legal' => 0.00,
                'forma_pago' => '02',
            ];
        }

        return $records;
    }

    /**
     * Obtiene los registros normalizados de Formato 607 para un período YYYY-MM.
     */
    public function getRecords607(string $yearMonth): array
    {
        $startDate = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        $payments = Payment::with('student')
            ->whereIn('status', ['paid', 'Completado'])
            ->whereNotNull('ncf')
            ->whereBetween('created_at', [$startDate, $endDate])
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

            $records[] = [
                'id' => $payment->id,
                'client_name' => $payment->company_name ?: ($payment->student ? trim($payment->student->first_name . ' ' . $payment->student->last_name) : 'Estudiante'),
                'rnc_cliente' => $rncCliente,
                'tipo_id' => $tipoId,
                'tipo_identificacion' => $tipoId,
                'ncf' => $ncf,
                'ncf_modificado' => '',
                'tipo_ingreso' => '01',
                'fecha_comprobante' => $payment->created_at ? $payment->created_at->format('Ymd') : $startDate->format('Ymd'),
                'fecha_retencion' => '',
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

        $voidedPayments = Payment::whereIn('status', ['cancelled', 'void', 'Anulado', 'refunded'])
            ->whereNotNull('ncf')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get();

        $records = [];
        foreach ($voidedPayments as $payment) {
            $records[] = [
                'id' => $payment->id,
                'ncf' => trim((string) $payment->ncf),
                'fecha_comprobante' => $payment->created_at ? $payment->created_at->format('Ymd') : $startDate->format('Ymd'),
                'tipo_anulacion' => '04', // Corrección de la Información / Anulación
            ];
        }

        return $records;
    }

    /**
     * Genera el archivo TXT oficial de 23 columnas para el Formato 606 (Compras y Gastos).
     */
    public function generate606(string $yearMonth): array
    {
        if (!$this->issuer->isElectronicBillingEnabled()) {
            return [
                'filename' => 'DGII_606_DISABLED.txt',
                'content' => "# Facturación Electrónica Deshabilitada\n",
                'count' => 0,
            ];
        }

        $records = $this->getRecords606($yearMonth);
        $rncEmpresa = $this->issuer->getEmitterRnc();
        $periodYm = str_replace('-', '', $yearMonth);

        $content = $this->reportBuilder->build606Txt($rncEmpresa, $periodYm, $records);

        return [
            'filename' => "DGII_F_606_{$rncEmpresa}_{$periodYm}.txt",
            'content' => $content,
            'count' => count($records),
        ];
    }

    /**
     * Genera el archivo TXT oficial de 23 columnas para el Formato 607 (Ventas e Ingresos).
     */
    public function generate607(string $yearMonth): array
    {
        if (!$this->issuer->isElectronicBillingEnabled()) {
            return [
                'filename' => 'DGII_607_DISABLED.txt',
                'content' => "# Facturación Electrónica Deshabilitada\n",
                'count' => 0,
            ];
        }

        $records = $this->getRecords607($yearMonth);
        $rncEmpresa = $this->issuer->getEmitterRnc();
        $periodYm = str_replace('-', '', $yearMonth);

        $content = $this->reportBuilder->build607Txt($rncEmpresa, $periodYm, $records);

        return [
            'filename' => "DGII_F_607_{$rncEmpresa}_{$periodYm}.txt",
            'content' => $content,
            'count' => count($records),
        ];
    }

    /**
     * Genera el archivo TXT oficial para el Formato 608 (Comprobantes Anulados).
     */
    public function generate608(string $yearMonth): array
    {
        if (!$this->issuer->isElectronicBillingEnabled()) {
            return [
                'filename' => 'DGII_608_DISABLED.txt',
                'content' => "# Facturación Electrónica Deshabilitada\n",
                'count' => 0,
            ];
        }

        $records = $this->getRecords608($yearMonth);
        $rncEmpresa = $this->issuer->getEmitterRnc();
        $periodYm = str_replace('-', '', $yearMonth);

        $content = $this->reportBuilder->build608Txt($rncEmpresa, $periodYm, $records);

        return [
            'filename' => "DGII_F_608_{$rncEmpresa}_{$periodYm}.txt",
            'content' => $content,
            'count' => count($records),
        ];
    }

    /**
     * Ejecuta el Pre-validador DGII y calcula la simulación de Declaración Jurada IT-1.
     */
    public function getFullTaxAnalysis(string $yearMonth): array
    {
        $records606 = $this->getRecords606($yearMonth);
        $records607 = $this->getRecords607($yearMonth);
        $records608 = $this->getRecords608($yearMonth);

        return [
            'records606' => $records606,
            'records607' => $records607,
            'records608' => $records608,
            'validation606' => $this->prevalidator->validateData('606', $records606),
            'validation607' => $this->prevalidator->validateData('607', $records607),
            'validation608' => $this->prevalidator->validateData('608', $records608),
            'it1Summary' => $this->reportBuilder->calculateIt1Summary($records606, $records607),
            'emitterRnc' => $this->issuer->getEmitterRnc(),
            'emitterName' => $this->issuer->getEmitterName(),
            'environment' => $this->issuer->getEnvironment(),
        ];
    }
}
