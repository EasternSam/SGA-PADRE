<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationsDropdown extends Component
{
    public $unreadCount = 0;

    protected $listeners = ['notificationReceived' => 'refreshNotifications'];

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
        // Traemos las últimas 10 notificaciones (leídas y no leídas)
        return Auth::user()->notifications()->latest()->take(10)->get();
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
            
            // Si tiene URL, redirigir
            if (isset($notification->data['url']) && $notification->data['url']) {
                return redirect($notification->data['url']);
            }
        }
        
        $this->refreshNotifications();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->refreshNotifications();
    }

    public function render()
    {
        return view('livewire.notifications-dropdown');
    }
}