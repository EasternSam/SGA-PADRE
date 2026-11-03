<?php
if (!function_exists('getMonochromeLogo')) {
    function getMonochromeLogo($url) {
        if (empty($url)) return '';
        if (str_starts_with($url, 'data:image/')) return $url;
        try {
            $imageContent = @file_get_contents($url);
            if (!$imageContent) return $url;

            $im = @imagecreatefromstring($imageContent);
            if (!$im) return $url;

            $width = imagesx($im);
            $height = imagesy($im);
            $isPng = (strpos($url, '.png') !== false || substr($imageContent, 1, 3) === 'PNG');

            if ($isPng) {
                $newImg = imagecreatetruecolor($width, $height);
                imagealphablending($newImg, false);
                imagesavealpha($newImg, true);
                $transparent = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
                imagefill($newImg, 0, 0, $transparent);

                for ($x = 0; $x < $width; $x++) {
                    for ($y = 0; $y < $height; $y++) {
                        $rgba = imagecolorat($im, $x, $y);
                        $colors = imagecolorsforindex($im, $rgba);
                        $alpha = $colors['alpha'];

                        if ($alpha < 127) {
                            $black = imagecolorallocatealpha($newImg, 0, 0, 0, $alpha);
                            imagesetpixel($newImg, $x, $y, $black);
                        }
                    }
                }
                $renderImg = $newImg;
            } else {
                imagefilter($im, IMG_FILTER_GRAYSCALE);
                imagefilter($im, IMG_FILTER_CONTRAST, -30);
                $renderImg = $im;
            }

            ob_start();
            imagepng($renderImg);
            $imageData = ob_get_clean();

            imagedestroy($im);
            if ($isPng) {
                imagedestroy($renderImg);
            }

            return 'data:image/png;base64,' . base64_encode($imageData);
        } catch (\Throwable $e) {
            return $url;
        }
    }
}

$isQuote   = isset($is_quote) && $is_quote;
$isEcf     = !$isQuote && ($invoice['is_ecf'] ?? false);
$ecfType   = (int)($invoice['ecf_type'] ?? 32);
$docName   = $isQuote ? 'Cotización' : ($isEcf ? 'e-CF' : 'Factura');
$docNum    = $isQuote ? ($invoice['quote_number'] ?? '') : (!empty($invoice['encf']) ? $invoice['encf'] : ($invoice['invoice_number'] ?? ''));
$dateLabel = $isQuote ? 'Validez' : 'Vence';
$dateField = $isQuote ? ($invoice['expiry_date'] ?? $invoice['due_date'] ?? '') : ($invoice['due_date'] ?? '');

$showFechaVencimiento = $isEcf && !in_array($ecfType, [32, 34]);
$fechaVencimientoSeq  = $settings['dgii_ncf_expiry_date'] ?? '31/12/2028';
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaVencimientoSeq)) {
    $fechaVencimientoSeq = date('d/m/Y', strtotime($fechaVencimientoSeq));
}

$ecfTypeNames = [
    31 => 'Factura Crédito Fiscal Electrónica',
    32 => 'Factura Consumo Electrónica',
    33 => 'Nota de Débito Electrónica',
    34 => 'Nota de Crédito Electrónica',
    41 => 'Comprobante de Compras Electrónico',
    43 => 'Gastos Menores Electrónico',
    44 => 'Regímenes Especiales Electrónico',
    45 => 'Comprobante Gubernamental Electrónico',
    46 => 'Comprobante de Exportaciones',
    47 => 'Pagos al Exterior Electrónico',
    1  => 'Factura de Crédito Fiscal (B01)',
    2  => 'Factura de Consumo (B02)',
    4  => 'Nota de Crédito (B04)',
    14 => 'Regímenes Especiales (B14)',
    15 => 'Comprobante Gubernamental (B15)',
];
$ecfTypeName = $ecfTypeNames[$ecfType] ?? ($isEcf ? 'Comprobante Electrónico' : 'Recibo / Factura');

