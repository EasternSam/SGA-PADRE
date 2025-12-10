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
    // --- PROPIEDADES DE ESTUDIANTE Y BÚSQUEDA (NUEVO) ---
    public ?Student $student = null; // Ahora es opcional al inicio
    public $search_query = '';
    public $student_results = [];
    public $show_search = true; // Controla si mostramos la barra de búsqueda

    public $show = false;

    // Propiedades del formulario
    public $student_id;
    public $payment_id = null;
    public $payment_concept_id;
    public $amount = 0.00;
    public $status = 'Completado';
    public $gateway = 'Efectivo';
    public $transaction_id = null; 
    public $notes = null; // Agregado para observaciones

    // --- POS: EFECTIVO Y CAMBIO (NUEVO) ---
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
            // La referencia es requerida si NO es efectivo
            'transaction_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($this->gateway !== 'Efectivo' && $this->gateway !== 'Otro')
            ],
            // Validar que el efectivo cubra el monto
            'cash_received' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) {
                    if ($this->gateway === 'Efectivo' && $value < $this->amount) {
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
        'transaction_id.required_if' => 'El número de referencia es obligatorio para este método de pago.',
    ];

    #[On('openPaymentModal')]
    public function openModal()
    {
        $this->resetForm();
        // No cargamos initial data aquí si no hay estudiante seleccionado aún
        if ($this->student) {
            $this->loadInitialData();
        }
        $this->show = true;
    }

    #[On('payEnrollment')]
    public function openForEnrollment($enrollmentId)
    {
        $this->resetForm();
        
        // Buscar la inscripción para obtener el estudiante automáticamente
        $enrollment = Enrollment::with('student')->find($enrollmentId);
        
        if ($enrollment && $enrollment->student) {
            $this->selectStudent($enrollment->student->id); // Esto carga al estudiante y sus datos
            
            // Asignar el ID de la inscripción
            $this->enrollment_id = $enrollmentId; 
            // Forzar la lógica de auto-rellenado
            $this->updatedEnrollmentId($enrollmentId); 
            
            $this->show = true;
        }
    }

    // Modificado: Student ahora es opcional en el mount
    public function mount(?Student $student = null)
    {
        $this->studentEnrollments = collect();
        $this->payment_concepts = collect();

        if ($student && $student->exists) {
            $this->selectStudent($student->id);
        } else {
            // Cargar conceptos aunque no haya estudiante, para el dropdown
            $this->payment_concepts = PaymentConcept::orderBy('name')->get();
        }
    }

    // --- LÓGICA DE BÚSQUEDA (POS) ---

    public function updatedSearchQuery()
    {
        if (strlen($this->search_query) < 2) {
            $this->student_results = [];
            return;
        }

        $this->student_results = Student::query()
            ->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $this->search_query . '%')
            ->orWhere('id_number', 'like', '%' . $this->search_query . '%') // Asumiendo matrícula o DNI
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
            $this->show_search = false; // Ocultar búsqueda para mostrar info del estudiante
            $this->loadInitialData(); // Cargar inscripciones del estudiante seleccionado
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

    // --- FIN LÓGICA BÚSQUEDA ---
    
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
            } else if ($selectedEnrollment) { 
                $this->amount = $selectedEnrollment->courseSchedule->module->price ?? 0.00;
                $this->payment_concept_id = $selectedEnrollment->courseSchedule->module->payment_concept_id ?? null;
                $this->payment_id_to_update = null;
                $this->isAmountDisabled = true;
                $this->isConceptDisabled = true;
            } else {
                $this->resetPaymentFields();
            }
        } else {
            $this->resetPaymentFields();
        }
        $this->calculateChange(); // Recalcular cambio si cambia el monto
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

            if ($selectedConcept && $selectedConcept->is_fixed_amount) { // Asumiendo campo is_fixed_amount
                $this->amount = $selectedConcept->default_amount ?? 0; // Asumiendo campo default_amount
                 // Si tu modelo PaymentConcept no tiene estos campos, usa amount o price
                 // $this->amount = $selectedConcept->amount; 
                $this->isAmountDisabled = true;
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
        if ($this->gateway === 'Efectivo') {
            $received = floatval($this->cash_received);
            $amount = floatval($this->amount);
            $this->change_amount = ($received >= $amount) ? ($received - $amount) : 0;
        } else {
            $this->change_amount = 0;
            $this->cash_received = 0;
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
                    // Si tienes campo 'notes' o 'comments' en DB:
                    // 'notes' => $this->notes, 
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
                    $payment = Payment::create($data);
                }
                
                $payment->refresh();
                $payment->load('student.user', 'enrollment.courseSchedule.module');

                if ($payment->status == 'Completado') {
                    Log::info("Pago {$payment->id} completado.");

                    if ($isNewStudent) {
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

            session()->flash('message', 'Pago registrado exitosamente. ' . ($this->gateway === 'Efectivo' ? "Devuelta: RD$ " . number_format($this->change_amount, 2) : ''));
            
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
        // Si quieres que al cerrar se olvide al estudiante seleccionado (estilo POS), descomenta:
        // $this->clearStudent(); 
    }

    private function resetForm()
    {
        $this->reset([
            'payment_id', 
            'payment_concept_id', 
            'amount', 
            'gateway', 
            'status', 
            'transaction_id',
            'enrollment_id',
            'payment_id_to_update',
            'isAmountDisabled',
            'isConceptDisabled',
            'cash_received',
            'change_amount',
            'notes'
        ]);

        $this->amount = 0.00;
        $this->gateway = 'Efectivo';
        $this->status = 'Completado';
        $this->resetErrorBag();
        // No reseteamos student_id aquí para permitir pagos consecutivos al mismo alumno
    }
}