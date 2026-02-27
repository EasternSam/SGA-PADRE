<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\Payment;
use App\Services\EcfService;
use App\Services\MatriculaService;
use App\Services\AccountingEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PaymentResult extends Component
{
    public $status = 'processing'; // 'processing', 'success', 'error'
    public $message = 'Verificando transacción...';
    public $orderId;
    public $authCode;
    public $paymentDetails;

    public function mount(Request $request, EcfService $ecfService, MatriculaService $matriculaService, AccountingEngine $accountingEngine)
    {
        Log::info('Kiosk Cardnet Debug: Retorno detectado', $request->all());

        $this->orderId = $request->input('OrdenId') ?? $request->input('OrdenID');
        $responseCode = $request->input('ResponseCode');
        $this->authCode = $request->input('AuthorizationCode');
        $txId = $request->input('TransactionId');
        
        $responseMessage = $request->input('ResponseMessage') ?? $request->input('ResponseMsg') ?? 'Transacción declinada';

        if (!$this->orderId) {
            $this->status = 'error';
            $this->message = 'No se recibió un número de orden válido desde el banco.';
            return;
        }

        $payment = Payment::with('student', 'paymentConcept', 'enrollment.courseSchedule.module')->find($this->orderId);

        if (!$payment) {
            $this->status = 'error';
            $this->message = 'Error crítico: Pago no encontrado en el sistema.';
            return;
        }

        // Si se perdió la sesión (a veces pasa en redirecciones externas), intentamos restaurarla en Kiosco
        if (!Auth::check() && $payment->user_id) {
            Auth::loginUsingId($payment->user_id);
        }

        $this->paymentDetails = $payment;

        if ($responseCode === '00') {
            // PAGO APROBADO
            $this->status = 'success';
            $this->message = '¡Pago Aprobado Exitosamente!';
            
            // Ya estaba pagado? (Doble callback prevention)
            if ($payment->status === 'Completado') {
                return; 
            }

            $payment->update([
                'status' => 'Completado',
                'transaction_id' => $this->authCode,
                'notes' => "Aprobado Cardnet Kiosco | Ref: {$txId} | Auth: {$this->authCode}",
            ]);

            try {
                // 1. Asentar Contabilidad (Accounting Engine)
                $accountingEngine->registerStudentPayment($payment);

                // 2. Emitir Comprobante Fiscal B02 (ECF)
                $ecfService->emitirComprobante($payment);
                
                // 3. Activar Inscripción y Moodle si aplica
                if ($payment->enrollment) {
                    $payment->enrollment->status = 'Cursando';
                    $payment->enrollment->save();
                    $matriculaService->activarInscripcion($payment); // Llama a Moodle auto
                }
                
                // 4. Generar Matrícula si es Nuevo
                $student = $payment->student;
                if ($student && !$student->student_code && $payment->paymentConcept && stripos($payment->paymentConcept->name, 'Inscripción') !== false) {
                    $matriculaService->generarMatricula($payment);
                }

                // Aquí idealmente enviaríamos el recibo por email, el código original de web.php lo hace.

            } catch (\Exception $e) {
                Log::error("Kiosk Cardnet Error post-proceso (Orden {$this->orderId}): " . $e->getMessage());
            }

        } else {
            // PAGO RECHAZADO
            $this->status = 'error';
            $this->message = "El pago fue rechazado. Razón: {$responseMessage}";

            $payment->update([
                'status' => 'Pendiente', 
                'notes' => "Intento fallido Kiosco [{$responseCode}]: {$responseMessage}",
            ]);
            Log::warning("Kiosk Cardnet Rechazo: Orden {$this->orderId} - Código {$responseCode} - Msg: {$responseMessage}");
        }
    }

    public function goDashboard()
    {
        return $this->redirectRoute('kiosk.dashboard', navigate: true);
    }

    public function render()
    {
        return view('livewire.kiosk.payment-result')->layout('layouts.kiosk');
    }
}
