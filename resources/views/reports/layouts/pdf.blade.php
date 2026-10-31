<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Reporte Oficial')</title>
    @php
        $primaryColor = \App\Models\Setting::get('brand_primary_color', \App\Support\BrandingDefaults::PRIMARY_COLOR) ?: '#211170';
        $secondaryColor = \App\Models\Setting::get('brand_secondary_color', \App\Support\BrandingDefaults::SECONDARY_COLOR) ?: '#1a0a53';
        $accentColor = \App\Models\Setting::get('brand_accent_color', \App\Support\BrandingDefaults::ACCENT_COLOR) ?: '#f7931e';
        $institutionName = \App\Models\Setting::get('institution_name', \App\Support\BrandingDefaults::INSTITUTION_NAME) ?: 'Academic+';
        $institutionRnc = \App\Models\Setting::get('institution_rnc', '');
        $institutionPhone = \App\Models\Setting::get('institution_phone', '');
        $institutionAddress = \App\Models\Setting::get('institution_address', '');
    @endphp
    <style>
        /* CSS Reset & Standards for DomPDF */
        @page {
            margin: 1.1cm 1.1cm 1.4cm 1.1cm;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 0;
            line-height: 1.35;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-bottom: 2.5px solid {{ $primaryColor }};
            padding-bottom: 10px;
        }

        .header-logo-cell {
            width: 35%;
            vertical-align: middle;
            text-align: left;
            padding-bottom: 8px;
        }

        .header-text-cell {
            width: 65%;
            text-align: right;
            vertical-align: middle;
            padding-bottom: 8px;
        }

        .logo {
            max-width: 155px;
            max-height: 54px;
            height: auto;
        }

        .inst-meta {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 3px;
        }

        .header h1 {
            font-size: 13pt;
            font-weight: bold;
            color: {{ $primaryColor }};
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .header h2 {
            font-size: 10.5pt;
            font-weight: bold;
            margin: 3px 0 0 0;
            color: #0f172a;
            text-transform: uppercase;
        }

        .header p {
            font-size: 8pt;
            margin: 3px 0 0 0;
            color: #475569;
        }

        /* KPI Summary Boxes */
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 14px -8px;
        }

        .kpi-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid {{ $primaryColor }};
            border-radius: 4px;
            padding: 8px 10px;
            vertical-align: top;
        }

        .kpi-card.accent {
            border-left-color: {{ $accentColor }};
        }

        .kpi-card.success {
            border-left-color: #059669;
        }

        .kpi-label {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.3px;
        }

        .kpi-value {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
        }

        /* Global Table Styles */
        .data-table, table.list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .data-table th, .data-table td,
        table.list-table th, table.list-table td {
            border: 1px solid #cbd5e1;
            padding: 5.5px 7px;
            vertical-align: middle;
        }

        .data-table th, table.list-table th {
            background-color: {{ $primaryColor }};
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.3px;
            text-align: left;
            border-color: {{ $primaryColor }};
        }

        .data-table tbody tr:nth-child(even),
        table.list-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7pt;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-neutral {
            background-color: #f1f5f9;
            color: #334155;
        }

        /* Utilities */
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .text-left { text-align: left !important; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: bold; }

        .text-green { color: #166534; }
        .text-red { color: #991b1b; }
        .text-gray { color: #64748b; }

        /* Totals / Summary Rows */
        .totals-row td, .summary-row td {
            background-color: #e2e8f0 !important;
            color: #0f172a;
            font-weight: bold;
            border-top: 2px solid {{ $primaryColor }} !important;
        }

        /* Signature Section */
        .signatures-table {
            width: 100%;
            margin-top: 38px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .signature-cell {
            width: 45%;
            text-align: center;
            vertical-align: bottom;
            padding: 0 20px;
        }

        .signature-line {
            border-top: 1px solid #334155;
            padding-top: 5px;
            font-size: 8pt;
            font-weight: bold;
            color: #1e293b;
        }

        .signature-sub {
            font-size: 7pt;
            color: #64748b;
            margin-top: 1px;
        }

        /* Footer */
        .meta-info {
            position: fixed;
            bottom: -22px;
            left: 0px;
            right: 0px;
            font-size: 7pt;
            color: #64748b;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            text-align: left;
        }

        .page-number {
            float: right;
            font-weight: bold;
        }

        .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
        }

        tr { page-break-inside: avoid; }

        @yield('styles')
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td class="header-logo-cell">
                @php
                    $logo = \App\Models\Setting::get('institution_logo');
                    $logoPath = public_path('branding/academic-logo.png');

                    if ($logo) {
                        if (\Illuminate\Support\Str::startsWith($logo, 'http')) {
                            $logoPath = $logo;
                        } elseif (file_exists(public_path('storage/' . $logo))) {
                            $logoPath = public_path('storage/' . $logo);
                        } elseif (file_exists(public_path(ltrim($logo, '/')))) {
                            $logoPath = public_path(ltrim($logo, '/'));
                        }
                    }

                    $base64 = null;
                    if ($logoPath && !str_starts_with($logoPath, 'http') && file_exists($logoPath)) {
                        $type = pathinfo($logoPath, PATHINFO_EXTENSION) ?: 'png';
                        $data = file_get_contents($logoPath);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                @endphp
                @if($base64)
                    <img src="{{ $base64 }}" class="logo" alt="Logo Institucional">
                @else
                    <div style="font-size: 15pt; font-weight: bold; color: {{ $primaryColor }};">{{ $institutionName }}</div>
                @endif
                <div class="inst-meta">
                    @if($institutionRnc) RNC: {{ $institutionRnc }} @endif
                    @if($institutionPhone) {{ $institutionRnc ? '·' : '' }} Tel: {{ $institutionPhone }} @endif
                    @if($institutionAddress) <br>{{ $institutionAddress }} @endif
                </div>
            </td>
            <td class="header-text-cell">
                <div class="header">
                    <h1>{{ $institutionName }}</h1>
                    <h2>@yield('title', 'Reporte Oficial')</h2>
                    <p>@yield('subtitle', 'Documento Oficial del Sistema de Gestión Académica')</p>
                </div>
            </td>
        </tr>
    </table>

    <!-- Main Content -->
    <div class="content">
        @yield('content')

        @if(isset($html))
            {!! $html !!}
        @endif
    </div>

    <!-- Footer Page Numbers and Timestamp -->
    <div class="meta-info">
        Emitido el: {{ now()->format('d/m/Y h:i A') }}
        @if(auth()->check())
            · Generado por: {{ auth()->user()->name }}
        @endif
        · {{ $institutionName }} (Documento Oficial)
        <span class="page-number"></span>
    </div>

</body>
</html>
