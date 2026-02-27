<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DgiiExportService
{
    /**
     * Genera el archivo TXT para el Formato 606 (Compras y Gastos).
     *
     * Estructura requerida DGII:
     * 1. RNC o Cédula
     * 2. Tipo de Identificación (1 = RNC, 2 = Cédula, 3 = Pasaporte)
     * 3. Tipo Bien o Servicio Comprado (01 - 11)
     * 4. NCF
     * 5. NCF o Documento Modificado
     * 6. Fecha Comprobante (YYYYMMDD)
     * 7. Fecha Pago (YYYYMMDD)
     * 8. Monto Facturado en Servicios
     * 9. Monto Facturado en Bienes
     * 10. Total Monto Facturado
     * 11. ITBIS Facturado
     * 12. ITBIS Retenido
     * 13. ITBIS Sujeto a Proporcionalidad (Dejar 0.00)
     * 14. ITBIS Llevado al Costo (Dejar 0.00)
     * 15. ITBIS por Adelantar (ITBIS Facturado - ITBIS Retenido)
     * 16. ITBIS Percibido en Compras (0.00)
     * 17. Tipo de Retención en ISR
     * 18. Monto Retención Renta
     * 19. ISR Percibido en Compras (0.00)
     * 20. Impuesto Selectivo al Consumo (0.00)
     * 21. Otros Impuestos / Tasas (0.00)
     * 22. Monto Propina Legal (0.00)
     * 23. Forma de Pago (01 = Efectivo, 02 = Cheque/Transferencia, 03 = Tarjeta)
     */
    public function generate606(string $yearMonth)
    {
        $startDate = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        // Obtener solo gastos pagados con NCF en el período
        $expenses = Expense::with('supplier')
            ->whereNotNull('ncf')
            ->whereNotNull('supplier_id')
            ->where('status', 'paid')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        $lines = [];

        foreach ($expenses as $expense) {
            $rnc = preg_replace('/[^0-9]/', '', $expense->supplier->rnc_cedula ?? '');
            $tipoId = (strlen($rnc) === 9) ? '1' : '2'; // 1=RNC, 2=Cedula
            
            // Si el NCF es B15 (Comprobante de Compras), el RNC es nuestro, no del suplidor,
            // pero para simplificar, asumimos que todos son B01 reportados por el proveedor.
            
            // Asumiendo que todos son servicios en un colegio, ajusta según necesidad
            $montoServicios = number_format($expense->subtotal, 2, '.', '');
            $montoBienes = '0.00';
            $totalMonto = number_format($expense->subtotal, 2, '.', '');
            
            $itbisFacturado = number_format($expense->itbis_amount, 2, '.', '');
            $itbisRetenido = number_format($expense->itbis_retained, 2, '.', '');
            
            $itbisPorAdelantar = number_format((float)$expense->itbis_amount - (float)$expense->itbis_retained, 2, '.', '');
            if ($itbisPorAdelantar < 0) $itbisPorAdelantar = '0.00';

            // Tipo Retención ISR (01 = Alquileres, 02 = Honorarios Profesionales, 08 = Otras)
            // Simplificado a '08' (Otras retenciones) si hay ISR, o vacío si no hay
            $tipoRetIsr = ($expense->isr_retained > 0) ? '08' : '';
            $montoRetRenta = ($expense->isr_retained > 0) ? number_format($expense->isr_retained, 2, '.', '') : '0.00';

            // Forma de pago: 02 = Transferencia/Cheque (asumido por defecto para colegios)
            $formaPago = '02';

            $line = implode('|', [
                $rnc,                                               // 1
                $tipoId,                                            // 2
                $expense->expense_type_606 ?? '02',                 // 3
                $expense->ncf,                                      // 4
                '',                                                 // 5 NCF Modificado
                $expense->expense_date->format('Ymd'),              // 6
                $expense->expense_date->format('Ymd'),              // 7 Fecha Pago
                $montoServicios,                                    // 8
                $montoBienes,                                       // 9
                $totalMonto,                                        // 10
                $itbisFacturado,                                    // 11
                $itbisRetenido,                                     // 12
                '0.00',                                             // 13 ITBIS Proporcionalidad
                '0.00',                                             // 14 ITBIS al Costo
                $itbisPorAdelantar,                                 // 15
                '0.00',                                             // 16 ITBIS Percibido
                $tipoRetIsr,                                        // 17 Tipo Ret ISR
                $montoRetRenta,                                     // 18 Monto Ret ISR
                '0.00',                                             // 19 ISR Percibido
                '0.00',                                             // 20 ISC
                '0.00',                                             // 21 Otros Impuestos
                '0.00',                                             // 22 Propina
                $formaPago                                          // 23
            ]);

            $lines[] = $line;
        }

        // Crear el archivo
        $rncEmpresa = config('app.rnc_empresa', '101000000'); // Debería salir de Settings si es multi-tenant
        $cantidadRegistros = count($lines);
        $fileName = "DGII_606_{$rncEmpresa}_{$yearMonth}.txt";
        
        // Cabecera No estándar pero requerida por OFV a veces, 
        // normalmente la DGII Ofv permite solo el cuerpo para carga txt.
        $content = implode("\r\n", $lines);

        return [
            'filename' => $fileName,
            'content'  => $content,
            'count'    => $cantidadRegistros
        ];
    }

    /**
     * Genera el archivo TXT para el Formato 607 (Ventas e Ingresos).
     */
    public function generate607(string $yearMonth)
    {
        $startDate = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $yearMonth)->endOfMonth();

        // Obtener solo ingresos facturados (pagos completados con NCF emitido)
        $payments = Payment::whereIn('status', ['paid', 'Completado'])
            ->whereNotNull('ncf')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $lines = [];

        foreach ($payments as $payment) {
            // RNC del Cliente (si es B01), sino en blanco para Consumo (B02)
            $rncCliente = '';
            $tipoId = '';

            if ($payment->ncf_type === '31' || str_starts_with($payment->ncf, 'B01') || str_starts_with($payment->ncf, 'E31')) {
                $rncCliente = preg_replace('/[^0-9]/', '', $payment->rnc_client ?? '');
                $tipoId = (strlen($rncCliente) === 9) ? '1' : '2';
            }

            // Pagos de colegio suelen estar Exentos de ITBIS. (Subtotal = Total Facturado)
            $montoFacturado = number_format($payment->amount, 2, '.', '');
            $itbisFacturado = '0.00'; 
            
            // Forma de Pago (01 = Efectivo, 02 = Transf/Cheque, 03 = Tarjeta)
            $formaPago = '03'; // Asumiendo Tarjeta como default de pasarela
            if ($payment->gateway === 'manual' || $payment->gateway === 'cash') {
                $formaPago = '01';
            }

            $line = implode('|', [
                $rncCliente,                                    // 1. RNC/Cédula Parcial
                $tipoId,                                        // 2. Tipo Identificación
                $payment->ncf,                                  // 3. NCF Emitido
                '',                                             // 4. NCF Modificado
                '03',                                           // 5. Tipo de Ingreso (03 = Ingresos por Servicios)
                $payment->created_at->format('Ymd'),            // 6. Fecha Comprobante
                '',                                             // 7. Fecha Retención
                $itbisFacturado,                                // 8. ITBIS Retenido
                '0.00',                                         // 9. ITBIS Retenido por Terceros
                '0.00',                                         // 10. ITBIS Percibido
                '0.00',                                         // 11. Retención Renta
                '0.00',                                         // 12. ISR Percibido
                '0.00',                                         // 13. Impuesto Selectivo al Consumo
                '0.00',                                         // 14. Otros Impuestos
                '0.00',                                         // 15. Monto Propina
                $montoFacturado,                                // 16. Monto Efectivo
                '0.00',                                         // 17. Monto Cheque / Transf
                '0.00',                                         // 18. Monto Tarjeta Débito/Crédito
                '0.00',                                         // 19. Monto Venta a Crédito
                '0.00',                                         // 20. Bonos
                '0.00',                                         // 21. Permuta
                '0.00',                                         // 22. Otras Formas de Pago
            ]);

            // Distribución de montos según la forma real cobrada
            $lineArray = explode('|', $line);
            if ($formaPago === '01') {
                $lineArray[15] = $montoFacturado; // Efectivo
            } elseif ($formaPago === '02') {
                $lineArray[16] = $montoFacturado; // Cheque/Transf
                $lineArray[15] = '0.00';
            } elseif ($formaPago === '03') {
                $lineArray[17] = $montoFacturado; // Tarjeta
                $lineArray[15] = '0.00';
            }
            $line = implode('|', $lineArray);

            $lines[] = $line;
        }

        $rncEmpresa = config('app.rnc_empresa', '101000000');
        $cantidadRegistros = count($lines);
        $fileName = "DGII_607_{$rncEmpresa}_{$yearMonth}.txt";
        
        $content = implode("\r\n", $lines);

        return [
            'filename' => $fileName,
            'content'  => $content,
            'count'    => $cantidadRegistros
        ];
    }
}
