<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo #{{ str_pad($receipt['id'], 6, '0', STR_PAD_LEFT) }} - {{ $issuer['name'] ?? 'Academic+' }}</title>
    <style>
        @page {
            margin: 12px 14px;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8.5pt;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .logo {
            max-width: 110px;
            max-height: 40px;
            margin-bottom: 4px;
        }

        .issuer-name {
            font-size: 10pt;
            font-weight: bold;
            margin: 2px 0;
            text-transform: uppercase;
        }

        .issuer-meta {
            font-size: 7.5pt;
            color: #374151;
        }

        .divider {
            border-top: 1px dashed #6b7280;
            margin: 7px 0;
        }

        .divider-solid {
            border-top: 1.5px solid #111827;
            margin: 7px 0;
        }

        .kv-table {
            width: 100%;
            border-collapse: collapse;
        }

        .kv-table td {
            padding: 1.5px 0;
            vertical-align: top;
            font-size: 8pt;
        }

        .kv-label {
            color: #4b5563;
            width: 42%;
        }

        .kv-value {
            text-align: right;
            font-weight: bold;
            width: 58%;
        }

        .total-box {
            background-color: #f3f4f6;
            border-top: 1.5px solid #111827;
            border-bottom: 1.5px solid #111827;
            padding: 5px 0;
            margin: 6px 0;
        }

        .total-table {
            width: 100%;
            border-collapse: collapse;
        }

        .total-table td {
            font-size: 10pt;
            font-weight: bold;
        }

        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 7pt;
            color: #4b5563;
        }
    </style>
</head>
<body>
    @php
        $logo = \App\Models\Setting::get('institution_logo');
        $logoPath = public_path('branding/academic-logo.png');

        if ($logo) {
            if (file_exists(public_path('storage/' . $logo))) {
                $logoPath = public_path('storage/' . $logo);
            } elseif (file_exists(public_path(ltrim($logo, '/')))) {
                $logoPath = public_path(ltrim($logo, '/'));
            }
        }

        $base64 = null;
        if ($logoPath && file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png';
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode(file_get_contents($logoPath));
        }
    @endphp

    <div class="text-center">
        @if($base64)
            <img src="{{ $base64 }}" class="logo" alt="Logo">
        @endif
        <div class="issuer-name">{{ $issuer['name'] ?? \App\Support\BrandingDefaults::institutionName() }}</div>
        @if(!empty($issuer['rnc']))
            <div class="issuer-meta">RNC: {{ $issuer['rnc'] }}</div>
        @endif
        @if(!empty($issuer['address']))
            <div class="issuer-meta">{{ $issuer['address'] }}</div>
        @endif
        <div class="issuer-meta">República Dominicana</div>
    </div>

    <div class="divider-solid"></div>

    <div class="text-center font-bold uppercase" style="font-size: 9pt; margin-bottom: 4px;">
        {{ ($receipt['ncf_kind'] ?? 'B02') === 'B01' ? 'Factura de Crédito Fiscal' : 'Recibo de Cobro / Consumo' }}
    </div>

    <table class="kv-table">
        <tr>
            <td class="kv-label">Recibo No:</td>
            <td class="kv-value">#{{ str_pad($receipt['id'], 6, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td class="kv-label">Fecha:</td>
            <td class="kv-value">{{ $receipt['date'] ?? now()->format('d/m/Y H:i') }}</td>
        </tr>
        @if(!empty($receipt['cashier']))
            <tr>
                <td class="kv-label">Cajero(a):</td>
                <td class="kv-value">{{ $receipt['cashier'] }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    <table class="kv-table">
        <tr>
            <td class="kv-label">Cliente:</td>
            <td class="kv-value">{{ $receipt['customer']['name'] ?? 'Cliente General' }}</td>
        </tr>
        @if(!empty($receipt['customer']['code']))
            <tr>
                <td class="kv-label">Matrícula:</td>
                <td class="kv-value">{{ $receipt['customer']['code'] }}</td>
            </tr>
        @endif
        @if(!empty($receipt['customer']['cedula']))
            <tr>
                <td class="kv-label">Cédula:</td>
                <td class="kv-value">{{ $receipt['customer']['cedula'] }}</td>
            </tr>
        @endif
        @if(!empty($receipt['rnc_client']))
            <tr>
                <td class="kv-label">RNC Fiscal:</td>
                <td class="kv-value">{{ $receipt['rnc_client'] }}</td>
            </tr>
        @endif
        @if(!empty($receipt['company_name']))
            <tr>
                <td class="kv-label">Razón Social:</td>
                <td class="kv-value">{{ $receipt['company_name'] }}</td>
            </tr>
        @endif
    </table>

    <div class="divider"></div>

    <table class="kv-table">
        <tr>
            <td class="kv-label">Tipo NCF:</td>
            <td class="kv-value">{{ ($receipt['ncf_kind'] ?? 'B02') === 'B01' ? 'Crédito Fiscal (B01)' : 'Consumo (B02)' }}</td>
        </tr>
        <tr>
            <td class="kv-label">NCF:</td>
            <td class="kv-value">{{ $receipt['ncf'] ?: 'En proceso' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div style="font-size: 8.5pt; font-weight: bold; margin-bottom: 2px;">
        {{ $receipt['concept'] ?? 'Concepto de Cobro' }}
    </div>
    <table class="kv-table">
        <tr>
            <td class="kv-label">{{ $receipt['quantity'] ?? 1 }} x RD$ {{ number_format((float) ($receipt['unit_price'] ?? $receipt['total'] ?? 0), 2) }}</td>
            <td class="kv-value">RD$ {{ number_format((float) ($receipt['subtotal'] ?? $receipt['total'] ?? 0), 2) }}</td>
        </tr>
        @if(($receipt['discount'] ?? 0) > 0)
            <tr>
                <td class="kv-label">Descuento:</td>
                <td class="kv-value">- RD$ {{ number_format((float) $receipt['discount'], 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="total-box">
        <table class="total-table">
            <tr>
                <td>TOTAL PAGADO</td>
                <td class="text-right">RD$ {{ number_format((float) ($receipt['total'] ?? 0), 2) }}</td>
            </tr>
        </table>
    </div>

    <table class="kv-table">
        <tr>
            <td class="kv-label">Método de Pago:</td>
            <td class="kv-value">{{ $receipt['gateway'] ?? 'Efectivo' }}</td>
        </tr>
        @if(!empty($receipt['cash_received']))
            <tr>
                <td class="kv-label">Efectivo Recibido:</td>
                <td class="kv-value">RD$ {{ number_format((float) $receipt['cash_received'], 2) }}</td>
            </tr>
            <tr>
                <td class="kv-label">Devuelta / Cambio:</td>
                <td class="kv-value">RD$ {{ number_format((float) ($receipt['change_amount'] ?? 0), 2) }}</td>
            </tr>
        @endif
    </table>

    <div class="footer">
        <div class="divider"></div>
        ¡Gracias por su pago!<br>
        Comprobante oficial generado por {{ $issuer['name'] ?? 'Academic+' }}<br>
        <strong>Powered by Gridbase Bills</strong>
    </div>
</body>
</html>
