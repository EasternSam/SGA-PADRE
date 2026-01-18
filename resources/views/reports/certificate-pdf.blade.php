<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Aprobación</title>
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
        .container {
            width: 100%;
            height: 100vh;
            position: relative;
            text-align: center;
        }
        .border-outer {
            position: absolute;
            top: 20px; left: 20px; right: 20px; bottom: 20px;
            border: 5px solid #1a365d; /* Azul oscuro */
        }
        .border-inner {
            position: absolute;
            top: 28px; left: 28px; right: 28px; bottom: 28px;
            border: 2px solid #c0b283; /* Dorado */
        }
        .content {
            position: relative;
            top: 50%;
            transform: translateY(-50%);
            padding: 0 80px;
        }
        .header-title {
            font-size: 50px;
            text-transform: uppercase;
            letter-spacing: 4px;
            color: #1a365d;
            margin-bottom: 10px;
        }
        .institution {
            font-size: 24px;
            color: #666;
            margin-bottom: 40px;
            letter-spacing: 2px;
        }
        .certifies {
            font-size: 18px;
            font-style: italic;
            margin-bottom: 20px;
        }
        .student-name {
            font-size: 42px;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            display: inline-block;
            padding: 0 40px 10px 40px;
        }
        .has-completed {
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .course-title {
            font-size: 32px;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 50px;
        }
        .footer {
            margin-top: 60px;
            display: table;
            width: 100%;
        }
        .sign-col {
            display: table-cell;
            width: 33%;
            vertical-align: top;
        }
        .sign-line {
            width: 80%;
            margin: 0 auto;
            border-top: 1px solid #333;
            margin-bottom: 10px;
        }
        .sign-title {
            font-weight: bold;
            font-size: 14px;
        }
        .date-text {
            font-size: 14px;
            color: #555;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="border-outer"></div>
        <div class="border-inner"></div>

        <div class="content">
            <div class="header-title">Certificado</div>
            <div class="institution">{{ $institution_name }}</div>

            <div class="certifies">Por medio del presente se certifica que</div>

            <div class="student-name">
                {{ $student->name }} {{ $student->last_name }}
            </div>

            <div class="has-completed">Ha completado y aprobado satisfactoriamente el curso de</div>

            <div class="course-title">
                {{ $course->name }}
            </div>

            <div class="date-text">
                Otorgado el día {{ $date }}
            </div>

            <div class="footer">
                <div class="sign-col">
                    <div style="height: 50px;"></div> <!-- Espacio firma -->
                    <div class="sign-line"></div>
                    <div class="sign-title">Dirección Académica</div>
                </div>
                <div class="sign-col">
                    <!-- Sello opcional -->
                </div>
                <div class="sign-col">
                    <div style="height: 50px;"></div> <!-- Espacio firma -->
                    <div class="sign-line"></div>
                    <div class="sign-title">Coordinación</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>