$badgeText = 'BORRADOR';
if (!empty($invoice['status'])) {
    if ($invoice['status'] === 'paid')    { $badgeText = 'PAGADA'; }
    elseif ($invoice['status'] === 'overdue' || $invoice['status'] === 'cancelled') { $badgeText = $invoice['status'] === 'cancelled' ? 'ANULADA' : 'VENCIDA'; }
    elseif ($invoice['status'] === 'sent' || $invoice['status'] === 'pending') { $badgeText = 'PENDIENTE'; }
    elseif ($invoice['status'] === 'partial') { $badgeText = 'PAGO PARCIAL'; }
}

$templateQuery = request()->query('template', $settings['invoice_pdf_template'] ?? 'thermal');
$is58mm = (str_contains($templateQuery, '58mm') || (isset($paper_size) && $paper_size === '58mm'));

$paperWidth = $is58mm ? 48 : 72;
$marginSide = $is58mm ? 1.0 : 1.5;

$itemCount = count($invoice['items'] ?? $items ?? []);
$baseHeight = 85;

$itemsHeight = 0;
foreach (($invoice['items'] ?? $items ?? []) as $item) {
    $descLength = strlen($item['description'] ?? '');
    $charsPerLine = $is58mm ? 14 : 24;
    $lines = max(1, ceil($descLength / $charsPerLine));
    $itemsHeight += $lines * 4.5 + 4;
}

$totalsHeight = 15;
if (($invoice['discount_amount'] ?? 0) > 0) { $totalsHeight += 4; }
if (($invoice['tax_amount'] ?? 0) > 0) { $totalsHeight += 4; }
if (($invoice['amount_paid'] ?? 0) > 0) { $totalsHeight += 8; }
if (!empty($invoice['cash_received'])) { $totalsHeight += 8; }

$notesHeight = 0;
if (!empty($invoice['notes'])) {
    $notesHeight += ceil(strlen($invoice['notes']) / 30) * 4 + 6;
}

$dgiiHeight = 58;
$cutterClearance = 14;

