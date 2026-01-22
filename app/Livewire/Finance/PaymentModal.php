<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use App\Models\Student;
use App\Models\PaymentConcept;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Services\MatriculaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class PaymentModal extends Component
{
    // --- PROPIEDADES DE ESTUDIANTE Y BÚSQUEDA ---
    public ?Student $student = null;
    public $search_query = '';
    public $student_results = [];
    public $show_search = true;

    public $show = false;

    // Propiedades del formulario
    public $student_id;
    public $payment_id = null;
    public $payment_concept_id;
    public $amount = 0.00;
    public $status = 'Completado';
    public $gateway = 'Efectivo';
    public $transaction_id = null; 
    public $notes = null;

    // --- POS: EFECTIVO Y CAMBIO ---
    public $cash_received = 0.00;
    public $change_amount = 0.00;

    // Propiedades de vinculación
    public $enrollment_id = null; 
    public $payment_id_to_update = null; 
    
    // Colecciones de datos
    public Collection $payment_concepts;
    public Collection $pendingDebts; // Nueva colección unificada para la vista

    public bool $isAmountDisabled = false;
    public bool $isConceptDisabled = false; 

    protected function rules()
    {
        return [
            'student_id' => 'required|exists:students,id',
            'payment_concept_id' => [
                Rule::requiredIf(empty($this->enrollment_id) && empty($this->payment_id_to_update)),
                'nullable',
                'exists:payment_concepts,id'
            ],
            'enrollment_id' => 'nullable|exists:enrollments,id',
            'amount' => 'required|numeric|min:0.01',
            // El gateway solo es requerido si el pago se completa ahora
            'gateway' => [
                Rule::requiredIf($this->status === 'Completado'),
                'string',
                'max:100'
            ],
            'status' => 'required|string|max:50',
            // La referencia es requerida si es un pago completado NO en efectivo
            'transaction_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn() => 
                    $this->status === 'Completado' && 
                    $this->gateway !== 'Efectivo' && 
                    $this->gateway !== 'Otro'
                )
            ],
            // Validar efectivo si es pago completado en efectivo
            'cash_received' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($this->status === 'Completado' && $this->gateway === 'Efectivo' && $value < $this->amount) {
                        $fail('El efectivo recibido es menor al monto a pagar.');
                    }
                },
            ],
        ];
    }
    
    protected $messages = [
        'student_id.required' => 'Debe seleccionar un estudiante.',
        'payment_concept_id.required' => 'Debe seleccionar un concepto de pago.',
        'amount.required' => 'El monto no puede estar vacío.',
        'transaction_id.required_if' => 'El número de referencia es obligatorio para pagos completados con este método.',
    ];

    #[On('openPaymentModal')]
    public function openModal()
    {
        $this->resetForm();
        if ($this->student) {
            $this->loadInitialData();
        }
        $this->show = true;
    }

    #[On('payEnrollment')]
    public function openForEnrollment($enrollmentId)
    {
        $this->resetForm();
        $enrollment = Enrollment::with('student')->find($enrollmentId);
        
        if ($enrollment && $enrollment->student) {
            $this->selectStudent($enrollment->student->id);
            $this->enrollment_id = $enrollmentId; 
            // Cargar datos de la deuda
            $this->selectDebt('enrollment', $enrollmentId);
            $this->show = true;
        }
    }

    public function mount(?Student $student = null)
    {
        $this->payment_concepts = collect();
        $this->pendingDebts = collect();

        if ($student && $student->exists) {
            $this->selectStudent($student->id);
        } else {
            $this->payment_concepts = PaymentConcept::orderBy('name')->get();
        }
    }

    // --- LÓGICA DE BÚSQUEDA ---
    public function updatedSearchQuery()
    {
        if (strlen($this->search_query) < 2) {
            $this->student_results = [];
            return;
        }

        $this->student_results = Student::query()
            ->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->search_query . '%')
            ->orWhere('student_code', 'like', '%' . $this->search_query . '%')
            ->orWhere('email', 'like', '%' . $this->search_query . '%')
            ->limit(5)
            ->get();
    }

    public function selectStudent($studentId)
    {
        $this->student = Student::find($studentId);
        
        if ($this->student) {
            $this->student_id = $this->student->id;
            $this->search_query = ''; 
            $this->student_results = [];
            $this->show_search = false; 
            $this->loadInitialData(); 
        }
    }

    public function clearStudent()
    {
        $this->student = null;
        $this->student_id = null;
        $this->pendingDebts = collect();
        $this->show_search = true;
        $this->resetForm();
    }

    public function loadInitialData()
    {
        try {
            $this->payment_concepts = PaymentConcept::orderBy('name')->get();
        } catch (\Exception $e) {
            $this->payment_concepts = collect();
        }

        if ($this->student_id) {
            $this->loadPendingDebts();
        }
    }

    public function loadPendingDebts()
    {
        $this->pendingDebts = collect();

        // 1. Obtener Pagos ya registrados como "Pendiente" (Deudas explícitas)
        $payments = Payment::where('student_id', $this->student_id)
            ->where('status', 'Pendiente')
            ->with('paymentConcept', 'enrollment.courseSchedule.module')
            ->get();

        foreach ($payments as $p) {
            $conceptName = $p->paymentConcept->name ?? $p->description ?? 'Deuda General';
            if ($p->enrollment) {
                $conceptName .= ' - ' . ($p->enrollment->courseSchedule->module->name ?? '');
            }

            $this->pendingDebts->push([
                'type' => 'payment',
                'id' => $p->id,
                'concept' => $conceptName,
                'amount' => $p->amount,
                'date' => $p->created_at,
                'is_enrollment' => false
            ]);
        }

        // 2. Obtener Inscripciones "Pendiente" que NO tienen pago asociado aún (Deuda implícita)
        $enrollments = Enrollment::where('student_id', $this->student_id)
            ->where('status', 'Pendiente')
            ->doesntHave('payment') // Solo las que no tienen un pago ya creado
            ->with('courseSchedule.module.course')
            ->get();

        foreach ($enrollments as $e) {
            $this->pendingDebts->push([
                'type' => 'enrollment',
                'id' => $e->id,
                'concept' => 'Inscripción: ' . ($e->courseSchedule->module->course->name ?? 'Curso') . ' - ' . ($e->courseSchedule->module->name ?? ''),
                'amount' => $e->courseSchedule->module->price ?? 0.00,
                'date' => $e->created_at,
                'is_enrollment' => true
            ]);
        }
    }

    /**
     * Método para seleccionar una deuda de la lista y rellenar el formulario
     */
    public function selectDebt($type, $id)
    {
        $this->resetPaymentFields();
        $this->resetErrorBag();

        if ($type === 'payment') {
            $payment = Payment::find($id);
            if ($payment) {
                $this->payment_id_to_update = $payment->id;
                $this->amount = $payment->amount;
                $this->payment_concept_id = $payment->payment_concept_id;
                $this->enrollment_id = $payment->enrollment_id;
                
                $this->isAmountDisabled = true; // Bloquear monto al pagar deuda existente
                $this->isConceptDisabled = true;
                $this->status = 'Completado'; // Asumimos que quiere pagar
            }
        } elseif ($type === 'enrollment') {
            $enrollment = Enrollment::with('courseSchedule.module')->find($id);
            if ($enrollment) {
                $this->enrollment_id = $enrollment->id;
                $this->amount = $enrollment->courseSchedule->module->price ?? 0.00;
                // Intentar buscar el concepto "Inscripción" o similar, o dejar nulo
                $this->payment_concept_id = null; // El usuario puede seleccionar el concepto si no está definido
                
                $this->isAmountDisabled = true;
                $this->status = 'Completado';
            }
        }
        
        $this->calculateChange();
    }

    private function resetPaymentFields()
    {
        $this->reset(['amount', 'payment_concept_id', 'isAmountDisabled', 'isConceptDisabled', 'payment_id_to_update', 'enrollment_id', 'transaction_id']);
        $this->amount = 0.00;
        $this->gateway = 'Efectivo';
        $this->status = 'Completado';
    }

    public function updatedPaymentConceptId($value)
    {
        if (!$this->isConceptDisabled) {
             // Si cambia concepto manualmente, limpiamos vinculaciones automáticas
             $this->enrollment_id = null;
             $this->payment_id_to_update = null;
        }
        
        $this->resetErrorBag('amount');

        if (!empty($value)) {
            $selectedConcept = $this->payment_concepts->firstWhere('id', (int)$value);
            if ($selectedConcept && isset($selectedConcept->amount) && $selectedConcept->amount > 0) {
                $this->amount = $selectedConcept->amount;
            }
        }
        $this->calculateChange();
    }

    // --- CÁLCULO DE CAMBIO (POS) ---
    public function updatedAmount() { $this->calculateChange(); }
    public function updatedCashReceived() { $this->calculateChange(); }
    public function updatedStatus() 
    { 
        // Si cambia a Pendiente, limpiamos método de pago visualmente (aunque backend lo maneja)
        $this->resetErrorBag(); 
    }

    private function calculateChange()
    {
        if ($this->gateway === 'Efectivo' && $this->status === 'Completado') {
            $received = floatval($this->cash_received);
            $amount = floatval($this->amount);
            $this->change_amount = ($received >= $amount) ? ($received - $amount) : 0;
        } else {
            $this->change_amount = 0;
        }
    }

    public function savePayment(MatriculaService $matriculaService)
    {
        // Si es deuda pendiente, ponemos gateway por defecto para pasar validación
        if ($this->status === 'Pendiente') {
            $this->gateway = 'Crédito'; 
        }

        $this->validate();
        
        $isNewStudent = !$this->student->student_code;

        try {
            $payment = DB::transaction(function () use ($matriculaService, $isNewStudent) {
                
                $data = [
                    'payment_concept_id' => $this->payment_concept_id,
                    'amount' => $this->amount,
                    'gateway' => $this->gateway,
                    'status' => $this->status,
                    'transaction_id' => $this->transaction_id,
                    'enrollment_id' => $this->enrollment_id,
                    'user_id' => Auth::id(),
                ];

                $payment = null;

                if ($this->payment_id_to_update) {
                    // Actualizar deuda existente (Pagarla)
                    $payment = Payment::find($this->payment_id_to_update);
                    if ($payment) {
                        $payment->update($data);
                    }
                } else {
                    // Crear nuevo registro (Cobro directo o Nueva Deuda)
                    $data['student_id'] = $this->student_id;
                    $data['currency'] = 'DOP';
                    if ($this->status === 'Pendiente') {
                        $data['due_date'] = now()->addDays(30);
                    }
                    $payment = Payment::create($data);
                }
                
                // Efectos secundarios solo si se COMPLETA el pago
                if ($payment && $payment->status == 'Completado') {
                    
                    // Matrícula automática
                    if ($isNewStudent && $payment->paymentConcept && stripos($payment->paymentConcept->name, 'Inscripción') !== false) {
                        $matriculaService->generarMatricula($payment);
                    } 
                    
                    // Activar inscripción
                    if ($payment->enrollment) {
                        $payment->enrollment->status = 'Cursando';
                        $payment->enrollment->save();
                    }
                }
                
                return $payment;
            });

            $msg = ($this->status === 'Pendiente') 
                ? 'Deuda registrada correctamente en la cuenta del estudiante.' 
                : 'Pago procesado exitosamente.';

            if ($this->status === 'Completado' && $this->gateway === 'Efectivo') {
                $msg .= " Devuelta: RD$ " . number_format($this->change_amount, 2);
            }

            session()->flash('message', $msg);
            
            $this->closeModal(); 
            $this->dispatch('paymentAdded'); 
            $this->dispatch('$refresh'); 

        } catch (\Exception $e) {
            Log::error("Error al guardar el pago: " . $e->getMessage());
            $this->addError('general', 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->show = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'payment_id', 'payment_concept_id', 'amount', 'gateway', 'status', 
            'transaction_id', 'enrollment_id', 'payment_id_to_update', 
            'isAmountDisabled', 'isConceptDisabled', 'cash_received', 
            'change_amount', 'notes'
        ]);

        $this->amount = 0.00;
        $this->gateway = 'Efectivo';
        $this->status = 'Completado';
        $this->resetErrorBag();
    }
}