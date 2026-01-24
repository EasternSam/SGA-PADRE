<?php

namespace App\Mail;

use App\Models\StudentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $request;

    public function __construct(StudentRequest $request)
    {
        $this->request = $request;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu solicitud ha sido aprobada',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-approved',
        );
    }
}