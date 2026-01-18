<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado - {{ $folio }}</title>
    <style>
        @page {
            margin: 0;
            size: a4 landscape;
        }
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 0;
            color: #333;
            background: #fff;
        }
        /* Marco Ornamental */
        .border-pattern {
            position: absolute;
            top: 15px; left: 15px; right: 15px; bottom: 15px;
            border: 2px solid #1a365d;
            padding: 5px;
        }
        .border-inner {
            position: absolute;
            top: 5px; left: 5px; right: 5px; bottom: 5px;
            border: 4px double #c0b283; /* Dorado */
            background-color: #fff;
            /* Fondo sutil opcional */
            /* background-image: radial-gradient(#f3f4f6 1px, transparent 1px); background-size: 20px 20px; */
        }
        
        .content-layer {
            position: relative;
            z-index: 10;
            padding: 40px 60px;
            text-align: center;
            height: 90%;
        }

        /* Encabezado */
        .header-title {
            font-size: 46px;
            text-transform: uppercase;
            letter-spacing: 6px;
            color: #1a365d;
            margin-top: 15px;
            margin-bottom: 5px;
            font-family: 'Helvetica', sans-serif;
            font-weight: bold;
        }
        .institution-sub {
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: #888;
            margin-bottom: 35px;
            border-bottom: 1px solid #eee;
            display: inline-block;
            padding-bottom: 10px;
        }

        /* Cuerpo */
        .certifies-text {
            font-size: 20px;
            font-style: italic;
            color: #555;
            margin-bottom: 15px;
        }
        .student-name {
            font-size: 52px;
            font-weight: bold;
            color: #000;
            margin: 10px 0;
            /* Si tienes fuentes cursivas instaladas, úsalas aquí */
            /* font-family: 'Pinyon Script', cursive; */
            border-bottom: 1px solid #c0b283;
            display: inline-block;
            padding: 0 50px 5px 50px;
            min-width: 60%;
        }
        .course-intro {
            font-size: 20px;
            margin-top: 30px;
            color: #555;
        }
        .course-name {
            font-size: 36px;
            font-weight: bold;
            color: #1a365d;
            margin: 15px 0 45px 0;
        }

        /* Pie de página y Firmas */
        .footer-table {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }
        .sign-cell {
            text-align: center;
            vertical-align: bottom;
            width: 35%;
        }
        .qr-cell {
            text-align: right;
            vertical-align: bottom;
            width: 30%;
            padding-right: 20px;
        }
        .date-cell {
            text-align: center;
            vertical-align: bottom;
            width: 35%;
            font-size: 14px;
            color: #666;
            padding-bottom: 10px;
        }

        .sign-line {
            border-top: 1px solid #333;
            width: 80%;
            margin: 0 auto 8px auto;
        }
        .sign-title {
            font-weight: bold;
            font-size: 15px;
            color: #1a365d;
            text-transform: uppercase;
        }
        .sign-subtitle {
            font-size: 12px;
            color: #666;
        }
        
        /* QR Container */
        .qr-box {
            display: inline-block;
            text-align: center;
        }
        .qr-img {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            padding: 4px;
            background: #fff;
        }
        .validation-caption {
            font-size: 9px;
            color: #888;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .folio-text {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 10px;
            color: #aaa;
            letter-spacing: 2px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="border-pattern">
        <div class="border-inner">
            <div class="content-layer">
                
                <div class="header-title">Certificado</div>
                <div class="institution-sub">{{ $institution_name }}</div>

                <div class="certifies-text">Otorga el presente reconocimiento a:</div>

                <div class="student-name">
                    {{ $student->name }} {{ $student->last_name }}
                </div>

                <div class="course-intro">Por haber completado satisfactoriamente el programa académico:</div>

                <div class="course-name">
                    {{ $course->name }}
                </div>

                <table class="footer-table">
                    <tr>
                        <td class="sign-cell">
                            <!-- Espacio visual para la firma -->
                            <div style="height: 50px;"></div> 
                            <div class="sign-line"></div>
                            <div class="sign-title">{{ $director_name }}</div>
                            <div class="sign-subtitle">Director Académico</div>
                        </td>
                        
                        <td class="date-cell">
                            Expedido el:<br>
                            <strong>{{ $date }}</strong>
                        </td>

                        <td class="qr-cell">
                            <div class="qr-box">
                                <img src="{{ $qr_code_url }}" class="qr-img" alt="Validación QR">
                                <div class="validation-caption">Escanear para validar</div>
                            </div>
                        </td>
                    </tr>
                </table>

                <div class="folio-text">FOLIO: {{ $folio }}</div>
            </div>
        </div>
    </div>
</body>
</html>