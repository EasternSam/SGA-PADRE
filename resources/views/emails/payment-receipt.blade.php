<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .header { background-color: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; }
        .content { padding: 20px 0; }
        .footer { font-size: 12px; color: #777; text-align: center; margin-top: 20px; }
        .amount { font-size: 18px; font-weight: bold; color: #2d3748; }
        .status-pending { color: #e53e3e; font-weight: bold; }
        .status-paid { color: #38a169; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($payment->status === 'Pendiente')
                <h2>Aviso de Deuda Generada</h2>
            @else
                <h2>Confirmación de Pago</h2>
            @endif
        </div>
        <div class="content">
            <p>Hola {{ $payment->student->first_name }},</p>
            
            @if($payment->status === 'Pendiente')
                <p>Se ha generado un nuevo compromiso de pago en tu cuenta. Por favor, realiza el pago antes de la fecha límite.</p>
            @else
                <p>Hemos recibido tu pago correctamente. A continuación los detalles:</p>
            @endif
            
            <ul>
                <li><strong>Concepto:</strong> {{ $payment->paymentConcept->name ?? $payment->description }}</li>
                <li><strong>Monto:</strong> <span class="amount">RD$ {{ number_format($payment->amount, 2) }}</span></li>
                <li><strong>Fecha:</strong> {{ $payment->created_at->format('d/m/Y h:i A') }}</li>
                
                @if($payment->status === 'Pendiente')
                    <li><strong>Estado:</strong> <span class="status-pending">PENDIENTE</span></li>
                    @if($payment->due_date)
                        <li><strong>Fecha Límite:</strong> {{ \Carbon\Carbon::parse($payment->due_date)->format('d/m/Y') }}</li>
                    @endif
                @else
                    <li><strong>Método:</strong> {{ $payment->gateway }}</li>
                    <li><strong>Estado:</strong> <span class="status-paid">PAGADO</span></li>
                @endif
            </ul>

            <p>Adjunto a este correo encontrarás el documento con el detalle en formato PDF.</p>
            
            @if($payment->status === 'Pendiente')
                <p>Puedes realizar el pago a través de tu portal de estudiante o en nuestras oficinas.</p>
            @else
                <p>Gracias por tu pago.</p>
            @endif
        </div>
        <div class="footer">
            <p>Este es un mensaje automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>