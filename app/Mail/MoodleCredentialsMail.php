<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MoodleCredentialsMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $username; // Agregado
    public $moodleUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $password, $username = null)
    {
        $this->user = $user;
        $this->password = $password;
        // Si no se pasa username, usamos el email (comportamiento fallback)
        $this->username = $username ?? $user->email;
        $this->moodleUrl = config('services.moodle.url');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎓 Tus Credenciales del Aula Virtual - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.moodle-credentials',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}