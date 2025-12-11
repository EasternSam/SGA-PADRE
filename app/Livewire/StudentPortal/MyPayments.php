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
    public $selectedEnrollmentId;
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

    public function openPaymentModal($enrollmentId)
    {
        $this->resetValidation();
        $this->reset(['cardName', 'cardNumber', 'cardExpiry', 'cardCvc', 'transferReference', 'paymentMethod']);
        $this->paymentMethod = 'card';

        $this->selectedEnrollmentId = $enrollmentId;
        
        // Cargar con curso padre para acceder a registration_fee
        $this->selectedEnrollment = Enrollment::with('courseSchedule.module.course', 'payment')->findOrFail($enrollmentId);
        
        // --- LOGICA DE MONTO CORREGIDA ---
        if ($this->selectedEnrollment->payment && $this->selectedEnrollment->payment->status == 'Pendiente') {
            // Si ya hay un pago generado, usamos ese monto (es lo más seguro)
            $this->amountToPay = $this->selectedEnrollment->payment->amount;
        } else {
            // Si no hay pago (raro), asumimos que es el primer pago (Inscripción)
            $this->amountToPay = $this->selectedEnrollment->courseSchedule->module->course->registration_fee ?? 0;
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

                // Buscar el pago existente primero
                $existingPayment = Payment::where('enrollment_id', $this->selectedEnrollmentId)
                    ->where('status', 'Pendiente')
                    ->first();

                if ($existingPayment) {
                    // Actualizar pago existente
                    $existingPayment->update([
                        'status' => $status,
                        'gateway' => $gateway,
                        'transaction_id' => $reference,
                        'user_id' => Auth::id(), // Quién pagó
                        // No tocamos el monto ni el concepto, respetamos lo que estaba generado
                    ]);
                    $payment = $existingPayment;
                } else {
                    // Crear nuevo pago (Fallback)
                    // Si no existía, asumimos que es inscripción
                    $concept = PaymentConcept::firstOrCreate(['name' => 'Inscripción']);
                    
                    $payment = Payment::create([
                        'enrollment_id' => $this->selectedEnrollmentId,
                        'student_id' => $this->student->id,
                        'payment_concept_id' => $concept->id,
                        'amount' => $this->amountToPay,
                        'currency' => 'DOP',
                        'status' => $status,
                        'gateway' => $gateway,
                        'transaction_id' => $reference,
                        'user_id' => Auth::id(),
                    ]);
                }

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
    
    public function downloadFinancialReport()
    {
        // Asegúrate de tener la ruta 'reports.financial-report' definida
        $url = route('reports.financial-report', $this->student->id); 
        $this->dispatch('open-pdf-modal', url: $url);
    }
}

