<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $type; // 'info', 'success', 'warning', 'danger'
    public $icon; // Icono FontAwesome sin prefijo (ej: 'check-circle')
    public $actionUrl;
    public array $extra;
    public bool $sendMail;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $type = 'info', $actionUrl = null, array $extra = [], bool $sendMail = false)
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->actionUrl = $actionUrl;
        $this->extra = $extra;
        $this->sendMail = $sendMail;
        
        // Asignar icono visual según tipo
        $this->icon = match($type) {
            'success' => 'check-circle',
            'warning' => 'exclamation-triangle',
            'danger', 'error' => 'times-circle',
            default => 'info-circle',
        };
    }

    /**
     * Determina los canales de envío.
     */
    public function via(object $notifiable): array
    {
        if ($this->sendMail) {
            return ['database', 'mail'];
        }

        // Registrar de inmediato en base de datos (campanita de notificaciones)
        return ['database'];
    }

    /**
     * Representación para Correo Electrónico.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('SGA: ' . $this->title) // Asunto del correo
            ->greeting('¡Hola!')
            ->line($this->message); // El cuerpo del mensaje

        // Si hay una acción/URL, añadir el botón
        if ($this->actionUrl) {
            $mail->action('Ver Detalles', $this->actionUrl);
        }

        return $mail->line('Gracias por ser parte de nuestra comunidad educativa.');
    }

    /**
     * Representación para Base de Datos (Campanita).
     */
    public function toArray(object $notifiable): array
    {
        return array_merge([
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'icon' => $this->icon,
            'url' => $this->actionUrl,
        ], $this->extra);
    }
}