$pageHeight = max(135, $baseHeight + $itemsHeight + $totalsHeight + $notesHeight + $dgiiHeight + $cutterClearance);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $isQuote ?? false ? 'Cotización' : 'Factura' }}</title>
    <style>
        @page {
            size: {{ $paperWidth }}mm {{ $pageHeight }}mm;
            margin: 0;
        }
        @media print {
            @page {
                size: {{ $paperWidth }}mm auto;
                margin: 0;
            }
            html, body {
                width: 100% !important;
                margin: 0 !important;
                padding: 1mm {{ $marginSide }}mm 10mm {{ $marginSide }}mm !important;
            }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier', 'DejaVu Sans Mono', monospace, sans-serif;
            font-size: {{ $is58mm ? '7pt' : '8pt' }};
            color: #000000;
            background: #FFFFFF;
            line-height: 1.3;
            margin: 2mm {{ $marginSide }}mm 12mm {{ $marginSide }}mm;
            -webkit-print-color-adjust: exact;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        .divider {
            border-top: 1px dashed #000000;
            margin: 5px 0;
        }

        /* ── HEADER ── */
        .company-name {
            font-size: {{ $is58mm ? '10pt' : '11pt' }};
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .company-info {
            font-size: {{ $is58mm ? '7pt' : '8pt' }};
            margin-bottom: 4px;
        }

        /* ── DOCUMENT INFO ── */
        .doc-title {
            font-size: {{ $is58mm ? '8.5pt' : '9.5pt' }};
            font-weight: bold;
            text-transform: uppercase;
            margin: 4px 0 2px 0;
        }
        .meta-info {
            font-size: {{ $is58mm ? '7.5pt' : '8pt' }};
            margin-bottom: 4px;
        }

        /* ── CLIENT INFO ── */
        .client-info {
            font-size: {{ $is58mm ? '7.5pt' : '8pt' }};
            margin-bottom: 5px;
        }

        /* ── TABLE ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }
        .items-table th {
            font-size: {{ $is58mm ? '7pt' : '8pt' }};
            font-weight: bold;
            border-bottom: 1px dashed #000000;
            padding: 3px 0;
            text-align: left;
        }
        .items-table td {
            font-size: {{ $is58mm ? '7.5pt' : '8pt' }};
            padding: 4px 0;
            vertical-align: top;
        }
        .items-table td.item-desc {
            word-wrap: break-word;
            max-width: {{ $is58mm ? '24mm' : '36mm' }};
        }

        /* ── TOTALS ── */
        .totals-table {
            width: 100%;
            margin-top: 5px;
        }
        .totals-table td {
            font-size: {{ $is58mm ? '7.5pt' : '8pt' }};
            padding: 2px 0;
        }
        .totals-table .label {
            text-align: right;
            padding-right: 6px;
        }
        .totals-table .val {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
        }
        .balance-row td {
            border-top: 1px dashed #000000;
            padding-top: 4px;
            font-size: {{ $is58mm ? '8.5pt' : '9.5pt' }};
        }

        /* ── FOOTER & QR ── */
        .qr-section {
            margin: 8px 0;
            text-align: center;
        }
        .qr-section img {
            width: {{ $is58mm ? '26mm' : '30mm' }};
            height: {{ $is58mm ? '26mm' : '30mm' }};
            display: inline-block;
        }
        .security-info {
            font-size: {{ $is58mm ? '7pt' : '7.5pt' }};
            margin-top: 4px;
            line-height: 1.4;
        }
        .footer-thanks {
            font-size: {{ $is58mm ? '7.5pt' : '8.5pt' }};
            margin-top: 8px;
            text-transform: uppercase;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div style="height: 2mm; width: 100%;"></div>

<!-- ── EMISOR (COMPANY) ── -->
<div class="text-center">
    @if(!empty($settings['company_logo']) || !empty($settings['pdf_logo_url']))
        <div style="margin-bottom: 4px;">
            <img src="{{ getMonochromeLogo($settings['company_logo'] ?: $settings['pdf_logo_url']) }}" style="max-width: 40mm; max-height: 12mm; display: inline-block; object-fit: contain;" alt="Logo">
        </div>
    @endif
    <div class="company-name">{{ $company['name'] ?? 'Academic+' }}</div>
    @if(!empty($settings['dgii_razon_social']) && $settings['dgii_razon_social'] !== ($company['name'] ?? ''))
        <div class="company-info">{{ $settings['dgii_razon_social'] }}</div>
    @endif
    <div class="company-info">
        @if(!empty($company['tax_id']))
            RNC: {{ $company['tax_id'] }}<br>
        @endif
        @if(!empty($company['address']))
            {{ $company['address'] }}<br>
        @endif
        @if(!empty($company['phone']))
            Tel: {{ $company['phone'] }}<br>
        @endif
        @if(!empty($company['email']))
            Email: {{ $company['email'] }}
        @endif
    </div>
</div>

<div class="divider"></div>

<!-- ── DOCUMENT INFO ── -->
<div class="text-center">
    <div class="doc-title">{{ strtoupper($ecfTypeName) }}</div>
    <div class="meta-info">
        <span class="bold">{{ $isEcf ? 'e-NCF' : 'Comprobante' }}:</span> {{ $docNum }}<br>
        @if(!empty($invoice['invoice_number']) && $invoice['invoice_number'] !== $docNum)
            <span class="bold">Recibo No:</span> {{ $invoice['invoice_number'] }}<br>
        @endif
        <span class="bold">Fecha Emisión:</span> {{ !empty($invoice['issue_date']) ? date('d-m-Y', strtotime($invoice['issue_date'])) : date('d-m-Y') }}<br>
        @if(!empty($invoice['cashier']))
            <span class="bold">Cajero(a):</span> {{ $invoice['cashier'] }}<br>
        @endif
        @if($showFechaVencimiento)
            <span class="bold">Vence Secuencia:</span> {{ $fechaVencimientoSeq }}<br>
        @endif
        @if(in_array($ecfType, [33, 34, 3, 4]) && !empty($invoice['modified_ncf']))
            <span class="bold">NCF Modificado:</span> {{ $invoice['modified_ncf'] }}<br>
        @endif
        <span class="bold">Estado:</span> {{ $badgeText }}
    </div>
</div>

<div class="divider"></div>

<!-- ── RECEPTOR (CLIENT) ── -->
<?php
$clientName = trim($client['company_name'] ?? '') ?: trim($client['contact_name'] ?? '');
$clientRnc = trim($client['tax_id'] ?? '');
$clientCode = trim($client['student_code'] ?? '');
$hasComprador = !in_array($ecfType, [43, 47]);
?>
@if($hasComprador && (!empty($clientName) || !empty($clientRnc)))
<div class="client-info">
    <span class="bold">CLIENTE:</span> {{ $clientName ?: 'Cliente General' }}<br>
    @if(!empty($clientRnc))
        <span class="bold">RNC/CÉDULA:</span> {{ $clientRnc }}<br>
    @endif
    @if(!empty($clientCode))
        <span class="bold">MATRÍCULA:</span> {{ $clientCode }}<br>
    @endif
</div>
<div class="divider"></div>
@endif

<!-- ── ITEMS ── -->
<?php $taxRate = (float)($invoice['tax_rate'] ?? 0); ?>
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 10%;">CANT</th>
            <th style="width: 44%;">DESC</th>
            <th style="width: 23%; text-align: right;">PRECIO</th>
            <th style="width: 23%; text-align: right;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
        @foreach (($invoice['items'] ?? $items ?? []) as $item)
            <?php
                $itemAmount = (float)($item['amount'] ?? ($item['quantity'] * $item['unit_price']));
            ?>
            <tr>
                <td style="white-space: nowrap;">{{ number_format($item['quantity'], 1) }}</td>
                <td class="item-desc">{{ $item['description'] }}</td>
                <td class="text-right" style="white-space: nowrap;">{{ number_format($item['unit_price'], 2) }}</td>
                <td class="text-right bold" style="white-space: nowrap;">{{ number_format($itemAmount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="divider"></div>

<!-- ── TOTALS ── -->
<table class="totals-table">
    <tr>
        <td class="label">SUBTOTAL</td>
        <td class="val">RD$ {{ number_format($invoice['subtotal'] ?? 0, 2) }}</td>
    </tr>
    @if (($invoice['discount_amount'] ?? 0) > 0)
    <tr>
        <td class="label">DESCUENTO</td>
        <td class="val">-RD$ {{ number_format($invoice['discount_amount'], 2) }}</td>
    </tr>
    @endif
    @if (($invoice['tax_amount'] ?? 0) > 0)
    <tr>
        <td class="label">ITBIS ({{ number_format($taxRate) }}%)</td>
        <td class="val">RD$ {{ number_format($invoice['tax_amount'], 2) }}</td>
    </tr>
    @endif
    <tr class="balance-row">
        <td class="label bold">TOTAL</td>
        <td class="val">RD$ {{ number_format($invoice['total'] ?? 0, 2) }}</td>
    </tr>
    @if(!empty($invoice['gateway']))
    <tr>
        <td class="label">MÉTODO PAGO</td>
        <td class="val">{{ $invoice['gateway'] }}</td>
    </tr>
    @endif
    @if(!empty($invoice['cash_received']))
    <tr>
        <td class="label">EFECTIVO RECIBIDO</td>
        <td class="val">RD$ {{ number_format((float)$invoice['cash_received'], 2) }}</td>
    </tr>
    <tr>
        <td class="label">CAMBIO / DEVUELTA</td>
        <td class="val">RD$ {{ number_format((float)($invoice['change_amount'] ?? 0), 2) }}</td>
    </tr>
    @endif
</table>

<!-- ── NOTES ── -->
@if(!empty($invoice['notes']))
    <div class="divider"></div>
    <div class="bold" style="font-size: 8px;">Notas:</div>
    <div style="font-size: 7.5px; padding: 2px 0;">{{ $invoice['notes'] }}</div>
@endif

<!-- ── DGII QR & SECURITY CODE ── -->
<?php
$rncEmisor = preg_replace('/[^0-9]/', '', $settings['company_tax_id'] ?? '');
$rncComprador = preg_replace('/[^0-9]/', '', $client['tax_id'] ?? '');
$encf = !empty($invoice['encf']) ? $invoice['encf'] : ($invoice['invoice_number'] ?? '');
$monto = number_format((float)($invoice['total'] ?? 0), 2, '.', '');
$fechaFirma = !empty($invoice['signed_at'])
    ? date('d-m-Y H:i:s', strtotime($invoice['signed_at']))
    : date('d-m-Y H:i:s');
$fechaEmision = !empty($invoice['issue_date']) ? date('d-m-Y', strtotime($invoice['issue_date'])) : date('d-m-Y');
$codSeguridad = !empty($invoice['security_code'])
    ? $invoice['security_code']
    : strtoupper(substr(hash('sha256', $rncEmisor . '|' . $encf . '|' . $monto), 0, 6));

if (!empty($invoice['qr_url'])) {
    $qrUrl = $invoice['qr_url'];
} else {
    $dgiiEnv = $settings['dgii_env'] ?? 'testing';
    $ecfQrPath = match($dgiiEnv) { 'production' => 'ecf', 'certification' => 'certecf', default => 'testecf' };
    $isRfce = $ecfType === 32 && (float)($invoice['total'] ?? 0) < 250000;
    $hasCompradorQr = !in_array($ecfType, [43, 47]) && (strlen($rncComprador) === 9 || strlen($rncComprador) === 11);

    if ($isRfce) {
        $qrUrl = "https://fc.dgii.gov.do/{$ecfQrPath}/ConsultaTimbreFC?"
            . "RncEmisor={$rncEmisor}"
            . "&ENCF={$encf}"
            . "&MontoTotal={$monto}"
            . "&CodigoSeguridad=" . urlencode($codSeguridad);
    } else {
        $qrUrl = "https://ecf.dgii.gov.do/{$ecfQrPath}/ConsultaTimbre?"
            . "RncEmisor={$rncEmisor}";
        if ($hasCompradorQr) {
            $qrUrl .= "&RncComprador={$rncComprador}";
        }
        $qrUrl .= "&ENCF={$encf}"
            . "&FechaEmision={$fechaEmision}"
            . "&MontoTotal={$monto}"
            . "&FechaFirma=" . urlencode($fechaFirma)
            . "&CodigoSeguridad=" . urlencode($codSeguridad);
    }
}

$qrImgSrc = '';
if (class_exists('\chillerlan\QRCode\QRCode')) {
    try {
        $qrOptions = new \chillerlan\QRCode\QROptions();
        if (defined('\chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG')) {
            $qrOptions->outputType = \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG;
        }
        $qrOptions->outputInterface = \chillerlan\QRCode\Output\QRGdImagePNG::class;
        $qrOptions->scale = 4;
        $qrOptions->quietzoneSize = 1;
        $qrOptions->outputBase64 = true;
        $qrImgSrc = (new \chillerlan\QRCode\QRCode($qrOptions))->render($qrUrl);
    } catch (\Throwable $e) {
        $qrImgSrc = '';
    }
}
if (empty($qrImgSrc)) {
    $qrImgSrc = "https://quickchart.io/qr?text=" . urlencode($qrUrl) . "&size=120&margin=1&format=png";
}
?>
<div class="divider"></div>
<div class="qr-section">
    <img src="<?= $qrImgSrc ?>" alt="QR DGII">
    <div class="security-info">
        <span class="bold">Código de Seguridad:</span> {{ $codSeguridad }}<br>
        <span class="bold">Fecha de Firma:</span> {{ $fechaFirma }}<br>
        <div style="font-size: 6.5px; margin-top: 3px; color: #555;">Representación impresa de e-CF</div>
    </div>
</div>

<div class="divider"></div>

<div class="text-center footer-thanks">
    ¡Gracias por su pago!<br>
    <span style="font-size: 7px; font-weight: normal; text-transform: none;">Powered by Gridbase Bills</span>
</div>
<div style="height: 6mm; width: 100%;"></div>

</body>
</html>
