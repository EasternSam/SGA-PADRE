<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .status-box { background-color: #ebf8ff; border: 1px solid #bee3f8; padding: 10px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Solicitud Aprobada</h2>
        <p>Hola {{ $request->student->first_name }},</p>
        
        <p>Tu solicitud realizada el {{ $request->created_at->format('d/m/Y') }} ha sido procesada.</p>
        
        <div class="status-box">
            <strong>Tipo de Solicitud:</strong> {{ ucfirst(str_replace('_', ' ', $request->type)) }}<br>
            <strong>Estado:</strong> <span style="color: green; font-weight: bold;">APROBADO</span>
        </div>

        @if($request->admin_notes)
            <p><strong>Nota de la administración:</strong></p>
            <p><i>{{ $request->admin_notes }}</i></p>
        @endif

        <p>Si esta solicitud generó algún cargo, podrás verlo reflejado en tu estado de cuenta.</p>
    </div>
</body>
</html>