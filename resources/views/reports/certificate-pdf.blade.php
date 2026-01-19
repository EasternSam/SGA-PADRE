<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado - {{ $folio ?? 'Vista Previa' }}</title>
    
    <style>
        /* FUENTES WEB */
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

        /* Configuración de Página A4 Horizontal (Landscape) */
        @page {
            margin: 0cm;
            size: 297mm 210mm;
        }
        
        body {
            font-family: 'EB Garamond', serif;
            color: #1f2937; /* Gray 800 */
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            background-color: #fff;
        }

        /* Contenedor Principal */
        .page-container {
            width: 297mm;
            height: 210mm;
            position: absolute;
            top: 0;
            left: 0;
            /* Fondo sutil tipo papel */
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            background-repeat: repeat;
        }

        /* Marcos y Bordes */
        .border-frame {
            position: absolute;
            top: 12mm;
            left: 12mm;
            right: 12mm;
            bottom: 12mm;
            border: 2px solid #b49b5a; /* Dorado */
            z-index: 10;
        }

        .border-frame-inner {
            position: absolute;
            top: 14mm;
            left: 14mm;
            right: 14mm;
            bottom: 14mm;
            border: 1px solid #e5e7eb; /* Gris claro */
            z-index: 10;
        }

        /* Decoración de esquinas */
        .corner-accent {
            position: absolute;
            width: 40px;
            height: 40px;
            border-color: #1f2937; /* Dark Gray */
            z-index: 20;
        }
        .c-tl { top: 12mm; left: 12mm; border-top: 4px solid #1f2937; border-left: 4px solid #1f2937; }
        .c-tr { top: 12mm; right: 12mm; border-top: 4px solid #1f2937; border-right: 4px solid #1f2937; }
        .c-bl { bottom: 12mm; left: 12mm; border-bottom: 4px solid #1f2937; border-left: 4px solid #1f2937; }
        .c-br { bottom: 12mm; right: 12mm; border-bottom: 4px solid #1f2937; border-right: 4px solid #1f2937; }

        /* Contenido */
        .content-wrapper {
            position: absolute;
            top: 20mm;
            left: 20mm;
            right: 20mm;
            bottom: 20mm;
            text-align: center;
            z-index: 30;
        }

        /* Tipografía */
        .header-title {
            font-family: 'Cinzel Decorative', serif;
            font-size: 36pt;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 8px;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .sub-title {
            font-size: 14pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 30px;
        }

        .presented-to {
            font-size: 16pt;
            font-style: italic;
            color: #4b5563;
            margin-bottom: 10px;
        }

        .student-name {
            font-family: 'Pinyon Script', cursive;
            font-size: 58pt;
            color: #b49b5a; /* Dorado */
            line-height: 1;
            margin: 10px 0 20px 0;
            text-shadow: 1px 1px 0px rgba(0,0,0,0.1);
        }

        .separator-line {
            height: 1px;
            width: 60%;
            background: #b49b5a; /* Color sólido para PDF */
            margin: 0 auto 25px auto;
        }

        .course-text {
            font-size: 16pt;
            color: #374151;
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .course-name {
            font-family: 'EB Garamond', serif;
            font-size: 26pt;
            font-weight: bold;
            color: #111827;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Firmas */
        .footer-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        
        .footer-table td {
            vertical-align: bottom;
            text-align: center;
            padding: 0 15px;
        }

        .signature-box {
            border-top: 1px solid #9ca3af;
            padding-top: 10px;
            width: 80%;
            margin: 0 auto;
        }

        .sign-role {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #1f2937;
        }
        
        .sign-dept {
            font-size: 8pt;
            color: #6b7280;
        }

        .qr-container {
            padding: 5px;
            background: white;
            border: 1px solid #e5e7eb;
            display: inline-block;
        }

        .folio-footer {
            position: absolute;
            bottom: 5mm;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            letter-spacing: 2px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    @php
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
        <!-- Elementos decorativos del marco -->
        <div class="border-frame"></div>
        <div class="border-frame-inner"></div>
        
        <div class="corner-accent c-tl"></div>
        <div class="corner-accent c-tr"></div>
        <div class="corner-accent c-bl"></div>
        <div class="corner-accent c-br"></div>

        <div class="content-wrapper">
            
            <!-- Logo -->
            <div style="height: 60px; margin-bottom: 20px;">
                @if(file_exists(public_path('centuu.png')))
                    <img src="{{ public_path('centuu.png') }}" style="height: 100%; object-fit: contain;" alt="Logo">
                @else
                    <div style="font-size: 24pt; font-family: 'Cinzel Decorative'; color: #b49b5a;">SGA</div>
                @endif
            </div>

            <!-- Títulos -->
            <div class="header-title">CERTIFICADO</div>
            <div class="sub-title">Centro de Tecnología Universal</div>

            <!-- Cuerpo -->
            <div class="presented-to">Otorga el presente reconocimiento a</div>
            
            <div class="student-name">{{ $studentName }}</div>
            
            <div class="separator-line"></div>

            <div class="course-text">
                Por haber completado satisfactoriamente los requisitos académicos<br>
                y demostrado excelencia en el programa de:
            </div>

            <div class="course-name">{{ $course->name ?? 'Nombre del Curso' }}</div>

            <div class="course-text" style="font-size: 12pt; margin-top: 5px; color: #6b7280;">
                Expedido el día {{ $date ?? date('d/m/Y') }}
            </div>

            <!-- Firmas y QR -->
            <table class="footer-table">
                <tr>
                    <td width="35%">
                        <div style="height: 40px;">
                            <!-- Espacio firma director -->
                        </div>
                        <div class="signature-box">
                            <div class="sign-role">{{ $director_name ?? 'Lic. Cielo Reynoso' }}</div>
                            <div class="sign-dept">Autoridad Certificadora</div>
                        </div>
                    </td>
                    
                    <td width="30%">
                        <div class="qr-container">
                            @if(isset($qr_code_url))
                                <img src="{{ $qr_code_url }}" alt="Validación" style="width: 80px; height: 80px; display: block;">
                            @else
                                <div style="width: 80px; height: 80px; background: #f3f4f6;"></div>
                            @endif
                        </div>
                        <div style="margin-top: 5px; font-size: 7pt; color: #b49b5a; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;">
                            Validación Digital Única
                        </div>
                    </td>

                    <td width="35%">
                        <div style="height: 40px;">
                            <!-- Espacio firma secretaría -->
                        </div>
                        <div class="signature-box">
                            <div class="sign-role">Vice-Rector</div>
                            <div class="sign-dept">Registro Oficial</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="folio-footer">
            FOLIO: {{ $folio ?? 'PENDIENTE' }} • DOCUMENTO OFICIAL DE VALIDACIÓN
        </div>
    </div>
</body>
</html>