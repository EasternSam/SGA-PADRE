<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Services\MatriculaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')] 
class MyPayments extends Component
{
    use WithPagination;

    public $student;
    
    // --- Modal de Pago ---
    public $showPaymentModal = false;
    public $selectedPaymentId; 
    public $selectedEnrollment; 
    public $amountToPay = 0;
    public $paymentMethod = 'card'; 
    
    // Campos Tarjeta
    public $cardName;
    public $cardNumber;
    public $cardExpiry;
    public $cardCvc;

    // Campos Transferencia
    public $transferReference;

    protected $rules = [
        'paymentMethod' => 'required|in:card,transfer',
        'transferReference' => 'required_if:paymentMethod,transfer',
    ];

    protected $messages = [
        'required_if' => 'Este campo es obligatorio.',
    ];

    public function mount()
    {
        $this->student = Student::where('user_id', Auth::id())->first();
    }

    public function openPaymentModal($paymentId)
    {
        $this->resetValidation();
        $this->reset(['cardName', 'cardNumber', 'cardExpiry', 'cardCvc', 'transferReference', 'paymentMethod']);
        $this->paymentMethod = 'card'; 

        $this->selectedPaymentId = $paymentId;
        
        $payment = Payment::with('enrollment.courseSchedule.module')->find($paymentId);
        
        if (!$payment) {
            $this->addError('general', 'El pago seleccionado no existe.');
            return;
        }

        $this->selectedEnrollment = $payment->enrollment;
        $this->amountToPay = $payment->amount;

        $this->showPaymentModal = true;
    }

    public function closeModal()
    {
        $this->showPaymentModal = false;
    }

    /**
     * Inicia el proceso de pago.
     */
    public function initiatePayment(MatriculaService $matriculaService)
    {
        Log::info('--- INICIO DEBUG PAGO ---');
        Log::info('Usuario intentando pagar: ' . Auth::id());
        Log::info('Método seleccionado: ' . $this->paymentMethod);
        Log::info('Monto: ' . $this->amountToPay);

        // Validaciones básicas
        $this->validate([
            'paymentMethod' => 'required|in:card,transfer',
            'transferReference' => 'required_if:paymentMethod,transfer',
        ]);

        if ($this->paymentMethod === 'card') {
            Log::info('Flujo Tarjeta detectado.');

            // Verificar datos críticos
            if (!$this->student) {
                Log::error('ERROR: No se encontró estudiante asociado al usuario.');
                $this->addError('general', 'Error de perfil de estudiante.');
                return;
            }

            // Preparar payload
            $payload = [
                'amount' => $this->amountToPay,
                'orderId' => $this->selectedPaymentId, 
                'description' => 'Pago #' . $this->selectedPaymentId, 
                'studentName' => $this->student->full_name,
                'studentEmail' => Auth::user()->email,
                'formId' => 'cardnet-form' 
            ];

            Log::info('Payload preparado para Cardnet:', $payload);
            
            // Disparar evento
            // NOTA: Enviamos el array directamente. En la vista manejaremos si llega anidado o no.
            $this->dispatch('start-cardnet-payment', $payload);
            
            Log::info('Evento start-cardnet-payment DISPARADO desde PHP.');
            
        } else {
            Log::info('Flujo Transferencia detectado.');
            $this->processPayment($matriculaService, 'Transferencia Bancaria', $this->transferReference, 'Pendiente');
        }
    }

    /**
     * Método llamado desde JS cuando Cardnet devuelve el token exitosamente.
     */
    public function processCardnetPayment($token, MatriculaService $matriculaService)
    {
        Log::info('--- CALLBACK CARDNET RECIBIDO ---');
        Log::info('Token recibido: ' . $token);

        if ($token) {
            $this->processPayment($matriculaService, 'Cardnet (Tarjeta)', $token, 'Completado');
        } else {
            Log::error('Error: Token vacío recibido desde JS.');
            $this->addError('general', 'Error al procesar con Cardnet. Token no recibido.');
        }
    }

    /**
     * Lógica centralizada de procesamiento de pago
     */
    private function processPayment(MatriculaService $matriculaService, $gateway, $transactionId, $status)
    {
        if (!$this->student || !$this->selectedPaymentId) return;

        try {
            DB::transaction(function () use ($matriculaService, $gateway, $transactionId, $status) {
                
                $payment = Payment::find($this->selectedPaymentId);

                if ($payment) {
                    $payment->update([
                        'status' => $status,
                        'gateway' => $gateway,
                        'transaction_id' => $transactionId,
                        'user_id' => Auth::id(),
                    ]);

                    if ($status === 'Completado') {
                        if (!$this->student->student_code && $payment->paymentConcept && stripos($payment->paymentConcept->name, 'Inscripción') !== false) {
                            $matriculaService->generarMatricula($payment);
                            $this->student->refresh();
                        }

                        $enrollment = $payment->enrollment;
                        if ($enrollment && $enrollment->status === 'Pendiente') {
                            $enrollment->status = 'Cursando';
                            $enrollment->save();
                        }

                        session()->flash('message', '¡Pago realizado con éxito!');
                    } else {
                        session()->flash('message', 'Pago reportado. Pendiente de validación.');
                    }
                }
            });

            $this->closeModal();
            $this->reset('selectedPaymentId');

        } catch (\Exception $e) {
            Log::error('Error pago estudiante DB: ' . $e->getMessage());
            $this->addError('general', 'Error procesando el pago. Intente más tarde.');
        }
    }

    public function downloadFinancialReport()
    {
        $url = route('reports.financial-report', $this->student->id); 
        $this->dispatch('open-pdf-modal', url: $url);
    }

    public function render()
    {
        if (!$this->student) {
            return view('livewire.student-portal.my-payments', [
                'pendingDebts' => collect(),
                'paymentHistory' => collect()
            ]);
        }

        $pendingDebts = Payment::where('student_id', $this->student->id)
            ->whereIn('status', ['Pendiente', 'pendiente'])
            ->with(['enrollment.courseSchedule.module.course', 'enrollment.courseSchedule.teacher'])
            ->orderBy('due_date', 'asc')
            ->get();

        $paymentHistory = Payment::where('student_id', $this->student->id)
            ->with(['paymentConcept', 'enrollment.courseSchedule.module'])
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('livewire.student-portal.my-payments', [
            'pendingDebts' => $pendingDebts,
            'paymentHistory' => $paymentHistory
        ]);
    }
}