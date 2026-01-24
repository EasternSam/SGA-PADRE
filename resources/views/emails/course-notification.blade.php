<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>{{ $type === 'start' ? '¡Tu curso está por comenzar!' : 'Tu curso está por finalizar' }}</h2>
        
        <p>Hola,</p>
        
        @if($type === 'start')
            <p>Te recordamos que el módulo <strong>{{ $schedule->module->name }}</strong> del curso <strong>{{ $schedule->module->course->name }}</strong> está programado para iniciar pronto.</p>
            <p><strong>Fecha de Inicio:</strong> {{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m/Y') }}</p>
            <p>¡Prepárate para aprender!</p>
        @else
            <p>Te informamos que el módulo <strong>{{ $schedule->module->name }}</strong> está llegando a su fin.</p>
            <p><strong>Fecha de Finalización:</strong> {{ \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') }}</p>
            <p>Asegúrate de completar todas tus asignaciones pendientes.</p>
        @endif

        <p>Saludos,<br>La Administración</p>
    </div>
</body>
</html>