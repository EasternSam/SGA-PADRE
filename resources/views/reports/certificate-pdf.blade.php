<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado - {{ $folio ?? 'Vista Previa' }}</title>
    
    <style>
        /* CORRECCIÓN DE FUENTES PARA DOMPDF:
           Usamos @font-face directo a los archivos TTF para evitar problemas de caché y formato WOFF2.
        */
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

        /* Reglas base y fuentes con fallbacks seguros */
        body {
            background-color: #fff;
            font-family: 'EB Garamond', serif; /* Fallback a serif estándar */
            color: #1a202c;
            margin: 0;
            padding: 0;
        }
        
        .diploma-font { 
            font-family: 'Cinzel Decorative', serif; /* Fallback seguro */
        }
        
        .script-font { 
            font-family: 'Pinyon Script', cursive; /* Fallback seguro */
        }
        
        /* Textura de pergamino sutil */
        .parchment-bg {
            background-color: #fdfbf7;
            background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.05'/%3E%3C/svg%3E");
        }

        /* Bordes ornamentales */
        .ornate-border {
            border: 3px solid #b49b5a; /* Dorado exterior */
            padding: 4px;
            outline: 4px solid #1a202c; /* Gris oscuro outline */
            outline-offset: 4px;
        }

        /* Ajustes específicos para DomPDF */
        @page {
            margin: 0;
            size: A4 landscape;
        }
        
        .page-container {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            padding: 40px;
            box-sizing: border-box;
        }

        /* Utilidades de posicionamiento para PDF (tablas son más seguras que flexbox en DomPDF antiguo) */
        .layout-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .layout-table td {
            vertical-align: bottom;
            padding: 0 10px;
        }
        
        /* Asegurar que las imágenes locales funcionen */
        img {
            max-width: 100%;
        }
        
        /* Utilidades de texto */
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    @php
        // Lógica para obtener el nombre completo del estudiante
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

    <!-- Contenedor Principal A4 Horizontal -->
    <div class="page-container parchment-bg">
        
        <!-- Marco Exterior Doble -->
        <div style="height: 95%; width: 100%; border: 4px double #1a202c; padding: 5px; box-sizing: border-box; position: relative;">
            
            <!-- Marco Ornamental Dorado -->
            <div style="height: 100%; width: 100%; border: 2px solid #b49b5a; padding: 20px; box-sizing: border-box; position: relative; text-align: center;" class="ornate-border">
                
                <!-- Decoraciones en las esquinas (usando border CSS simple para compatibilidad) -->
                <div style="position: absolute; top: 5px; left: 5px; width: 40px; height: 40px; border-top: 4px solid #b49b5a; border-left: 4px solid #b49b5a; opacity: 0.6;"></div>
                <div style="position: absolute; top: 5px; right: 5px; width: 40px; height: 40px; border-top: 4px solid #b49b5a; border-right: 4px solid #b49b5a; opacity: 0.6;"></div>
                <div style="position: absolute; bottom: 5px; left: 5px; width: 40px; height: 40px; border-bottom: 4px solid #b49b5a; border-left: 4px solid #b49b5a; opacity: 0.6;"></div>
                <div style="position: absolute; bottom: 5px; right: 5px; width: 40px; height: 40px; border-bottom: 4px solid #b49b5a; border-right: 4px solid #b49b5a; opacity: 0.6;"></div>

                <!-- Sección Superior: Logo y Título -->
                <div style="width: 100%; margin-top: 10px; text-align: center;">
                    <!-- Logo Institucional -->
                    <div style="height: 80px; margin-bottom: 10px;">
                        @if(file_exists(public_path('centuu.png')))
                            <img src="{{ public_path('centuu.png') }}" style="height: 80px; opacity: 0.9;" alt="Logo">
                        @else
                            <!-- Texto alternativo si falla la imagen -->
                            <h2 style="margin:0; color:#4a5568;">{{ $institution_name ?? 'SGA' }}</h2>
                        @endif
                    </div>

                    <h1 class="diploma-font uppercase" style="font-size: 48px; color: #1a202c; letter-spacing: 4px; margin: 10px 0; line-height: 1;">
                        Diploma de Honor
                    </h1>
                    
                    <div style="height: 2px; width: 150px; background-color: #b49b5a; margin: 15px auto;"></div>
                    
                    <p style="font-size: 20px; color: #4a5568; font-style: italic; margin-bottom: 5px;">
                        La institución {{ $institution_name ?? 'SGA PADRE' }} otorga el presente reconocimiento a
                    </p>
                </div>

                <!-- Nombre del Estudiante -->
                <div style="margin: 20px 0; text-align: center;">
                    <div class="script-font" style="font-size: 56px; color: #1a202c; border-bottom: 1px solid #cbd5e0; display: inline-block; padding: 0 40px 10px 40px; min-width: 60%;">
                        {{ $studentName }}
                    </div>
                </div>

                <!-- Cuerpo del Texto -->
                <div style="width: 80%; margin: 0 auto; font-size: 18px; color: #2d3748; line-height: 1.6;">
                    <p style="margin-bottom: 10px;">
                        Por haber completado satisfactoriamente los requisitos académicos exigidos para el curso de:
                    </p>
                    <p class="diploma-font" style="font-size: 28px; font-weight: bold; color: #2c5282; margin: 15px 0;">
                        {{ $course->name ?? 'Nombre del Curso' }}
                    </p>
                    <p style="font-style: italic; font-size: 16px;">
                        Demostrando excelencia, compromiso y dedicación en su desempeño durante el programa.
                    </p>
                </div>

                <!-- Sección Inferior: Firmas y QR -->
                <table class="layout-table">
                    <tr>
                        <!-- Firma Izquierda -->
                        <td width="35%" class="text-center">
                            <div style="height: 50px;"></div>
                            <div style="border-top: 1px solid #4a5568; width: 80%; margin: 0 auto;"></div>
                            <p style="margin-top: 5px; font-weight: bold; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                                {{ $director_name ?? 'Director Académico' }}
                            </p>
                            <p style="font-size: 10px; color: #718096; margin: 0;">Dirección Académica</p>
                        </td>

                        <!-- QR Central -->
                        <td width="30%" class="text-center" style="vertical-align: bottom;">
                            <div style="display: inline-block; padding: 5px; border: 3px double #b49b5a; background: white;">
                                <!-- QR con ruta absoluta remota permitida por isRemoteEnabled -->
                                <img src="{{ $qr_code_url ?? '' }}" alt="QR" style="width: 85px; height: 85px; display: block;">
                            </div>
                            <div style="margin-top: 5px;">
                                <span style="font-size: 9px; font-weight: bold; text-transform: uppercase; color: #975a16; background: #fffaf0; padding: 2px 6px; border: 1px solid #fbd38d;">
                                    Validación Digital
                                </span>
                            </div>
                        </td>

                        <!-- Firma Derecha -->
                        <td width="35%" class="text-center">
                            <div style="height: 50px;"></div>
                            <div style="border-top: 1px solid #4a5568; width: 80%; margin: 0 auto;"></div>
                            <p style="margin-top: 5px; font-weight: bold; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                                Secretaría General
                            </p>
                            <p style="font-size: 10px; color: #718096; margin: 0;">Certificación Oficial</p>
                        </td>
                    </tr>
                </table>

                <!-- Pie de Página -->
                <div style="position: absolute; bottom: 10px; left: 0; width: 100%; text-align: center;">
                    <span style="font-size: 9px; color: #a0aec0; text-transform: uppercase; letter-spacing: 2px; font-family: monospace;">
                        Expedido el {{ $date ?? date('d/m/Y') }} • Folio: {{ $folio ?? 'PENDIENTE' }}
                    </span>
                </div>

            </div>
        </div>
    </div>
</body>
</html>