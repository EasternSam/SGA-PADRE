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
        // --- DETECTIVE DE PAGOS ---
        // Esto dejará un rastro en laravel.log indicando exactamente qué archivo/línea
        // creó CADA pago. Así descubriremos de dónde viene el de RD$2,000.
        
        // Filtramos para obtener un stack trace limpio solo de tu aplicación (app/)
        $stack = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20))->map(function ($trace) {
            return ($trace['file'] ?? '') . ':' . ($trace['line'] ?? '');
        })->filter(function ($line) {
            return str_contains($line, 'app/') && !str_contains($line, 'PaymentObserver');
        })->values();

        Log::info("💰 PAGO CREADO (ID: {$payment->id}) | Monto: {$payment->amount} | Concepto ID: {$payment->payment_concept_id}", [
            'Origen' => $stack->first(), // El archivo inmediato que lo creó
            'Traza_Completa' => $stack->take(5) // Contexto adicional
        ]);
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