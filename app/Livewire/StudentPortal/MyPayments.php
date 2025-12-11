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
    public $selectedPaymentId; // <-- Nuevo: ID específico del pago a procesar
    public $selectedEnrollment; // Información de contexto
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
        'cardName' => 'required_if:paymentMethod,card',
        'cardNumber' => 'required_if:paymentMethod,card|min:16|max:19',
        'cardExpiry' => 'required_if:paymentMethod,card',
        'cardCvc' => 'required_if:paymentMethod,card|min:3|max:4',
        'transferReference' => 'required_if:paymentMethod,transfer',
    ];

    protected $messages = [
        'required_if' => 'Este campo es obligatorio.',
        'cardNumber.min' => 'Número de tarjeta inválido.',
        'cardCvc.min' => 'CVC inválido.',
    ];

    public function mount()
    {
        $this->student = Student::where('user_id', Auth::id())->first();
    }

    /**
     * Abre el modal para pagar un PAGO específico (no solo una inscripción).
     */
    public function openPaymentModal($paymentId)
    {
        $this->resetValidation();
        $this->reset(['cardName', 'cardNumber', 'cardExpiry', 'cardCvc', 'transferReference', 'paymentMethod']);
        $this->paymentMethod = 'card';

        $this->selectedPaymentId = $paymentId;
        
        // Buscar el pago específico
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

    public function processPayment(MatriculaService $matriculaService)
    {
        $this->validate();

        if (!$this->student || !$this->selectedPaymentId) return;

        try {
            DB::transaction(function () use ($matriculaService) {
                
                $status = ($this->paymentMethod === 'card') ? 'Completado' : 'Pendiente';
                $gateway = ($this->paymentMethod === 'card') ? 'Tarjeta Online' : 'Transferencia Bancaria';
                
                $reference = ($this->paymentMethod === 'card') 
                    ? 'TX-' . strtoupper(uniqid()) 
                    : $this->transferReference;

                // Buscar el pago real por ID
                $payment = Payment::find($this->selectedPaymentId);

                if ($payment) {
                    // Actualizar el pago existente
                    $payment->update([
                        'status' => $status,
                        'gateway' => $gateway,
                        'transaction_id' => $reference,
                        'user_id' => Auth::id(),
                    ]);

                    if ($status === 'Completado') {
                        // Si es inscripción inicial, generar matrícula
                        if (!$this->student->student_code && $payment->paymentConcept && $payment->paymentConcept->name === 'Inscripción') {
                            $matriculaService->generarMatricula($payment);
                            $this->student->refresh();
                        }

                        // Activar inscripción si estaba pendiente
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

        // --- CORRECCIÓN CLAVE: Buscar PAGOS pendientes, no Inscripciones ---
        // Esto traerá tanto la inscripción inicial pendiente como las mensualidades vencidas
        $pendingDebts = Payment::where('student_id', $this->student->id)
            ->whereIn('status', ['Pendiente', 'pendiente'])
            ->with(['enrollment.courseSchedule.module.course', 'enrollment.courseSchedule.teacher'])
            ->orderBy('due_date', 'asc') // Ordenar por fecha de vencimiento (más urgente primero)
            ->get();

        // Historial (excluyendo los pendientes que mostramos arriba para no duplicar visualmente si se desea, 
        // pero generalmente el historial muestra todo o solo lo pasado. Aquí mostramos todo ordenado por fecha)
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