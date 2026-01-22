<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-CF #{{ $payment->id }} - CENTU</title>
    <style>
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { margin: 0.2cm; }
            .no-print { display: none; }
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 76mm;
            margin: 0 auto;
            padding: 5px;
            background: #fff;
            color: #000;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .logo-img { 
            max-width: 100px; 
            height: auto; 
            margin-bottom: 5px;
            filter: grayscale(100%) contrast(120%);
        }
        .company-name { font-weight: bold; font-size: 13px; margin-bottom: 2px; }
        .info { font-size: 10px; line-height: 1.3; }
        .separator { border-bottom: 1px dashed #000; margin: 6px 0; }
        .separator-solid { border-bottom: 1px solid #000; margin: 6px 0; }
        
        /* Título del tipo de comprobante */
        .ncf-title {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-top: 8px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .kv-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .kv-label { font-weight: bold; }
        .kv-value { text-align: right; max-width: 60%; word-wrap: break-word; }

        .details-table { width: 100%; border-collapse: collapse; margin: 5px 0; }
        .details-table th { text-align: left; border-bottom: 1px solid #000; font-size: 10px; padding: 2px 0; }
        .details-table td { padding: 4px 0; font-size: 10px; vertical-align: top; }
        .col-desc { width: 65%; }
        .col-price { width: 35%; text-align: right; }
        
        .totals-area { margin-top: 5px; }
        .total-row { font-weight: bold; font-size: 14px; margin-top: 5px; border-top: 1px solid #000; padding-top: 5px; }
        
        .footer { text-align: center; margin-top: 15px; font-size: 9px; line-height: 1.4; }
        .qr-code { text-align: center; margin-top: 10px; margin-bottom: 5px; }
    </style>
</head>
<body onload="window.print()">

    <!-- ENCABEZADO FISCAL -->
    <div class="header">
        <img src="{{ asset('centuu.png') }}" alt="LOGO" class="logo-img">
        <div class="company-name">CENTRO DE TECNOLOGÍA UNIVERSAL</div>
        <div class="info">
            <strong>RNC: 101-14245-6</strong><br>
            Av. Doctor Delgado #103 Gazcue, Distrito Nacional, RD<br>
            Tel: (809) 221-3222
        </div>
    </div>

    <div class="separator-solid"></div>

    <!-- DATOS DEL COMPROBANTE -->
    <div class="ncf-title">
        @if($payment->ncf)
            FACTURA DE {{ $payment->ncf_type == '31' ? 'CRÉDITO FISCAL' : 'CONSUMO' }} ELECTRÓNICA
        @else
            RECIBO DE INGRESO (NO VÁLIDO PARA CRÉDITO FISCAL)
        @endif
    </div>

    <div class="info">
        @if($payment->ncf)
            <div class="kv-row">
                <span class="kv-label">e-NCF:</span>
                <span class="kv-value">{{ $payment->ncf }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">VENCIMIENTO:</span>
                <span class="kv-value">{{ $payment->ncf_expiration ? $payment->ncf_expiration->format('d/m/Y') : 'N/A' }}</span>
            </div>
        @else
            <div class="kv-row">
                <span class="kv-label">ID INTERNO:</span>
                <span class="kv-value">#{{ str_pad($payment->id, 8, '0', STR_PAD_LEFT) }}</span>
            </div>
        @endif
        
        <div class="kv-row">
            <span class="kv-label">FECHA:</span>
            <span class="kv-value">{{ $payment->created_at->format('d/m/Y') }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">HORA:</span>
            <span class="kv-value">{{ $payment->created_at->format('h:i A') }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <!-- DATOS DEL CLIENTE -->
    <div class="info">
        <div class="kv-row">
            <span class="kv-label">RAZÓN SOCIAL:</span>
            <span class="kv-value">{{ strtoupper($payment->student->full_name) }}</span>
        </div>
        <div class="kv-row">
            <span class="kv-label">RNC/CÉDULA:</span>
            <span class="kv-value">{{ $payment->student->rnc ?? $payment->student->student_code ?? '000-0000000-0' }}</span>
        </div>
    </div>

    <div class="separator-solid"></div>

    <!-- DETALLE -->
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
                    <strong>{{ strtoupper($payment->paymentConcept->name ?? 'SERVICIOS') }}</strong>
                    @if($payment->enrollment)
                        <br>
                        <span style="font-size: 9px; color: #333;">
                            {{ $payment->enrollment->courseSchedule->module->course->name ?? '' }}
                        </span>
                    @endif
                </td>
                <td class="col-price">
                    <span style="display:block; font-size:9px; color:#555;">E (Exento)</span>
                    {{ number_format($payment->amount, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- TOTALES -->
    <div class="totals-area">
        <div class="kv-row total-row">
            <span class="kv-label" style="font-size: 13px;">TOTAL A PAGAR:</span>
            <span class="kv-value" style="font-size: 13px;">RD$ {{ number_format($payment->amount, 2) }}</span>
        </div>

        <div class="separator"></div>

        <div class="kv-row">
            <span class="kv-label">FORMA DE PAGO:</span>
            <span class="kv-value">{{ strtoupper($payment->gateway) }}</span>
        </div>
        @if($payment->gateway === 'Efectivo' && isset($payment->cash_received))
            <div class="kv-row">
                <span class="kv-label">RECIBIDO:</span>
                <span class="kv-value">{{ number_format($payment->cash_received, 2) }}</span>
            </div>
            <div class="kv-row">
                <span class="kv-label">DEVUELTA:</span>
                <span class="kv-value">{{ number_format($payment->cash_received - $payment->amount, 2) }}</span>
            </div>
        @endif
    </div>

    <!-- PIE DE PÁGINA Y QR -->
    <div class="footer">
        @if($payment->dgii_qr_url)
            <div class="qr-code">
                <!-- Generación de QR usando API pública (Para producción usar biblioteca local como simple-qrcode) -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode($payment->dgii_qr_url) }}" alt="QR e-CF" width="90">
            </div>
            <p><strong>CÓDIGO SEGURIDAD:</strong> {{ $payment->security_code }}</p>
        @else
            <p style="margin-top:10px; border:1px solid #000; padding:2px;">COMPROBANTE PROVISIONAL</p>
        @endif

        <p style="margin-top:10px">¡Gracias por preferirnos!</p>
        <p style="margin-top: 5px; font-size: 8px;">Copia Cliente</p>
    </div>

</body>
</html>