<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\MatriculaService;
use App\Services\CardnetRedirectionService;
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
    public $showPaymentModal = false;
    public $selectedPaymentId; 
    public $selectedEnrollment;
    public $amountToPay = 0;
    public $paymentMethod = 'card'; 
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
        $this->reset(['transferReference', 'paymentMethod']);
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

    public function initiatePayment(MatriculaService $matriculaService, CardnetRedirectionService $cardnetService)
    {
        $this->validate();

        // CASO 1: TARJETA (Redirección)
        if ($this->paymentMethod === 'card') {
            try {
                $payment = Payment::find($this->selectedPaymentId);
                
                if (!$payment) {
                    $this->addError('general', 'Error: Pago no encontrado.');
                    return;
                }

                // 1. Marcar pago como pendiente y en proceso
                $payment->update([
                    'gateway' => 'Tarjeta',
                    'status' => 'Pendiente', 
                    'notes' => 'Redirigiendo a Cardnet...',
                ]);

                // 2. Obtener datos del formulario desde el servicio
                $formInfo = $cardnetService->prepareFormData($payment->amount, $payment->id, Request::ip());
                
                // 3. Emitir evento al navegador para que construya y envíe el form
                // Usamos 'data' para pasar el array completo
                $this->dispatch('submit-cardnet-form', data: $formInfo);
                
            } catch (\Exception $e) {
                Log::error("Error iniciando Cardnet: " . $e->getMessage());
                $this->addError('general', 'Error al conectar con la pasarela.');
            }
        } 
        // CASO 2: TRANSFERENCIA
        else {
            $this->processManualPayment($matriculaService, 'Transferencia Bancaria', $this->transferReference, 'Pendiente');
        }
    }

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
                    session()->flash('message', 'Pago reportado exitosamente. Pendiente de validación.');
                }
            });
            $this->closeModal();
            $this->reset('selectedPaymentId');
        } catch (\Exception $e) {
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
            return view('livewire.student-portal.my-payments', ['pendingDebts' => collect(), 'paymentHistory' => collect()]);
        }

        $pendingDebts = Payment::where('student_id', $this->student->id)
            ->whereIn('status', ['Pendiente', 'pendiente'])
            ->with(['enrollment.courseSchedule.module.course', 'paymentConcept'])
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