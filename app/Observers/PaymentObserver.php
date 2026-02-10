<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\MatriculaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Se ejecuta cuando se CREA un registro por primera vez.
     * Útil para detectar quién está insertando pagos en la base de datos.
     */
    public function created(Payment $payment): void
    {
        // --- DETECTIVE DE PAGOS MEJORADO ---
        // Capturamos el stack trace completo para ver de dónde viene CUALQUIER pago.
        
        $stack = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 25))->map(function ($trace) {
            return ($trace['file'] ?? '') . ':' . ($trace['line'] ?? '');
        })->filter(function ($line) {
            return str_contains($line, 'app/') && !str_contains($line, 'PaymentObserver');
        })->values();

        // Log general para todos los pagos
        Log::info("💰 PAGO CREADO (ID: {$payment->id}) | Monto: {$payment->amount} | Concepto: {$payment->payment_concept_id}", [
            'Origen' => $stack->first(),
        ]);

        // ALERTA ROJA: Si el monto es sospechoso (ej: 2000 o diferente de la inscripción esperada de 1300)
        // Ajusta la condición si quieres ser más específico, aquí pongo > 1500 como ejemplo
        if ($payment->amount >= 1500) {
            Log::critical("🚨 PAGO FANTASMA DETECTADO (ID: {$payment->id}) DE {$payment->amount}! 🚨", [
                'Student_ID' => $payment->student_id,
                'Enrollment_ID' => $payment->enrollment_id,
                'Creado_Por' => $stack->first(),
                'Traza_Completa' => $stack->take(10)->toArray()
            ]);
        }
    }

    /**
     * Se ejecuta al crear o actualizar un pago.
     */
    public function saved(Payment $payment): void
    {
        $this->invalidateFinanceCache();

        // Lógica de activación de matrícula (Mantenemos tu corrección anterior)
        if ($payment->status === 'Completado' && ($payment->wasRecentlyCreated || $payment->wasChanged('status'))) {
            
            Log::info("PaymentObserver: Pago {$payment->id} pasó a estado 'Completado'. Invocando MatriculaService::generarMatricula.");
            
            try {
                app(MatriculaService::class)->generarMatricula($payment);
            } catch (\Exception $e) {
                Log::error("PaymentObserver: Error crítico invocando MatriculaService: " . $e->getMessage());
            }
        }
    }

    /**
     * Se ejecuta al eliminar un pago.
     */
    public function deleted(Payment $payment): void
    {
        $this->invalidateFinanceCache();
    }

    /**
     * Cambia la "versión" de los datos financieros.
     */
    private function invalidateFinanceCache(): void
    {
        Cache::put('finance_data_version', now()->timestamp);
    }
}