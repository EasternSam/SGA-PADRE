<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado</title>
    <style>
        @font-face {
            font-family: 'Cinzel Decorative';
            src: url('https://fonts.gstatic.com/s/cinzeldecorative/v16/daaHSScvJGqLYhG8nNt8KPPswUAPni7TTd1u8kY.ttf') format('truetype');
            font-weight: 700;
        }
        @font-face {
            font-family: 'EB Garamond';
            src: url('https://fonts.gstatic.com/s/ebgaramond/v26/SlGDmQSNjdsmc35JDF1K5E55YMjF_7DPuQ.ttf') format('truetype');
            font-weight: 400;
        }
        @font-face {
            font-family: 'Pinyon Script';
            src: url('https://fonts.gstatic.com/s/pinyonscript/v17/6xKtdSZaM9PENbF29TaCNqsPPlWZ58M.ttf') format('truetype');
            font-weight: 400;
        }

        @page { margin: 0; size: 297mm 210mm; }
        body { margin: 0; padding: 0; width: 297mm; height: 210mm; }
        
        .page-container {
            width: 100%; height: 100%; position: relative;
        }
        
        .element {
            position: absolute;
            line-height: 1.2;
        }
    </style>
</head>
<body>
    @php
        // Preparar variables para reemplazo
        $replacements = [
            '{student_name}' => $studentName ?? 'Nombre Estudiante',
            '{course_name}' => $course->name ?? 'Curso',
            '{date}' => $date ?? date('d/m/Y'),
            '{folio}' => $folio ?? 'FOLIO-000',
            '{director_name}' => $director_name ?? 'Director',
            '{institution_name}' => $institution_name ?? 'Institución',
        ];
        
        // Factor de conversión (aprox 1px pantalla = 1px PDF en 96 DPI, pero mejor usar ratio)
        // El editor usa 1123px de ancho (que es A4 a 96 DPI)
        // DomPDF renderiza a 72 DPI por defecto, pero si definimos la página en mm, 
        // y usamos px, puede variar. 
        // Ajuste fino: 1px editor = 1px PDF (si DomPDF está configurado correctamente o usando medidas relativas)
    @endphp

    <div class="page-container" 
         style="background-image: url('{{ isset($bg_image) ? public_path($bg_image) : "" }}'); background-size: cover; background-position: center;">
        
        @if(!isset($bg_image))
            <!-- Fondo por defecto -->
            <div style="position:absolute; inset:0; background-color: #fdfbf7; z-index:-1;"></div>
        @endif

        @foreach($elements as $el)
            @php
                $content = $el['content'];
                foreach($replacements as $key => $val) {
                    $content = str_replace($key, $val, $content);
                }
                
                // Conversión de escala si es necesario (ej: 0.75 si pasas de 96dpi a 72dpi)
                // Por ahora 1:1 suele funcionar si el canvas editor coincide con A4 pixels
                $scale = 1; 
                $left = $el['x'] * $scale;
                $top = $el['y'] * $scale;
                $width = $el['width'] * $scale;
                $fontSize = $el['fontSize']; // pt suele renderizarse bien
            @endphp

            @if($el['type'] === 'qr')
                <div class="element" style="left: {{ $left }}px; top: {{ $top }}px; width: {{ $width }}px;">
                    <img src="{{ $qr_code_url ?? '' }}" style="width: 100%; height: auto;">
                </div>
            @else
                <div class="element" 
                     style="left: {{ $left }}px; 
                            top: {{ $top }}px; 
                            width: {{ $width }}px;
                            font-family: '{{ $el['fontFamily'] }}';
                            font-size: {{ $fontSize }}pt;
                            font-weight: {{ $el['fontWeight'] ?? 'normal' }};
                            color: {{ $el['color'] }};
                            text-align: {{ $el['textAlign'] }};">
                    {!! nl2br(e($content)) !!}
                </div>
            @endif
        @endforeach
    </div>
</body>
</html>