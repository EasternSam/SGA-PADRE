<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado - {{ $folio ?? 'Vista Previa' }}</title>
    <!-- Tailwind CSS (Vía CDN para facilitar estilos, aunque se recomienda CSS inline para producción PDF estricta) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Fuentes de Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@700;900&family=EB+Garamond:ital,wght@0,400;0,600;1,400&family=Pinyon+Script&display=swap" rel="stylesheet">
    
    <style>
        /* Reglas base y fuentes */
        body {
            background-color: #fff;
            font-family: 'EB Garamond', serif;
            color: #1a202c;
        }
        .diploma-font { font-family: 'Cinzel Decorative', cursive; }
        .script-font { font-family: 'Pinyon Script', cursive; }
        
        /* Textura de pergamino sutil */
        .parchment-bg {
            background-color: #fdfbf7;
            /* Patrón de ruido SVG embebido para textura de papel */
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
            height: 100vh; /* Ocupar toda la altura de la página PDF */
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
        <div class="h-full w-full border-4 border-double border-gray-800 p-2 box-border relative">
            
            <!-- Marco Ornamental Dorado -->
            <div class="h-full w-full border border-yellow-600 ornate-border relative p-8 text-center">
                
                <!-- Decoraciones en las esquinas -->
                <div class="absolute top-2 left-2 w-16 h-16 border-t-4 border-l-4 border-yellow-600 opacity-60"></div>
                <div class="absolute top-2 right-2 w-16 h-16 border-t-4 border-r-4 border-yellow-600 opacity-60"></div>
                <div class="absolute bottom-2 left-2 w-16 h-16 border-b-4 border-l-4 border-yellow-600 opacity-60"></div>
                <div class="absolute bottom-2 right-2 w-16 h-16 border-b-4 border-r-4 border-yellow-600 opacity-60"></div>

                <!-- Sección Superior: Logo y Título -->
                <div class="w-full text-center mt-2">
                    <!-- Logo Institucional -->
                    <div style="height: 80px; margin-bottom: 10px; display: flex; justify-content: center;">
                        @if(file_exists(public_path('centuu.png')))
                            <img src="{{ public_path('centuu.png') }}" style="height: 80px; opacity: 0.9;" alt="Logo">
                        @else
                            <!-- Icono genérico si no hay logo -->
                            <svg style="width: 50px; height: 50px; color: #4a5568; margin: 0 auto;" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L1 21h22L12 2zm0 3.516L20.297 19H3.703L12 5.516zM11 10h2v4h-2zm0 5h2v2h-2z"/>
                            </svg>
                        @endif
                    </div>

                    <h1 class="text-5xl font-black text-gray-900 diploma-font tracking-widest uppercase leading-none mb-2">
                        Diploma de Honor
                    </h1>
                    
                    <div class="h-1 w-32 bg-yellow-600 mx-auto my-4"></div>
                    
                    <p class="text-xl text-gray-600 italic font-serif">
                        La institución {{ $institution_name ?? 'SGA PADRE' }} otorga el presente reconocimiento a
                    </p>
                </div>

                <!-- Nombre del Estudiante -->
                <div class="w-full my-6 text-center">
                    <h2 class="text-6xl text-gray-900 script-font leading-none py-2 border-b border-gray-300 inline-block px-12 min-w-[60%]">
                        {{ $studentName }}
                    </h2>
                </div>

                <!-- Cuerpo del Texto -->
                <div class="max-w-4xl mx-auto text-lg text-gray-700 leading-relaxed text-center px-8">
                    <p>
                        Por haber completado satisfactoriamente los requisitos académicos exigidos para el curso de:
                    </p>
                    <p class="text-3xl font-bold text-gray-900 mt-2 mb-2 diploma-font text-blue-900">
                        {{ $course->name ?? 'Nombre del Curso' }}
                    </p>
                    <p class="text-base italic">
                        Demostrando excelencia, compromiso y dedicación en su desempeño durante el programa.
                    </p>
                </div>

                <!-- Sección Inferior: Firmas y QR (Estructura de Tabla para PDF) -->
                <table class="layout-table">
                    <tr>
                        <!-- Firma Izquierda -->
                        <td width="35%" class="text-center">
                            <div style="height: 60px;">
                                <!-- Espacio para firma escaneada si se desea -->
                            </div>
                            <div style="border-top: 1px solid #4a5568; width: 80%; margin: 0 auto;"></div>
                            <p class="mt-2 font-bold text-gray-800 uppercase text-xs tracking-wider">
                                {{ $director_name ?? 'Director Académico' }}
                            </p>
                            <p class="text-[10px] text-gray-500">Dirección Académica</p>
                        </td>

                        <!-- QR Central -->
                        <td width="30%" class="text-center align-bottom">
                            <div style="display: inline-block; padding: 6px; border: 4px double #b49b5a; background: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                <!-- QR generado externamente pasado por el controlador -->
                                <img src="{{ $qr_code_url ?? '' }}" alt="QR Validación" style="width: 90px; height: 90px; display: block;">
                            </div>
                            <div style="margin-top: 8px; background-color: #fffaf0; border: 1px solid #fbd38d; padding: 2px 8px; display: inline-block;">
                                <p class="uppercase text-[8px] font-bold tracking-[0.15em] text-yellow-800">
                                    Validación Digital
                                </p>
                            </div>
                        </td>

                        <!-- Firma Derecha -->
                        <td width="35%" class="text-center">
                            <div style="height: 60px;">
                                <!-- Espacio para segunda firma -->
                            </div>
                            <div style="border-top: 1px solid #4a5568; width: 80%; margin: 0 auto;"></div>
                            <p class="mt-2 font-bold text-gray-800 uppercase text-xs tracking-wider">
                                Secretaría General
                            </p>
                            <p class="text-[10px] text-gray-500">Certificación Oficial</p>
                        </td>
                    </tr>
                </table>

                <!-- Pie de Página -->
                <div class="absolute bottom-2 left-0 w-full text-center">
                    <span class="text-[9px] text-gray-400 uppercase tracking-widest font-mono">
                        Expedido el {{ $date ?? date('d/m/Y') }} • Folio: {{ $folio ?? 'PENDIENTE' }} • Registro Oficial
                    </span>
                </div>

            </div>
        </div>
    </div>
</body>
</html>