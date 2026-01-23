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
    public $selectedPaymentId; // ID del pago (deuda) seleccionado
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
        // 'cardName' => 'required_if:paymentMethod,card', // Cardnet maneja esto en su modal
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
        $this->paymentMethod = 'card'; // Default a tarjeta para Cardnet

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
     * Si es Cardnet, dispara el evento JS. Si es Transferencia, procesa directamente.
     */
    public function initiatePayment(MatriculaService $matriculaService)
    {
        // Validaciones básicas (solo referencia si es transferencia)
        $this->validate([
            'paymentMethod' => 'required|in:card,transfer',
            'transferReference' => 'required_if:paymentMethod,transfer',
        ]);

        if ($this->paymentMethod === 'card') {
            // Preparar datos para Cardnet y disparar JS
            $this->dispatch('start-cardnet-payment', [
                'amount' => $this->amountToPay,
                'orderId' => $this->selectedPaymentId, // Usamos el ID del pago como referencia
                'description' => 'Pago Matricula/Curso', // Puedes personalizar esto
                'studentName' => $this->student->full_name,
                'studentEmail' => Auth::user()->email,
                'formId' => 'cardnet-form' // ID del form oculto en la vista
            ]);
            
            // No cerramos el modal ni procesamos nada más hasta que Cardnet responda
        } else {
            // Proceso normal para Transferencia
            $this->processPayment($matriculaService, 'Transferencia Bancaria', $this->transferReference, 'Pendiente');
        }
    }

    /**
     * Método llamado desde JS cuando Cardnet devuelve el token exitosamente.
     */
    public function processCardnetPayment($token, MatriculaService $matriculaService)
    {
        // AQUÍ IRÍA LA LLAMADA AL BACKEND DE CARDNET PARA CONFIRMAR EL PAGO USANDO EL TOKEN
        // Por ahora simularemos que si hay token, es exitoso.
        // En producción: $cardnetService->processPayment($token, $amount, $orderId);

        if ($token) {
            $this->processPayment($matriculaService, 'Cardnet (Tarjeta)', $token, 'Completado');
        } else {
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
                        // Lógica de matrícula y activación
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
            Log::error('Error pago estudiante: ' . $e->getMessage());
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