<?php

namespace App\Livewire\StudentPortal;

use Livewire\Component;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\MatriculaService;
use App\Services\CardnetRedirectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.dashboard')] 
class MyPayments extends Component
{
    use WithPagination;

    public $student;
    
    // --- Modal de Pago ---
    public $showPaymentModal = false;
    public $selectedPaymentId; 
    public $selectedEnrollment;
    public $amountToPay = 0;
    public $paymentMethod = 'card'; 
    public $transferReference;

    // --- NUEVO: Comprobante Fiscal ---
    public $ncfType = 'B02'; // Por defecto Consumidor Final (B02)
    public $rnc = '';
    public $companyName = '';

    // Campos Cardnet
    public $cardnetUrl = '';
    public $cardnetFields = [];

    protected function rules()
    {
        return [
            'paymentMethod' => 'required|in:card,transfer',
            'transferReference' => 'required_if:paymentMethod,transfer',
            // Reglas para NCF
            'ncfType' => 'required|in:B01,B02,B14,B15', // B01: Crédito Fiscal, B02: Consumidor Final
            'rnc' => 'required_if:ncfType,B01|nullable|string|max:20',
            'companyName' => 'required_if:ncfType,B01|nullable|string|max:150',
        ];
    }

    protected $messages = [
        'required_if' => 'Este campo es obligatorio.',
        'rnc.required_if' => 'El RNC es obligatorio para Crédito Fiscal.',
        'companyName.required_if' => 'La Razón Social es obligatoria para Crédito Fiscal.',
    ];

    public function mount()
    {
        $this->student = Student::where('user_id', Auth::id())->first();
        
        // Pre-llenar datos fiscales si el estudiante ya los tiene guardados (opcional)
        if ($this->student && $this->student->rnc) {
             $this->rnc = $this->student->rnc;
             // Si tiene RNC guardado, quizás prefiere B01 por defecto
             // $this->ncfType = 'B01'; 
        }
    }

    public function openPaymentModal($paymentId)
    {
        $this->resetValidation();
        $this->reset(['transferReference', 'paymentMethod', 'cardnetUrl', 'cardnetFields', 'ncfType', 'rnc', 'companyName']);
        
        $this->paymentMethod = 'card'; 
        $this->ncfType = 'B02'; // Resetear a consumidor final siempre al abrir

        $this->selectedPaymentId = $paymentId;
        
        $payment = Payment::with('enrollment.courseSchedule.module')->find($paymentId);
        
        if (!$payment) {
            $this->addError('general', 'El pago seleccionado no existe.');
            return;
        }

        $this->selectedEnrollment = $payment->enrollment;
        $this->amountToPay = $payment->amount;
        $this->showPaymentModal = true;
    }

    public function closeModal()
    {
        $this->showPaymentModal = false;
    }

    public function initiatePayment(MatriculaService $matriculaService, CardnetRedirectionService $cardnetService)
    {
        $this->validate();

        // Datos fiscales para guardar en el pago
        $fiscalData = [
            'ncf_type_requested' => $this->ncfType, // Guardamos qué pidió
            'rnc_client' => ($this->ncfType === 'B01') ? $this->rnc : null,
            'company_name' => ($this->ncfType === 'B01') ? $this->companyName : null,
        ];

        // 1. PAGO CON TARJETA (Redirección)
        if ($this->paymentMethod === 'card') {
            try {
                $payment = Payment::find($this->selectedPaymentId);
                
                if (!$payment) {
                    $this->addError('general', 'Error: Pago no encontrado.');
                    return;
                }

                // Guardar intención y datos fiscales
                $payment->update(array_merge([
                    'gateway' => 'Tarjeta',
                    'status' => 'Pendiente', 
                    'notes' => 'Redirigiendo a Cardnet...',
                ], $fiscalData));

                // Generar formulario
                $formInfo = $cardnetService->prepareFormData($payment->amount, $payment->id, Request::ip());
                
                $this->cardnetUrl = $formInfo['url'];
                $this->cardnetFields = $formInfo['fields'];

                if (empty($this->cardnetUrl)) {
                    $this->addError('general', 'Error de configuración: La URL de la pasarela está vacía.');
                    return;
                }

                $this->dispatch('submit-cardnet-form', data: $formInfo);
                
            } catch (\Exception $e) {
                Log::error("Error iniciando Cardnet estudiante: " . $e->getMessage());
                $this->addError('general', 'Error al conectar con la pasarela de pagos.');
            }
        } 
        // 2. TRANSFERENCIA
        else {
            $this->processManualPayment($matriculaService, 'Transferencia Bancaria', $this->transferReference, 'Pendiente', $fiscalData);
        }
    }

    private function processManualPayment(MatriculaService $matriculaService, $gateway, $transactionId, $status, $fiscalData)
    {
        if (!$this->student || !$this->selectedPaymentId) return;

        try {
            DB::transaction(function () use ($matriculaService, $gateway, $transactionId, $status, $fiscalData) {
                $payment = Payment::find($this->selectedPaymentId);
                if ($payment) {
                    $payment->update(array_merge([
                        'status' => $status,
                        'gateway' => $gateway,
                        'transaction_id' => $transactionId,
                        'user_id' => Auth::id(),
                    ], $fiscalData));
                    
                    session()->flash('message', 'Pago reportado exitosamente. Pendiente de validación.');
                }
            });

            $this->closeModal();
            $this->reset('selectedPaymentId');
        } catch (\Exception $e) {
            Log::error('Error pago manual estudiante: ' . $e->getMessage());
            $this->addError('general', 'Error procesando el pago. Intente más tarde.');
        }
    }

    public function downloadFinancialReport()
    {
        $url = route('reports.financial-report', $this->student->id); 
        $this->dispatch('open-pdf-modal', url: $url);
    }

    public function render()
    {
        if (!$this->student) {
            return view('livewire.student-portal.my-payments', ['pendingDebts' => collect(), 'paymentHistory' => collect()]);
        }

        $pendingDebts = Payment::where('student_id', $this->student->id)
            ->whereIn('status', ['Pendiente', 'pendiente'])
            ->with(['enrollment.courseSchedule.module.course', 'paymentConcept'])
            ->orderBy('due_date', 'asc')
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