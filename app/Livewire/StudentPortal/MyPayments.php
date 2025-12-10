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
use Livewire\Attributes\Layout; // Importar atributo Layout

// Especificar el layout que usa tu aplicación (layouts.app o layouts.dashboard)
#[Layout('layouts.dashboard')] 
class MyPayments extends Component
{
    use WithPagination;

    public $student;
    
    // --- Modal de Pago ---
    public $showPaymentModal = false;
    public $selectedEnrollmentId;
    public $selectedEnrollment;
    public $amountToPay = 0;
    public $paymentMethod = 'card'; // 'card' o 'transfer'
    
    // Campos Tarjeta (Simulados)
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
        // Buscar el estudiante vinculado al usuario logueado
        $this->student = Student::where('user_id', Auth::id())->first();
    }

    public function openPaymentModal($enrollmentId)
    {
        $this->resetValidation();
        $this->reset(['cardName', 'cardNumber', 'cardExpiry', 'cardCvc', 'transferReference', 'paymentMethod']);
        $this->paymentMethod = 'card';

        $this->selectedEnrollmentId = $enrollmentId;
        $this->selectedEnrollment = Enrollment::with('courseSchedule.module')->findOrFail($enrollmentId);
        
        // Determinar el monto a pagar
        if ($this->selectedEnrollment->payment && $this->selectedEnrollment->payment->status == 'Pendiente') {
            $this->amountToPay = $this->selectedEnrollment->payment->amount;
        } else {
            $this->amountToPay = $this->selectedEnrollment->courseSchedule->module->price ?? 0;
        }

        $this->showPaymentModal = true;
    }

    public function closeModal()
    {
        $this->showPaymentModal = false;
    }

    public function processPayment(MatriculaService $matriculaService)
    {
        $this->validate();

        if (!$this->student) return;

        try {
            DB::transaction(function () use ($matriculaService) {
                
                $status = ($this->paymentMethod === 'card') ? 'Completado' : 'Pendiente';
                $gateway = ($this->paymentMethod === 'card') ? 'Tarjeta Online' : 'Transferencia Bancaria';
                
                $reference = ($this->paymentMethod === 'card') 
                    ? 'TX-' . strtoupper(uniqid()) 
                    : $this->transferReference;

                $payment = Payment::updateOrCreate(
                    [
                        'enrollment_id' => $this->selectedEnrollmentId,
                        'status' => 'Pendiente'
                    ],
                    [
                        'student_id' => $this->student->id,
                        'payment_concept_id' => $this->selectedEnrollment->courseSchedule->module->payment_concept_id ?? 1, 
                        'amount' => $this->amountToPay,
                        'currency' => 'DOP',
                        'status' => $status,
                        'gateway' => $gateway,
                        'transaction_id' => $reference,
                        'user_id' => Auth::id(),
                    ]
                );

                if ($status === 'Completado') {
                    if (!$this->student->student_code) {
                        $matriculaService->generarMatricula($payment);
                        $this->student->refresh();
                    }

                    $enrollment = Enrollment::find($this->selectedEnrollmentId);
                    if ($enrollment) {
                        $enrollment->status = 'Cursando';
                        $enrollment->save();
                    }

                    session()->flash('message', '¡Pago realizado con éxito! Tu inscripción está activa.');
                } else {
                    session()->flash('message', 'Pago reportado. Tu inscripción se activará al validar la transferencia.');
                }
            });

            $this->closeModal();

        } catch (\Exception $e) {
            Log::error('Error pago estudiante: ' . $e->getMessage());
            $this->addError('general', 'Error procesando el pago. Intente más tarde.');
        }
    }

    public function render()
    {
        if (!$this->student) {
            return view('livewire.student-portal.my-payments', [
                'pendingDebts' => collect(),
                'paymentHistory' => collect()
            ]);
        }

        $pendingDebts = Enrollment::where('student_id', $this->student->id)
            ->whereIn('status', ['Pendiente', 'pendiente'])
            ->with(['courseSchedule.module.course', 'courseSchedule.teacher', 'payment'])
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