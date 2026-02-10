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
        // --- DETECTIVE DE PAGOS NIVEL NUCLEAR ---
        
        // 1. Obtener traza completa SIN FILTROS para no perder nada
        // Aumentamos el límite y quitamos filtros estrictos para ver todo
        $rawStack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);
        
        $stack = collect($rawStack)->map(function ($trace) {
            return ($trace['file'] ?? '[internal]') . ':' . ($trace['line'] ?? '?');
        })->values();

        // Buscamos el primer archivo de nuestra app que no sea este observer
        $appSource = $stack->first(function ($line) {
            return str_contains($line, 'app/') && !str_contains($line, 'PaymentObserver');
        });

        // 2. Recopilar contexto del entorno EXTREMO
        $context = [
            'Payment_ID' => $payment->id,
            'Amount' => $payment->amount,
            'Concept_ID' => $payment->payment_concept_id,
            'Student_ID' => $payment->student_id,
            'Enrollment_ID' => $payment->enrollment_id,
            'User_ID_Auth' => auth()->id() ?? 'Guest',
            'Running_In_Console' => app()->runningInConsole(),
            'Request_URL' => app()->runningInConsole() ? 'CLI Command' : request()->fullUrl(),
            'Request_Method' => app()->runningInConsole() ? 'N/A' : request()->method(),
            'Request_IP' => app()->runningInConsole() ? 'Local' : request()->ip(),
            'Request_Params' => app()->runningInConsole() ? [] : request()->all(),
            'Creado_Por_Archivo_App' => $appSource ?? 'Fuera de app/ (Vendor/Framework)',
        ];

        // 3. Log Estándar (siempre visible)
        Log::info("💰 PAGO CREADO (ID: {$payment->id}) | Monto: {$payment->amount}", $context);

        // 4. ALERTA NUCLEAR PARA MONTOS > 1500 (o el monto sospechoso)
        if ($payment->amount >= 1500) {
            Log::emergency("🚨🚨🚨 ¡¡¡PAGO FANTASMA DETECTADO (ID: {$payment->id}) DE {$payment->amount}!!! 🚨🚨🚨");
            Log::emergency("================================================================");
            Log::emergency("🔍 CULPABLE (APP): " . ($appSource ?? 'NO ENCONTRADO EN APP/'));
            Log::emergency("👤 USUARIO: " . $context['User_ID_Auth']);
            Log::emergency("🌍 URL: " . $context['Request_URL']);
            Log::emergency("📂 PARÁMETROS REQUEST: " . json_encode($context['Request_Params']));
            Log::emergency("📜 TRAZA COMPLETA (PRIMEROS 20):");
            foreach ($stack->take(20) as $index => $line) {
                Log::emergency("   #{$index}: {$line}");
            }
            Log::emergency("================================================================");
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