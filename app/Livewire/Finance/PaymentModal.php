<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use App\Models\Student;
use App\Models\PaymentConcept;
use App\Models\Payment;
use App\Models\Enrollment; // <-- AÑADIDO: Importar el modelo Enrollment
use App\Services\MatriculaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB; // <-- AÑADIDO: Importar DB para la transacción

class PaymentModal extends Component
{
    public Student $student;
    public $show = false;

    // Propiedades del formulario
    public $student_id;
    public $payment_id = null; // ID del pago que se está editando (si aplica)
    public $payment_concept_id;
    public $amount = 0.00;
    public $status = 'Completado';
    public $gateway = 'Efectivo';
    public $transaction_id = null; 

    // Propiedades de vinculación
    public $enrollment_id = null; 
    public $payment_id_to_update = null; 
    public Collection $studentEnrollments; 
    public Collection $payment_concepts;
    public bool $isAmountDisabled = false;
    public bool $isConceptDisabled = false; 

    /**
     * Reglas de validación
     */
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
            'transaction_id' => 'nullable|string|max:255',
        ];
    }
    
    /**
     * Mensajes de validación personalizados
     */
    protected $messages = [
        'payment_concept_id.required' => 'Debe seleccionar un concepto de pago.',
        'amount.required' => 'El monto no puede estar vacío.',
        'amount.numeric' => 'El monto debe ser un número.',
        'amount.min' => 'El monto debe ser al menos 0.01.',
    ];

    /**
     * Escucha el evento 'openPaymentModal' (general)
     */
    #[On('openPaymentModal')]
    public function openModal()
    {
        $this->resetForm();
        $this->loadInitialData(); // Cargar datos frescos al abrir
        $this->show = true;
    }

    /**
     * Escucha el evento 'payEnrollment' (específico)
     * y abre el modal pre-cargado con la inscripción.
     */
    #[On('payEnrollment')]
    public function openForEnrollment($enrollmentId)
    {
        $this->resetForm();
        $this->loadInitialData(); // Cargar datos frescos al abrir

        // Asignar el ID de la inscripción
        $this->enrollment_id = $enrollmentId; 
        
        // Forzar la lógica de auto-rellenado
        $this->updatedEnrollmentId($enrollmentId); 
        
        $this->show = true;
    }

    /**
     * Mount se ejecuta al inicializar el componente.
     */
    public function mount(Student $student)
    {
        $this->student = $student;
        $this->student_id = $this->student->id;
        $this->loadInitialData();
    }
    
    /**
     * Carga los datos necesarios para el formulario (conceptos y matrículas pendientes)
     */
    public function loadInitialData()
    {
        try {
            $this->payment_concepts = PaymentConcept::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error("Error al cargar conceptos de pago: " . $e->getMessage());
            $this->payment_concepts = collect();
        }

        try {
            $this->studentEnrollments = Enrollment::where('student_id', $this->student_id)
                ->where('status', 'Pendiente')
                ->with([
                    'courseSchedule.module', 
                    'payment' // Cargar el pago pendiente
                ])
                ->get();
        } catch (\Exception $e) {
            Log::error("Error al cargar inscripciones pendientes: " . $e->getMessage());
            $this->studentEnrollments = collect();
        }
    }

    /**
     * Se dispara cuando el admin selecciona una inscripción pendiente.
     */
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
                // Caso en que la inscripción exista pero el pago no (ej. se borró manually)
                $this->amount = $selectedEnrollment->courseSchedule->module->price ?? 0.00;
                $this->payment_concept_id = $selectedEnrollment->courseSchedule->module->payment_concept_id ?? null;
                $this->payment_id_to_update = null; // No hay pago para actualizar, se creará uno nuevo
                $this->isAmountDisabled = true;
                $this->isConceptDisabled = true;
            } else {
                // Si el valor no es válido o está vacío
                $this->reset(['amount', 'payment_concept_id', 'isAmountDisabled', 'isConceptDisabled', 'payment_id_to_update']);
                $this->amount = 0.00;
            }
        } else {
            // Si deselecciona (modo manual)
            $this->reset(['amount', 'payment_concept_id', 'isAmountDisabled', 'isConceptDisabled', 'payment_id_to_update']);
            $this->amount = 0.00;
        }
    }

    /**
     * Se dispara cuando se cambia el concepto (MODO MANUAL)
     */
    public function updatedPaymentConceptId($value)
    {
        // Si el usuario está cambiando el concepto manualmente, rompemos la vinculación con la inscripción.
        if (!$this->isConceptDisabled) {
             $this->enrollment_id = null;
             $this->payment_id_to_update = null;
        }
       
        $this->resetErrorBag('amount');

        if (!empty($value)) {
            $selectedConcept = $this->payment_concepts->firstWhere('id', (int)$value);

            if ($selectedConcept && $selectedConcept->is_fixed_amount) {
                $this->amount = $selectedConcept->default_amount;
                $this->isAmountDisabled = true;
            } else {
                // Si no es un monto fijo, pero estamos vinculados a una inscripción, no cambiar el monto.
                if (!$this->enrollment_id) {
                     $this->amount = 0.00;
                     $this->isAmountDisabled = false;
                }
            }
        } else {
            // Si se deselecciona el concepto y no hay inscripción, resetear monto.
             if (!$this->enrollment_id) {
                $this->amount = 0.00;
                $this->isAmountDisabled = false;
             }
        }
    }


    /**
     * Guarda el nuevo pago o actualiza uno existente.
     */
    public function savePayment(MatriculaService $matriculaService)
    {
        $this->validate();
        
        // --- INICIO DE LA MODIFICACIÓN ---
        // Determinar si el estudiante es nuevo ANTES de la transacción
        $isNewStudent = !$this->student->student_code; // <-- CORRECCIÓN: $this.student cambiado a $this->student
        // --- FIN DE LA MODIFICACIÓN ---

        try {
            // Pasamos $isNewStudent al closure de la transacción
            $payment = DB::transaction(function () use ($matriculaService, $isNewStudent) {
                $payment = null;

                if ($this->payment_id_to_update) {
                    // --- Caso 1: Actualizar un pago pendiente existente ---
                    $payment = Payment::find($this->payment_id_to_update);
                    if ($payment) {
                        $payment->update([
                            'payment_concept_id' => $this->payment_concept_id,
                            'amount' => $this->amount, // Asegurarse de actualizar el monto
                            'gateway' => $this->gateway,
                            'status' => $this->status,
                            'transaction_id' => $this->transaction_id,
                            'enrollment_id' => $this->enrollment_id, // Asegurarse de que esté vinculado
                            'user_id' => Auth::id(), // Registrar quién procesó el pago
                        ]);
                    } else {
                        // Esto no debería pasar si la UI funciona bien, pero es una salvaguarda
                        throw new \Exception("Error: No se encontró el pago pendiente (ID: {$this->payment_id_to_update}) para actualizar.");
                    }

                } else {
                    // --- Caso 2: Crear un nuevo registro de pago ---
                    $payment = Payment::create([
                        'student_id' => $this->student_id,
                        'payment_concept_id' => $this->payment_concept_id,
                        'enrollment_id' => $this->enrollment_id, // Puede ser null si no se seleccionó inscripción
                        'amount' => $this->amount,
                        'currency' => 'DOP', // Asumiendo DOP, o tomar de config
                        'status' => $this->status,
                        'gateway' => $this->gateway,
                        'transaction_id' => $this->transaction_id,
                        'user_id' => Auth::id(), // Registrar quién procesó el pago
                    ]);
                }
                
                // Refrescar el objeto $payment desde la BBDD para asegurar que las relaciones
                // (especialmente si se acaba de crear) estén cargadas.
                $payment->refresh();
                
                // Cargar relaciones necesarias
                $payment->load('student.user', 'enrollment.courseSchedule.module');

                // Si el pago se marcó como "Completado"
                if ($payment->status == 'Completado') {
                    
                    // --- INICIO DE NUEVOS LOGS PARA DEBUG ---
                    Log::info("Pago {$payment->id} marcado como 'Completado'. Verificando inscripción. ID de inscripción asociado: " . ($payment->enrollment_id ?? 'NINGUNO'));
                    Log::info("Relación 'enrollment' cargada: " . ($payment->enrollment ? 'SI' : 'NO'));
                    // --- FIN DE NUEVOS LOGS PARA DEBUG ---

                    // --- INICIO DE LA LÓGICA MODIFICADA ---
                    if ($isNewStudent) {
                        // --- Lógica para Estudiante Nuevo ---
                        // El servicio se encarga de todo: crear matrícula, activar usuario, activar inscripción.
                        Log::info("Pago {$payment->id} completado. Ejecutando MatriculaService para nuevo estudiante ID: {$this->student_id}.");
                        $matriculaService->generarMatricula($payment);
                    
                    } else {
                        // --- Lógica para Estudiante Existente ---
                        // Si el estudiante ya existe, solo activamos la inscripción (si hay una).
                        if ($payment->enrollment) {
                            $payment->enrollment->status = 'Cursando'; // O 'Activo'
                            $payment->enrollment->save();
                            Log::info("Pago {$payment->id} completado. Inscripción {$payment->enrollment->id} activada para estudiante existente ID: {$this->student_id}.");
                        } else {
                            // Pago completado sin inscripción asociada (ej. pago manual de un concepto)
                            Log::info("Pago {$payment->id} completado (sin inscripción asociada) para estudiante existente ID: {$this->student_id}.");
                        }
                    }
                    // --- FIN DE LA LÓGICA MODIFICADA ---
                }
                
                return $payment; // Devolver el pago procesado
            });

            session()->flash('message', 'Pago registrado exitosamente.');
            $this->closeModal(); 
            $this->dispatch('paymentAdded'); // Emitir evento para refrescar otros componentes (ej. StudentProfile)
            
            // Refrescar este componente para actualizar la lista de inscripciones pendientes
            $this->dispatch('$refresh'); 

        } catch (\Exception $e) {
            Log::error("Error al guardar el pago: " . $e->getMessage());
            // Mostramos el error en el modal en lugar de cerrarlo
            $this->addError('general', 'Error al procesar el pago: ' . $e->getMessage());
        }
    }

    /**
     * Cierra el modal.
     */
    public function closeModal()
    {
        $this->show = false;
        $this->resetForm();
    }

    /**
     * Resetea todos los campos del formulario.
     */
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
            'isConceptDisabled'
        ]);

        $this->amount = 0.00;
        $this->gateway = 'Efectivo';
        $this->status = 'Completado';
        $this->resetErrorBag();
    }
}