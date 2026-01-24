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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Confirmación de Pago</h2>
        </div>
        <div class="content">
            <p>Hola {{ $payment->student->first_name }},</p>
            <p>Hemos recibido tu pago correctamente. A continuación los detalles:</p>
            
            <ul>
                <li><strong>Concepto:</strong> {{ $payment->paymentConcept->name ?? $payment->description }}</li>
                <li><strong>Monto:</strong> <span class="amount">RD$ {{ number_format($payment->amount, 2) }}</span></li>
                <li><strong>Fecha:</strong> {{ $payment->created_at->format('d/m/Y h:i A') }}</li>
                <li><strong>Método:</strong> {{ $payment->gateway }}</li>
            </ul>

            <p>Adjunto a este correo encontrarás el recibo oficial en formato PDF.</p>
            <p>Gracias por tu pago.</p>
        </div>
        <div class="footer">
            <p>Este es un mensaje automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>