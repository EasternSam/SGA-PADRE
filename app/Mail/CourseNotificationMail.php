<?php

namespace App\Mail;

use App\Models\CourseSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable; // Se elimina ShouldQueue
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $schedule;
    public $type; // 'start' o 'end'

    public function __construct(CourseSchedule $schedule, string $type)
    {
        $this->schedule = $schedule;
        $this->type = $type;
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'start' 
            ? 'Recordatorio: Tu curso está por iniciar' 
            : 'Aviso: Tu curso está por finalizar';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.course-notification');
    }
}