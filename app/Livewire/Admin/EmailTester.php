<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomSystemMail;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')]
class EmailTester extends Component
{
    public $emailTo;
    public $subject;
    public $messageBody;

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

        try {
            // Enviar el correo usando la clase Mailable que creamos
            Mail::to($this->emailTo)->send(new CustomSystemMail($this->subject, $this->messageBody));

            session()->flash('success', '¡Correo enviado correctamente a ' . $this->emailTo . '!');
            
            // Limpiar formulario
            $this->reset(['emailTo', 'subject', 'messageBody']);

        } catch (\Exception $e) {
            session()->flash('error', 'Error al enviar el correo: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.email-tester');
    }
}