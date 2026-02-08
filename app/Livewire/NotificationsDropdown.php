<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationsDropdown extends Component
{
    public $unreadCount = 0;

    // Escuchar eventos para actualizar sin recargar (ej: cuando llega push notification)
    protected $listeners = ['notificationReceived' => 'refreshNotifications', '$refresh'];

    public function mount()
    {
        $this->refreshNotifications();
    }

    public function refreshNotifications()
    {
        if (Auth::check()) {
            $this->unreadCount = Auth::user()->unreadNotifications()->count();
        }
    }

    public function getNotificationsProperty()
    {
        // Traemos las últimas 10 notificaciones para mostrar en el dropdown
        return Auth::check() 
            ? Auth::user()->notifications()->latest()->take(10)->get() 
            : collect();
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
            
            // Si la notificación tiene una URL de acción, redirigimos
            if (isset($notification->data['url']) && $notification->data['url']) {
                return redirect($notification->data['url']);
            }
        }
        
        $this->refreshNotifications();
    }

    public function markAllAsRead()
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
            $this->refreshNotifications();
        }
    }

    public function render()
    {
        return view('livewire.notifications-dropdown');
    }
}