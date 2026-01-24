<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable; // Se elimina ShouldQueue
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    protected $pdfContent; // Contiene Base64 String

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, $pdfContent)
    {
        $this->payment = $payment;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Cambiar asunto dinámicamente según estado
        $subject = ($this->payment->status === 'Pendiente') 
            ? 'Aviso de Deuda Pendiente - SGA' 
            : 'Comprobante de Pago - SGA';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-receipt',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $filename = ($this->payment->status === 'Pendiente') ? 'Detalle_Deuda_' : 'Recibo_Pago_';

        // Decodificamos el Base64 aquí mismo para recuperar el binario
        return [
            Attachment::fromData(fn () => base64_decode($this->pdfContent), $filename . $this->payment->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}