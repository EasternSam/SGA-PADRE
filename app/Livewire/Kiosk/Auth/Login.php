<?php

namespace App\Livewire\Kiosk\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Login extends Component
{
    public $document_id = '';
    public $pin = '';
    public $errorMessage = '';

    // QR WhatsApp Style Login
    public $qrToken;
    public $qrUrl;
    public $qrSvg;

    // Configuración para el teclado numérico en pantalla
    public $focusedInput = 'document_id'; // Puede ser 'document_id' o 'pin'
    
    public function mount()
    {
        $this->refreshQr();
    }

    public function refreshQr()
    {
        // 1. Generate unique session token
        $this->qrToken = Str::uuid()->toString();
        
        // 2. Store in cache for 2 minutes (pending state)
        Cache::put("kiosk_qr_{$this->qrToken}", ['status' => 'pending', 'user_id' => null], now()->addMinutes(2));
        
        // 3. Generate the authorization URL for the phone to scan
        $this->qrUrl = route('kiosk.auth.mobile', ['token' => $this->qrToken]);
        
        // 4. Generate SVG QR Code visually
        $options = new QROptions([
            'version'      => 5,
            'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'     => QRCode::ECC_L,
            'addQuietzone' => false,
            'imageBase64'  => false,
        ]);
        $this->qrSvg = (new QRCode($options))->render($this->qrUrl);
    }

    public function checkQrAuthorization()
    {
        $sessionData = Cache::get("kiosk_qr_{$this->qrToken}");

        if (!$sessionData) {
            // Token expired, refresh QR code automatically
            $this->refreshQr();
            return;
        }

        if ($sessionData['status'] === 'authorized' && !empty($sessionData['user_id'])) {
            // Phone authorized the login!
            $user = User::find($sessionData['user_id']);
            
            if ($user && $user->hasRole(['Estudiante']) && $user->hasActiveAccess()) {
                // Destroy token to prevent reuse
                Cache::forget("kiosk_qr_{$this->qrToken}");
                
                // Login
                Auth::login($user);
                session()->regenerate();
                
                // Redirect
                return redirect()->to('/kiosk/dashboard');
            } else {
                $this->errorMessage = 'El usuario autenticado no tiene permisos para usar el Kiosco.';
                $this->refreshQr(); // Generate new one just in case
            }
        }
    }

    public function setFocus($input)
    {
        $this->focusedInput = $input;
        $this->errorMessage = ''; // Limpiamos errores al cambiar de input
    }
    
    public function magicScanInput($scannedData)
    {
        // 1. Limpiar el string escaneado (eliminar guiones o espacios si el escáner los envía)
        // Solo dejamos números y letras (por si escanean matrículas con letras)
        $cleanData = preg_replace('/[^a-zA-Z0-9]/', '', $scannedData);
        
        // 2. Asignarlo al campo de Document ID
        // Truncamos preventivamente a 15 caracteres máximo
        $this->document_id = substr($cleanData, 0, 15);
        
        // 3. Cambiar automáticamente el foco al PIN para que el usuario solo tenga que 
        // teclear su clave secreta y presionar Entrar.
        $this->focusedInput = 'pin';
        $this->errorMessage = '';
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

        // Buscar al estudiante por su Cédula (con o sin guiones) o Matrícula
        $user = User::whereHas('student', function ($q) {
            // Limpiamos los guiones tanto del input como de la DB para una búsqueda robusta
            $cleanInput = preg_replace('/[^0-9a-zA-Z]/', '', $this->document_id);
            
            $q->whereRaw("REPLACE(cedula, '-', '') = ?", [$cleanInput])
              ->orWhere('student_code', $cleanInput)
              ->orWhere('cedula', $this->document_id)
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
