<?php

namespace App\Livewire\Kiosk\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends Component
{
    public $document_id = '';
    public $pin = '';
    public $errorMessage = '';

    // Configuración para el teclado numérico en pantalla
    public $focusedInput = 'document_id'; // Puede ser 'document_id' o 'pin'
    
    public function setFocus($input)
    {
        $this->focusedInput = $input;
        $this->errorMessage = ''; // Limpiamos errores al cambiar de input
    }
    
    public function appendDigit($digit)
    {
        if ($this->focusedInput === 'document_id') {
            // Asumimos un máximo de 15 caracteres para la cédula
            if (strlen($this->document_id) < 15) {
                $this->document_id .= $digit;
            }
        } elseif ($this->focusedInput === 'pin') {
            if (strlen($this->pin) < 4) {
                $this->pin .= $digit;
            }
        }
    }
    
    public function deleteDigit()
    {
        if ($this->focusedInput === 'document_id') {
            $this->document_id = substr($this->document_id, 0, -1);
        } elseif ($this->focusedInput === 'pin') {
            $this->pin = substr($this->pin, 0, -1);
        }
    }
    
    public function clearInput()
    {
        if ($this->focusedInput === 'document_id') {
            $this->document_id = '';
        } elseif ($this->focusedInput === 'pin') {
            $this->pin = '';
        }
    }

    public function login()
    {
        $this->errorMessage = '';

        if (empty($this->document_id)) {
            $this->errorMessage = 'Por favor ingrese su Cédula / Matrícula.';
            $this->focusedInput = 'document_id';
            return;
        }

        if (empty($this->pin)) {
            $this->errorMessage = 'Por favor ingrese su PIN de 4 dígitos.';
            $this->focusedInput = 'pin';
            return;
        }

        // Buscar al estudiante por su Cédula o Matrícula
        $user = User::whereHas('student', function ($q) {
            $q->where('cedula', $this->document_id)
              ->orWhere('student_code', $this->document_id);
        })->first();

        // Si no lo encuentra por cédula, intentar por email en el modelo User directamente
        if (!$user) {
            $user = User::where('email', $this->document_id)->first();
        }

        if (!$user) {
            $this->errorMessage = 'No se encontró ningún estudiante con esa Identificación.';
            $this->document_id = '';
            $this->pin = '';
            $this->focusedInput = 'document_id';
            return;
        }

        // Verificar si es estudiante (o solicitante)
        if (!$user->hasRole(['Estudiante'])) {
            $this->errorMessage = 'Solo los estudiantes pueden usar el Kiosco.';
            return;
        }

        // Verificar PIN
        if ($user->kiosk_pin === null) {
            $this->errorMessage = 'Su cuenta no tiene un PIN configurado. Por favor solicítelo en caja.';
            return;
        }

        if ($user->kiosk_pin !== $this->pin) {
            $this->errorMessage = 'El PIN ingresado es incorrecto.';
            $this->pin = '';
            $this->focusedInput = 'pin';
            return;
        }

        // Verificar si tiene acceso prohibido/expirado
        if (!$user->hasActiveAccess()) {
            $this->errorMessage = 'Su acceso al sistema está suspendido por falta de pago.';
            return;
        }

        // Autenticar manualmente
        Auth::login($user);

        // Regenerar sesión por seguridad
        session()->regenerate();

        // Redirigir al dashboard del kiosco
        // return redirect()->route('kiosk.dashboard');
        // Temporalmente enviar a una página cualquiera o hacer un DD para que el usuario vea que funcionó
        return redirect()->to('/kiosk/dashboard'); 
    }

    public function render()
    {
        return view('livewire.kiosk.auth.login')->layout('layouts.kiosk');
    }
}
