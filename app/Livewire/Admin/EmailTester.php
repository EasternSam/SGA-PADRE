<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Mail\CustomSystemMail;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class EmailTester extends Component
{
    public $emailTo;
    public $subject;
    public $messageBody;
    
    // Nueva variable para mostrar el diagnóstico en la vista
    public $debugLog = [];

    protected $rules = [
        'emailTo' => 'required|email',
        'subject' => 'required|string|min:3',
        'messageBody' => 'required|string|min:10',
    ];

    protected $messages = [
        'emailTo.required' => 'El destinatario es obligatorio.',
        'emailTo.email' => 'Ingrese un correo válido.',
        'subject.required' => 'El asunto es obligatorio.',
        'messageBody.required' => 'El mensaje no puede estar vacío.',
    ];

    public function sendEmail()
    {
        $this->validate();
        $this->debugLog = []; // Limpiar log anterior

        try {
            // 1. Obtener configuración actual (Runtime)
            $transport = Config::get('mail.default');
            $host = Config::get("mail.mailers.{$transport}.host");
            $port = Config::get("mail.mailers.{$transport}.port");
            $username = Config::get("mail.mailers.{$transport}.username");
            $from = Config::get('mail.from.address');

            $this->addDebug("--- INICIO DIAGNÓSTICO ---");
            $this->addDebug("Driver Configurado: " . strtoupper($transport));
            
            if ($transport === 'log') {
                $this->addDebug("⚠️ ALERTA: El sistema está en modo LOG. No se enviará ningún correo real. Revise storage/logs/laravel.log.");
            } else {
                $this->addDebug("Host: {$host} | Puerto: {$port}");
                $this->addDebug("Usuario SMTP: {$username}");
                $this->addDebug("Remitente (From): {$from}");
            }

            // 2. Intento de envío
            $this->addDebug("Intentando enviar correo a: {$this->emailTo}...");
            
            // Enviamos el correo (ahora síncrono gracias al cambio en el Mailable)
            Mail::to($this->emailTo)->send(new CustomSystemMail($this->subject, $this->messageBody));

            $this->addDebug("✅ Mail::send() ejecutado correctamente.");
            session()->flash('success', 'El proceso de envío finalizó sin errores de conexión.');

        } catch (\Exception $e) {
            $this->addDebug("❌ ERROR CRÍTICO: " . $e->getMessage());
            Log::error("Error EmailTester: " . $e->getMessage());
            session()->flash('error', 'Falló el envío. Revise el log de diagnóstico abajo.');
        }
    }

    private function addDebug($message)
    {
        $this->debugLog[] = "[" . now()->format('H:i:s') . "] " . $message;
    }

    public function render()
    {
        return view('livewire.admin.email-tester');
    }
}