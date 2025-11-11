<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use App\Models\Student;
use App\Models\PaymentConcept;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentModal extends Component
{
    public Student $student; // Se recibe desde el componente padre

    // Propiedades del formulario
    public $student_id;
    public $payment_concept_id;
    public $amount;
    public $payment_method = 'Efectivo'; // Valor por defecto
    public $status = 'Completado'; // Valor por defecto
    public $description;

    public $payment_concepts = [];

    // Reglas de validación
    protected function rules()
    {
        return [
            'student_id' => 'required|exists:students,id',
            'payment_concept_id' => 'required|exists:payment_concepts,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Mount se ejecuta al inicializar el componente.
     * Carga los datos necesarios.
     */
    public function mount(Student $student)
    {
        $this->student = $student;
        $this->student_id = $this->student->id;
        
        // Cargar los conceptos de pago para el dropdown
        try {
            $this->payment_concepts = PaymentConcept::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error("Error cargando conceptos de pago: " . $e->getMessage());
            $this->payment_concepts = [];
        }
    }

    /**
     * Guarda el nuevo pago.
     */
    public function savePayment()
    {
        $this->validate();

        try {
            Payment::create([
                'student_id' => $this->student_id,
                'payment_concept_id' => $this->payment_concept_id,
                'amount' => $this->amount,
                'payment_method' => $this->payment_method,
                'status' => $this->status,
                'description' => $this->description,
                'user_id' => Auth::id(), // Asigna el usuario que registra el pago
            ]);

            // Notifica al componente padre (StudentProfile) que se creó un pago
            $this->dispatch('paymentCreated'); 
            
            // Cierra el modal
            $this->dispatch('close-modal', 'payment-modal');

            // Resetea el formulario
            $this->resetForm();

            // Envía una notificación flash (opcional, pero recomendado)
            // El componente padre (StudentProfile) ya tiene un listener para 'message'
            session()->flash('message', 'Pago registrado exitosamente.');

        } catch (\Exception $e) {
            Log::error("Error al guardar el pago: " . $e->getMessage());
            // Envía un error al modal (puedes manejarlo con Alpine)
            $this->dispatch('show-notification', message: 'Error al registrar el pago.', type: 'error');
        }
    }

    /**
     * Resetea los campos del formulario.
     */
    public function resetForm()
    {
        $this->payment_concept_id = null;
        $this->amount = '';
        $this->payment_method = 'Efectivo';
        $this->status = 'Completado';
        $this->description = '';
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