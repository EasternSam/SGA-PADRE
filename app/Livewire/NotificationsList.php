<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class NotificationsList extends Component
{
    use WithPagination;

    public $filter = 'all'; // 'all', 'unread', 'read'

    protected $queryString = ['filter' => ['except' => 'all']];

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public function readAndRedirect($notificationId)
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();
        if ($notification) {
            $notification->markAsRead();
            if (isset($notification->data['url']) && $notification->data['url']) {
                return redirect($notification->data['url']);
            }
        }
    }

    public function markAsUnread($notificationId)
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();
        if ($notification) {
            $notification->update(['read_at' => null]);
        }
    }

    public function deleteNotification($notificationId)
    {
        $notification = Auth::user()->notifications()->where('id', $notificationId)->first();
        if ($notification) {
            $notification->delete();
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        session()->flash('success', 'Todas las notificaciones han sido marcadas como leídas.');
    }

    public function clearAll()
    {
        Auth::user()->notifications()->delete();
        session()->flash('success', 'Historial de notificaciones vaciado.');
    }

    public function render()
    {
        $query = Auth::user()->notifications();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->latest()->paginate(20);

        return view('livewire.notifications-list', [
            'notifications' => $notifications
        ])->layout('layouts.dashboard', ['header' => 'Centro de Notificaciones']);
    }
}
