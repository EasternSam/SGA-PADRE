<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #{{ $payment->id }}</title>
    <style>
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { margin: 0.5cm; }
        }
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente tipo ticket */
            font-size: 12px;
            width: 80mm; /* Ancho estándar de impresora térmica */
            margin: 0 auto;
            padding: 10px;
            background: #fff;
            color: #000;
        }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 1px dashed #000; padding-bottom: 10px; }
        .logo { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
        .info { font-size: 10px; margin-bottom: 2px; }
        
        .details { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .details th { text-align: left; border-bottom: 1px solid #000; font-size: 10px; }
        .details td { padding: 4px 0; font-size: 11px; }
        .price { text-align: right; }
        
        .totals { margin-top: 10px; border-top: 1px dashed #000; padding-top: 5px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .total-row { font-weight: bold; font-size: 14px; margin-top: 5px; }
        
        .footer { text-align: center; margin-top: 20px; font-size: 10px; border-top: 1px dashed #000; padding-top: 10px; }
        
        /* Utilidad para ocultar en pantalla normal si se desea ver solo al imprimir */
        .no-print { display: none; }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="logo">{{ config('app.name', 'SGA System') }}</div>
        <div class="info">RNC: 1-01-00000-0</div>
        <div class="info">Tel: (809) 555-5555</div>
        <div class="info">{{ now()->format('d/m/Y h:i A') }}</div>
        <div class="info">Cajero: {{ auth()->user()->name ?? 'Sistema' }}</div>
        <div class="info">Recibo #: {{ str_pad($payment->id, 8, '0', STR_PAD_LEFT) }}</div>
    </div>

    <div class="info" style="margin: 10px 0;">
        <strong>Cliente:</strong> {{ $payment->student->full_name }}<br>
        <strong>ID:</strong> {{ $payment->student->student_code ?? 'N/A' }}
    </div>

    <table class="details">
        <thead>
            <tr>
                <th>DESC</th>
                <th class="price">VALOR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $payment->paymentConcept->name ?? 'Pago General' }}
                    @if($payment->enrollment)
                        <br><span style="font-size: 9px;">({{ Str::limit($payment->enrollment->courseSchedule->module->name, 25) }})</span>
                    @endif
                </td>
                <td class="price">{{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <div class="row">
            <span>Subtotal:</span>
            <span>{{ number_format($payment->amount, 2) }}</span>
        </div>
        <!-- Si hubiera impuestos, agregarlos aquí -->
        
        <div class="row total-row">
            <span>TOTAL:</span>
            <span>RD$ {{ number_format($payment->amount, 2) }}</span>
        </div>
        
        <div class="row" style="margin-top: 10px; font-size: 11px;">
            <span>Método:</span>
            <span>{{ $payment->gateway }}</span>
        </div>
        
        @if($payment->gateway === 'Efectivo' && isset($payment->cash_received))
             <!-- Nota: cash_received no se guarda en BD actualmente en Payment, 
                  si lo necesitas persistente, habría que agregar columna. 
                  Por ahora lo mostramos si se pasa como parámetro o si lo agregas al modelo. -->
        @endif
    </div>

    <div class="footer">
        <p>¡Gracias por su pago!</p>
        <p>Conserve este recibo para cualquier reclamación.</p>
        <p>--- Copia Cliente ---</p>
    </div>

    <script>
        // Cerrar ventana automáticamente después de imprimir (opcional)
        window.onafterprint = function() {
            // window.close(); 
        };
    </script>
</body>
</html>