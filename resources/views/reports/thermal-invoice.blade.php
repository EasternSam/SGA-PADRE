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
        .qr-img { width: 90px; height: 90px; }
    </style>
</head>
<body onload="window.print()">

    <!-- ENCABEZADO -->
    <div class="header">
        @if(file_exists(public_path('centuu.png')))
            <img src="{{ asset('centuu.png') }}" alt="LOGO" class="logo-img">
        @else
            <div style="font-size: 20px; font-weight: bold;">CENTU</div>
        @endif
        <div class="company-name">CENTRO DE TECNOLOGÍA UNIVERSAL</div>
        <div class="info">
            <strong>RNC: 101-14245-6</strong><br>
            Av. Doctor Delgado #103 Gazcue, Distrito Nacional, RD<br>
            Tel: (809) 221-3222
        </div>
    </div>

    <div class="separator-solid"></div>

    <!-- TÍTULO DINÁMICO -->
    <div class="ncf-title">
        @if($payment->ncf)
            {{-- Si ya tiene NCF asignado --}}
            @if($payment->ncf_type == '31')
                FACTURA DE CRÉDITO FISCAL
            @else
                FACTURA DE CONSUMO
            @endif
        @else
            {{-- Si no tiene NCF (Provisional) verificamos la intención del cliente (rnc_client o ncf_type) --}}
            @if($payment->ncf_type == '31' || !empty($payment->rnc_client))
                SOLICITUD CRÉDITO FISCAL
            @else
                RECIBO DE INGRESO
            @endif
        @endif
    </div>

    <div class="info">
        @if($payment->ncf)
            <div class="kv-row">
                <span class="kv-label">NCF:</span>
                <span class="kv-value">{{ $payment->ncf }}</span>
            </div>
            @if($payment->ncf_expiration)
                <div class="kv-row">
                    <span class="kv-label">VENCIMIENTO:</span>
                    <span class="kv-value">{{ \Carbon\Carbon::parse($payment->ncf_expiration)->format('d/m/Y') }}</span>
                </div>
            @endif
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
        @if(!empty($payment->company_name))
            {{-- Si existe Razón Social guardada en el pago --}}
            <div class="kv-row">
                <span class="kv-label">RAZÓN SOCIAL:</span>
                <span class="kv-value">{{ strtoupper($payment->company_name) }}</span>
            </div>
        @else
            {{-- Consumidor Final / Estudiante --}}
            <div class="kv-row">
                <span class="kv-label">CLIENTE:</span>
                <span class="kv-value">{{ strtoupper($payment->student->full_name) }}</span>
            </div>
        @endif

        <div class="kv-row">
            <span class="kv-label">{{ !empty($payment->rnc_client) ? 'RNC:' : 'MATRÍCULA:' }}</span>
            <span class="kv-value">
                {{ !empty($payment->rnc_client) ? $payment->rnc_client : ($payment->student->student_code ?? 'N/A') }}
            </span>
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
                    <strong>{{ strtoupper($payment->paymentConcept->name ?? 'SERVICIOS EDUCATIVOS') }}</strong>
                    @if($payment->enrollment && $payment->enrollment->courseSchedule)
                        <br>
                        <span style="font-size: 9px; color: #333;">
                            {{ $payment->enrollment->courseSchedule->module->name ?? '' }}
                            @if($payment->enrollment->courseSchedule->section_name)
                                ({{ $payment->enrollment->courseSchedule->section_name }})
                            @endif
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
        
        @if($payment->gateway === 'Tarjeta' || str_contains(strtolower($payment->gateway), 'cardnet'))
            @if($payment->transaction_id)
                <div class="kv-row">
                    <span class="kv-label">AUTORIZACIÓN:</span>
                    <span class="kv-value">{{ $payment->transaction_id }}</span>
                </div>
            @endif
        @endif

        @if($payment->gateway === 'Efectivo' && isset($payment->cash_received) && $payment->cash_received > 0)
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

    <!-- PIE -->
    <div class="footer">
        @if(!empty($payment->dgii_qr_url))
            <div class="qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode($payment->dgii_qr_url) }}" alt="QR e-CF" class="qr-img">
            </div>
            @if(!empty($payment->security_code))
                <p><strong>CÓDIGO SEGURIDAD:</strong> {{ $payment->security_code }}</p>
            @endif
        @else
            <p style="margin-top:10px; border:1px solid #000; padding:2px;">COMPROBANTE VÁLIDO PARA FINES LEGALES</p>
            @if(isset($payment->ncf_type_requested) && $payment->ncf_type_requested == 'B01')
                <p style="font-size: 8px; margin-top:2px;">(SOLICITUD DE CRÉDITO FISCAL EN PROCESO)</p>
            @endif
        @endif

        <p style="margin-top:10px">¡Gracias por preferirnos!</p>
        <p style="margin-top: 5px; font-size: 8px;">Copia Cliente</p>
        <div style="font-size: 8px; margin-top: 5px;">Generado: {{ now()->format('d/m/Y h:i A') }}</div>
    </div>
</body>
</html>