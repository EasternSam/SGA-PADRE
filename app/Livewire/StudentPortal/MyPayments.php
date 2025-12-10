<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\MatriculaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;

class MyPayments extends Component
{
    use WithPagination;

    public $student;
    
    // Propiedades del Modal de Pago
    public $showPaymentModal = false;
    public $selectedEnrollmentId;
    public $selectedEnrollment;
    public $amountToPay = 0;
    public $paymentMethod = 'card'; // 'card', 'transfer'
    
    // Campos simulados de tarjeta
    public $cardName;
    public $cardNumber;
    public $cardExpiry;
    public $cardCvc;

    // Campos de transferencia
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
        'required_if' => 'Este campo es obligatorio para el método seleccionado.',
        'cardNumber.min' => 'El número de tarjeta es inválido.',
        'cardCvc.min' => 'CVC inválido.',
    ];

    public function mount()
    {
        // Obtener el estudiante asociado al usuario logueado
        $this->student = Student::where('user_id', Auth::id())->firstOrFail();
    }

    public function openPaymentModal($enrollmentId)
    {
        $this->resetValidation();
        $this->reset(['cardName', 'cardNumber', 'cardExpiry', 'cardCvc', 'transferReference', 'paymentMethod']);
        $this->paymentMethod = 'card'; // Default

        $this->selectedEnrollmentId = $enrollmentId;
        $this->selectedEnrollment = Enrollment::with('courseSchedule.module')->findOrFail($enrollmentId);
        
        // Calcular monto (precio del módulo - pagos parciales si hubieran)
        // Por ahora asumimos pago total del módulo
        $this->amountToPay = $this->selectedEnrollment->courseSchedule->module->price ?? 0;

        $this->showPaymentModal = true;
    }

    public function closeModal()
    {
        $this->showPaymentModal = false;
    }

    public function processPayment(MatriculaService $matriculaService)
    {
        $this->validate();

        try {
            DB::transaction(function () use ($matriculaService) {
                
                // 1. Determinar estado según método de pago
                // Tarjeta = Completado (Simulación de éxito)
                // Transferencia = Pendiente (Requiere revisión administrativa)
                $status = ($this->paymentMethod === 'card') ? 'Completado' : 'Pendiente';
                $gateway = ($this->paymentMethod === 'card') ? 'Tarjeta de Crédito (Online)' : 'Transferencia Bancaria';
                $reference = ($this->paymentMethod === 'card') ? 'ONLINE-' . strtoupper(uniqid()) : $this->transferReference;

                // 2. Crear el registro de Pago
                $payment = Payment::create([
                    'student_id' => $this->student->id,
                    'enrollment_id' => $this->selectedEnrollmentId,
                    'payment_concept_id' => $this->selectedEnrollment->courseSchedule->module->payment_concept_id ?? 1, // Fallback a ID 1 o buscar 'Mensualidad'
                    'amount' => $this->amountToPay,
                    'currency' => 'DOP',
                    'status' => $status,
                    'gateway' => $gateway,
                    'transaction_id' => $reference,
                    'user_id' => Auth::id(), // El propio estudiante registra su acción
                ]);

                // 3. Actualizar la Inscripción si el pago fue exitoso (Tarjeta)
                if ($status === 'Completado') {
                    
                    // Si el estudiante es nuevo (sin matrícula), generar matrícula
                    if (!$this->student->student_code) {
                        $matriculaService->generarMatricula($payment);
                    }

                    // Activar la inscripción
                    $enrollment = Enrollment::find($this->selectedEnrollmentId);
                    $enrollment->status = 'Cursando';
                    $enrollment->save();

                    session()->flash('message', '¡Pago procesado con éxito! Tu inscripción está activa.');
                } else {
                    session()->flash('message', 'Pago reportado correctamente. Esperando validación administrativa.');
                }

            });

            $this->closeModal();
            // No necesitamos $this->dispatch('$refresh') porque Livewire refresca automáticamente al cambiar estado

        } catch (\Exception $e) {
            Log::error('Error en pago estudiante: ' . $e->getMessage());
            $this->addError('general', 'Ocurrió un error al procesar el pago. Intente nuevamente.');
        }
    }

    public function render()
    {
        // 1. Deudas Pendientes (Inscripciones en estado Pendiente)
        $pendingDebts = Enrollment::where('student_id', $this->student->id)
            ->whereIn('status', ['Pendiente', 'pendiente'])
            ->with(['courseSchedule.module.course', 'courseSchedule.teacher'])
            ->get();

        // 2. Historial de Pagos
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