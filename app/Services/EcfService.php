<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\NcfSequence;
use Illuminate\Support\Str;

class EcfService
{
    /**
     * Asigna un NCF y Código de Seguridad a un pago completado.
     */
    public function emitirComprobante(Payment $payment)
    {
        // 1. Determinar el tipo de comprobante necesario
        // Si el estudiante tiene RNC válido, es Crédito Fiscal (31), si no, Consumo (32).
        $tipo = $this->determinarTipoComprobante($payment->student);

        // 2. Buscar la secuencia activa
        $secuencia = NcfSequence::where('type_code', $tipo)
            ->where('is_active', true)
            ->whereDate('expiration_date', '>=', now())
            ->first();

        if (!$secuencia) {
            // Manejo de error si no hay secuencia disponible (podrías lanzar excepción o loguear)
            return; 
        }

        // 3. Generar el NCF
        $ncf = $secuencia->getNextNcf();

        if (!$ncf) {
            // Secuencia agotada
            return;
        }

        // 4. Generar Código de Seguridad (En producción real, esto viene de firmar el XML)
        // Simulamos 6 caracteres alfanuméricos como lo pide la DGII para el QR
        $securityCode = strtoupper(Str::random(6));

        // 5. Actualizar el pago
        $payment->update([
            'ncf' => $ncf,
            'ncf_type' => $tipo,
            'ncf_expiration' => $secuencia->expiration_date,
            'security_code' => $securityCode,
            'dgii_status' => 'generated' // Marcamos como generado internamente
        ]);
    }

    private function determinarTipoComprobante($student)
    {
        // Lógica: Si tiene RNC (generalmente 9 u 11 dígitos), es B31/E31
        if (!empty($student->rnc) && (strlen($student->rnc) == 9 || strlen($student->rnc) == 11)) {
            return '31'; // e-CF Crédito Fiscal
        }

        return '32'; // e-CF Consumo
    }
}