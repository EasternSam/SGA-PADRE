<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado - {{ $folio ?? 'Vista Previa' }}</title>
    
    <style>
        /* FUENTES: Usamos .ttf directos para mejor compatibilidad con DomPDF */
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

        /* Configuración de Página A4 Horizontal Exacta */
        @page {
            margin: 0cm;
            size: 297mm 210mm; /* Tamaño A4 Landscape exacto */
        }
        
        body {
            font-family: 'EB Garamond', serif;
            color: #1a202c;
            margin: 0;
            padding: 0;
            width: 297mm;
            height: 210mm;
            background-color: #fdfbf7; /* Color de fondo pergamino */
        }

        /* Contenedor Principal: Define el área segura de impresión */
        .page-container {
            width: 277mm; /* 297mm - 10mm margen izq - 10mm margen der */
            height: 190mm; /* 210mm - 10mm margen arr - 10mm margen aba */
            position: absolute;
            top: 10mm;
            left: 10mm;
            /* Fondo de ruido/pergamino */
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.05'/%3E%3C/svg%3E");
        }

        /* Marcos */
        .outer-border {
            width: 100%;
            height: 100%;
            border: 5px double #1a202c; /* Doble línea oscura */
            padding: 5px;
            box-sizing: border-box;
        }

        .inner-border {
            width: 100%;
            height: 100%;
            border: 2px solid #b49b5a; /* Línea dorada */
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            text-align: center;
        }

        /* Decoraciones de Esquinas (CSS Puro) */
        .corner {
            position: absolute;
            width: 30px;
            height: 30px;
            border-color: #b49b5a;
            border-style: solid;
            opacity: 0.7;
        }
        .top-left { top: 4px; left: 4px; border-width: 3px 0 0 3px; }
        .top-right { top: 4px; right: 4px; border-width: 3px 3px 0 0; }
        .bottom-left { bottom: 4px; left: 4px; border-width: 0 0 3px 3px; }
        .bottom-right { bottom: 4px; right: 4px; border-width: 0 3px 3px 0; }

        /* Tipografía */
        .diploma-font { font-family: 'Cinzel Decorative', serif; }
        .script-font { font-family: 'Pinyon Script', cursive; }
        
        .header-title {
            font-size: 42pt; /* Usar pt es más seguro para impresión */
            color: #1a202c;
            letter-spacing: 3px;
            margin: 5px 0;
            text-transform: uppercase;
            line-height: 1;
        }

        .student-name {
            font-size: 48pt;
            color: #1a202c;
            border-bottom: 1px solid #cbd5e0;
            display: inline-block;
            padding: 0 40px 5px 40px;
            margin: 15px 0;
            line-height: 1.2;
        }

        /* Tablas para layout (DomPDF ama las tablas) */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .layout-table td {
            vertical-align: bottom;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #4a5568; 
            width: 80%; 
            margin: 0 auto 5px auto;
        }
    </style>
</head>
<body>
    @php
        // Lógica del nombre del estudiante
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
        <div class="outer-border">
            <div class="inner-border">
                
                <!-- Decoraciones Esquinas -->
                <div class="corner top-left"></div>
                <div class="corner top-right"></div>
                <div class="corner bottom-left"></div>
                <div class="corner bottom-right"></div>

                <!-- Encabezado -->
                <div style="margin-top: 5px;">
                    <!-- Logo -->
                    <div style="height: 70px; margin-bottom: 5px;">
                        @if(file_exists(public_path('centuu.png')))
                            <img src="{{ public_path('centuu.png') }}" style="height: 100%; opacity: 0.9;" alt="Logo">
                        @endif
                    </div>

                    <h1 class="diploma-font header-title">Diploma de Honor</h1>
                    
                    <div style="height: 2px; width: 100px; background-color: #b49b5a; margin: 10px auto;"></div>
                    
                    <p style="font-size: 16pt; color: #4a5568; font-style: italic; margin: 0;">
                        La institución <strong style="color: #2d3748;">{{ $institution_name ?? 'SGA PADRE' }}</strong> otorga el presente reconocimiento a:
                    </p>
                </div>

                <!-- Nombre -->
                <div class="script-font student-name">
                    {{ $studentName }}
                </div>

                <!-- Cuerpo -->
                <div style="width: 85%; margin: 0 auto; font-size: 14pt; color: #2d3748; line-height: 1.4;">
                    <p style="margin: 0;">
                        Por haber completado satisfactoriamente los requisitos académicos exigidos para el curso de:
                    </p>
                    <p class="diploma-font" style="font-size: 22pt; font-weight: bold; color: #2c5282; margin: 15px 0;">
                        {{ $course->name ?? 'Nombre del Curso' }}
                    </p>
                    <p style="font-style: italic; font-size: 13pt; margin: 0;">
                        Demostrando excelencia, compromiso y dedicación en su desempeño durante el programa.
                    </p>
                </div>

                <!-- Firmas y QR -->
                <table class="layout-table">
                    <tr>
                        <!-- Firma Izquierda -->
                        <td width="35%">
                            <div style="height: 40px;"></div> <!-- Espacio firma -->
                            <div class="signature-line"></div>
                            <p style="font-weight: bold; font-size: 10pt; text-transform: uppercase; margin: 0;">
                                {{ $director_name ?? 'Director Académico' }}
                            </p>
                            <p style="font-size: 9pt; color: #718096; margin: 0;">Dirección Académica</p>
                        </td>

                        <!-- QR Centro -->
                        <td width="30%">
                            <div style="display: inline-block; padding: 4px; border: 3px double #b49b5a; background: white;">
                                <img src="{{ $qr_code_url ?? '' }}" alt="QR" style="width: 80px; height: 80px; display: block;">
                            </div>
                            <div style="margin-top: 4px;">
                                <span style="font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #975a16; background: #fffaf0; padding: 2px 6px; border: 1px solid #fbd38d;">
                                    Validación Digital
                                </span>
                            </div>
                        </td>

                        <!-- Firma Derecha -->
                        <td width="35%">
                            <div style="height: 40px;"></div> <!-- Espacio firma -->
                            <div class="signature-line"></div>
                            <p style="font-weight: bold; font-size: 10pt; text-transform: uppercase; margin: 0;">
                                Secretaría General
                            </p>
                            <p style="font-size: 9pt; color: #718096; margin: 0;">Certificación Oficial</p>
                        </td>
                    </tr>
                </table>

                <!-- Pie de página -->
                <div style="position: absolute; bottom: 10px; width: 100%; left: 0;">
                    <p style="font-size: 8pt; color: #a0aec0; text-transform: uppercase; letter-spacing: 2px; font-family: monospace; margin: 0;">
                        Expedido el {{ $date ?? date('d/m/Y') }} • Folio: {{ $folio ?? 'PENDIENTE' }}
                    </p>
                </div>

            </div>
        </div>
    </div>
</body>
</html>