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
            'ncfType' => 'required|in:B01,B02', // B01: Crédito Fiscal, B02: Consumidor Final
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
        // Asumiendo que el modelo Student tiene campo 'rnc' o similar
        if ($this->student && $this->student->rnc) {
             $this->rnc = $this->student->rnc;
             // $this->ncfType = 'B01'; // Descomentar si se quiere preseleccionar
        }
    }

    public function openPaymentModal($paymentId)
    {
        $this->resetValidation();
        // Reseteamos también los campos de NCF para que empiece limpio
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
        // Estos se guardarán en las columnas 'ncf_type', 'rnc_client', 'company_name' o similares
        // Asegúrate de que tu modelo Payment y la tabla 'payments' tengan estos campos si quieres persistirlos.
        // Si no existen en la tabla 'payments' directamente, puedes guardarlos en 'notes' o una tabla relacionada.
        // Asumiendo que existen o se pueden pasar en un array data.
        
        // NOTA: Basado en tu migración `2026_01_24_000000_add_ecf_columns_to_payments_table.php`,
        // tienes `ncf_type` (2 chars).
        // Sin embargo, para guardar la solicitud del cliente (RNC y nombre), necesitamos campos adicionales o usar 'notes'.
        // Aquí asumiré que puedes guardar 'ncf_type' con el valor solicitado (31 para B01, 32 para B02 según DGII estándar, 
        // o guardar 'B01'/'B02' si tu lógica lo maneja así). Tu migración dice varchar(2), así que quizas usas códigos internos.
        // Voy a asumir que quieres guardar la *solicitud*.
        
        $ncfTypeCode = ($this->ncfType === 'B01') ? '31' : '32'; // Mapeo estándar DGII: 31 = Crédito, 32 = Consumo
        
        $fiscalData = [
            'ncf_type' => $ncfTypeCode, 
            // Si no tienes columnas específicas para RNC y Razón Social en `payments`, 
            // las guardaremos en `notes` temporalmente o deberías agregar esas columnas.
            // Para este ejemplo, las agrego al array de update, asumiendo que el modelo las soporta o las ignorará si no están en fillable.
            // Si da error SQL, las moveremos a 'notes'.
        ];
        
        // Construir nota fiscal si es B01
        $fiscalNote = "";
        if ($this->ncfType === 'B01') {
            $fiscalNote = " | Solicitud NCF B01 - RNC: {$this->rnc} - Empresa: {$this->companyName}";
        }

        // 1. PAGO CON TARJETA (Redirección)
        if ($this->paymentMethod === 'card') {
            try {
                $payment = Payment::find($this->selectedPaymentId);
                
                if (!$payment) {
                    $this->addError('general', 'Error: Pago no encontrado.');
                    return;
                }

                // Guardar intención y datos fiscales antes de ir a Cardnet
                $payment->update(array_merge([
                    'gateway' => 'Tarjeta',
                    'status' => 'Pendiente', 
                    'notes' => 'Redirigiendo a Cardnet...' . $fiscalNote,
                    // 'rnc_client' => $this->rnc, // Descomentar si tienes esta columna
                    // 'company_name' => $this->companyName, // Descomentar si tienes esta columna
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
            $this->processManualPayment($matriculaService, 'Transferencia Bancaria', $this->transferReference, 'Pendiente', $fiscalData, $fiscalNote);
        }
    }

    private function processManualPayment(MatriculaService $matriculaService, $gateway, $transactionId, $status, $fiscalData, $fiscalNote)
    {
        if (!$this->student || !$this->selectedPaymentId) return;

        try {
            DB::transaction(function () use ($matriculaService, $gateway, $transactionId, $status, $fiscalData, $fiscalNote) {
                $payment = Payment::find($this->selectedPaymentId);
                if ($payment) {
                    $payment->update(array_merge([
                        'status' => $status,
                        'gateway' => $gateway,
                        'transaction_id' => $transactionId,
                        'user_id' => Auth::id(),
                        'notes' => ($payment->notes ?? '') . $fiscalNote
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