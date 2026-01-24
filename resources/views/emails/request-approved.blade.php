<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .status-box { background-color: #ebf8ff; border: 1px solid #bee3f8; padding: 10px; margin: 15px 0; }
        .payment-box { margin-top: 15px; padding: 10px; border: 1px dashed #cbd5e0; background-color: #fffaf0; border-radius: 5px; }
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

        {{-- NUEVO: Mostrar detalles del pago si existe --}}
        @if($request->payment)
            <div class="payment-box">
                <p style="margin: 0; color: #744210; font-weight: bold;">Información de Cargo Generado:</p>
                <ul style="margin-top: 5px; margin-bottom: 0; padding-left: 20px; color: #2d3748;">
                    <li><strong>Concepto:</strong> {{ $request->payment->description ?? 'Servicio' }}</li>
                    <li><strong>Monto a Pagar:</strong> RD$ {{ number_format($request->payment->amount, 2) }}</li>
                    <li><strong>Estado del Pago:</strong> 
                        <span style="color: {{ $request->payment->status == 'Completado' ? 'green' : 'red' }}; font-weight: bold;">
                            {{ strtoupper($request->payment->status) }}
                        </span>
                    </li>
                    @if($request->payment->due_date && $request->payment->status == 'Pendiente')
                        <li><strong>Fecha Límite:</strong> {{ \Carbon\Carbon::parse($request->payment->due_date)->format('d/m/Y') }}</li>
                    @endif
                </ul>
            </div>
            <p>Puedes realizar el pago a través de tu portal de estudiante.</p>
        @else
            <p>Si esta solicitud generó algún cargo, podrás verlo reflejado en tu estado de cuenta.</p>
        @endif
    </div>
</body>
</html>