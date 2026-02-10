<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\MatriculaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentObserver
{
    /**
     * Se ejecuta al crear o actualizar un pago.
     */
    public function saved(Payment $payment): void
    {
        $this->invalidateFinanceCache();

        // CORRECCIÓN: Detectar si el pago se ha completado (ya sea nuevo o actualizado)
        // Esto dispara la lógica de generación de matrícula e inscripción automáticamente.
        if ($payment->status === 'Completado' && ($payment->wasRecentlyCreated || $payment->wasChanged('status'))) {
            
            Log::info("PaymentObserver: Pago {$payment->id} pasó a estado 'Completado'. Invocando MatriculaService::generarMatricula.");
            
            try {
                // Invocamos el método PRINCIPAL que orquesta todo el flujo:
                // 1. Generar matrícula (si no tiene)
                // 2. Crear usuario (si no tiene)
                // 3. Activar inscripción
                // 4. Sincronizar con Moodle
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
     * Esto obliga a todos los dashboards a recargar la data fresca inmediatamente.
     */
    private function invalidateFinanceCache(): void
    {
        // Guardamos el timestamp actual como versión.
        // Al cambiar este número, todas las llaves de caché que lo usan quedan obsoletas.
        Cache::put('finance_data_version', now()->timestamp);
    }
}