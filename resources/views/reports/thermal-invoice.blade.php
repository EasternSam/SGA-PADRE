<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #{{ $payment->id }} - CENTU</title>
    <style>
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { margin: 0.2cm; }
            .no-print { display: none; }
        }
        body {
            font-family: 'Courier New', Courier, monospace; /* Tipografía monoespaciada para alineación */
            font-size: 11px;
            width: 76mm; /* Ajuste seguro para papel de 80mm */
            margin: 0 auto;
            padding: 5px;
            background: #fff;
            color: #000;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .logo-img { 
            max-width: 120px; 
            height: auto; 
            margin-bottom: 5px;
            filter: grayscale(100%) contrast(120%); /* Optimizado para impresión térmica */
        }
        
        .company-name { font-weight: bold; font-size: 14px; margin-bottom: 2px; }
        .info { font-size: 10px; line-height: 1.3; }
        .separator { border-bottom: 1px dashed #000; margin: 8px 0; }
        .separator-solid { border-bottom: 1px solid #000; margin: 8px 0; }
        
        .section-title { 
            text-align: center; 
            font-weight: bold; 
            font-size: 11px; 
            background-color: #eee; /* Gris suave en pantalla, blanco en térmica */
            padding: 2px 0;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .kv-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .kv-label { font-weight: bold; }
        .kv-value { text-align: right; max-width: 65%; word-wrap: break-word; }

        .details-table { width: 100%; border-collapse: collapse; margin: 5px 0; }
        .details-table th { text-align: left; border-bottom: 1px solid #000; font-size: 10px; padding: 2px 0; }
        .details-table td { padding: 4px 0; font-size: 10px; vertical-align: top; }
        .col-desc { width: 70%; }
        .col-price { width: 30%; text-align: right; }
        
        .totals-area { margin-top: 5px; }
        .total-row { font-weight: bold; font-size: 14px; margin-top: 5px; border-top: 1px solid #000; padding-top: 5px; }
        
        .footer { text-align: center; margin-top: 20px; font-size: 9px; line-height: 1.4; }
    </style>
</head>
<body onload="window.print()">

    <!-- ENCABEZADO -->
    <div class="header">
        <img src="{{ asset('centuu.png') }}" alt="LOGO" class="logo-img">
        <div class="company-name">CENTRO DE TECNOLOGÍA UNIVERSAL</div>
        <div class="info">
            RNC: 1-01-00000-0<br>
            Calle Principal #123, Hato Mayor<br>
            Tel: (809) 555-5555 | Info@centu.edu.do
        </div>
    </div>

    <div class="separator-solid"></div>

    <!-- DATOS DEL RECIBO -->
    <div class="info">
        <div class="kv-row">
            <span class="kv-label">NO. RECIBO:</span>
            <span class="kv-value">#{{ str_pad($payment->id, 8, '0', STR_PAD_LEFT) }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">FECHA:</span>
            <span class="kv-value">{{ $payment->created_at->format('d/m/Y h:i A') }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">CAJERO:</span>
            <span class="kv-value">{{ strtoupper(auth()->user()->name ?? 'SISTEMA') }}</span>
        </div>
        @if($payment->transaction_id)
        <div class="kv-row">
            <span class="kv-label">REFERENCIA:</span>
            <span class="kv-value">{{ $payment->transaction_id }}</span>
        </div>
        @endif
        <div class="kv-row">
            <span class="kv-label">CONDICIÓN:</span>
            <span class="kv-value">{{ $payment->status == 'Completado' ? 'CONTADO' : 'CRÉDITO' }}</span>
        </div>
    </div>

    <!-- DATOS DEL CLIENTE -->
    <div class="section-title">DATOS DEL ESTUDIANTE</div>
    <div class="info">
        <div class="kv-row">
            <span class="kv-label">MATRÍCULA:</span>
            <span class="kv-value">{{ $payment->student->student_code ?? 'N/A' }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">ALUMNO:</span>
            <span class="kv-value">{{ strtoupper($payment->student->full_name) }}</span>
        </div>
        @if($payment->student->mobile_phone)
        <div class="kv-row">
            <span class="kv-label">TEL:</span>
            <span class="kv-value">{{ $payment->student->mobile_phone }}</span>
        </div>
        @endif
    </div>

    <!-- DETALLE DE FACTURA -->
    <div class="separator-solid"></div>
    <table class="details-table">
        <thead>
            <tr>
                <th class="col-desc">DESCRIPCIÓN</th>
                <th class="col-price">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="col-desc">
                    <strong>{{ strtoupper($payment->paymentConcept->name ?? 'PAGO GENERAL') }}</strong>
                    @if($payment->enrollment)
                        <br>
                        <span style="font-size: 9px; color: #333;">
                            {{ $payment->enrollment->courseSchedule->module->course->name ?? '' }}<br>
                            > {{ Str::limit($payment->enrollment->courseSchedule->module->name ?? '', 30) }}<br>
                            SEC: {{ $payment->enrollment->courseSchedule->section_name ?? 'ÚNICA' }}
                        </span>
                    @endif
                    @if($payment->description)
                        <br><span style="font-size: 9px; font-style: italic;">Nota: {{ $payment->description }}</span>
                    @endif
                </td>
                <td class="col-price">
                    {{ number_format($payment->amount, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- TOTALES -->
    <div class="totals-area">
        <div class="kv-row">
            <span class="kv-label">SUBTOTAL:</span>
            <span class="kv-value">{{ number_format($payment->amount, 2) }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">DESCUENTO:</span>
            <span class="kv-value">0.00</span>
        </div>
        
        <div class="kv-row total-row">
            <span class="kv-label" style="font-size: 14px;">TOTAL A PAGAR:</span>
            <span class="kv-value" style="font-size: 14px;">RD$ {{ number_format($payment->amount, 2) }}</span>
        </div>

        <div class="separator"></div>

        <div class="kv-row">
            <span class="kv-label">FORMA PAGO:</span>
            <span class="kv-value">{{ strtoupper($payment->gateway) }}</span>
        </div>
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        <p style="font-weight: bold; margin-bottom: 5px;">¡GRACIAS POR PREFERIRNOS!</p>
        <p>Verifique su recibo antes de salir.<br>No se aceptan devoluciones de efectivo.</p>
        <p style="margin-top: 10px;">--- COPIA CLIENTE ---</p>
        <p style="font-size: 8px; margin-top: 5px;">Impreso: {{ now()->format('d/m/Y h:i:s A') }}</p>
    </div>

</body>
</html>