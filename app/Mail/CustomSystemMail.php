<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Se mantiene la importación pero no la usaremos en la clase
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// NOTA: He quitado "implements ShouldQueue" para que el envío sea inmediato y podamos ver errores.
class CustomSystemMail extends Mailable 
{
    use Queueable, SerializesModels;

    public $customSubject;
    public $customMessage;

    /**
     * Create a new message instance.
     */
    public function __construct($subject, $message)
    {
        $this->customSubject = $subject;
        $this->customMessage = $message;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.custom-system',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}