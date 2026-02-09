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
    public $username;
    public $moodleUrl;

    public function __construct(User $user, $password, $username = null)
    {
        $this->user = $user;
        $this->password = $password;
        $this->username = $username ?? $user->email;
        $this->moodleUrl = config('services.moodle.url');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎓 Tus Credenciales del Aula Virtual - ' . config('app.name'),
        );
    }

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