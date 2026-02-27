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

        if ($user) {
            $query = Payment::with('paymentConcept')
                ->where('status', 'Pendiente');
                
            if ($this->student) {
                $query->where(function ($q) use ($user) {
                    $q->where('student_id', $this->student->id)
                      ->orWhere('user_id', $user->id);
                });
            } else {
                $query->where('user_id', $user->id);
            }

            $this->pendingPayments = $query->orderBy('due_date', 'asc')->get();
            $this->totalDebt = $this->pendingPayments->sum('amount');
        }
    }

    public function goBack()
    {
        return $this->redirectRoute('kiosk.dashboard', navigate: true);
    }

    public function initiatePayment($paymentId, \App\Services\CardnetRedirectionService $cardnetService)
    {
        $user = Auth::user();
        if (!$user) return;

        $payment = Payment::find($paymentId);
        
        $isAuthorized = false;
        if ($payment) {
            // Safe check for the typed property
            $student = $user->student;
            
            if ($student && $payment->student_id === $student->id) {
                $isAuthorized = true;
            } elseif ($payment->user_id === $user->id) {
                $isAuthorized = true;
            }
        }

        if (!$payment || $payment->status === 'Completado' || !$isAuthorized) {
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

            $formInfo = $cardnetService->prepareFormData(
                $payment->amount, 
                $payment->id, 
                request()->ip(),
                'kiosk.cardnet.response', // Custom Return Route
                'kiosk.cardnet.cancel'    // Custom Cancel Route
            );
            
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
