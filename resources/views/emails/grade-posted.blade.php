<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .grade-box { background-color: #f0fff4; border: 1px solid #c6f6d5; padding: 15px; text-align: center; margin: 20px 0; border-radius: 5px; }
        .grade-value { font-size: 24px; font-weight: bold; color: #2f855a; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Nueva Calificación Publicada</h2>
        <p>Hola {{ $enrollment->student->first_name }},</p>
        
        <p>El profesor ha publicado tu calificación final para el módulo:</p>
        <p><strong>{{ $enrollment->courseSchedule->module->name }}</strong></p>
        
        <div class="grade-box">
            Tu Calificación Final:
            <div class="grade-value">{{ number_format($enrollment->final_grade, 2) }}</div>
        </div>

        <p>Puedes ver más detalles ingresando a tu portal de estudiante.</p>
    </div>
</body>
</html>