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
        // --- DETECTIVE DE PAGOS NIVEL AGRESIVO ---
        
        // 1. Obtener traza limpia de la aplicación
        $stack = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 30))->map(function ($trace) {
            return ($trace['file'] ?? '') . ':' . ($trace['line'] ?? '');
        })->filter(function ($line) {
            return str_contains($line, 'app/') && !str_contains($line, 'PaymentObserver');
        })->values();

        // 2. Recopilar contexto del entorno (¿Fue por web? ¿Fue por comando?)
        $context = [
            'Payment_ID' => $payment->id,
            'Amount' => $payment->amount,
            'Concept_ID' => $payment->payment_concept_id,
            'Student_ID' => $payment->student_id,
            'Enrollment_ID' => $payment->enrollment_id,
            'Running_In_Console' => app()->runningInConsole(), // TRUE si es Cron Job/Artisan
            'Request_URL' => app()->runningInConsole() ? 'CLI Command' : request()->fullUrl(),
            'Request_Params' => app()->runningInConsole() ? [] : request()->all(), // Ver qué datos envió el navegador
            'Creado_Por_Archivo' => $stack->first(),
        ];

        // 3. Log Estándar
        Log::info("💰 PAGO REGISTRADO (ID: {$payment->id}) | Monto: {$payment->amount}", $context);

        // 4. ALERTA NUCLEAR: Si el monto es sospechoso (>= 1500)
        if ($payment->amount >= 1500) {
            Log::emergency("🚨🚨🚨 ¡¡¡PAGO FANTASMA DETECTADO (ID: {$payment->id}) DE {$payment->amount}!!! 🚨🚨🚨");
            Log::emergency("----------------------------------------------------------------");
            Log::emergency("🔍 CULPABLE INMEDIATO: " . $stack->first());
            Log::emergency("🌍 ORIGEN: " . ($context['Running_In_Console'] ? "Consola/Cron" : "Petición Web: " . $context['Request_URL']));
            Log::emergency("📂 DATOS REQUEST: " . json_encode($context['Request_Params']));
            Log::emergency("📜 TRAZA DETALLADA DE LA CREACIÓN:");
            Log::emergency($stack->implode("\n <--- "));
            Log::emergency("----------------------------------------------------------------");
        }
    }

    /**
     * Se ejecuta al crear o actualizar un pago.
     */
    public function saved(Payment $payment): void
    {
        $this->invalidateFinanceCache();

        // Lógica de activación de matrícula
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