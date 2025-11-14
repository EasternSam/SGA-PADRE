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
use Illuminate\Support\Facades\DB; // <-- AÑADIDO para la transacción

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
    public $payment_id_to_update = null; // <-- AÑADIDO: ID del pago PENDIENTE a actualizar
    public Collection $studentEnrollments; 
    public Collection $payment_concepts;
    public bool $isAmountDisabled = false;
    public bool $isConceptDisabled = false; // <-- AÑADIDO PARA LA LÓGICA DE DESHABILITAR

    /**
     * Reglas de validación
     */
    protected function rules()
    {
        return [
            'student_id' => 'required|exists:students,id',
            
            // --- ¡¡¡CORRECCIÓN DE LÓGICA DE VALIDACIÓN!!! ---
            // payment_concept_id es requerido SÓLO SI enrollment_id está vacío
            'payment_concept_id' => [
                Rule::requiredIf(empty($this->enrollment_id)),
                'nullable',
                'exists:payment_concepts,id'
            ],
            // --- FIN DE LA CORRECCIÓN ---

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
        
        // Asignar el ID de la inscripción
        $this->enrollment_id = $enrollmentId; 
        
        // Forzar la lógica de auto-rellenado
        // Esto ahora también buscará y establecerá $payment_id_to_update
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
        
        try {
            $this->payment_concepts = PaymentConcept::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error("Error al cargar conceptos de pago: " . $e->getMessage());
            $this->payment_concepts = collect();
        }

        // --- CORREGIDO ---
        // Cargar las inscripciones Y sus pagos pendientes asociados
        try {
            $this->studentEnrollments = Enrollment::where('student_id', $this->student_id)
                ->where('status', 'Pendiente')
                ->with([
                    'courseSchedule.module', 
                    'payment.paymentConcept' // <-- Cargar el pago pendiente y su concepto
                ])
                ->get();
        } catch (\Exception $e) {
            Log::error("Error al cargar inscripciones pendientes: " . $e->getMessage());
            $this->studentEnrollments = collect();
        }
    }

    /**
     * Se dispara cuando el admin selecciona una inscripción pendiente.
     * ¡¡¡ESTA ES LA LÓGICA CORREGIDA!!!
     */
    public function updatedEnrollmentId($value)
    {
        $this->resetErrorBag();
        $this->payment_id_to_update = null; // Resetear

        if (!empty($value)) {
            $selectedEnrollment = $this->studentEnrollments->firstWhere('id', (int)$value);

            // Verificar si la inscripción tiene un pago pendiente asociado
            if ($selectedEnrollment && $selectedEnrollment->payment) {
                
                $pendingPayment = $selectedEnrollment->payment;
                
                // Auto-rellenar monto y concepto DESDE EL PAGO PENDIENTE
                $this->amount = $pendingPayment->amount;
                $this->payment_concept_id = $pendingPayment->payment_concept_id; // (Puede ser null)
                
                // Guardar el ID del pago que vamos a ACTUALIZAR
                $this->payment_id_to_update = $pendingPayment->id; 
                
                $this->isAmountDisabled = true; // El monto del curso es fijo

                // --- ¡¡¡CORRECCIÓN DE LÓGICA DE UI!!! ---
                // Si seleccionamos una inscripción, el concepto SIEMPRE debe estar deshabilitado.
                $this->isConceptDisabled = true; 
                // --- FIN DE LA CORRECCIÓN ---

            } else {
                // Fallback (si la inscripción no tiene pago, lo cual sería un error de datos)
                Log::warning("La inscripción {$value} no tiene un pago pendiente asociado.");
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
        $this->resetErrorBag('amount');

        // Des-seleccionar la inscripción y el pago a actualizar
        $this->enrollment_id = null;
        $this->payment_id_to_update = null; // <-- AÑADIDO
        $this->isConceptDisabled = false; // <-- AÑADIDO (en modo manual, nunca está deshabilitado)

        if (!empty($value)) {
            $selectedConcept = $this->payment_concepts->firstWhere('id', (int)$value);

            if ($selectedConcept && $selectedConcept->is_fixed_amount) {
                $this->amount = $selectedConcept->default_amount;
                $this->isAmountDisabled = true;
            } else {
                $this->amount = 0.00;
                $this->isAmountDisabled = false;
            }
        } else {
            $this->amount = 0.00;
            $this->isAmountDisabled = false;
        }
    }


    /**
     * Guarda el nuevo pago.
     * ¡¡¡ESTA ES LA LÓGICA CORREGIDA!!!
     */
    public function savePayment(MatriculaService $matriculaService)
    {
        $this->validate();
        $payment = null;

        try {
            // Usamos una transacción por si falla la matriculación
            DB::transaction(function () use (&$payment, $matriculaService) {

                // --- LÓGICA DE PAGO ACTUALIZADA ---
                if ($this->payment_id_to_update) {
                    
                    // 1. ACTUALIZAR el pago pendiente existente
                    $payment = Payment::find($this->payment_id_to_update);
                    if ($payment) {
                        $payment->update([
                            'payment_concept_id' => $this->payment_concept_id, // (Guardará null si es null)
                            'gateway' => $this->gateway,
                            'status' => $this->status,
                            'transaction_id' => $this->transaction_id,
                            
                            // --- ¡¡¡LA CORRECCIÓN MÁS IMPORTANTE ESTÁ AQUÍ!!! ---
                            // Nos aseguramos de que el pago SÍ esté vinculado al enrollment_id del modal.
                            'enrollment_id' => $this->enrollment_id
                        ]);
                    } else {
                         throw new \Exception("Error: No se encontró el pago pendiente (ID: {$this->payment_id_to_update}) para actualizar.");
                    }

                } else {
                    
                    // 2. CREAR un nuevo pago (modo manual)
                    $payment = Payment::create([
                        'student_id' => $this->student_id,
                        'payment_concept_id' => $this->payment_concept_id,
                        'enrollment_id' => $this->enrollment_id, // (Será null si es manual)
                        'amount' => $this->amount,
                        'gateway' => $this->gateway, 
                        'status' => $this->status,
                        'transaction_id' => $this->transaction_id,
                    ]);
                }
                // --- FIN DE LA LÓGICA DE PAGO ---

                // --- INICIO DE LA LÓGICA DE MATRICULACIÓN ---
                // Si el pago se acaba de marcar como "Completado"
                if ($payment && $payment->status == 'Completado') {
                    
                    // --- ¡¡¡ESTA ES LA LÍNEA QUE FALTABA!!! ---
                    // Debemos re-cargar el objeto $payment FRESCO de la BD
                    // para asegurarnos de que el 'enrollment_id' (si se acaba de crear)
                    // y todas sus relaciones estén disponibles para el servicio.
                    $payment->refresh(); 
                    // --- FIN DE LA CORRECCIÓN ---

                    // Cargar las relaciones necesarias que el servicio usará
                    // (La línea $payment->load() de tu archivo original también es válida si 'enrollment' es una relación)
                    $payment->load('student.user', 'enrollment'); 
                    
                    // Llamamos al servicio para que maneje la activación
                    $matriculaService->generarMatricula($payment);
                }
                // --- FIN DE LA LÓGICA DE MATRICULACIÓN ---

            }); // Fin de la transacción

            // --- Lógica original (se mantiene) ---
            $this->show = false; // <-- CERRAR EL MODAL
            $this->resetForm();
            $this->dispatch('flashMessage', ['message' => '¡Pago registrado exitosamente!', 'type' => 'success']);
            $this->dispatch('paymentAdded'); // Este evento debe refrescar la data

        } catch (\Exception $e) {
            Log::error("Error al guardar el pago: " . $e->getMessage());
            $this->dispatch('flashMessage', ['message' => 'Error al registrar el pago: ' . $e->getMessage(), 'type' => 'error']);
        }
    }

    /**
     * Resetea los campos del formulario.
     */
    public function resetForm()
    {
        $this->reset([
            'payment_concept_id',
            'enrollment_id',
            'amount',
            'gateway',
            'status',
            'transaction_id',
            'isAmountDisabled',
            'payment_id_to_update', // <-- AÑADIDO AL RESET
            'isConceptDisabled' // <-- AÑADIDO AL RESET
        ]);
        
        $this->amount = 0.00;
        $this->gateway = 'Efectivo';
        $this->status = 'Completado';
        $this->isAmountDisabled = false;
        $this->isConceptDisabled = false; // <-- AÑADIDO AL RESET

        $this->resetErrorBag();
    }

    /**
     * Renderiza la vista del modal.
     */
    public function render()
    {
        return view('livewire.finance.payment-modal');
    }
}