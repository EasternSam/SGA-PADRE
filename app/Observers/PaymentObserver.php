<?php

namespace App\Observers;

use App\Models\Payment;
use Illuminate\Support\Facades\Cache;

class PaymentObserver
{
    /**
     * Se ejecuta al crear o actualizar un pago.
     */
    public function saved(Payment $payment): void
    {
        $this->invalidateFinanceCache();
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