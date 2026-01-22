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
    public Collection $studentEnrollments; 
    public Collection $payment_concepts;
    public bool $isAmountDisabled = false;
    public bool $isConceptDisabled = false; 

    protected function rules()
    {
        return [
            'student_id' => 'required|exists:students,id',
            'payment_concept_id' => [
                Rule::requiredIf(empty($this->enrollment_id)),
                'nullable',
                'exists:payment_concepts,id'
            ],
            'enrollment_id' => 'nullable|exists:enrollments,id',
            'amount' => 'required|numeric|min:0.01',
            'gateway' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            // La referencia es requerida si NO es efectivo y el estado es completado
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
            // Validar que el efectivo cubra el monto solo si se está cobrando ahora
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
            $this->updatedEnrollmentId($enrollmentId); 
            $this->show = true;
        }
    }

    public function mount(?Student $student = null)
    {
        $this->studentEnrollments = collect();
        $this->payment_concepts = collect();

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
            ->orWhere('student_code', 'like', '%' . $this->search_query . '%') // Usar student_code en lugar de id_number
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
        $this->studentEnrollments = collect();
        $this->show_search = true;
        $this->reset(['amount', 'enrollment_id', 'payment_concept_id', 'cash_received', 'change_amount']);
    }

    public function loadInitialData()
    {
        try {
            $this->payment_concepts = PaymentConcept::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error("Error conceptos: " . $e->getMessage());
            $this->payment_concepts = collect();
        }

        if ($this->student_id) {
            try {
                $this->studentEnrollments = Enrollment::where('student_id', $this->student_id)
                    ->where('status', 'Pendiente')
                    ->with([
                        'courseSchedule.module', 
                        'payment'
                    ])
                    ->get();
            } catch (\Exception $e) {
                Log::error("Error inscripciones: " . $e->getMessage());
                $this->studentEnrollments = collect();
            }
        }
    }

    public function updatedEnrollmentId($value)
    {
        $this->resetErrorBag();
        $this->payment_id_to_update = null;

        if (!empty($value)) {
            $selectedEnrollment = $this->studentEnrollments->firstWhere('id', (int)$value);

            if ($selectedEnrollment && $selectedEnrollment->payment) {
                $pendingPayment = $selectedEnrollment->payment;
                $this->amount = $pendingPayment->amount;
                $this->payment_concept_id = $pendingPayment->payment_concept_id; 
                $this->payment_id_to_update = $pendingPayment->id; 
                $this->isAmountDisabled = true; 
                $this->isConceptDisabled = true; 
                // Si ya existe pago pendiente, asumimos que ahora se quiere completar
                $this->status = 'Completado'; 
            } else if ($selectedEnrollment) { 
                $this->amount = $selectedEnrollment->courseSchedule->module->price ?? 0.00;
                $this->payment_concept_id = $selectedEnrollment->courseSchedule->module->payment_concept_id ?? null;
                $this->payment_id_to_update = null;
                $this->isAmountDisabled = true;
                $this->isConceptDisabled = true;
                $this->status = 'Completado';
            } else {
                $this->resetPaymentFields();
            }
        } else {
            $this->resetPaymentFields();
        }
        $this->calculateChange();
    }

    private function resetPaymentFields()
    {
        $this->reset(['amount', 'payment_concept_id', 'isAmountDisabled', 'isConceptDisabled', 'payment_id_to_update']);
        $this->amount = 0.00;
    }

    public function updatedPaymentConceptId($value)
    {
        if (!$this->isConceptDisabled) {
             $this->enrollment_id = null;
             $this->payment_id_to_update = null;
        }
        
        $this->resetErrorBag('amount');

        if (!empty($value)) {
            $selectedConcept = $this->payment_concepts->firstWhere('id', (int)$value);

            // Verificar si el concepto tiene monto fijo (adaptar según tu modelo real)
            if ($selectedConcept && isset($selectedConcept->amount) && $selectedConcept->amount > 0) {
                $this->amount = $selectedConcept->amount;
                // Opcional: Deshabilitar si es estricto
                // $this->isAmountDisabled = true;
            } else {
                if (!$this->enrollment_id) {
                     $this->amount = 0.00;
                     $this->isAmountDisabled = false;
                }
            }
        } else {
             if (!$this->enrollment_id) {
                 $this->amount = 0.00;
                 $this->isAmountDisabled = false;
             }
        }
        $this->calculateChange();
    }

    // --- CÁLCULO DE CAMBIO (POS) ---
    public function updatedAmount() { $this->calculateChange(); }
    public function updatedCashReceived() { $this->calculateChange(); }

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
        $this->validate();
        
        $isNewStudent = !$this->student->student_code;

        try {
            $payment = DB::transaction(function () use ($matriculaService, $isNewStudent) {
                $payment = null;

                $data = [
                    'payment_concept_id' => $this->payment_concept_id,
                    'amount' => $this->amount,
                    'gateway' => $this->gateway,
                    'status' => $this->status,
                    'transaction_id' => $this->transaction_id,
                    'enrollment_id' => $this->enrollment_id,
                    'user_id' => Auth::id(),
                ];

                if ($this->payment_id_to_update) {
                    $payment = Payment::find($this->payment_id_to_update);
                    if ($payment) {
                        $payment->update($data);
                    } else {
                        throw new \Exception("Error: No se encontró el pago pendiente para actualizar.");
                    }
                } else {
                    $data['student_id'] = $this->student_id;
                    $data['currency'] = 'DOP';
                    // Si es deuda pendiente, ponemos vencimiento a 30 días por defecto
                    if ($this->status === 'Pendiente') {
                        $data['due_date'] = now()->addDays(30);
                    }
                    $payment = Payment::create($data);
                }
                
                $payment->refresh();
                
                // Procesar efectos secundarios solo si se completa
                if ($payment->status == 'Completado') {
                    if ($isNewStudent && $payment->paymentConcept && stripos($payment->paymentConcept->name, 'Inscripción') !== false) {
                        Log::info("Generando matrícula para nuevo estudiante.");
                        $matriculaService->generarMatricula($payment);
                    } else {
                        if ($payment->enrollment) {
                            $payment->enrollment->status = 'Cursando';
                            $payment->enrollment->save();
                        }
                    }
                }
                
                return $payment;
            });

            $msg = ($this->status === 'Pendiente') 
                ? 'Deuda registrada correctamente en la cuenta del estudiante.' 
                : 'Pago registrado exitosamente. ' . ($this->gateway === 'Efectivo' ? "Devuelta: RD$ " . number_format($this->change_amount, 2) : '');

            session()->flash('message', $msg);
            
            $this->closeModal(); 
            $this->dispatch('paymentAdded'); 
            $this->dispatch('$refresh'); 

        } catch (\Exception $e) {
            Log::error("Error al guardar el pago: " . $e->getMessage());
            $this->addError('general', 'Error al procesar el pago: ' . $e->getMessage());
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