<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Reporte Oficial')</title>
    <style>
        /* CSS Reset & Standards for DOMpdf */
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            color: #333;
            background: white;
            margin: 0;
            padding: 0;
        }

        /* 1CM Margins as requested */
        @page {
            margin: 1cm;
        }

        /* Header Layout */
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        
        .header-logo-cell {
            width: 180px;
            vertical-align: middle;
            text-align: left;
        }
        
        .header-text-cell {
            text-align: right;
            vertical-align: middle;
        }
        
        .logo {
            width: 150px;
            height: auto;
        }
        
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 12pt;
            margin: 5px 0 0 0;
            color: #374151;
        }
        
        .header p {
            font-size: 10pt;
            margin: 5px 0 0 0;
            color: #374151;
        }

        /* Global Table Styles */
        .data-table, table.list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .data-table th, .data-table td,
        table.list-table th, table.list-table td {
            border: 1px solid #cbd5e0;
            padding: 6px;
            vertical-align: middle;
        }
        
        .data-table th, table.list-table th {
            background-color: #f7fafc;
            color: #2d3748;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            text-align: left;
        }

        /* Utilities */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: bold; }
        
        .text-green { color: #166534; }
        .text-red { color: #991b1b; }
        .text-gray { color: #718096; }
        
        /* Totals / Summary Rows */
        .totals-row td, .summary-row td {
            background-color: #edf2f7;
            font-weight: bold;
            border-top: 2px solid #4a5568 !important;
        }

        /* Footer */
        .meta-info {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            font-size: 7pt;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
            text-align: left;
        }
        
        .page-number {
            float: right;
        }
        
        /* Magic for DOMpdf page numbering */
        .page-number:before {
            content: "Página " counter(page) " de " counter(pages);
        }

        /* Prevent page drops inside table rows */
        tr { page-break-inside: avoid; }
        
        /* Additional Custom Styling yield */
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
                    $logoPath = public_path('centuu.png'); // Fallback por defecto
                    
                    if ($logo) {
                        if (\Illuminate\Support\Str::startsWith($logo, 'http')) {
                            $logoPath = $logo;
                        } elseif (file_exists(public_path('storage/' . $logo))) {
                            $logoPath = public_path('storage/' . $logo);
                        } elseif (file_exists(public_path($logo))) {
                            $logoPath = public_path($logo);
                        }
                    }

                    // DOMpdf maneja las imágenes de forma mucho más confiable usando Base64
                    $base64 = $logoPath;
                    if (file_exists($logoPath)) {
                        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                        $data = file_get_contents($logoPath);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    }
                @endphp
                <img src="{{ $base64 }}" class="logo" alt="Logo Institucional">
            </td>
            <td class="header-text-cell">
                <div class="header">
                    <h1>{{ \App\Models\Setting::get('institution_name', 'SGA-CENTU') }}</h1>
                    <h2>@yield('title', 'Reporte')</h2>
                    <p>@yield('subtitle', 'Documento Oficial')</p>
                </div>
            </td>
        </tr>
    </table>

    <!-- Main Content -->
    <div class="content">
        @yield('content')
        
        {{-- Generic Fallback Content If Used From Livewire Export --}}
        @if(isset($html))
            {!! $html !!}
        @endif
    </div>

    <!-- Footer Page Numbers and Timestamp -->
    <div class="meta-info">
        Generado el: {{ now()->format('d/m/Y h:i A') }} | Documento Confidencial
        <span class="page-number"></span>
    </div>

</body>
</html>
