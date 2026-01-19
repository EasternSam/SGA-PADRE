<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado - {{ $folio ?? 'Vista Previa' }}</title>
    
    <style>
        /* =========================================
           FUENTES
           ========================================= */
        @font-face {
            font-family: 'Cinzel Decorative';
            font-style: normal;
            font-weight: 700;
            src: url('https://fonts.gstatic.com/s/cinzeldecorative/v16/daaHSScvJGqLYhG8nNt8KPPswUAPni7TTd1u8kY.ttf') format('truetype');
        }
        
        @font-face {
            font-family: 'EB Garamond';
            font-style: normal;
            font-weight: 400;
            src: url('https://fonts.gstatic.com/s/ebgaramond/v26/SlGDmQSNjdsmc35JDF1K5E55YMjF_7DPuQ.ttf') format('truetype');
        }

        @font-face {
            font-family: 'Pinyon Script';
            font-style: normal;
            font-weight: 400;
            src: url('https://fonts.gstatic.com/s/pinyonscript/v17/6xKtdSZaM9PENbF29TaCNqsPPlWZ58M.ttf') format('truetype');
        }

        /* =========================================
           CONFIGURACIÓN DE PÁGINA
           ========================================= */
        @page {
            margin: 0cm;
            size: 297mm 210mm; /* A4 Landscape */
        }
        
        body {
            font-family: 'EB Garamond', serif;
            color: #1f2937;
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            background-color: #ffffff;
        }

        /* =========================================
           ESTRUCTURA
           ========================================= */
        .page-container {
            width: 297mm;
            height: 210mm;
            position: relative;
            box-sizing: border-box;
            background-color: #ffffff;
            overflow: hidden;
        }

        /* Fondo decorativo sutil (opcional, moderno y limpio) */
        .bg-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 600px;
            height: 600px;
            opacity: 0.03;
            z-index: 1;
            pointer-events: none;
        }

        /* Marcos Modernos */
        .frame-outer {
            position: absolute;
            top: 10mm;
            left: 10mm;
            right: 10mm;
            bottom: 10mm;
            border: 1px solid #1f2937; /* Marco fino oscuro */
            z-index: 10;
        }

        .frame-inner {
            position: absolute;
            top: 13mm;
            left: 13mm;
            right: 13mm;
            bottom: 13mm;
            border: 2px solid #c5a059; /* Dorado elegante */
            z-index: 10;
        }

        /* Contenido Central */
        .content-wrapper {
            position: absolute;
            top: 20mm;
            left: 20mm;
            right: 20mm;
            bottom: 20mm;
            text-align: center;
            z-index: 30;
            display: table; /* Hack para centrado vertical en domPDF si fuera necesario, pero usaremos margin */
            width: 90%; /* Ajuste relativo al contenedor padre */
            margin: 0 auto;
        }

        /* =========================================
           TIPOGRAFÍA Y ELEMENTOS
           ========================================= */
        
        .logo-container {
            height: 70px;
            margin-bottom: 25px;
            margin-top: 10px;
        }

        .header-title {
            font-family: 'Cinzel Decorative', serif;
            font-size: 42pt;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 12px; /* Espaciado moderno */
            line-height: 1;
            margin-bottom: 5px;
        }

        .sub-title {
            font-family: 'EB Garamond', serif;
            font-size: 13pt;
            color: #c5a059; /* Dorado */
            text-transform: uppercase;
            letter-spacing: 4px;
            margin-bottom: 35px;
            font-weight: 600;
        }

        .presented-to {
            font-size: 14pt;
            font-style: italic;
            color: #4b5563;
            margin-bottom: 5px;
        }

        .student-name {
            font-family: 'Pinyon Script', cursive;
            font-size: 64pt;
            color: #1f2937; /* Nombre oscuro para contraste moderno */
            line-height: 1.1;
            margin: 10px 0 15px 0;
            padding: 0 20px;
        }

        /* Línea decorativa minimalista */
        .separator-line {
            height: 1px;
            width: 150px;
            background: #c5a059;
            margin: 0 auto 25px auto;
            position: relative;
        }
        
        /* Diamante central en la línea */
        .separator-diamond {
            width: 6px;
            height: 6px;
            background: #c5a059;
            transform: rotate(45deg);
            position: absolute;
            top: -2.5px;
            left: 50%;
            margin-left: -3px;
        }

        .course-text {
            font-size: 14pt;
            color: #4b5563;
            margin-bottom: 15px;
            line-height: 1.6;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .course-name {
            font-family: 'Cinzel Decorative', serif; /* Cambio a fuente decorativa para el curso */
            font-size: 22pt;
            font-weight: 700;
            color: #c5a059;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 20px;
        }

        .date-text {
            font-family: 'EB Garamond', serif;
            font-size: 11pt;
            font-style: italic;
            color: #6b7280;
            margin-top: 5px;
        }

        /* =========================================
           FIRMAS (Tabla)
           ========================================= */
        .footer-table {
            width: 100%;
            margin-top: 45px;
            border-collapse: collapse;
        }
        
        .footer-table td {
            vertical-align: bottom;
            text-align: center;
            padding: 0 20px;
        }

        .signature-box {
            border-top: 1px solid #d1d5db; /* Gris muy claro */
            padding-top: 12px;
            width: 85%;
            margin: 0 auto;
        }

        .sign-role {
            font-family: 'EB Garamond', serif;
            font-size: 10pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: #1f2937;
            margin-bottom: 2px;
        }
        
        .sign-dept {
            font-size: 9pt;
            color: #9ca3af;
            font-style: italic;
        }

        /* QR Estilizado */
        .qr-wrapper {
            padding: 8px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            display: inline-block;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        /* Folio Footer */
        .folio-footer {
            position: absolute;
            bottom: 16mm; /* Ajustado para estar dentro del marco */
            width: 100%;
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
            letter-spacing: 3px;
            font-family: sans-serif;
            text-transform: uppercase;
            z-index: 40;
        }
        
        /* Utilidad para debug de layout */
        /* table, td { border: 1px solid red; } */
    </style>
</head>
<body>
    @php
        // Lógica de usuario preservada exactamente como el original
        $user = $student->user ?? null;
        $studentName = 'N/A';
        
        if ($student ?? false) {
            $first = $student->first_name ?? $student->name ?? $student->nombres ?? $student->firstname ?? $user->first_name ?? $user->name ?? '';
            $last = $student->last_name ?? $student->apellidos ?? $student->lastname ?? $user->last_name ?? $user->lastname ?? '';
            $studentName = trim($first . ' ' . $last);
            
            if (empty($studentName)) {
                $studentName = $student->full_name ?? $student->fullname ?? $user->full_name ?? $user->fullname ?? '';
            }
            
            if (empty($studentName) && $student) {
                 $studentName = $student->email ?? $user->email ?? 'Sin Nombre';
            }
        }
    @endphp

    <div class="page-container">
        <!-- Marcos Estilizados -->
        <div class="frame-outer"></div>
        <div class="frame-inner"></div>
        
        <!-- Marca de agua sutil (Logo SVG o texto) -->
        <div class="bg-watermark">
            <!-- Si tienes un SVG de logo se puede poner aquí con opacity baja, 
                 o dejar vacío para un look ultra limpio -->
        </div>

        <div class="content-wrapper">
            
            <!-- Logo -->
            <div class="logo-container">
                @if(file_exists(public_path('centuu.png')))
                    <img src="{{ public_path('centuu.png') }}" style="height: 100%; object-fit: contain;" alt="Logo">
                @else
                    <!-- Fallback más elegante -->
                    <div style="font-size: 32pt; font-family: 'Cinzel Decorative'; color: #c5a059; line-height: 1;">SGA</div>
                @endif
            </div>

            <!-- Cabecera -->
            <div class="sub-title">Centro de Tecnología Universal</div>
            <div class="header-title">Certificado</div>

            <!-- Cuerpo Principal -->
            <div style="margin-top: 30px;">
                <div class="presented-to">Se otorga el presente reconocimiento a</div>
                
                <div class="student-name">{{ $studentName }}</div>
                
                <div class="separator-line">
                    <div class="separator-diamond"></div>
                </div>

                <div class="course-text">
                    Por haber completado satisfactoriamente los requisitos académicos<br>
                    y demostrado excelencia en el programa de:
                </div>

                <div class="course-name">{{ $course->name ?? 'Nombre del Curso' }}</div>

                <div class="date-text">
                    Expedido el día {{ $date ?? date('d/m/Y') }}
                </div>
            </div>

            <!-- Sección Inferior: Firmas y QR -->
            <table class="footer-table">
                <tr>
                    <td width="35%">
                        <div style="height: 50px; margin-bottom: 5px;">
                            <!-- Espacio para imagen de firma digital Director -->
                        </div>
                        <div class="signature-box">
                            <div class="sign-role">{{ $director_name ?? 'Dirección Académica' }}</div>
                            <div class="sign-dept">Autoridad Certificadora</div>
                        </div>
                    </td>
                    
                    <td width="30%">
                        <div class="qr-wrapper">
                            @if(isset($qr_code_url))
                                <img src="{{ $qr_code_url }}" alt="Validación" style="width: 70px; height: 70px; display: block;">
                            @else
                                <div style="width: 70px; height: 70px; background: #f9fafb; display: flex; align-items: center; justify-content: center; color: #d1d5db; font-size: 9px;">QR CODE</div>
                            @endif
                        </div>
                        <div style="margin-top: 8px; font-size: 6pt; color: #c5a059; text-transform: uppercase; letter-spacing: 2px;">
                            Validación Oficial
                        </div>
                    </td>

                    <td width="35%">
                        <div style="height: 50px; margin-bottom: 5px;">
                            <!-- Espacio para imagen de firma digital Secretaría -->
                        </div>
                        <div class="signature-box">
                            <div class="sign-role">Secretaría General</div>
                            <div class="sign-dept">Registro Oficial</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="folio-footer">
            FOLIO: {{ $folio ?? 'PENDIENTE' }} &nbsp;|&nbsp; DOCUMENTO OFICIAL DE VALIDACIÓN
        </div>
    </div>
</body>
</html>