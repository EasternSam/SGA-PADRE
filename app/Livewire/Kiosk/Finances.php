<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use App\Models\Student;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.kiosk')]
class Finances extends Component
{
    public ?Student $student;
    public $pendingPayments = [];
    public $totalDebt = 0;

    public function mount()
    {
        $user = Auth::user();
        $this->student = $user?->student;

        if ($this->student) {
            $this->pendingPayments = Payment::with('paymentConcept')
                ->where('student_id', $this->student->id)
                ->where('status', 'Pendiente')
                ->orderBy('due_date', 'asc')
                ->get();

            $this->totalDebt = $this->pendingPayments->sum('amount');
        }
    }

    public function goBack()
    {
        return $this->redirectRoute('kiosk.dashboard', navigate: true);
    }

    public function initiatePayment($paymentId, \App\Services\CardnetRedirectionService $cardnetService)
    {
        if (!$this->student) return;

        $payment = Payment::find($paymentId);
        
        if (!$payment || $payment->status === 'Completado' || $payment->student_id !== $this->student->id) {
            $this->dispatch('notify', message: 'El pago no es válido o ya fue realizado.', type: 'error');
            return;
        }

        try {
            $payment->update([
                'gateway' => 'Tarjeta',
                'status' => 'Pendiente', 
                'notes' => 'Redirigiendo a Cardnet (Kiosco)...',
                'ncf_type' => '32', // B02 Consumidor Final por defecto en kiosco
            ]);

            $formInfo = $cardnetService->prepareFormData($payment->amount, $payment->id, request()->ip());
            
            // Emitimos evento para que Alpine/JS arme el form invisible de Cardnet y haga submit
            $this->dispatch('submit-cardnet-form', data: $formInfo);
            
        } catch (\Exception $e) {
            Log::error("Kiosk Cardnet Error: " . $e->getMessage());
            $this->dispatch('notify', message: 'Error de conexión con la pasarela de pago.', type: 'error');
        }
    }

    public function render()
    {
        return view('livewire.kiosk.finances');
    }
}
