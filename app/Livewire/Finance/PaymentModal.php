<?php

namespace App\Livewire\Finance;

use Livewire\Component;
use App\Models\Student;
use App\Models\PaymentConcept;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection; // Asegúrate de que Collection esté importado

class PaymentModal extends Component
{
    public Student $student;

    // Propiedades del formulario
    public $student_id;
    public $payment_concept_id;
    public $amount = 0.00; // Inicializar con un valor
    public $payment_method = 'Efectivo'; // Valor por defecto
    public $status = 'Completado'; // Valor por defecto
    public $description;

    public Collection $payment_concepts; // Tipado para mejor autocompletado
    public bool $isAmountDisabled = false; // Controla si el campo de monto está deshabilitado

    /**
     * Reglas de validación
     */
    protected function rules()
    {
        return [
            'student_id' => 'required|exists:students,id',
            'payment_concept_id' => 'required|exists:payment_concepts,id',
            // El monto es requerido y debe ser numérico, mínimo 0.01
            'amount' => 'required|numeric|min:0.01', 
            'payment_method' => 'required|string|max:100',
            'status' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
        ];
    }
    
    /**
     * Mensajes de validación personalizados
     */
    protected $messages = [
        'payment_concept_id.required' => 'Debe seleccionar un concepto de pago.',
        'amount.required' => 'El monto es obligatorio.',
        'amount.numeric' => 'El monto debe ser un número.',
        'amount.min' => 'El monto debe ser al menos 0.01.',
    ];

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
            // Quitamos el filtro 'where('status', 'activo')' para mostrar todos
            $this->payment_concepts = PaymentConcept::orderBy('name')->get();
        } catch (\Exception $e) {
            Log::error("Error cargando conceptos de pago: " . $e->getMessage());
            $this->payment_concepts = collect(); // Devuelve una colección vacía en caso de error
        }
    }

    /**
     * Método que se dispara cuando la propiedad $payment_concept_id cambia.
     * (Gracias a wire:model.live en el select)
     */
    public function updatedPaymentConceptId($value)
    {
        // Reseteamos errores de 'amount' si el usuario cambia el concepto
        $this->resetErrorBag('amount');

        if (!empty($value)) {
            // Buscamos el concepto seleccionado en la colección que ya cargamos
            $selectedConcept = $this->payment_concepts->firstWhere('id', (int)$value);

            if ($selectedConcept && $selectedConcept->is_fixed_amount) {
                // Si es un monto fijo, actualiza el monto y deshabilita el input
                $this->amount = $selectedConcept->default_amount;
                $this->isAmountDisabled = true;
            } else {
                // Si no es fijo (o es 'Otro'), resetea el monto y habilita el input
                $this->amount = 0.00;
                $this->isAmountDisabled = false;
            }
        } else {
            // Si se deselecciona (e.g., "Seleccione un concepto..."), resetea
            $this->amount = 0.00;
            $this->isAmountDisabled = false;
        }
    }


    /**
     * Guarda el nuevo pago.
     */
    public function savePayment()
    {
        // Validar los datos del formulario
        $this->validate();

        try {
            // Crear el pago en la base de datos
            Payment::create([
                'student_id' => $this->student_id,
                'payment_concept_id' => $this->payment_concept_id,
                'amount' => $this->amount,
                'payment_method' => $this->payment_method,
                'status' => $this->status,
                'description' => $this->description,
                'user_id' => Auth::id(), // Asigna el usuario autenticado que registra el pago
            ]);

            // Cierra el modal
            $this->dispatch('close-modal', 'payment-modal');

            // Resetea el formulario para la próxima vez que se abra
            $this->resetForm();

            // Envía evento para el mensaje flash (toast) en la página principal
            $this->dispatch('flashMessage', ['message' => '¡Pago registrado exitosamente!', 'type' => 'success']);
            
            // --- ¡MODIFICACIÓN! ---
            // Envía el evento 'paymentCreated' que 'Index.php' está escuchando
            $this->dispatch('paymentCreated'); 

        } catch (\Exception $e) {
            Log::error("Error al guardar el pago: " . $e->getMessage());
            // Envía un error al modal (puedes manejarlo con Alpine o un flash de sesión)
            $this->dispatch('flashMessage', ['message' => 'Error al registrar el pago.', 'type' => 'error']);
        }
    }

    /**
     * Resetea los campos del formulario.
     * Este método se llama con Alpine.js cuando se abre el modal.
     */
    public function resetForm()
    {
        $this->reset([
            'payment_concept_id',
            'amount',
            'payment_method',
            'status',
            'description',
            'isAmountDisabled'
        ]);
        
        // Re-establecemos los valores por defecto
        $this->amount = 0.00;
        $this->payment_method = 'Efectivo';
        $this->status = 'Completado';
        $this->isAmountDisabled = false;

        // Limpiamos cualquier error de validación previo
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