<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $type; // 'info', 'success', 'warning', 'danger'
    public $icon; // Icono FontAwesome o SVG path
    public $actionUrl;

    /**
     * Create a new notification instance.
     *
     * @param string $title Título corto
     * @param string $message Mensaje detallado
     * @param string $type Tipo para color (info, success, warning)
     * @param string|null $actionUrl URL para redirigir al hacer clic
     */
    public function __construct($title, $message, $type = 'info', $actionUrl = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->type = $type;
        $this->actionUrl = $actionUrl;
        
        // Asignar icono según tipo
        $this->icon = match($type) {
            'success' => 'check-circle', // Inscripciones
            'warning' => 'exclamation-triangle', // Fechas próximas
            'danger'  => 'times-circle', // Errores/Vencidos
            default   => 'info-circle',
        };
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'icon' => $this->icon,
            'url' => $this->actionUrl,
        ];
    }
}