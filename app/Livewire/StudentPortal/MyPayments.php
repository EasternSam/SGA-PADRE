<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\PaymentConcept;
use App\Services\MatriculaService;
use App\Services\CardnetRedirectionService; // Usamos el servicio de redirección
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
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
    
    // Campos Tarjeta (Ya no se usan directamente, pero se mantienen para lógica visual si aplica)
    // Cardnet maneja los datos sensibles en su página.

    // Campos Transferencia
    public $transferReference;

    // Campos para formulario Cardnet
    public $cardnetUrl = '';
    public $cardnetFields = [];

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
        $this->reset(['transferReference', 'paymentMethod', 'cardnetUrl', 'cardnetFields']);
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
    public function initiatePayment(MatriculaService $matriculaService, CardnetRedirectionService $cardnetService)
    {
        $this->validate();

        // 1. PAGO CON TARJETA (Redirección a Cardnet)
        if ($this->paymentMethod === 'card') {
            try {
                $payment = Payment::find($this->selectedPaymentId);
                
                if (!$payment) {
                    $this->addError('general', 'Error: Pago no encontrado.');
                    return;
                }

                // Actualizar estado a pendiente y agregar nota
                $payment->update([
                    'gateway' => 'Tarjeta',
                    'status' => 'Pendiente', 
                    'notes' => 'Redirigiendo a Cardnet...',
                ]);

                // Preparar formulario POST
                $formInfo = $cardnetService->prepareFormData($payment->amount, $payment->id, Request::ip());
                
                $this->cardnetUrl = $formInfo['url'];
                $this->cardnetFields = $formInfo['fields'];

                // Disparar evento al frontend para enviar formulario
                $this->dispatch('submit-cardnet-form');
                
            } catch (\Exception $e) {
                Log::error("Error iniciando Cardnet estudiante: " . $e->getMessage());
                $this->addError('general', 'Error al conectar con la pasarela de pagos.');
            }
        } 
        // 2. PAGO CON TRANSFERENCIA
        else {
            $this->processManualPayment($matriculaService, 'Transferencia Bancaria', $this->transferReference, 'Pendiente');
        }
    }

    /**
     * Procesa pagos manuales (Transferencia)
     */
    private function processManualPayment(MatriculaService $matriculaService, $gateway, $transactionId, $status)
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

                    // Si fuera completado directo (ej: si hubiera un método instantáneo que no sea tarjeta)
                    if ($status === 'Completado') {
                       // Lógica de activación (normalmente transferencia requiere validación manual admin)
                    } else {
                        session()->flash('message', 'Pago reportado exitosamente. Pendiente de validación por administración.');
                    }
                }
            });

            $this->closeModal();
            $this->reset('selectedPaymentId');

        } catch (\Exception $e) {
            Log::error('Error pago estudiante manual: ' . $e->getMessage());
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