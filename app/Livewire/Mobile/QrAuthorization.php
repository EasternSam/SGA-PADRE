<?php

namespace App\Livewire\Mobile;

use Livewire\Component;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class QrAuthorization extends Component
{
    public $token;
    public $status = 'pending'; // pending, authorized, denied, expired, invalid

    public function mount($token)
    {
        $this->token = $token;
        $sessionData = Cache::get("kiosk_qr_{$this->token}");

        if (!$sessionData) {
            $this->status = 'invalid'; // Or expired
            return;
        }

        if ($sessionData['status'] !== 'pending') {
            $this->status = $sessionData['status'];
        }
    }

    public function authorizeLogin()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $sessionData = Cache::get("kiosk_qr_{$this->token}");
        if ($sessionData && $sessionData['status'] === 'pending') {
            // Update cache to notify Kiosk
            Cache::put("kiosk_qr_{$this->token}", [
                'status' => 'authorized',
                'user_id' => $user->id
            ], now()->addMinutes(2));
            
            $this->status = 'authorized';
        } else {
             $this->status = 'invalid';
        }
    }

    public function denyLogin()
    {
        $sessionData = Cache::get("kiosk_qr_{$this->token}");
         if ($sessionData) {
            Cache::put("kiosk_qr_{$this->token}", [
                'status' => 'denied',
                'user_id' => null
            ], now()->addMinutes(2));
         }
        $this->status = 'denied';
    }

    public function render()
    {
        return view('livewire.mobile.qr-authorization')
               ->layout('layouts.dashboard'); // Use standard mobile app layout
    }
}
